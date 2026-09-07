import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.R1_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputRoot = process.env.R1_STATE_DIR || '/tmp/r1-design-system';
const preset = process.env.R1_PRESET || 'default';
const fixtureId = process.env.R1_FIXTURE_ID || '';
const outputDir = path.join(outputRoot, preset);
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

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
  commerce: {
    '--aznet-theme-font-size-sm': '0.8125rem',
    '--aznet-theme-line-height-body': '1.5',
    '--aznet-theme-radius-card': '0.75rem',
    '--aznet-theme-space-section': '2.75rem',
  },
};

if (! Object.hasOwn(expectedVariables, preset)) {
  throw new Error(`Unsupported R1 preset ${preset}`);
}
if (! fixtureId) {
  throw new Error('R1_FIXTURE_ID is required');
}

const summary = { preset, baseUrl, frontend: [], editor: null };
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

function verifyPresetStylesheet(hrefs, label) {
  const presetSheets = hrefs.filter((href) => /\/assets\/css\/presets\/(editorial|commerce)\.css(?:\?|$)/i.test(href));
  if (preset === 'default') {
    if (presetSheets.length !== 0) throw new Error(`${label}: default loaded preset stylesheet ${presetSheets.join(', ')}`);
    return;
  }
  if (presetSheets.length !== 1 || ! presetSheets[0].includes(`/assets/css/presets/${preset}.css`)) {
    throw new Error(`${label}: expected only ${preset} preset stylesheet, got ${presetSheets.join(', ') || '<none>'}`);
  }
}

async function inspectFrontend(browser, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
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

  const result = { viewport: viewportName, status: 'failed', variables: {}, stylesheets: [], error: null };
  try {
    const response = await page.goto(`${baseUrl}/design-system-fixture/`, { waitUntil: 'networkidle' });
    if (! response || response.status() !== 200) throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    await page.getByText('R1 Design System Fixture', { exact: false }).first().waitFor({ state: 'visible' });
    await page.locator('.wp-block-buttons').waitFor({ state: 'visible' });
    await page.locator('.wp-block-columns').waitFor({ state: 'visible' });
    await page.locator('img[alt="R1 media fixture"]').waitFor({ state: 'visible' });

    const body = page.locator('body');
    const bodyClass = await body.getAttribute('class');
    if (! String(bodyClass || '').split(/\s+/).includes(`aznet-theme-preset--${preset}`)) {
      throw new Error(`missing body preset class aznet-theme-preset--${preset}`);
    }

    result.variables = await cssVariables(body, Object.keys(expectedVariables[preset]));
    verifyVariables(result.variables, expectedVariables[preset], `frontend ${viewportName}`);

    result.stylesheets = await page.locator('link[rel="stylesheet"]').evaluateAll((links) => links.map((link) => link.href));
    verifyPresetStylesheet(result.stylesheets, `frontend ${viewportName}`);

    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (overflow > 1) throw new Error(`horizontal overflow ${overflow}px`);

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `frontend-${viewportName}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);
    if (failedSubresources.length > 0) throw new Error(`failed subresources: ${failedSubresources.join(' | ')}`);
    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R1 ${preset} frontend ${viewportName}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`frontend-${viewportName}: ${result.error}`);
    console.error(`FAIL: R1 ${preset} frontend ${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.frontend.push(result);
    await context.close();
  }
}

async function inspectEditor(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  const result = { status: 'failed', variables: {}, stylesheets: [], canvas: null, error: null };
  try {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('r1-browser-password');
    await Promise.all([
      page.waitForURL(/wp-admin/),
      page.locator('#wp-submit').click(),
    ]);

    await page.goto(`${baseUrl}/wp-admin/post.php?post=${fixtureId}&action=edit`, { waitUntil: 'domcontentloaded' });
    await page.locator('.edit-post-layout, .interface-interface-skeleton').first().waitFor({ state: 'visible', timeout: 30000 });
    await page.waitForTimeout(1500);

    let editorPage = page;
    const iframe = page.locator('iframe[name="editor-canvas"]');
    if (await iframe.count()) {
      await iframe.first().waitFor({ state: 'visible', timeout: 30000 });
      const frame = page.frames().find((candidate) => candidate.name() === 'editor-canvas');
      if (! frame) throw new Error('editor-canvas iframe exists but frame was not resolved');
      editorPage = frame;
      result.canvas = 'iframe';
    } else {
      result.canvas = 'document';
    }

    const wrapper = editorPage.locator('.editor-styles-wrapper').first();
    await wrapper.waitFor({ state: 'visible', timeout: 30000 });
    await editorPage.getByText('R1 Design System Fixture', { exact: false }).first().waitFor({ state: 'visible', timeout: 30000 });

    result.variables = await cssVariables(wrapper, Object.keys(expectedVariables[preset]));
    verifyVariables(result.variables, expectedVariables[preset], 'editor');

    // WordPress may inline, concatenate or otherwise transform editor styles before
    // they reach the editor canvas. Computed semantic variables are the behavioral
    // contract for editor/frontend parity; literal <link> URLs are recorded only as
    // diagnostic evidence and are not an implementation requirement.
    result.stylesheets = await editorPage.locator('link[rel="stylesheet"]').evaluateAll((links) => links.map((link) => link.href));

    await page.screenshot({ path: path.join(screenshotDir, 'editor.png'), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R1 ${preset} editor parity (${result.canvas})`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`editor: ${result.error}`);
    console.error(`FAIL: R1 ${preset} editor: ${result.error}`);
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
  throw new Error(`R1 ${preset} L4 failed:\n${failures.join('\n')}`);
}
console.log(`PASS: R1 ${preset} Design System L4`);
