import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.LAW01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.LAW01_STATE_DIR || '/tmp/homepage-law01';
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const requiredSelectors = [
  '.aznet-theme-law01-hero',
  '.aznet-theme-law01-services',
  '.aznet-theme-law01-editorial',
  '.aznet-theme-law01-team',
  '.aznet-theme-law01-articles',
];

const forbiddenBurgundySelectors = [
  '.aznet-theme-law01-topics',
  '.aznet-theme-law01-analysis',
  '.aznet-theme-law01-news',
  '.aznet-theme-law01-process',
  '.aznet-theme-law01-faq',
  '.aznet-theme-law01-final-cta',
];

const summary = { baseUrl, cases: [] };
const failures = [];

async function visibleKeyboardFocus(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  await page.keyboard.press('Tab');
  return page.evaluate(() => {
    const element = document.activeElement;
    if (!(element instanceof HTMLElement)) return null;
    const rect = element.getBoundingClientRect();
    const style = window.getComputedStyle(element);
    const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
    const hasOutline = style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent';
    const hasShadow = Boolean(style.boxShadow && style.boxShadow !== 'none');
    return {
      tag: element.tagName.toLowerCase(),
      className: String(element.className),
      href: element.getAttribute('href'),
      visible: rect.width > 1 && rect.height > 1,
      hasIndicator: Boolean(hasOutline || hasShadow),
    };
  });
}

async function inspectViewport(browser, name, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const requestFailures = [];

  page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('requestfailed', (request) => requestFailures.push(`${request.method()} ${request.url()} ${request.failure()?.errorText || ''}`));

  const result = {
    viewport: name,
    status: 'failed',
    mainCount: null,
    h1Count: null,
    serviceCards: null,
    teamCards: null,
    heroImages: null,
    teamImages: null,
    articleImages: null,
    footerColumns: null,
    utilityLinks: null,
    articleLinks: null,
    overflowPx: null,
    focus: null,
    blockingAxeViolations: null,
    consoleErrors,
    pageErrors,
    requestFailures,
    error: null,
  };

  try {
    const response = await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`homepage HTTP status was ${response?.status() ?? 'missing'}`);

    await page.locator('.aznet-theme-site-header').waitFor({ state: 'visible', timeout: 15000 });
    await page.locator('main#main.aznet-theme-main--front-page').waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-homepage--law-01-burgundy-gold').waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-site-footer').waitFor({ state: 'visible' });

    const lawCss = await page.locator('link[href*="/assets/css/components/homepage-law-01.css"]').count();
    if (lawCss !== 1) throw new Error(`expected exactly one Law 01 stylesheet, got ${lawCss}`);
    const variantCss = await page.locator('link[href*="/assets/css/components/homepage-law-01-variants.css"]').count();
    if (variantCss !== 1) throw new Error(`expected exactly one Law 01 variant stylesheet, got ${variantCss}`);
    if (await page.locator('.aznet-theme-site-header--law01-burgundy-gold').count() !== 1) throw new Error('Burgundy Law01 header class missing');

    result.mainCount = await page.locator('main#main').count();
    if (result.mainCount !== 1) throw new Error(`expected one main#main, got ${result.mainCount}`);

    result.h1Count = await page.locator('.aznet-theme-homepage--law-01 h1').count();
    if (result.h1Count !== 1) throw new Error(`expected one Law 01 H1, got ${result.h1Count}`);

    const heroTitle = (await page.locator('.aznet-theme-law01-hero h1').textContent())?.trim() || '';
    if (heroTitle !== 'AZnet Law 01') throw new Error(`Hero H1 must use WordPress site title, got: ${heroTitle}`);
    const heroValue = (await page.locator('.aznet-theme-law01-hero__value').textContent())?.trim() || '';
    if (!heroValue.includes('Bảo vệ quyền lợi của bạn bằng giải pháp pháp lý rõ ràng')) throw new Error(`Front Page title value proposition missing: ${heroValue}`);
    const heroLede = (await page.locator('.aznet-theme-law01-hero .aznet-theme-law01-lede').textContent())?.trim() || '';
    if (!heroLede.includes('Dịch vụ pháp lý chuyên nghiệp')) throw new Error(`Front Page excerpt support copy missing: ${heroLede}`);

    if (await page.locator('#law01-native-body').count() !== 1) throw new Error('native Front Page the_content() sentinel missing');

    for (const selector of requiredSelectors) {
      if (await page.locator(selector).count() < 1) throw new Error(`missing Law 01 section ${selector}`);
    }
    for (const selector of forbiddenBurgundySelectors) {
      if (await page.locator(selector).count() > 0) throw new Error(`Burgundy visual closure rendered excluded section ${selector}`);
    }

    const slogan = (await page.locator('.aznet-theme-law01-hero__slogan').textContent())?.trim() || '';
    if (!slogan.includes('Tận tâm với khách hàng')) throw new Error(`Site Tagline slogan was not projected into Hero: ${slogan}`);

    if (viewport.width > 960) {
      const headingMetrics = await page.locator('.aznet-theme-law01-section h2').evaluateAll((nodes) => nodes.map((node) => {
        const rect = node.getBoundingClientRect();
        const style = window.getComputedStyle(node);
        return {
          text: node.textContent?.trim() || '',
          height: rect.height,
          lineHeight: Number.parseFloat(style.lineHeight || '0'),
          fontSize: Number.parseFloat(style.fontSize || '0'),
          whiteSpace: style.whiteSpace,
        };
      }));
      if (headingMetrics.length < 4) throw new Error(`expected current Burgundy Law 01 heading coverage, got ${headingMetrics.length}`);
      const headingSizes = new Set(headingMetrics.map((item) => item.fontSize.toFixed(2)));
      const invalidHeading = headingMetrics.find((item) => item.whiteSpace !== 'nowrap' || item.height > item.lineHeight * 1.25);
      if (invalidHeading) throw new Error(`Law 01 section heading must stay on one desktop line: ${JSON.stringify(invalidHeading)}`);
      if (headingSizes.size !== 1) throw new Error(`Law 01 section headings must use one consistent size: ${JSON.stringify(headingMetrics)}`);
    }

    const servicesIntro = (await page.locator('.aznet-theme-law01-services__intro').textContent())?.trim() || '';
    if (!servicesIntro.includes('cá nhân và doanh nghiệp')) throw new Error(`Services Page excerpt intro missing: ${servicesIntro}`);

    result.serviceCards = await page.locator('.aznet-theme-law01-services .aznet-theme-law01-card').count();
    if (result.serviceCards !== 6) throw new Error(`expected 6 mapped service cards, got ${result.serviceCards}`);

    result.teamCards = await page.locator('.aznet-theme-law01-team-card').count();
    if (result.teamCards !== 2) throw new Error(`expected 2 WordPress-owned team child Page cards, got ${result.teamCards}`);

    result.heroImages = await page.locator('.aznet-theme-law01-hero__media img').count();
    if (result.heroImages !== 1) throw new Error(`visual QA fixture must provide 1 Hero featured image, got ${result.heroImages}`);
    result.teamImages = await page.locator('.aznet-theme-law01-team-card__media img').count();
    if (result.teamImages !== 2) throw new Error(`visual QA fixture must provide 2 Team featured images, got ${result.teamImages}`);
    result.articleImages = await page.locator('.aznet-theme-law01-article-card__media img').count();
    if (result.articleImages !== 3) throw new Error(`visual QA fixture must provide 3 Latest Posts featured images, got ${result.articleImages}`);

    if (await page.locator('.aznet-theme-site-footer--professional').count() !== 1) throw new Error('visual QA fixture must exercise the professional Footer preset');
    for (const selector of ['.aznet-theme-site-footer__navigation', '.aznet-theme-site-footer__contact', '.aznet-theme-site-footer__social-column']) {
      if (await page.locator(selector).count() !== 1) throw new Error(`visual QA fixture missing populated Footer column ${selector}`);
    }
    result.footerColumns = 4;

    result.utilityLinks = await page.locator('.aznet-theme-site-header__utility-menu a').count();
    if (result.utilityLinks < 2) throw new Error(`expected header phone/hotline utility links, got ${result.utilityLinks}`);
    const utilityHrefs = await page.locator('.aznet-theme-site-header__utility-menu a').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('href')));
    if (utilityHrefs.some((href) => !href?.startsWith('tel:'))) throw new Error(`header utility links must remain WordPress-owned tel: links: ${JSON.stringify(utilityHrefs)}`);

    const emptyCards = await page.locator('.aznet-theme-law01-card:empty, .aznet-theme-law01-article-card:empty').count();
    if (emptyCards > 0) throw new Error(`empty public cards rendered: ${emptyCards}`);

    const editorialHrefs = await page.evaluate(() => [...document.querySelectorAll('.aznet-theme-law01-articles article a')].map((node) => node.href));
    result.articleLinks = editorialHrefs.length;
    if (editorialHrefs.length !== 3) throw new Error(`expected 3 Latest Posts links in Burgundy demo closure, got ${editorialHrefs.length}`);
    if (new Set(editorialHrefs).size !== editorialHrefs.length) throw new Error('request-local display ledger did not prevent repeated editorial links');

    const contactHref = await page.locator('.aznet-theme-law01-hero .aznet-theme-law01-button').getAttribute('href');
    if (!contactHref || !contactHref.includes('/lien-he/')) throw new Error(`Hero contact CTA is not mapped to Contact Page: ${contactHref}`);

    result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflowPx > 1) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    result.focus = await visibleKeyboardFocus(page);
    if (!result.focus?.visible || !result.focus.hasIndicator || !result.focus.className.includes('aznet-theme-skip-link') || result.focus.href !== '#main') {
      throw new Error(`first keyboard focus is not the visible skip link: ${JSON.stringify(result.focus)}`);
    }

    if (viewport.width <= 390) {
      const trigger = page.locator('[data-aznet-theme-nav-trigger]');
      const panel = page.locator('[data-aznet-theme-nav-panel]');
      await trigger.waitFor({ state: 'visible' });
      await trigger.focus();
      await page.keyboard.press('Enter');
      await panel.waitFor({ state: 'visible' });
      if (await trigger.getAttribute('aria-expanded') !== 'true') throw new Error('mobile nav did not expand');
      if (await panel.locator('.aznet-theme-site-header__utility-menu a').count() < 2) throw new Error('mobile utility links missing from expanded navigation');
      const openOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (openOverflow > 1) throw new Error(`mobile nav caused horizontal overflow: ${openOverflow}px`);
      await page.keyboard.press('Escape');
      await panel.waitFor({ state: 'hidden' });
      if (await trigger.getAttribute('aria-expanded') !== 'false') throw new Error('mobile nav did not collapse after Escape');
    }

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `law01-${name}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);

    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);
    if (requestFailures.length > 0) throw new Error(`request failures: ${requestFailures.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `law01-${name}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: homepage-law01-${name}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${name}: ${result.error}`);
    console.error(`FAIL: homepage-law01-${name}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `law01-${name}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [name, viewport] of Object.entries(viewports)) await inspectViewport(browser, name, viewport);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));
if (summary.cases.length !== 4) failures.push(`expected 4 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`Law 01 L4 verification failed:\n${failures.join('\n')}`);
console.log('PASS: Homepage Composer Law 01 Burgundy browser/visual/a11y automated verification 4/4');