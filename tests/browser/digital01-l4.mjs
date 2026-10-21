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
    // An outer overflow clip can hide severe offscreen layout while scrollWidth
    // remains unchanged. Test each actual critical section and visible card.
    const geometry = await page.evaluate(() => {
      const vw = document.documentElement.clientWidth;
      const hero = document.querySelector('.aznet-digital01__hero');
      const heroBox = hero?.getBoundingClientRect();
      const headlineBox = hero?.querySelector('h1')?.getBoundingClientRect();
      const eyebrowBox = hero?.querySelector('.aznet-digital01__eyebrow')?.getBoundingClientRect();
      const bad = [...document.querySelectorAll('.aznet-digital01 .aznet-digital01__section, .aznet-digital01 .aznet-digital01__card, .aznet-digital01 :is(h1,h2,h3)')]
        .map(el => ({ label: el.className || el.tagName, rect: el.getBoundingClientRect() }))
        .filter(({rect}) => rect.width > 0 && (rect.left < -1 || rect.right > vw + 1 || rect.right <= 0 || rect.left >= vw))
        .slice(0, 12)
        .map(({label,rect}) => ({label: String(label).slice(0,65), left: Math.round(rect.left), right: Math.round(rect.right), viewport:vw}));
      return {
        heroLeft: heroBox?.left ?? null,
        heroRight: heroBox?.right ?? null,
        headlineLeft: headlineBox?.left ?? null,
        eyebrowLeft: eyebrowBox?.left ?? null,
        viewport: vw,
        bad,
      };
    });
    assert(geometry.heroLeft !== null && Math.abs(geometry.heroLeft) <= 1 && Math.abs(geometry.heroRight - geometry.viewport) <= 1,
      `D-035 full-width flush Hero failed: ${JSON.stringify(geometry)}`);
    assert(geometry.bad.length === 0, `visible sections/cards clipped despite scrollWidth test: ${JSON.stringify(geometry.bad)}`);
    // Root/full-bleed does not authorize flush text. Section content must retain
    // accessible gutters and Hero text/buttons must share a stable left edge.
    const heroInner = await page.evaluate(() => {
      const names = {
        eyebrow: '.aznet-digital01__hero > .aznet-digital01__eyebrow',
        title: '.aznet-digital01__hero > h1',
        intro: '.aznet-digital01__hero > p:not(.aznet-digital01__eyebrow)',
        buttons: '.aznet-digital01__hero > .wp-block-buttons',
      };
      return Object.fromEntries(Object.entries(names).map(([key,selector]) => {
        const el = document.querySelector(selector);
        if (!el) return [key, null];
        const rect = el.getBoundingClientRect();
        return [key, {left:Math.round(rect.left), right:Math.round(rect.right)}];
      }));
    });
    const requiredGutter = viewport.width < 600 ? 16 : 30;
    const heroLefts = Object.values(heroInner).map(item => item?.left).filter(Number.isFinite);
    assert(heroLefts.length === 4, `Hero content incomplete: ${JSON.stringify(heroInner)}`);
    assert(heroLefts.every(x => x >= requiredGutter), `Hero text/CTA touches viewport edge: ${JSON.stringify(heroInner)}`);
    assert(Math.max(...heroLefts) - Math.min(...heroLefts) <= 3,
      `Hero heading, introductory copy and CTA are not left-aligned: ${JSON.stringify(heroInner)}`);
    assert(geometry.headlineLeft !== null && geometry.eyebrowLeft !== null
      && Math.abs(geometry.headlineLeft - geometry.eyebrowLeft) <= 4,
      `D-036 Hero heading and constrained eyebrow are not aligned: ${JSON.stringify(geometry)}`);
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

    // Real Classic Editor authoring round-trip, not just an editor-selector assertion.
    // Use Text mode to preserve WordPress-native block comments and the public Query block.
    const textTab = page.locator('#content-html');
    assert(await textTab.count() === 1, 'Classic Text tab is unavailable');
    await textTab.click();
    await page.locator('#content').waitFor({ state: 'visible', timeout: 12000 });
    const original = await page.locator('#content').inputValue();
    const phrase = 'Chỉnh sửa nội dung giới thiệu này theo năng lực thực tế của doanh nghiệp.';
    assert(original.includes(phrase), 'editable owner-neutral Homepage paragraph absent');
    assert(original.includes('<!-- wp:query'), 'native WordPress Query block absent before Classic save');
    const edited = original.replace(phrase, phrase + ' Classic Editor round-trip verified.');
    assert(edited !== original, 'Classic authoring sentinel substitution did not occur');
    await page.locator('#content').fill(edited);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 25000 }),
      page.locator('#publish').click(),
    ]);
    await page.locator('#content').waitFor({ state: 'attached', timeout: 20000 });
    const afterSave = await page.locator('#content').inputValue();
    assert(afterSave.includes('Classic Editor round-trip verified.'), 'Classic Text changes did not persist');
    assert(afterSave.includes('<!-- wp:query') && afterSave.includes('aznet-digital01'),
      'Classic Editor saved Page but lost WordPress-native sections/Query blocks');
    const storedFrontend = await page.request.get(base);
    assert(storedFrontend.status() === 200, 'Front Page unavailable following Classic save');
    const updatedFrontend = await storedFrontend.text();
    assert(updatedFrontend.includes('Classic Editor round-trip verified.'), 'Edited Classic text not visible publicly');
    assert(updatedFrontend.includes('Digital 01 Technology Article'), 'Query Loop broken after Classic update');
    entry.classicRoundTrip = true;

    // A Classic Editor preference includes a WYSIWYG Visual tab. Verify it does
    // not silently destroy WordPress-native Query Loop or Homepage block markers.
    const visualTab = page.locator('#content-tmce');
    assert(await visualTab.count() === 1, 'Classic Visual tab unavailable');
    await visualTab.click();
    const visualBody = page.frameLocator('#content_ifr').locator('body');
    await visualBody.waitFor({ state: 'visible', timeout: 20000 });
    const visualParagraph = page.frameLocator('#content_ifr').locator('p').filter({ hasText: phrase }).first();
    assert(await visualParagraph.count() === 1, 'editable Hero paragraph unavailable in Classic Visual tab');
    await visualParagraph.click();
    await page.keyboard.press('End');
    await page.keyboard.insertText(' Visual tab round-trip verified.');
    await page.locator('#content-html').click();
    await page.locator('#content').waitFor({ state: 'visible', timeout: 12000 });
    const visualMarkup = await page.locator('#content').inputValue();
    assert(visualMarkup.includes('Visual tab round-trip verified.'), 'Classic Visual edit was not reflected in Text source');
    assert(visualMarkup.includes('<!-- wp:query') && visualMarkup.includes('aznet-digital01'),
      'Classic Visual toggle destroyed native Homepage markup/Query before saving');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 25000 }),
      page.locator('#publish').click(),
    ]);
    const persistedVisual = await page.locator('#content').inputValue();
    assert(persistedVisual.includes('Visual tab round-trip verified.') && persistedVisual.includes('<!-- wp:query'),
      'Classic Visual save did not preserve the edit and native Query Loop');
    const visualFrontend = await page.request.get(base);
    assert(visualFrontend.status() === 200, 'Front Page unavailable after Visual tab save');
    const visualHtml = await visualFrontend.text();
    assert(visualHtml.includes('Visual tab round-trip verified.'), 'Classic Visual edit missing from public output');
    assert(visualHtml.includes('Digital 01 Technology Article'), 'WordPress Query Loop not present after Visual save');
    entry.visualRoundTrip = true;

    await page.goto(`${base}/wp-admin/admin.php?page=aznet-theme&section=homepage`, { waitUntil: 'domcontentloaded' });
    await page.locator('.aznet-theme-homepage-authoring').waitFor({ state: 'visible', timeout: 20000 });
    const editorLink = page.getByRole('link', { name: 'Chỉnh sửa nội dung trang chủ' });
    assert(await editorLink.count() === 1, 'native Theme authoring control has no direct WordPress Page edit action');
    const target = String(await editorLink.getAttribute('href') || '');
    assert(target.includes(`post=${frontId}`), 'native Theme authoring control linked the wrong WordPress Page');
    assert(!(await page.locator('.aznet-theme-homepage-authoring').innerText()).includes('Chưa hỗ trợ'), 'native authoring still reports unsupported source');
    await page.screenshot({ path: path.join(output, 'digital01-theme-homepage-authoring.png'), fullPage: true });

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
console.log('PASS: Digital 01 clean WordPress L3/L4 desktop/tablet/mobile/axe, Classic Text/Visual save and native source mapping');
