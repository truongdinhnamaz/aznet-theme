import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import path from 'node:path';

const siteUrl = 'https://aznet.vn/';
const output = process.env.AZNET_BRAND_POSTDEPLOY_DIR || '/tmp/aznet-brand-postdeploy';
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ headless: true });
const viewports = [
  { name: 'desktop-1440', width: 1440, height: 900 },
  { name: 'mobile-390', width: 390, height: 844 },
];
const outcomes = [];
let failure = false;
for (const view of viewports) {
  const context = await browser.newContext({
    viewport: { width: view.width, height: view.height },
    deviceScaleFactor: 1,
    locale: 'vi-VN',
  });
  const page = await context.newPage();
  const jsErrors = [];
  const assetErrors = [];
  page.on('pageerror', err => jsErrors.push(String(err)));
  page.on('response', res => {
    if (res.status() >= 400 &&
      /\/wp-content\/(?:uploads\/|themes\/aznet-theme\/)/.test(res.url())) {
      assetErrors.push({ status: res.status(), url: res.url() });
    }
  });
  const problems = [];
  try {
    let loaded = false;
    for (let attempt = 1; attempt <= 3 && !loaded; attempt++) {
      try {
        const response = await page.goto(siteUrl, {
          waitUntil: 'domcontentloaded', timeout: 90000,
        });
        if (!response || response.status() !== 200) {
          throw new Error('homepage HTTP ' + (response?.status() ?? 'null'));
        }
        await page.locator('main .aznet-digital01--brand-2026').waitFor({
          state: 'attached', timeout: 20000,
        });
        loaded = true;
      } catch (error) {
        if (attempt === 3) throw error;
        await page.waitForTimeout(2500);
      }
    }
    await page.waitForLoadState('networkidle', { timeout: 25000 }).catch(() => {});
    // WordPress normally lazy-loads the real Query Loop images. Scroll them
    // into the viewport and verify the actual public Media bytes decode.
    const images = page.locator('.aznet-digital01--brand-2026 img');
    for (const img of await images.all()) {
      await img.scrollIntoViewIfNeeded();
      await img.evaluate(async el => {
        try { await el.decode(); } catch { /* record naturalWidth below */ }
      });
    }
    const faq = page.locator('.aznet-digital01--brand-2026 .aznet-digital01__faq details').first();
    const hasFaq = await faq.count() > 0;
    const faqKeyboard = { opened: false, closed: false };
    if (hasFaq) {
      const trigger = faq.locator('summary');
      await trigger.focus();
      await page.keyboard.press('Enter');
      faqKeyboard.opened = await faq.evaluate(el => el.open);
      await page.keyboard.press('Enter');
      faqKeyboard.closed = await faq.evaluate(el => !el.open);
    }
    const observed = await page.evaluate(() => {
      const sel = '.aznet-digital01--brand-2026';
      const root = document.querySelector(sel);
      const sections = [
        'hero','offer-paths','services','brand-why','process',
        'about','products','articles','faq','cta'
      ];
      const imgItems = [...document.querySelectorAll(sel + ' img')];
      const queryImages = [...document.querySelectorAll(
        sel + ' .aznet-digital01__articles .wp-block-post-featured-image img'
      )];
      const anchors = [...document.querySelectorAll(sel + ' a')];
      return {
        title: document.title,
        bodyClass: document.body.className,
        rootPresent: !!root,
        mainCount: document.querySelectorAll('main').length,
        headerCount: document.querySelectorAll('header.aznet-theme-site-header').length,
        footerCount: document.querySelectorAll('footer.aznet-theme-site-footer').length,
        h1Text: [...document.querySelectorAll(sel + ' h1')].map(el => el.textContent.trim()),
        pageId: !!document.querySelector('#post-43515'),
        sections: Object.fromEntries(sections.map(k => [
          k, !!document.querySelector(sel + ' .aznet-digital01__' + k)
        ])),
        offerCards: document.querySelectorAll(sel + ' .aznet-digital01__offer-card').length,
        serviceCards: document.querySelectorAll(sel + ' .aznet-digital01__service').length,
        processSteps: document.querySelectorAll(sel + ' .aznet-digital01__step').length,
        productCards: document.querySelectorAll(sel + ' .aznet-digital01__product').length,
        queryArticles: document.querySelectorAll(sel + ' .aznet-digital01__articles .wp-block-post').length,
        faqCount: document.querySelectorAll(sel + ' .aznet-digital01__faq details').length,
        ctaLinks: document.querySelectorAll(sel + ' .aznet-digital01__cta a[href]').length,
        images: imgItems.map(img => ({
          src: img.currentSrc || img.src,
          alt: img.alt,
          decoded: img.complete && img.naturalWidth > 0,
          naturalWidth: img.naturalWidth,
        })),
        queryImages: queryImages.map(img => ({
          src: img.currentSrc || img.src,
          decoded: img.complete && img.naturalWidth > 0,
        })),
        anchorCount: anchors.length,
        emptyAnchors: anchors.filter(a => !a.getAttribute('href') ||
          a.getAttribute('href') === '#' ||
          /^javascript:/i.test(a.getAttribute('href'))).length,
        cssActive: !!document.querySelector(
          'link[href*="homepage-digital-01-brand-2026.css?ver=1.3.97"]'
        ),
        cssBaseActive: !!document.querySelector(
          'link[href*="homepage-digital-01.css?ver=1.3.97"]'
        ),
        debugStyles: {
          brandLinkTags: [...document.querySelectorAll('link')].filter(el =>
            (el.href || '').includes('digital-01') || (el.href || '').includes('cache/')
          ).map(el => ({ href: el.href, rel: el.rel, media: el.media, disabled: el.disabled })),
          stylesheets: [...document.styleSheets].map(sheet => sheet.href).filter(Boolean).filter(
            href => href.includes('digital-01') || href.includes('cache/')
          ),
          processLink: (() => {
            const link = document.querySelector('.aznet-digital01__process .aznet-digital01__text-link a');
            if (!link) return null;
            const style = getComputedStyle(link);
            const parent = getComputedStyle(link.parentElement);
            return {
              html: link.outerHTML.slice(0, 400), color: style.color, fontSize: style.fontSize,
              weight: style.fontWeight, parentColor: parent.color,
              directChild: link.matches('.aznet-digital01__process .aznet-digital01__text-link > a'),
              ancestors: [link.parentElement?.className, link.parentElement?.parentElement?.className],
            };
          })(),
        },
        canonical: document.querySelector('link[rel="canonical"]')?.href || '',
        robots: document.querySelector('meta[name="robots"]')?.content || '',
        viewportWidth: window.innerWidth,
        scrollWidth: document.documentElement.scrollWidth,
        totalHeight: document.documentElement.scrollHeight,
      };
    });
    if (!observed.rootPresent || !observed.pageId) problems.push('public homepage is not Brand Light Page #43515');
    if (observed.mainCount !== 1 || observed.h1Text.length !== 1 ||
        observed.h1Text[0] !== 'Xây dựng website tăng trưởng thực sự') {
      problems.push('main or Hero H1 invalid');
    }
    if (observed.headerCount !== 1 || observed.footerCount !== 1) problems.push('site shell missing');
    if (!Object.values(observed.sections).every(Boolean)) problems.push('some native Brand Light sections missing');
    if (observed.offerCards !== 3 || observed.serviceCards !== 4 ||
        observed.processSteps !== 5 || observed.productCards !== 2 ||
        observed.queryArticles !== 3 || observed.faqCount !== 5 ||
        observed.ctaLinks !== 2) problems.push('expected WordPress section item counts mismatch');
    if (observed.images.length < 6 || observed.images.some(x => !x.decoded) ||
        observed.queryImages.length !== 3 ||
        observed.queryImages.some(x => !x.decoded)) problems.push('real WordPress Media failed to decode');
    if (observed.emptyAnchors) problems.push('empty or placeholder CTA links');
    if (!observed.cssActive || !observed.cssBaseActive) problems.push('Brand Light CSS unavailable');
    if (observed.scrollWidth > observed.viewportWidth + 2) problems.push('horizontal overflow');
    if (!faqKeyboard.opened || !faqKeyboard.closed) problems.push('FAQ Enter keyboard interaction failure');
    if (!observed.canonical.startsWith('https://aznet.vn')) problems.push('unexpected canonical URL');
    if (observed.robots.toLowerCase().includes('noindex')) problems.push('homepage marked noindex');
    let axe = { violations: [], incomplete: [] };
    try {
      const report = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa'])
        .analyze();
      axe = {
        violations: report.violations.map(x => ({
          id: x.id, impact: x.impact, description: x.description,
          nodes: x.nodes.slice(0, 4).map(n => ({ target: n.target, failureSummary: n.failureSummary }))
        })),
        incomplete: report.incomplete.map(x => ({ id: x.id, impact: x.impact })),
      };
    } catch (error) {
      problems.push('axe audit failed: ' + String(error));
    }
    const severe = axe.violations.filter(v => ['critical','serious'].includes(v.impact));
    if (severe.length) problems.push('serious/critical axe findings: ' + severe.map(v => v.id).join(', '));
    if (jsErrors.length) problems.push('runtime JavaScript pageerror');
    if (assetErrors.length) problems.push('theme or Media HTTP >=400');
    await page.evaluate(() => {
      if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
      window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    });
    await page.waitForFunction(() => window.scrollY === 0, null, { timeout: 6000 });
    await page.waitForTimeout(250);
    await page.screenshot({
      path: path.join(output, 'aznet-live-brand2026-' + view.name + '.png'),
      fullPage: true, animations: 'disabled',
    });
    outcomes.push({ viewport: view, observed, faqKeyboard, axe, jsErrors, assetErrors, problems });
    console.log('AZNET_PUBLIC_POSTDEPLOY ' + JSON.stringify({ viewport: view, observed, faqKeyboard, violations: axe.violations, jsErrors, assetErrors, problems }));
    if (problems.length) failure = true;
  } catch (error) {
    failure = true;
    const failureDetails = String(error);
    outcomes.push({ viewport: view, problems: [failureDetails], jsErrors, assetErrors });
    console.error('AZNET_PUBLIC_POSTDEPLOY_ERROR ' + view.name + ' ' + failureDetails);
    await page.screenshot({
      path: path.join(output, 'aznet-live-brand2026-error-' + view.name + '.png'),
      fullPage: true,
    }).catch(() => {});
  }
  await context.close();
}
fs.writeFileSync(path.join(output,'postdeploy-summary.json'), JSON.stringify({
  siteUrl, provenance: 'public WordPress production on 2026-10-26, no login or mutation',
  outcomes,
}, null, 2));
await browser.close();
if (failure) {
  console.error('FAIL: public WordPress Brand Light postdeploy L4; inspect artifact');
  process.exit(1);
}
console.log('PASS: public WordPress Brand Light postdeploy desktop/mobile + keyboard + Media decode + axe');
