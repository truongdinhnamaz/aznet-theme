import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

// Focused WordPress-clean L4 for the native "latest posts" Front Page,
// configured static Front Page, and separate WordPress Posts Page.
// This augments (never replaces) the retained F Homepage L4 suite.
const baseUrl = (process.env.F_HOME_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.F_HOME_STATE_DIR || '/tmp/f-home-native';
const mode = process.env.F_POSTS_MODE || '';
if (!['posts-front', 'posts-page', 'static'].includes(mode)) {
  throw new Error('F_POSTS_MODE must be posts-front, posts-page or static');
}
const outDir = path.join(stateDir, 'posts-index', mode);
fs.mkdirSync(outDir, { recursive: true });
const viewports = [
  { name: 'desktop', width: 1440, height: 900 },
  { name: 'mobile', width: 390, height: 844 },
];
const summary = { mode, cases: [] };
const failures = [];

async function inspect(browser, viewport) {
  const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height } });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  const result = { viewport: viewport.name, status: 'failed', cardCount: null, page2CardCount: null, overflow: null, axeBlocking: null, error: null };
  try {
    const url = mode === 'posts-page' ? baseUrl + '/news-index/' : baseUrl + '/';
    const response = await page.goto(url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error('HTTP ' + (response?.status() ?? 'missing'));

    if (await page.locator('main#main').count() !== 1) throw new Error('expected exactly one main#main');
    await page.locator('.aznet-theme-site-header').first().waitFor({ state: 'visible' });
    await page.locator('.aznet-theme-site-footer').first().waitFor({ state: 'visible' });
    const duplicateIds = await page.evaluate(() => {
      const seen = new Set();
      const duplicates = new Set();
      for (const element of document.querySelectorAll('[id]')) {
        if (!element.id) continue;
        if (seen.has(element.id)) duplicates.add(element.id);
        seen.add(element.id);
      }
      return [...duplicates];
    });
    if (duplicateIds.length) throw new Error('duplicate IDs: ' + duplicateIds.join(', '));
    result.overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflow > 1) throw new Error('horizontal overflow ' + result.overflow + 'px');

    // Native skip link must be first in keyboard order and visibly focused.
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const element = document.activeElement;
      if (!(element instanceof HTMLElement)) return { ok: false };
      const css = getComputedStyle(element);
      const r = element.getBoundingClientRect();
      return {
        ok: element.classList.contains('aznet-theme-skip-link') && element.getAttribute('href') === '#main',
        visible: r.width > 1 && r.height > 1,
        indicator: (css.outlineStyle !== 'none' && Number.parseFloat(css.outlineWidth) > 0) ||
          (css.boxShadow !== 'none' && css.boxShadow.length > 0),
      };
    });
    if (!focus.ok || !focus.visible || !focus.indicator) throw new Error('skip link focus not accessible');

    const rawMainText = String(await page.locator('main#main').textContent());
    if (rawMainText.includes('FULL_ARTICLE_BODY_SENTINEL')) {
      throw new Error('WordPress posts index leaks full article contents');
    }

    if (mode === 'static') {
      await page.locator('main#main.aznet-theme-main--front-page').waitFor({ state: 'visible' });
      if ((rawMainText.match(/F native homepage sentinel/g) || []).length !== 1) {
        throw new Error('static Front Page lost its single WordPress content body');
      }
      if (await page.locator('main#main .aznet-theme-listing__grid').count()) {
        throw new Error('static Front Page was replaced with posts listing');
      }
    } else {
      await page.locator('main#main.aznet-theme-main--listing.aznet-theme-main--posts-page').waitFor({ state: 'visible' });
      const cards = page.locator('main#main .aznet-theme-listing__grid .aznet-theme-card');
      result.cardCount = await cards.count();
      if (result.cardCount !== 3) throw new Error('expected three native posts cards; got ' + result.cardCount);
      if (await page.locator('main#main h1.aznet-theme-listing__title').count() !== 1) {
        throw new Error('expected one posts index h1');
      }
      if (await page.locator('main#main h2.aznet-theme-card__title a').count() !== 3) {
        throw new Error('native cards missing Post title links');
      }
      const stylesheet = await page.locator('link[href*="/generic-content.css"]').count();
      if (!stylesheet) throw new Error('native posts index did not enqueue generic-content.css');
      const pagination = page.locator('.aznet-theme-listing__pagination a.next');
      if (await pagination.count() !== 1) throw new Error('native posts pagination missing Next link');
    }

    const axe = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(outDir, 'axe-' + viewport.name + '.json'), JSON.stringify(axe, null, 2));
    const serious = axe.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical');
    result.axeBlocking = serious.map((v) => v.id);
    if (serious.length) throw new Error('blocking axe: ' + serious.map((v) => v.id).join(', '));

    await page.screenshot({ path: path.join(outDir, viewport.name + '.png'), fullPage: true });

    if (mode !== 'static') {
      const next = page.locator('.aznet-theme-listing__pagination a.next');
      await Promise.all([page.waitForLoadState('domcontentloaded'), next.click()]);
      if (await page.locator('main#main').count() !== 1) throw new Error('pagination lost main landmark');
      result.page2CardCount = await page.locator('.aznet-theme-listing__grid .aznet-theme-card').count();
      if (result.page2CardCount !== 2) throw new Error('expected two Post cards on pagination page 2; got ' + result.page2CardCount);
      if (String(await page.locator('main#main').textContent()).includes('FULL_ARTICLE_BODY_SENTINEL')) {
        throw new Error('pagination page 2 leaks full article content');
      }
      const overflow2 = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (overflow2 > 1) throw new Error('pagination page 2 overflow ' + overflow2 + 'px');
    }

    if (consoleErrors.length) throw new Error('console errors: ' + consoleErrors.join(' | '));
    if (pageErrors.length) throw new Error('page errors: ' + pageErrors.join(' | '));
    result.status = 'passed';
    console.log('PASS: ' + mode + ' ' + viewport.name + ' native posts/front Page L4');
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(mode + '/' + viewport.name + ': ' + result.error);
    await page.screenshot({ path: path.join(outDir, viewport.name + '-failure.png'), fullPage: true }).catch(() => {});
    console.error('FAIL: ' + mode + ' ' + viewport.name + ': ' + result.error);
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}
const browser = await chromium.launch({ headless: true });
try {
  for (const viewport of viewports) await inspect(browser, viewport);
} finally {
  await browser.close();
}
fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error('Posts-front L4: ' + failures.join('; '));
console.log('PASS: ' + mode + ' 2/2 WordPress-native browser cases');
