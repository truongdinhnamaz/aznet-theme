import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.P4_HOME_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.P4_HOME_STATE_DIR || '/tmp/p4-homepage-blueprint';
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
  '.aznet-theme-homepage-blueprint__hero',
  '.aznet-theme-homepage-blueprint__trust',
  '#services.aznet-theme-homepage-blueprint__services',
  '.aznet-theme-homepage-blueprint__about',
  '.aznet-theme-homepage-blueprint__process',
  '.aznet-theme-homepage-blueprint__articles',
  '.aznet-theme-homepage-blueprint__faq',
  '#contact.aznet-theme-homepage-blueprint__contact',
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

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('requestfailed', (request) => requestFailures.push(`${request.method()} ${request.url()} ${request.failure()?.errorText || ''}`));

  const result = {
    viewport: name,
    status: 'failed',
    mainCount: null,
    h1Count: null,
    articleCards: null,
    detailsCount: null,
    heroFlow: null,
    overflowPx: null,
    duplicateIds: [],
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
    await page.locator('.aznet-theme-homepage-blueprint').waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-site-footer').waitFor({ state: 'visible' });

    const homepageCss = await page.locator('link[href*="/assets/css/components/homepage.css"]').count();
    if (homepageCss < 1) throw new Error('Front Page did not load scoped homepage.css');

    result.mainCount = await page.locator('main#main').count();
    if (result.mainCount !== 1) throw new Error(`expected one main#main, got ${result.mainCount}`);

    result.h1Count = await page.locator('.aznet-theme-homepage-blueprint h1').count();
    if (result.h1Count !== 1) throw new Error(`expected one blueprint H1, got ${result.h1Count}`);

    for (const selector of requiredSelectors) {
      if (await page.locator(selector).count() < 1) throw new Error(`missing Homepage section ${selector}`);
    }

    result.heroFlow = await page.evaluate(() => {
      const hero = document.querySelector('.aznet-theme-homepage-blueprint__hero');
      const h1 = hero?.querySelector('h1');
      const lead = hero?.querySelector('.aznet-theme-homepage-blueprint__lead');
      const buttons = hero?.querySelector('.wp-block-buttons');
      if (!(h1 instanceof HTMLElement) || !(lead instanceof HTMLElement) || !(buttons instanceof HTMLElement)) return null;
      const h1Rect = h1.getBoundingClientRect();
      const leadRect = lead.getBoundingClientRect();
      const buttonsRect = buttons.getBoundingClientRect();
      const buttonWidths = [...buttons.querySelectorAll('.wp-block-button__link')].map((node) => node.getBoundingClientRect().width);
      return {
        h1Top: h1Rect.top,
        h1Bottom: h1Rect.bottom,
        leadTop: leadRect.top,
        leadBottom: leadRect.bottom,
        buttonsTop: buttonsRect.top,
        buttonWidths,
      };
    });
    if (!result.heroFlow) throw new Error('unable to inspect Homepage hero geometry');
    if (result.heroFlow.leadTop < result.heroFlow.h1Bottom - 1) {
      throw new Error(`Hero lead is not stacked below H1: ${JSON.stringify(result.heroFlow)}`);
    }
    if (result.heroFlow.buttonsTop < result.heroFlow.leadBottom - 1) {
      throw new Error(`Hero CTA group is not stacked below lead: ${JSON.stringify(result.heroFlow)}`);
    }
    const minButtonWidth = viewport.width <= 390 ? Math.min(220, viewport.width - 80) : 80;
    if (result.heroFlow.buttonWidths.some((width) => width < minButtonWidth)) {
      throw new Error(`Hero CTA is visually squeezed below ${minButtonWidth}px: ${JSON.stringify(result.heroFlow)}`);
    }

    result.articleCards = await page.locator('.aznet-theme-homepage-blueprint__article-card').count();
    if (result.articleCards < 1) throw new Error('native Query block rendered no Homepage article cards');

    result.detailsCount = await page.locator('.aznet-theme-homepage-blueprint__faq details').count();
    if (result.detailsCount < 3) throw new Error(`expected at least 3 FAQ details blocks, got ${result.detailsCount}`);

    result.duplicateIds = await page.evaluate(() => {
      const seen = new Set();
      const duplicates = new Set();
      for (const node of document.querySelectorAll('[id]')) {
        if (!node.id) continue;
        if (seen.has(node.id)) duplicates.add(node.id);
        seen.add(node.id);
      }
      return [...duplicates].sort();
    });
    if (result.duplicateIds.length > 0) throw new Error(`duplicate IDs: ${result.duplicateIds.join(', ')}`);

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
      const openOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (openOverflow > 1) throw new Error(`mobile nav caused horizontal overflow: ${openOverflow}px`);
      await page.keyboard.press('Escape');
      await panel.waitFor({ state: 'hidden' });
      if (await trigger.getAttribute('aria-expanded') !== 'false') throw new Error('mobile nav did not collapse after Escape');
    }

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `homepage-${name}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);

    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);
    if (requestFailures.length > 0) throw new Error(`request failures: ${requestFailures.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `homepage-${name}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: p4-homepage-blueprint-${name}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${name}: ${result.error}`);
    console.error(`FAIL: p4-homepage-blueprint-${name}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `homepage-${name}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [name, viewport] of Object.entries(viewports)) {
    await inspectViewport(browser, name, viewport);
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));
if (summary.cases.length !== 4) failures.push(`expected 4 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`P4 Homepage blueprint L4 verification failed:\n${failures.join('\n')}`);
console.log('PASS: P4 Homepage blueprint browser/visual/a11y automated verification 4/4');
