import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const base = (process.env.DIGITAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const output = process.env.DIGITAL01_STATE_DIR || '/tmp/digital01-preview';
fs.mkdirSync(output, { recursive: true });
const summary = { head: process.env.GITHUB_SHA || 'local', pages: [], editor: null, failures: [] };

function assert(ok, message) { if (!ok) throw new Error(message); }
async function audit(browser, key, viewport) {
  const context = await browser.newContext({ viewport, reducedMotion: viewport.width < 600 ? 'reduce' : 'no-preference' });
  const page = await context.newPage();
  const entry = { viewport: key, status: 'failed', issue: null };
  const consoleErrors = [], pageErrors = [];
  page.on('console', m => { if (m.type() === 'error') consoleErrors.push(m.text()); });
  page.on('pageerror', e => pageErrors.push(e.message));
  try {
    const response = await page.goto(base, { waitUntil: 'networkidle' });
    assert(response?.status() === 200, `HTTP ${response?.status()}`);
    await page.locator('.aznet-digital01__hero').waitFor({ state: 'visible' });
    assert((await page.locator('body').getAttribute('class') || '').includes('aznet-theme-preset--digital-01'), 'visual preset not active');
    assert(await page.locator('main#main').count() === 1, 'expected exactly one main landmark');
    assert(await page.locator('h1').count() === 1, 'expected exactly one H1');
    assert(await page.locator('.aznet-theme-homepage--digital-01').count() === 1, 'native wrapper missing');
    assert(await page.locator('.aznet-theme-homepage--law-01').count() === 0, 'Law 01 leaked into Digital 01');
    assert(await page.locator('.aznet-digital01__card').count() >= 5, 'service/product cards missing');
    assert(await page.locator('.aznet-digital01__articles .wp-block-post').count() === 3, 'native WordPress Query Loop must render 3 latest Posts');
    assert(await page.locator('.aznet-theme-site-header').count() === 1, 'Header missing');
    assert(await page.locator('.aznet-theme-site-footer').count() === 1, 'Footer missing');

    const stylesheetLoaded = await page.evaluate(() => [...document.styleSheets].some(s => String(s.href || '').includes('homepage-digital-01.css')));
    assert(stylesheetLoaded, 'surface-aware Digital 01 stylesheet missing');
    const themeColor = await page.locator('body').evaluate(el => getComputedStyle(el).getPropertyValue('--aznet-theme-color-primary').trim());
    assert(themeColor === '#365cf5', `Digital 01 semantic tokens missing: ${themeColor}`);

    const ids = await page.evaluate(() => {
      const seen = new Set(), duplicates = [];
      for (const node of document.querySelectorAll('[id]')) {
        if (!node.id) continue;
        if (seen.has(node.id)) duplicates.push(node.id);
        seen.add(node.id);
      }
      return duplicates;
    });
    assert(ids.length === 0, `duplicate IDs: ${ids.join(',')}`);
    const widthOverflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    assert(widthOverflow <= 1, `horizontal overflow ${widthOverflow}px`);
    await page.locator('a[href="#aznet-digital01-services"]').focus();
    assert(await page.locator('a[href="#aznet-digital01-services"]').evaluate(e => document.activeElement === e), 'anchor keyboard focus failed');
    assert(await page.locator('#aznet-digital01-services').count() === 1, 'anchor target missing');

    const axe = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(output, `axe-${key}.json`), JSON.stringify(axe, null, 2));
    const blocking = axe.violations.filter(v => ['critical','serious'].includes(v.impact));
    assert(blocking.length === 0, `axe serious/critical: ${blocking.map(v => v.id).join(',')}`);
    assert(consoleErrors.length === 0 && pageErrors.length === 0, `console/page errors: ${[...consoleErrors,...pageErrors].join(' | ')}`);
    await page.screenshot({ path: path.join(output, `digital01-${key}.png`), fullPage: true });
    entry.status = 'passed';
  } catch (e) {
    entry.issue = String(e);
    summary.failures.push(`${key}: ${entry.issue}`);
    await page.screenshot({ path: path.join(output, `digital01-${key}-failed.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.pages.push(entry);
    await context.close();
  }
}

async function auditEditor(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 960 } });
  const page = await context.newPage();
  const entry = { status: 'failed', mode: 'unknown', issue: null };
  try {
    const frontId = fs.readFileSync('/tmp/digital01-front-id.txt', 'utf8').trim();
    await page.goto(`${base}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill('admin');
    await page.locator('#user_pass').fill('digital01-test-password');
    await Promise.all([page.waitForURL(/wp-admin/), page.locator('#wp-submit').click()]);
    await page.goto(`${base}/wp-admin/post.php?post=${frontId}&action=edit`, { waitUntil: 'domcontentloaded' });
    await page.locator('#content').waitFor({ state: 'attached', timeout: 20000 });
    const content = await page.locator('#content').inputValue();
    assert(content.includes('aznet-digital01') && content.includes('<!-- wp:query'), 'WordPress native Page content missing in accepted Classic Editor');
    assert(await page.locator('.edit-post-layout, .interface-interface-skeleton').count() === 0, 'unexpected Gutenberg editor changed accepted D-024 Classic policy');
    await page.screenshot({ path: path.join(output, 'digital01-editor-classic.png'), fullPage: true });
    entry.mode = 'classic';
    entry.status = 'passed';
  } catch(e) {
    entry.issue = String(e);
    summary.failures.push(`editor: ${entry.issue}`);
    await page.screenshot({ path: path.join(output, 'digital01-editor-failed.png'), fullPage: true }).catch(() => {});
  } finally {
    summary.editor = entry;
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  await audit(browser, 'desktop-1440', { width: 1440, height: 1000 });
  await audit(browser, 'tablet-1024', { width: 1024, height: 900 });
  await audit(browser, 'mobile-390', { width: 390, height: 844 });
  await auditEditor(browser);
} finally {
  await browser.close();
  fs.writeFileSync(path.join(output, 'summary.json'), JSON.stringify(summary, null, 2));
}
if (summary.failures.length) {
  console.error(summary.failures.join('\n'));
  process.exit(1);
}
console.log('PASS: Digital 01 clean WordPress L3/L4 desktop/tablet/mobile/axe and retained Classic editor policy');
