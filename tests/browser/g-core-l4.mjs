import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.G_CORE_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.G_CORE_STATE_DIR || '/tmp/g5-core';
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

const routes = {
  home: { path: '/', status: 200, sentinel: 'G5 front sentinel' },
  page: { path: '/native-page/', status: 200, sentinel: 'G5 page sentinel' },
  post: { path: '/core-sentinel-post/', status: 200, sentinel: 'G5 post core-sentinel content' },
  archive: { path: '/category/core-test/', status: 200, sentinel: 'Core Sentinel Post' },
  search: { path: '/?s=core-sentinel', status: 200, sentinel: 'Core Sentinel Post' },
  notfound: { path: '/definitely-missing/', status: 404, sentinel: '' },
};

const summary = { baseUrl, cases: [] };
const failures = [];

async function visibleFirstKeyboardFocus(page) {
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
      className: String(element.className || ''),
      href: element.getAttribute('href'),
      visible: rect.width > 1 && rect.height > 1,
      hasIndicator: Boolean(hasOutline || hasShadow),
    };
  });
}

async function keyboardReachPrimaryNav(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  for (let i = 0; i < 12; i += 1) {
    await page.keyboard.press('Tab');
    const inPrimaryNav = await page.evaluate(() => Boolean(document.activeElement?.closest?.('.aznet-theme-site-header__nav')));
    if (inPrimaryNav) return true;
  }
  return false;
}

async function inspectCase(browser, routeName, route, viewportName, viewport) {
  const context = await browser.newContext({
    viewport,
    reducedMotion: viewport.width <= 390 ? 'reduce' : 'no-preference',
  });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const key = `${routeName}-${viewportName}`;
  const result = {
    route: routeName,
    viewport: viewportName,
    expectedStatus: route.status,
    actualStatus: null,
    mainCount: null,
    duplicateIds: [],
    overflowPx: null,
    firstFocus: null,
    primaryNavKeyboardReachable: null,
    reducedMotion: null,
    blockingAxeViolations: null,
    stylesheets: [],
    consoleErrors,
    pageErrors,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(`${baseUrl}${route.path}`, { waitUntil: 'networkidle' });
    result.actualStatus = response?.status() ?? null;
    if (!response || response.status() !== route.status) {
      throw new Error(`expected HTTP ${route.status}, got ${response?.status() ?? 'missing'}`);
    }

    await page.locator('.aznet-theme-site-header').waitFor({ state: 'visible', timeout: 15000 });
    await page.locator('main#main').waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-site-footer').waitFor({ state: 'visible' });
    if (route.sentinel) {
      await page.getByText(route.sentinel, { exact: false }).first().waitFor({ state: 'visible' });
    }

    result.mainCount = await page.locator('main#main').count();
    if (result.mainCount !== 1) throw new Error(`expected exactly one main#main, got ${result.mainCount}`);

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

    result.firstFocus = await visibleFirstKeyboardFocus(page);
    if (!result.firstFocus?.visible || !result.firstFocus.hasIndicator || !result.firstFocus.className.includes('aznet-theme-skip-link') || result.firstFocus.href !== '#main') {
      throw new Error(`first keyboard focus is not the visible skip link: ${JSON.stringify(result.firstFocus)}`);
    }

    if (viewport.width > 390) {
      await page.locator('.aznet-theme-site-header__nav a').first().waitFor({ state: 'visible' });
      result.primaryNavKeyboardReachable = await keyboardReachPrimaryNav(page);
      if (!result.primaryNavKeyboardReachable) throw new Error('primary navigation was not reachable by Tab keyboard navigation');
    } else {
      const summaryControl = page.locator('.aznet-theme-site-header__mobile > summary');
      await summaryControl.waitFor({ state: 'visible' });
      await summaryControl.focus();
      await page.keyboard.press('Enter');
      await page.locator('.aznet-theme-site-header__mobile-panel').waitFor({ state: 'visible' });
      await page.locator('.aznet-theme-site-header__mobile-panel nav a').first().waitFor({ state: 'visible' });
      const openOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (openOverflow > 1) throw new Error(`mobile menu caused horizontal overflow: ${openOverflow}px`);
      result.primaryNavKeyboardReachable = true;
    }

    result.reducedMotion = await page.evaluate(() => window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    if (viewport.width <= 390 && !result.reducedMotion) throw new Error('reduced-motion preference was not active in the mobile test context');

    result.stylesheets = await page.locator('link[rel="stylesheet"]').evaluateAll((links) => links.map((link) => link.href));
    const wooStyles = result.stylesheets.filter((href) => /woocommerce-(product|archive|cart|checkout|account)\.css/i.test(href));
    if (wooStyles.length > 0) throw new Error(`Woo component styles loaded on WordPress-clean route: ${wooStyles.join(', ')}`);

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `${key}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) {
      throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);
    }

    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `${key}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: g5-${key}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${key}: ${result.error}`);
    console.error(`FAIL: g5-${key}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `${key}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const [routeName, route] of Object.entries(routes)) {
      await inspectCase(browser, routeName, route, viewportName, viewport);
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));

const expectedCases = Object.keys(viewports).length * Object.keys(routes).length;
if (summary.cases.length !== expectedCases) failures.push(`expected ${expectedCases} browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`G5 core L4 verification failed:\n${failures.join('\n')}`);

console.log(`PASS: G5 core browser/responsive/a11y/performance ${expectedCases}/${expectedCases}`);
