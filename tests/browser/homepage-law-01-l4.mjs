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
  '1920x1080': { width: 1920, height: 1080 },
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
  '.aznet-theme-law01-process',
  '.aznet-theme-law01-faq',
  '.aznet-theme-law01-articles',
  '.aznet-theme-law01-final-cta',
];

const forbiddenBurgundySelectors = [
  '.aznet-theme-law01-topics',
  '.aznet-theme-law01-analysis',
  '.aznet-theme-law01-news',
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
    if (heroTitle !== 'Văn phòng luật sư kiểm soát độc lập') throw new Error(`Hero H1 must come from the dedicated Hero Page, got: ${heroTitle}`);
    const heroValue = (await page.locator('.aznet-theme-law01-hero__value').textContent())?.trim() || '';
    if (!heroValue.includes('Bảo vệ quyền lợi của bạn bằng giải pháp pháp lý rõ ràng')) throw new Error(`Dedicated Hero Page excerpt missing: ${heroValue}`);
    const heroBody = (await page.locator('.aznet-theme-law01-hero__body').textContent())?.trim() || '';
    if (!heroBody.includes('Trọn tâm với khách')) throw new Error(`Dedicated Hero Page body missing: ${heroBody}`);
    if (heroTitle === 'AZnet Law 01' || heroTitle === 'Trang chủ nội dung') throw new Error('Hero H1 leaked Site Title or Front Page title dependency');

    if (await page.locator('#law01-native-body').count() !== 1) throw new Error('native Front Page the_content() sentinel missing');

    for (const selector of requiredSelectors) {
      if (await page.locator(selector).count() < 1) throw new Error(`missing Law 01 section ${selector}`);
    }
    for (const selector of forbiddenBurgundySelectors) {
      if (await page.locator(selector).count() > 0) throw new Error(`Burgundy Homepage rendered non-approved section ${selector}`);
    }

    const compositionOrder = [
      '.aznet-theme-law01-hero',
      '.aznet-theme-law01-hero__trust-grid',
      '.aznet-theme-law01-services',
      '.aznet-theme-law01-profile',
      '.aznet-theme-law01-process',
      '.aznet-theme-law01-faq',
      '.aznet-theme-law01-articles',
      '.aznet-theme-law01-final-cta',
      '.aznet-theme-site-footer',
    ];
    const compositionTops = [];
    for (const selector of compositionOrder) {
      const box = await page.locator(selector).first().boundingBox();
      if (!box) throw new Error(`Burgundy composition order probe missing ${selector}`);
      compositionTops.push({ selector, y: box.y });
    }
    for (let index = 1; index < compositionTops.length; index += 1) {
      if (compositionTops[index].y <= compositionTops[index - 1].y) {
        throw new Error(`Burgundy composition order mismatch: ${JSON.stringify(compositionTops)}`);
      }
    }

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
    if (await page.locator('.aznet-theme-law01-services__intro').isVisible()) throw new Error('reference Services excerpt must be visually omitted while remaining source-backed in markup');

    const heroContactLinks = page.locator('.aznet-theme-law01-hero__contact-nav a');
    if (await heroContactLinks.count() !== 2) throw new Error(`reference Hero must render 2 WordPress-owned phone/hotline links, got ${await heroContactLinks.count()}`);
    const heroContactHrefs = await heroContactLinks.evaluateAll((nodes) => nodes.map((node) => node.getAttribute('href')));
    if (heroContactHrefs.some((href) => !href?.startsWith('tel:'))) throw new Error(`Hero contact links must remain WordPress-owned tel: links: ${JSON.stringify(heroContactHrefs)}`);

    const heroBodyParagraphs = page.locator('.aznet-theme-law01-hero__body > p');
    if (await heroBodyParagraphs.count() !== 2) throw new Error('reference Hero fixture must exercise description + slogan body presentation');
    const heroBodyStyles = await heroBodyParagraphs.evaluateAll((nodes) => nodes.map((node) => getComputedStyle(node).fontStyle));
    if (heroBodyStyles[0] === 'italic' || heroBodyStyles[1] !== 'italic') throw new Error(`Hero body must present description normally and final slogan in italic: ${JSON.stringify(heroBodyStyles)}`);

    if (
      await page.locator('.aznet-theme-site-header__logo').count() !== 1 ||
      await page.locator('.aznet-theme-site-header__brand-title').count() !== 1 ||
      await page.locator('.aznet-theme-site-header__brand-kicker').count() !== 1
    ) {
      throw new Error('reference Header must render logo + WordPress site-title + Law01 presentation kicker');
    }
    if (await page.locator('.aznet-theme-site-footer__logo').count() !== 1 || await page.locator('.aznet-theme-site-footer__brand-title').count() !== 1) {
      throw new Error('reference Footer must render logo + WordPress site-title lockup');
    }

    result.serviceCards = await page.locator('.aznet-theme-law01-services .aznet-theme-law01-card').count();
    if (result.serviceCards !== 6) throw new Error(`expected 6 mapped service cards, got ${result.serviceCards}`);

    const parityMetrics = await page.evaluate(() => {
      const heroSection = document.querySelector('.aznet-theme-law01-hero')?.getBoundingClientRect();
      const heroGrid = document.querySelector('.aznet-theme-law01-hero__grid')?.getBoundingClientRect();
      const trustGrid = document.querySelector('.aznet-theme-law01-hero__trust-grid')?.getBoundingClientRect();
      const servicesContainer = document.querySelector('.aznet-theme-law01-services > .aznet-theme-law01-container')?.getBoundingClientRect();
      const profileContainer = document.querySelector('.aznet-theme-law01-profile > .aznet-theme-law01-container')?.getBoundingClientRect();
      const articlesContainer = document.querySelector('.aznet-theme-law01-articles > .aznet-theme-law01-container')?.getBoundingClientRect();
      const nativeEntry = document.querySelector('.aznet-theme-homepage--law-01-burgundy-gold > .aznet-theme-entry--front-page')?.getBoundingClientRect();
      const processContainer = document.querySelector('.aznet-theme-law01-process > .aznet-theme-law01-container')?.getBoundingClientRect();
      const faqContainer = document.querySelector('.aznet-theme-law01-faq > .aznet-theme-law01-container')?.getBoundingClientRect();
      const finalCtaContainer = document.querySelector('.aznet-theme-law01-final-cta > .aznet-theme-law01-container')?.getBoundingClientRect();
      const processCopy = document.querySelector('.aznet-theme-law01-process__copy')?.getBoundingClientRect();
      const processBody = document.querySelector('.aznet-theme-law01-process__body')?.getBoundingClientRect();
      const faqCopy = document.querySelector('.aznet-theme-law01-faq__copy')?.getBoundingClientRect();
      const faqAction = document.querySelector('.aznet-theme-law01-faq__action')?.getBoundingClientRect();
      const finalCtaCopy = document.querySelector('.aznet-theme-law01-final-cta__copy')?.getBoundingClientRect();
      const finalCtaActions = document.querySelector('.aznet-theme-law01-final-cta__actions')?.getBoundingClientRect();
      const headerInner = document.querySelector('.aznet-theme-site-header__inner')?.getBoundingClientRect();
      const footerInner = document.querySelector('.aznet-theme-site-footer__inner')?.getBoundingClientRect();
      const content = document.querySelector('.aznet-theme-law01-hero__content')?.getBoundingClientRect();
      const visual = document.querySelector('.aznet-theme-law01-hero__visual')?.getBoundingClientRect();
      const trust = [...document.querySelectorAll('.aznet-theme-law01-hero__trust-item')].map((node) => node.getBoundingClientRect());
      const services = [...document.querySelectorAll('.aznet-theme-law01-services .aznet-theme-law01-card')].map((node) => node.getBoundingClientRect());
      const aboutCopy = document.querySelector('.aznet-theme-law01-profile__about-copy')?.getBoundingClientRect();
      const aboutMedia = document.querySelector('.aznet-theme-law01-profile__about-media')?.getBoundingClientRect();
      const teamBand = document.querySelector('.aznet-theme-law01-profile__team-band')?.getBoundingClientRect();
      const articles = [...document.querySelectorAll('.aznet-theme-law01-articles .aznet-theme-law01-article-card')].map((node) => node.getBoundingClientRect());
      const heroVisualNode = document.querySelector('.aznet-theme-law01-hero__visual');
      const heroBlend = heroVisualNode ? getComputedStyle(heroVisualNode, '::before') : null;
      const heroTitleNode = document.querySelector('.aznet-theme-law01-hero h1');
      const heroTitleStyle = heroTitleNode ? getComputedStyle(heroTitleNode) : null;
      return {
        heroSection: heroSection ? { x: heroSection.x, y: heroSection.y, width: heroSection.width, height: heroSection.height } : null,
        heroGrid: heroGrid ? { x: heroGrid.x, y: heroGrid.y, width: heroGrid.width, height: heroGrid.height } : null,
        trustGrid: trustGrid ? { x: trustGrid.x, y: trustGrid.y, width: trustGrid.width, height: trustGrid.height } : null,
        servicesContainer: servicesContainer ? { x: servicesContainer.x, y: servicesContainer.y, width: servicesContainer.width, height: servicesContainer.height } : null,
        profileContainer: profileContainer ? { x: profileContainer.x, y: profileContainer.y, width: profileContainer.width, height: profileContainer.height } : null,
        articlesContainer: articlesContainer ? { x: articlesContainer.x, y: articlesContainer.y, width: articlesContainer.width, height: articlesContainer.height } : null,
        nativeEntry: nativeEntry ? { x: nativeEntry.x, y: nativeEntry.y, width: nativeEntry.width, height: nativeEntry.height } : null,
        processContainer: processContainer ? { x: processContainer.x, y: processContainer.y, width: processContainer.width, height: processContainer.height } : null,
        faqContainer: faqContainer ? { x: faqContainer.x, y: faqContainer.y, width: faqContainer.width, height: faqContainer.height } : null,
        finalCtaContainer: finalCtaContainer ? { x: finalCtaContainer.x, y: finalCtaContainer.y, width: finalCtaContainer.width, height: finalCtaContainer.height } : null,
        processCopy: processCopy ? { x: processCopy.x, y: processCopy.y, width: processCopy.width, height: processCopy.height } : null,
        processBody: processBody ? { x: processBody.x, y: processBody.y, width: processBody.width, height: processBody.height } : null,
        faqCopy: faqCopy ? { x: faqCopy.x, y: faqCopy.y, width: faqCopy.width, height: faqCopy.height } : null,
        faqAction: faqAction ? { x: faqAction.x, y: faqAction.y, width: faqAction.width, height: faqAction.height } : null,
        finalCtaCopy: finalCtaCopy ? { x: finalCtaCopy.x, y: finalCtaCopy.y, width: finalCtaCopy.width, height: finalCtaCopy.height } : null,
        finalCtaActions: finalCtaActions ? { x: finalCtaActions.x, y: finalCtaActions.y, width: finalCtaActions.width, height: finalCtaActions.height } : null,
        headerInner: headerInner ? { x: headerInner.x, y: headerInner.y, width: headerInner.width, height: headerInner.height } : null,
        footerInner: footerInner ? { x: footerInner.x, y: footerInner.y, width: footerInner.width, height: footerInner.height } : null,
        content: content ? { x: content.x, y: content.y, width: content.width, height: content.height } : null,
        visual: visual ? { x: visual.x, y: visual.y, width: visual.width, height: visual.height } : null,
        trust: trust.map((rect) => ({ x: rect.x, y: rect.y, width: rect.width, height: rect.height })),
        services: services.map((rect) => ({ x: rect.x, y: rect.y, width: rect.width, height: rect.height })),
        aboutCopy: aboutCopy ? { x: aboutCopy.x, y: aboutCopy.y, width: aboutCopy.width, height: aboutCopy.height } : null,
        aboutMedia: aboutMedia ? { x: aboutMedia.x, y: aboutMedia.y, width: aboutMedia.width, height: aboutMedia.height } : null,
        teamBand: teamBand ? { x: teamBand.x, y: teamBand.y, width: teamBand.width, height: teamBand.height } : null,
        articles: articles.map((rect) => ({ x: rect.x, y: rect.y, width: rect.width, height: rect.height })),
        heroBlend: heroBlend ? {
          content: heroBlend.content,
          backgroundImage: heroBlend.backgroundImage,
          pointerEvents: heroBlend.pointerEvents,
          width: heroBlend.width,
        } : null,
        heroTitleStyle: heroTitleStyle ? {
          lineHeight: heroTitleStyle.lineHeight,
          fontSize: heroTitleStyle.fontSize,
          letterSpacing: heroTitleStyle.letterSpacing,
        } : null,
      };
    });

    if (!parityMetrics.content || !parityMetrics.visual) throw new Error('Hero visual-parity metrics unavailable');
    if (viewport.width >= 1024) {
      if (!parityMetrics.heroSection || !parityMetrics.heroGrid || !parityMetrics.trustGrid || !parityMetrics.servicesContainer || !parityMetrics.profileContainer || !parityMetrics.articlesContainer || !parityMetrics.nativeEntry || !parityMetrics.processContainer || !parityMetrics.faqContainer || !parityMetrics.finalCtaContainer) {
        throw new Error('Law 01 reference shell geometry unavailable');
      }
      if (Math.abs(parityMetrics.heroSection.x) > 1 || Math.abs(parityMetrics.heroSection.width - viewport.width) > 2) {
        throw new Error(`desktop Hero section must span the viewport, got x=${parityMetrics.heroSection.x.toFixed(1)} width=${parityMetrics.heroSection.width.toFixed(1)} viewport=${viewport.width}`);
      }
      for (const [label, rect] of [
        ['Trust', parityMetrics.trustGrid],
        ['Services', parityMetrics.servicesContainer],
        ['Profile', parityMetrics.profileContainer],
        ['Native content', parityMetrics.nativeEntry],
        ['Process', parityMetrics.processContainer],
        ['FAQ', parityMetrics.faqContainer],
        ['Articles', parityMetrics.articlesContainer],
        ['Final CTA', parityMetrics.finalCtaContainer],
      ]) {
        if (Math.abs(parityMetrics.heroGrid.x - rect.x) > 2 || Math.abs(parityMetrics.heroGrid.width - rect.width) > 2) {
          throw new Error(`desktop Law 01 sections must share one vertical shell: Hero x=${parityMetrics.heroGrid.x.toFixed(1)} width=${parityMetrics.heroGrid.width.toFixed(1)}; ${label} x=${rect.x.toFixed(1)} width=${rect.width.toFixed(1)}`);
        }
      }
      if (parityMetrics.headerInner && (Math.abs(parityMetrics.heroGrid.x - parityMetrics.headerInner.x) > 2 || Math.abs(parityMetrics.heroGrid.width - parityMetrics.headerInner.width) > 2)) {
        throw new Error('desktop Header and Law 01 content must align to the same reference shell');
      }
      if (parityMetrics.footerInner && (Math.abs(parityMetrics.heroGrid.x - parityMetrics.footerInner.x) > 2 || Math.abs(parityMetrics.heroGrid.width - parityMetrics.footerInner.width) > 2)) {
        throw new Error('desktop Footer and Law 01 content must align to the same reference shell');
      }
      const contentRatio = parityMetrics.content.width / parityMetrics.heroGrid.width;
      if (contentRatio < 0.52 || contentRatio > 0.56) throw new Error(`desktop Hero content column must stay near approved 54% shell target, got ${contentRatio.toFixed(3)}`);
      if (Math.abs(parityMetrics.content.y - parityMetrics.visual.y) > 2) throw new Error('desktop Hero columns must align on one row');
      const visualRight = parityMetrics.visual.x + parityMetrics.visual.width;
      if (Math.abs(visualRight - viewport.width) > 2) {
        throw new Error(`desktop Hero image surface must blend to the viewport edge without a right gutter, got right=${visualRight.toFixed(1)} viewport=${viewport.width}`);
      }
      if (!parityMetrics.heroBlend || parityMetrics.heroBlend.content === 'none' || !parityMetrics.heroBlend.backgroundImage.includes('linear-gradient')) {
        throw new Error('desktop Hero image must render the decorative edge-blend transition');
      }
      if (parityMetrics.heroBlend.pointerEvents !== 'none') throw new Error('Hero edge-blend transition must remain non-interactive');
      if (!parityMetrics.heroTitleStyle) throw new Error('Hero title computed style unavailable');
      const heroTitleFont = Number.parseFloat(parityMetrics.heroTitleStyle.fontSize);
      const heroTitleLine = Number.parseFloat(parityMetrics.heroTitleStyle.lineHeight);
      if (!(heroTitleLine / heroTitleFont < 1.05)) throw new Error(`Hero title line-height must stay editorially compact, got ${parityMetrics.heroTitleStyle.lineHeight} / ${parityMetrics.heroTitleStyle.fontSize}`);
      if (parityMetrics.trust.length !== 4 || new Set(parityMetrics.trust.map((item) => Math.round(item.y))).size !== 1) {
        throw new Error('desktop trust strip must remain one four-item row');
      }
      const serviceRows = new Set(parityMetrics.services.map((item) => Math.round(item.y))).size;
      if (viewport.width > 1180) {
        if (parityMetrics.services.length !== 6 || serviceRows !== 1) {
          throw new Error('wide desktop Services must remain one six-card row');
        }
      } else if (parityMetrics.services.length !== 6 || serviceRows !== 2) {
        throw new Error('compact desktop Services must retain the responsive two-row 3+3 layout');
      }
      if (!parityMetrics.aboutCopy || !parityMetrics.teamBand) throw new Error('Profile visual-parity metrics unavailable');
      if (Math.abs(parityMetrics.aboutCopy.y - parityMetrics.teamBand.y) > 2) throw new Error('desktop About and Team must share one horizontal reference band');
      const profilePairWidth = parityMetrics.aboutCopy.width + parityMetrics.teamBand.width;
      const teamRatio = parityMetrics.teamBand.width / profilePairWidth;
      if (teamRatio < 0.50 || teamRatio > 0.54) throw new Error(`desktop Team width must stay near the reference 52% target, got ${teamRatio.toFixed(3)}`);
      if (parityMetrics.aboutMedia && parityMetrics.aboutMedia.width > 1) throw new Error('desktop reference About column must remain text-led without a second large media panel');
      if (parityMetrics.articles.length !== 3 || new Set(parityMetrics.articles.map((item) => Math.round(item.y))).size !== 1) {
        throw new Error('desktop Latest Posts must remain one three-card row');
      }
      if (!parityMetrics.processCopy || !parityMetrics.processBody || parityMetrics.processBody.x <= parityMetrics.processCopy.x) {
        throw new Error('desktop Process must present source copy and handoff in one horizontal reference band');
      }
      if (!parityMetrics.faqCopy || !parityMetrics.faqAction || parityMetrics.faqAction.x <= parityMetrics.faqCopy.x) {
        throw new Error('desktop FAQ must keep the source-backed CTA beside the FAQ copy');
      }
      if (!parityMetrics.finalCtaCopy || !parityMetrics.finalCtaActions || parityMetrics.finalCtaActions.x <= parityMetrics.finalCtaCopy.x) {
        throw new Error('desktop final CTA must keep contact actions beside the contact copy');
      }
    }
    if (viewport.width <= 390) {
      if (parityMetrics.visual.y <= parityMetrics.content.y) throw new Error('mobile Hero visual must stack after Hero content');
      const mobileVisualRight = parityMetrics.visual.x + parityMetrics.visual.width;
      if (Math.abs(parityMetrics.visual.x) > 2 || Math.abs(mobileVisualRight - viewport.width) > 2) {
        throw new Error(`mobile Hero image surface must bleed naturally to both viewport edges, got x=${parityMetrics.visual.x.toFixed(1)} right=${mobileVisualRight.toFixed(1)} viewport=${viewport.width}`);
      }
      if (new Set(parityMetrics.trust.map((item) => Math.round(item.y))).size !== 4) throw new Error('mobile trust strip must stack to one item per row');
      if (new Set(parityMetrics.services.map((item) => Math.round(item.y))).size !== 6) throw new Error('mobile Services cards must stack to one card per row');
      if (!parityMetrics.aboutCopy || !parityMetrics.teamBand || parityMetrics.teamBand.y <= parityMetrics.aboutCopy.y) throw new Error('mobile Profile must stack Team after About');
      if (new Set(parityMetrics.articles.map((item) => Math.round(item.y))).size !== 3) throw new Error('mobile Latest Posts cards must stack to one card per row');
      if (!parityMetrics.processCopy || !parityMetrics.processBody || parityMetrics.processBody.y <= parityMetrics.processCopy.y) throw new Error('mobile Process handoff must stack after the Process heading');
      if (!parityMetrics.faqCopy || !parityMetrics.faqAction || parityMetrics.faqAction.y <= parityMetrics.faqCopy.y) throw new Error('mobile FAQ CTA must stack after FAQ copy');
      if (!parityMetrics.finalCtaCopy || !parityMetrics.finalCtaActions || parityMetrics.finalCtaActions.y <= parityMetrics.finalCtaCopy.y) throw new Error('mobile final CTA actions must stack after CTA copy');
    }

    result.teamCards = await page.locator('.aznet-theme-law01-team-card').count();
    if (result.teamCards !== 4) throw new Error(`expected 4 WordPress-owned team child Page cards for the reference portrait row, got ${result.teamCards}`);

    result.heroImages = await page.locator('.aznet-theme-law01-hero__media img').count();
    if (result.heroImages !== 1) throw new Error(`visual QA fixture must provide 1 Hero featured image, got ${result.heroImages}`);
    result.teamImages = await page.locator('.aznet-theme-law01-team-card__media img').count();
    if (result.teamImages !== 4) throw new Error(`visual QA fixture must provide 4 Team featured images, got ${result.teamImages}`);
    result.articleImages = await page.locator('.aznet-theme-law01-article-card__media img').count();
    if (result.articleImages !== 3) throw new Error(`visual QA fixture must provide 3 Latest Posts featured images, got ${result.articleImages}`);

    if (await page.locator('.aznet-theme-site-footer--professional').count() !== 1) throw new Error('visual QA fixture must exercise the professional Footer preset');
    for (const selector of ['.aznet-theme-site-footer__navigation', '.aznet-theme-site-footer__contact', '.aznet-theme-site-footer__social-column']) {
      if (await page.locator(selector).count() !== 1) throw new Error(`visual QA fixture missing populated Footer column ${selector}`);
    }
    result.footerColumns = 4;

    result.consultationLinks = await page.locator('.aznet-theme-site-header > .aznet-theme-site-header__inner > .aznet-theme-site-header__actions .aznet-theme-site-header__consultation').count();
    if (result.consultationLinks !== 1) throw new Error(`expected one source-backed Header consultation action, got ${result.consultationLinks}`);
    const consultationHref = await page.locator('.aznet-theme-site-header > .aznet-theme-site-header__inner > .aznet-theme-site-header__actions .aznet-theme-site-header__consultation').getAttribute('href');
    if (!consultationHref || !consultationHref.includes('/lien-he/')) throw new Error(`Header consultation action is not mapped to Contact Page: ${consultationHref}`);
    if (await page.locator('.aznet-theme-site-header > .aznet-theme-site-header__inner > .aznet-theme-site-header__actions .aznet-theme-site-header__utility-menu a').count() !== 0) {
      throw new Error('Law01 desktop Header must not duplicate Hero phone/hotline utility links');
    }

    const emptyCards = await page.locator('.aznet-theme-law01-card:empty, .aznet-theme-law01-article-card:empty').count();
    if (emptyCards > 0) throw new Error(`empty public cards rendered: ${emptyCards}`);

    const editorialCards = page.locator('.aznet-theme-law01-articles article');
    if (await editorialCards.count() !== 3) throw new Error(`expected 3 Latest Posts cards in Burgundy demo closure, got ${await editorialCards.count()}`);
    const editorialHrefs = [];
    for (let index = 0; index < 3; index += 1) {
      const card = editorialCards.nth(index);
      const mediaHref = await card.locator('.aznet-theme-law01-article-card__media-link').getAttribute('href');
      const titleHref = await card.locator('h3 a').getAttribute('href');
      if (!mediaHref || mediaHref !== titleHref) throw new Error(`Latest Post card ${index + 1} media/title links must target the same WordPress Post`);
      editorialHrefs.push(titleHref);
    }
    result.articleLinks = editorialHrefs.length;
    if (new Set(editorialHrefs).size !== editorialHrefs.length) throw new Error('request-local display ledger did not prevent repeated editorial Posts');

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
      if (await panel.locator('.aznet-theme-site-header__consultation').count() !== 1) throw new Error('mobile source-backed consultation action missing from expanded navigation');
      if (await panel.locator('.aznet-theme-site-header__utility-menu a').count() !== 0) throw new Error('mobile Law01 Header must not duplicate Hero phone/hotline utility links');
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
if (summary.cases.length !== 5) failures.push(`expected 5 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`Law 01 L4 verification failed:\n${failures.join('\n')}`);
console.log('PASS: Homepage Composer Law 01 Burgundy browser/visual/a11y automated verification 5/5');