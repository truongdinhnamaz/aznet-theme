import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.F8_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.F8_STATE_DIR || '/tmp/f8-native';
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');

fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

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
      className: element.className,
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

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const result = {
    viewport: name,
    width: viewport.width,
    height: viewport.height,
    status: 'failed',
    mainCount: null,
    duplicateIds: [],
    overflowPx: null,
    focus: null,
    blockingAxeViolations: null,
    consoleErrors,
    pageErrors,
    error: null,
  };

  try {
    const response = await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`homepage HTTP status was ${response?.status() ?? 'missing'}`);

    await page.locator('.aznet-theme-site-header').waitFor({ state: 'visible', timeout: 15000 });
    await page.locator('main#main.aznet-theme-main--front-page').waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-site-footer').waitFor({ state: 'visible' });
    await page.getByText('F8 native homepage sentinel', { exact: false }).first().waitFor({ state: 'visible' });

    result.mainCount = await page.locator('main#main').count();
    if (result.mainCount !== 1) throw new Error(`expected one main#main, got ${result.mainCount}`);

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
    if (!result.focus?.visible || !result.focus.hasIndicator || !String(result.focus.className).includes('aznet-theme-skip-link') || result.focus.href !== '#main') {
      throw new Error(`first keyboard focus is not the visible skip link: ${JSON.stringify(result.focus)}`);
    }

    if (viewport.width <= 390) {
      const mobileSummary = page.locator('.aznet-theme-site-header__mobile > summary');
      await mobileSummary.waitFor({ state: 'visible' });
      await mobileSummary.click();
      const mobilePanel = page.locator('.aznet-theme-site-header__mobile-panel');
      await mobilePanel.waitFor({ state: 'visible' });
      const openOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (openOverflow > 1) throw new Error(`mobile menu caused horizontal overflow: ${openOverflow}px`);
    }

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `homepage-${name}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) {
      throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);
    }

    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `homepage-${name}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: f8-native-homepage-${name}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${name}: ${result.error}`);
    console.error(`FAIL: f8-native-homepage-${name}: ${result.error}`);
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

if (summary.cases.length !== 3) failures.push(`expected 3 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`F8 native Homepage L4 verification failed:\n${failures.join('\n')}`);

console.log('PASS: F8 native Homepage browser/visual/a11y automated verification 3/3');
