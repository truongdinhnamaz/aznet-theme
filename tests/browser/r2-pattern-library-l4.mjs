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
  default: { '--aznet-theme-container-content': '45rem', '--aznet-theme-line-height-body': '1.6', '--aznet-theme-space-section': '3.25rem' },
  editorial: { '--aznet-theme-container-content': '42rem', '--aznet-theme-line-height-body': '1.7', '--aznet-theme-space-section': '3.75rem' },
};
if (!Object.hasOwn(expectedVariables, preset)) throw new Error(`Unsupported R2 preset ${preset}`);

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

function normalize(value) { return String(value || '').trim().toLowerCase().replace(/\s+/g, ' '); }
async function cssVariables(locator, names) {
  return locator.evaluate((element, requested) => {
    const style = getComputedStyle(element);
    return Object.fromEntries(requested.map((name) => [name, style.getPropertyValue(name).trim()]));
  }, names);
}
function verifyVariables(actual, expected, label) {
  for (const [name, expectedValue] of Object.entries(expected)) {
    if (normalize(actual[name]) !== normalize(expectedValue)) throw new Error(`${label} ${name}: expected ${expectedValue}, got ${actual[name] || '<empty>'}`);
  }
}

async function inspectFrontend(browser, viewportName, viewport) {
  const context = await browser.newContext({ viewport, reducedMotion: viewport.width <= 390 ? 'reduce' : 'no-preference' });
  const page = await context.newPage();
  const result = { viewport: viewportName, status: 'failed', error: null };
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  try {
    const response = await page.goto(`${baseUrl}/r2-pattern-fixture/`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    await page.locator('main#main').waitFor({ state: 'visible' });
    for (const selector of requiredCoreSelectors) await page.locator(selector).first().waitFor({ state: 'visible', timeout: 15000 });

    const body = page.locator('body');
    const bodyClass = String((await body.getAttribute('class')) || '');
    if (!bodyClass.split(/\s+/).includes(`aznet-theme-preset--${preset}`)) throw new Error(`missing body preset class aznet-theme-preset--${preset}`);
    verifyVariables(await cssVariables(body, Object.keys(expectedVariables[preset])), expectedVariables[preset], `frontend ${viewportName}`);
    if (await page.locator('main#main').count() !== 1) throw new Error('expected exactly one main#main');

    const duplicateIds = await page.evaluate(() => {
      const seen = new Set(); const duplicates = new Set();
      for (const node of document.querySelectorAll('[id]')) { if (!node.id) continue; if (seen.has(node.id)) duplicates.add(node.id); seen.add(node.id); }
      return [...duplicates];
    });
    if (duplicateIds.length) throw new Error(`duplicate IDs: ${duplicateIds.join(', ')}`);
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (overflow > 1) throw new Error(`horizontal overflow ${overflow}px`);

    const articleCards = await page.locator('.aznet-theme-pattern__article-card').count();
    if (articleCards < 1) throw new Error('native Query block did not render article cards');
    const details = await page.locator('.aznet-theme-pattern--content-faq details').count();
    if (details < 3) throw new Error(`FAQ expected at least 3 details blocks, got ${details}`);
    if (wooEnabled) {
      await page.locator('.aznet-theme-pattern--commerce-featured-products').first().waitFor({ state: 'visible', timeout: 20000 });
      await page.locator('.aznet-theme-pattern--commerce-category-grid').first().waitFor({ state: 'visible', timeout: 20000 });
    } else if (await page.locator('.aznet-theme-pattern--commerce-featured-products, .aznet-theme-pattern--commerce-category-grid').count()) {
      throw new Error('Woo-dependent patterns rendered in Woo-absent fixture');
    }

    const axe = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `frontend-${viewportName}.json`), JSON.stringify(axe, null, 2));
    const blocking = axe.violations.filter((v) => ['critical', 'serious'].includes(v.impact));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.map((v) => v.id).join(', ')}`);
    if (consoleErrors.length) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length) throw new Error(`page errors: ${pageErrors.join(' | ')}`);
    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`frontend-${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `frontend-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally { summary.frontend.push(result); await context.close(); }
}

async function inspectEditor(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  const result = { status: 'failed', mode: null, patternMarkupPresent: false, blockEditorPresent: null, error: null };
  try {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('r2-browser-password');
    await Promise.all([page.waitForURL(/wp-admin/), page.locator('#wp-submit').click()]);
    await page.goto(`${baseUrl}/wp-admin/post.php?post=${fixtureId}&action=edit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#post').waitFor({ state: 'visible', timeout: 30000 });
    await page.locator('#content').waitFor({ state: 'attached', timeout: 30000 });
    const content = await page.locator('#content').inputValue();
    result.mode = 'classic';
    result.patternMarkupPresent = content.includes('aznet-theme-pattern--hero-split') && content.includes('aznet-theme-pattern--content-faq');
    result.blockEditorPresent = await page.locator('.edit-post-layout, .interface-interface-skeleton').count();
    if (!result.patternMarkupPresent) throw new Error('Classic Editor did not retain representative pattern markup');
    if (result.blockEditorPresent !== 0) throw new Error('native Page unexpectedly exposed Block Editor');
    await page.screenshot({ path: path.join(screenshotDir, 'editor-classic.png'), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`editor: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, 'editor-classic-failure.png'), fullPage: true }).catch(() => {});
  } finally { summary.editor = result; await context.close(); }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) await inspectFrontend(browser, viewportName, viewport);
  await inspectEditor(browser);
} finally { await browser.close(); }
fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error(`R2 ${preset} L4 failed:\n${failures.join('\n')}`);
console.log(`PASS: R2 ${preset} ${wooEnabled ? 'Woo' : 'clean'} pattern library L4`);
