import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.X4_STATE_DIR || '/tmp/x4';
const outputDir = process.env.X4_L4_DIR || '/tmp/x4-l4';
const fixturePath = path.join(stateDir, 'fixture.json');
if (!fs.existsSync(fixturePath)) throw new Error(`X4 fixture is required: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const summary = { fixture, cases: [], controls: [] };
const failures = [];

function collectBrowserErrors(page) {
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  return { consoleErrors, pageErrors };
}

async function assertCountAtLeast(locator, minimum, label) {
  const count = await locator.count();
  if (count < minimum) throw new Error(`${label}: expected at least ${minimum}, got ${count}`);
}

async function assertVisibleFocus(page, selector) {
  const target = page.locator(selector).first();
  if (await target.count() !== 1) throw new Error(`focus target missing: ${selector}`);
  await target.focus();
  const state = await target.evaluate((element) => {
    const style = window.getComputedStyle(element);
    const rect = element.getBoundingClientRect();
    return {
      focusVisible: element.matches(':focus-visible'),
      outlineStyle: style.outlineStyle,
      outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      inViewport: rect.left >= -1 && rect.right <= window.innerWidth + 1,
    };
  });
  if (!state.focusVisible || state.outlineStyle === 'none' || state.outlineWidth < 1 || !state.inViewport) {
    throw new Error(`focus presentation failed for ${selector}: ${JSON.stringify(state)}`);
  }
}

async function assertPageQuality(page, caseName, viewportName) {
  const overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
  if (overflow > 1) throw new Error(`${caseName}/${viewportName}: horizontal overflow ${overflow}px`);
  const axe = await new AxeBuilder({ page }).analyze();
  const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
  fs.writeFileSync(path.join(outputDir, `axe-${caseName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
  if (blocking.length) throw new Error(`${caseName}/${viewportName}: blocking axe ${blocking.map((item) => item.id).join(', ')}`);
  return { overflow, blockingAxeViolations: blocking.length };
}

async function requireNavigationAsset(page) {
  if (await page.locator('#aznet-theme-navigation-css').count() !== 1) {
    throw new Error('X4 navigation stylesheet handle missing');
  }
}

async function runBehavior(browser, behavior, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const errors = collectBrowserErrors(page);
  const result = {
    behavior,
    viewport: viewportName,
    initialUrl: null,
    finalUrl: null,
    urlChanged: false,
    overflow: null,
    blockingAxeViolations: null,
    browserErrorCount: null,
    status: 'failed',
    error: null,
  };

  try {
    if (behavior === 'archive') {
      const response = await page.goto(fixture.archive_url, { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(`archive HTTP ${response?.status()}`);
      result.initialUrl = page.url();
      await requireNavigationAsset(page);
      const navigation = page.locator('.aznet-theme-listing__pagination .navigation.pagination[aria-label="Archive pagination"]');
      if (await navigation.count() !== 1) throw new Error('archive pagination landmark missing');
      await assertCountAtLeast(navigation.locator('.page-numbers'), 2, 'archive page numbers');
      await assertVisibleFocus(page, '.aznet-theme-listing__pagination a.page-numbers');
      const next = page.locator('.aznet-theme-listing__pagination a.next.page-numbers');
      if (await next.count() !== 1) throw new Error('archive native next link missing');
      await next.click();
      await page.waitForLoadState('networkidle');
      result.finalUrl = page.url();
      result.urlChanged = result.finalUrl !== result.initialUrl;
      if (!result.urlChanged) throw new Error('archive native pagination URL did not change');
      if (await page.locator('.aznet-theme-listing__pagination .page-numbers.current').count() !== 1) throw new Error('archive current-page state missing');
      await requireNavigationAsset(page);
    } else if (behavior === 'search') {
      const response = await page.goto(fixture.search_url, { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(`search HTTP ${response?.status()}`);
      result.initialUrl = page.url();
      await requireNavigationAsset(page);
      const navigation = page.locator('.aznet-theme-listing__pagination .navigation.pagination[aria-label="Search results pagination"]');
      if (await navigation.count() !== 1) throw new Error('search pagination landmark missing');
      await assertCountAtLeast(navigation.locator('.page-numbers'), 2, 'search page numbers');
      await assertVisibleFocus(page, '.aznet-theme-listing__pagination a.page-numbers');
      const next = page.locator('.aznet-theme-listing__pagination a.next.page-numbers');
      if (await next.count() !== 1) throw new Error('search native next link missing');
      await next.click();
      await page.waitForLoadState('networkidle');
      result.finalUrl = page.url();
      result.urlChanged = result.finalUrl !== result.initialUrl;
      if (!result.urlChanged) throw new Error('search native pagination URL did not change');
      if (await page.locator('.aznet-theme-listing__pagination .page-numbers.current').count() !== 1) throw new Error('search current-page state missing');
      await requireNavigationAsset(page);
    } else if (behavior === 'post-pages') {
      const response = await page.goto(fixture.post_url, { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(`post page-links HTTP ${response?.status()}`);
      result.initialUrl = page.url();
      await requireNavigationAsset(page);
      if (await page.locator('#x4-post-page-one').count() !== 1) throw new Error('native Post page-one marker missing');
      const navigation = page.locator('.aznet-theme-article__page-links[aria-label="Article pages"]');
      if (await navigation.count() !== 1) throw new Error('Post page-links landmark missing');
      await assertVisibleFocus(page, '.aznet-theme-article__page-links a.post-page-numbers');
      const pageTwo = page.locator('.aznet-theme-article__page-links a.post-page-numbers').filter({ hasText: /^2$/ });
      if (await pageTwo.count() !== 1) throw new Error('native Post page-two link missing');
      await pageTwo.click();
      await page.waitForLoadState('networkidle');
      result.finalUrl = page.url();
      result.urlChanged = result.finalUrl !== result.initialUrl;
      if (!result.urlChanged) throw new Error('native Post page URL did not change');
      if (await page.locator('#x4-post-page-two').count() !== 1) throw new Error('native Post page two marker missing');
      await requireNavigationAsset(page);
    } else if (behavior === 'adjacent-post') {
      const response = await page.goto(fixture.post_url, { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(`adjacent Post HTTP ${response?.status()}`);
      result.initialUrl = page.url();
      await requireNavigationAsset(page);
      const navigation = page.locator('.aznet-theme-article .post-navigation[aria-label="Post navigation"]');
      if (await navigation.count() !== 1) throw new Error('adjacent Post navigation landmark missing');
      await assertCountAtLeast(navigation.locator('a'), 2, 'adjacent Post links');
      const widths = await navigation.locator('.nav-title').evaluateAll((elements) => elements.map((element) => element.getBoundingClientRect().width));
      if (widths.length < 2 || widths.some((width) => width <= 1)) throw new Error(`adjacent Post titles collapsed: ${JSON.stringify(widths)}`);
      await assertVisibleFocus(page, '.aznet-theme-article .post-navigation a');
      const first = navigation.locator('a').first();
      await first.click();
      await page.waitForLoadState('networkidle');
      result.finalUrl = page.url();
      result.urlChanged = result.finalUrl !== result.initialUrl;
      if (!result.urlChanged) throw new Error('adjacent Post native URL did not change');
      await requireNavigationAsset(page);
    } else if (behavior === 'comments') {
      const response = await page.goto(fixture.post_url, { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(`comment pagination HTTP ${response?.status()}`);
      result.initialUrl = page.url();
      await requireNavigationAsset(page);
      const navigation = page.locator('.aznet-theme-comments .comments-pagination[aria-label="Comments pagination"]');
      if (await navigation.count() !== 1) throw new Error('comment pagination landmark missing');
      await assertCountAtLeast(navigation.locator('.page-numbers'), 2, 'comment page numbers');
      if (await navigation.locator('.page-numbers.current').count() !== 1) throw new Error('comment current-page state missing');
      await assertVisibleFocus(page, '.aznet-theme-comments .comments-pagination a.page-numbers');
      const before = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
      const link = navigation.locator('a.page-numbers').first();
      await link.click();
      await page.waitForLoadState('networkidle');
      result.finalUrl = page.url();
      result.urlChanged = result.finalUrl !== result.initialUrl;
      if (!result.urlChanged) throw new Error('native comment pagination URL did not change');
      const after = await page.locator('.aznet-theme-comments .comment-content').allTextContents();
      if (JSON.stringify(before) === JSON.stringify(after)) throw new Error('native comment pagination did not change comment page');
      await requireNavigationAsset(page);
    } else {
      throw new Error(`unknown X4 behavior: ${behavior}`);
    }

    const quality = await assertPageQuality(page, behavior, viewportName);
    result.overflow = quality.overflow;
    result.blockingAxeViolations = quality.blockingAxeViolations;
    result.browserErrorCount = errors.consoleErrors.length + errors.pageErrors.length;
    if (result.browserErrorCount) throw new Error(`unexpected browser errors: ${JSON.stringify(errors)}`);
    await page.screenshot({ path: path.join(outputDir, `${behavior}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${behavior}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `failure-${behavior}-${viewportName}.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

async function runAssetControls(browser) {
  const context = await browser.newContext({ viewport: viewports['1440x1000'] });
  const page = await context.newPage();
  const controls = [
    ['home', fixture.home_url, 200],
    ['page', fixture.page_url, 200],
    ['missing', fixture.missing_url, 404],
  ];
  try {
    for (const [name, url, expectedStatus] of controls) {
      const response = await page.goto(url, { waitUntil: 'networkidle' });
      const status = response?.status() ?? null;
      const assetCount = await page.locator('#aznet-theme-navigation-css').count();
      const record = { name, url: page.url(), status, navigationAssetCount: assetCount, result: 'failed' };
      if (status !== expectedStatus) {
        record.error = `expected HTTP ${expectedStatus}, got ${status}`;
        failures.push(`control/${name}: ${record.error}`);
      } else if (assetCount !== 0) {
        record.error = 'X4 navigation asset leaked onto control route';
        failures.push(`control/${name}: ${record.error}`);
      } else {
        record.result = 'passed';
      }
      summary.controls.push(record);
    }
  } finally {
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const behavior of ['archive', 'search', 'post-pages', 'adjacent-post', 'comments']) {
      await runBehavior(browser, behavior, viewportName, viewport);
    }
  }
  await runAssetControls(browser);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (summary.cases.length !== 20) {
  failures.push(`expected exactly 20 behavior/viewport cases, got ${summary.cases.length}`);
}
if (failures.length) {
  failures.forEach((failure) => console.error(`FAIL: ${failure}`));
  process.exit(1);
}
console.log(`PASS: X4 browser pagination/navigation matrix ${summary.cases.length}/${summary.cases.length}`);
