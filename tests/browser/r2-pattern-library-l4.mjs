import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.R2_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputRoot = process.env.R2_STATE_DIR || '/tmp/r2-pattern-library';
const preset = process.env.R2_PRESET || 'default';
const fixtureId = process.env.R2_FIXTURE_ID || '';
const wooEnabled = process.env.R2_WOO_ENABLED === '1';
const outputDir = path.join(outputRoot, `${preset}-${wooEnabled ? 'woo' : 'clean'}`);
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

if (!fixtureId) throw new Error('R2_FIXTURE_ID is required');

const expectedVariables = {
  default: {
    '--aznet-theme-container-content': '45rem',
    '--aznet-theme-line-height-body': '1.6',
    '--aznet-theme-space-section': '3.25rem',
  },
  editorial: {
    '--aznet-theme-container-content': '42rem',
    '--aznet-theme-line-height-body': '1.7',
    '--aznet-theme-space-section': '3.75rem',
  },
};

if (!Object.hasOwn(expectedVariables, preset)) {
  throw new Error(`Unsupported R2 preset ${preset}`);
}

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

const requiredCoreSelectors = [
  '.aznet-theme-pattern--hero-split',
  '.aznet-theme-pattern--trust-feature-grid',
  '.aznet-theme-pattern--content-article-grid',
  '.aznet-theme-pattern--content-faq',
  '.aznet-theme-pattern--utility-contact',
];

const summary = { preset, wooEnabled, baseUrl, frontend: [], editor: null };
const failures = [];

function normalize(value) {
  return String(value || '').trim().toLowerCase().replace(/\s+/g, ' ');
}

async function cssVariables(locator, names) {
  return locator.evaluate((element, requestedNames) => {
    const style = getComputedStyle(element);
    return Object.fromEntries(requestedNames.map((name) => [name, style.getPropertyValue(name).trim()]));
  }, names);
}

function verifyVariables(actual, expected, label) {
  for (const [name, expectedValue] of Object.entries(expected)) {
    if (normalize(actual[name]) !== normalize(expectedValue)) {
      throw new Error(`${label} ${name}: expected ${expectedValue}, got ${actual[name] || '<empty>'}`);
    }
  }
}

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
    const style = getComputedStyle(element);
    const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
    const hasOutline = style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent';
    const hasShadow = Boolean(style.boxShadow && style.boxShadow !== 'none');
    return {
      className: String(element.className || ''),
      href: element.getAttribute('href'),
      visible: rect.width > 1 && rect.height > 1,
      hasIndicator: Boolean(hasOutline || hasShadow),
    };
  });
}

async function inspectFrontend(browser, viewportName, viewport) {
  const context = await browser.newContext({
    viewport,
    reducedMotion: viewport.width <= 390 ? 'reduce' : 'no-preference',
  });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const failedSubresources = [];

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() >= 400 && response.request().resourceType() !== 'document') {
      failedSubresources.push(`${response.status()} ${response.request().resourceType()} ${response.url()}`);
    }
  });

  const result = {
    viewport: viewportName,
    status: 'failed',
    variables: {},
    duplicateIds: [],
    overflowPx: null,
    firstFocus: null,
    blockingAxeViolations: null,
    error: null,
  };

  try {
    const response = await page.goto(`${baseUrl}/r2-pattern-fixture/`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    await page.locator('main#main').waitFor({ state: 'visible' });
    for (const selector of requiredCoreSelectors) {
      await page.locator(selector).first().waitFor({ state: 'visible', timeout: 15000 });
    }

    const body = page.locator('body');
    const bodyClass = String((await body.getAttribute('class')) || '');
    if (!bodyClass.split(/\s+/).includes(`aznet-theme-preset--${preset}`)) {
      throw new Error(`missing body preset class aznet-theme-preset--${preset}`);
    }

    result.variables = await cssVariables(body, Object.keys(expectedVariables[preset]));
    verifyVariables(result.variables, expectedVariables[preset], `frontend ${viewportName}`);

    if (await page.locator('main#main').count() !== 1) throw new Error('expected exactly one main#main');

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
    if (result.overflowPx > 1) throw new Error(`horizontal overflow ${result.overflowPx}px`);

    const image = page.locator('img[alt="R2 media fixture"]').first();
    await image.waitFor({ state: 'visible', timeout: 15000 });
    const imageMetrics = await image.evaluate((node) => ({
      naturalWidth: node.naturalWidth,
      naturalHeight: node.naturalHeight,
      width: node.getBoundingClientRect().width,
      viewportWidth: document.documentElement.clientWidth,
    }));
    if (imageMetrics.naturalWidth <= 0 || imageMetrics.naturalHeight <= 0) throw new Error('R2 media fixture failed to load');
    if (imageMetrics.width > imageMetrics.viewportWidth + 1) throw new Error(`R2 media exceeds viewport: ${imageMetrics.width}px`);

    const articleCards = await page.locator('.aznet-theme-pattern__article-card').count();
    if (articleCards < 1) throw new Error('native Query block did not render any article cards');
    const details = await page.locator('.aznet-theme-pattern--content-faq details').count();
    if (details < 3) throw new Error(`FAQ pattern expected at least 3 details blocks, got ${details}`);

    if (wooEnabled) {
      await page.locator('.aznet-theme-pattern--commerce-featured-products').first().waitFor({ state: 'visible', timeout: 20000 });
      await page.locator('.aznet-theme-pattern--commerce-category-grid').first().waitFor({ state: 'visible', timeout: 20000 });
    } else {
      if (await page.locator('.aznet-theme-pattern--commerce-featured-products, .aznet-theme-pattern--commerce-category-grid').count()) {
        throw new Error('Woo-dependent patterns rendered in Woo-absent fixture');
      }
    }

    result.firstFocus = await visibleFirstKeyboardFocus(page);
    if (!result.firstFocus?.visible || !result.firstFocus.hasIndicator || !result.firstFocus.className.includes('aznet-theme-skip-link') || result.firstFocus.href !== '#main') {
      throw new Error(`first keyboard focus is not the visible Theme skip link: ${JSON.stringify(result.firstFocus)}`);
    }

    if (viewport.width <= 390) {
      const motion = await page.evaluate(() => ({
        preference: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
        fast: getComputedStyle(document.body).getPropertyValue('--aznet-theme-motion-fast').trim(),
        base: getComputedStyle(document.body).getPropertyValue('--aznet-theme-motion-base').trim(),
      }));
      if (!motion.preference || motion.fast !== '0ms' || motion.base !== '0ms') {
        throw new Error(`reduced-motion tokens not collapsed: ${JSON.stringify(motion)}`);
      }
    }

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `frontend-${viewportName}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);

    if (failedSubresources.length > 0) throw new Error(`failed subresources: ${failedSubresources.join(' | ')}`);
    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R2 ${preset} ${wooEnabled ? 'Woo' : 'clean'} frontend ${viewportName}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`frontend-${viewportName}: ${result.error}`);
    console.error(`FAIL: R2 ${preset} frontend ${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.frontend.push(result);
    await context.close();
  }
}

async function inspectEditor(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  const result = { status: 'failed', variables: {}, canvas: null, patternCount: null, error: null };

  try {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('r2-browser-password');
    await Promise.all([
      page.waitForURL(/wp-admin/),
      page.locator('#wp-submit').click(),
    ]);

    await page.goto(`${baseUrl}/wp-admin/post.php?post=${fixtureId}&action=edit`, { waitUntil: 'domcontentloaded' });
    await page.locator('.edit-post-layout, .interface-interface-skeleton').first().waitFor({ state: 'visible', timeout: 30000 });
    await page.waitForTimeout(1800);

    let editorPage = page;
    const iframe = page.locator('iframe[name="editor-canvas"]');
    if (await iframe.count()) {
      await iframe.first().waitFor({ state: 'visible', timeout: 30000 });
      const frame = page.frames().find((candidate) => candidate.name() === 'editor-canvas');
      if (!frame) throw new Error('editor-canvas iframe exists but was not resolved');
      editorPage = frame;
      result.canvas = 'iframe';
    } else {
      result.canvas = 'document';
    }

    const wrapper = editorPage.locator('.editor-styles-wrapper').first();
    await wrapper.waitFor({ state: 'visible', timeout: 30000 });
    await editorPage.getByText('Đặt giá trị cốt lõi', { exact: false }).first().waitFor({ state: 'visible', timeout: 30000 });
    await editorPage.getByText('Câu hỏi thường gặp', { exact: false }).first().waitFor({ state: 'visible', timeout: 30000 });

    result.variables = await cssVariables(wrapper, Object.keys(expectedVariables[preset]));
    verifyVariables(result.variables, expectedVariables[preset], 'editor');

    result.patternCount = await editorPage.locator('.aznet-theme-pattern').count();
    const minimumPatterns = wooEnabled ? 7 : 5;
    if (result.patternCount < minimumPatterns) {
      throw new Error(`editor expected at least ${minimumPatterns} representative pattern roots, got ${result.patternCount}`);
    }

    await page.screenshot({ path: path.join(screenshotDir, 'editor.png'), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R2 ${preset} ${wooEnabled ? 'Woo' : 'clean'} editor parity (${result.canvas})`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`editor: ${result.error}`);
    console.error(`FAIL: R2 ${preset} editor: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, 'editor-failure.png'), fullPage: true }).catch(() => {});
  } finally {
    summary.editor = result;
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    await inspectFrontend(browser, viewportName, viewport);
  }
  await inspectEditor(browser);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length > 0) {
  throw new Error(`R2 ${preset} L4 failed:\n${failures.join('\n')}`);
}
console.log(`PASS: R2 ${preset} ${wooEnabled ? 'Woo' : 'clean'} pattern library L4`);
