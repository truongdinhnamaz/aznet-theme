/**
 * Discardable WordPress 6.9 Wellness 01 pattern browser/editor QA.
 * Theme owns presentation; no booking/provider/WordPress content mutation here.
 */
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import { createHash } from 'node:crypto';
import { inspectWellnessLayout } from './wellness01-layout-probe.mjs';

const base = process.env.WELLNESS_BASE_URL || 'http://127.0.0.1:8080';
const pageId = process.env.WELLNESS_PAGE_ID;
const draftId = process.env.WELLNESS_DRAFT_ID;
const out = process.env.WELLNESS_EVIDENCE_DIR || '/tmp/wellness01-evidence';
if (!pageId || !draftId) throw new Error('WELLNESS_PAGE_ID and WELLNESS_DRAFT_ID are required');
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ headless: true });
const problems = [];
const captures = [];
const genericFingerprint = createHash('sha256').update(fs.readFileSync(new URL('../../assets/css/components/generic-content.css', import.meta.url))).digest('hex').slice(0, 12);
for (const [label, width, height] of [['desktop', 1440, 900], ['tablet-wide', 1024, 768], ['tablet', 768, 1024], ['mobile', 390, 844]]) {
  const page = await browser.newPage({ viewport: { width, height }, reducedMotion: 'reduce' });
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  const response = await page.goto(`${base}/?page_id=${pageId}`, { waitUntil: 'networkidle' });
  if (response.status() !== 200) problems.push(`${label} HTTP ${response.status()}`);
  const info = await page.evaluate(() => {
    const root = document.querySelector('.aznet-theme-wellness01');
    const links = root ? [...root.querySelectorAll('a[href^="#"]')] : [];
    return {
      root: !!root,
      h1: root?.querySelectorAll('h1').length || 0,
      columns: root?.querySelectorAll('.wp-block-column').length || 0,
      viewportWidth: window.innerWidth,
      docWidth: document.documentElement.scrollWidth,
      unresolvedAnchors: links.filter(a => !document.getElementById(a.getAttribute('href').slice(1))).map(a => a.getAttribute('href')),
      title: root?.querySelector('h1')?.textContent?.trim() || '',
      signupForms: root?.querySelectorAll('form').length || 0,
    };
  });
  if (!info.root || info.h1 !== 1 || info.columns < 5) problems.push(`${label} missing structure: ${JSON.stringify(info)}`);
  if (info.docWidth > info.viewportWidth + 4) problems.push(`${label} horizontal overflow: ${info.docWidth} > ${info.viewportWidth}`);
  if (info.unresolvedAnchors.length) problems.push(`${label} missing anchors: ${info.unresolvedAnchors}`);
  if (info.signupForms) problems.push(`${label} unexpected Theme-owned signup form`);
  // scrollWidth alone cannot detect sections clipped by an overflow:clip root.
  const layout = await page.evaluate(inspectWellnessLayout);
  problems.push(...layout.failures.map(failure => `${label} ${failure}`));
  if (!await page.locator('.aznet-theme-homepage--wellness-01 .aznet-theme-wellness01').count()
    || !layout.themeStyles.some(url => url.includes('/homepage-wellness-01.css?ver='))) {
    problems.push(`${label} missing actual Wellness composition or surface stylesheet`);
  }
  const fixtureNames = ['Custom white foreground', 'Short white foreground', 'White background stays dark'];
  if (fixtureNames.some(name => layout.buttons.filter(button => button.name === name).length !== 1)) {
    problems.push(`${label} missing custom-color regression Buttons inside the Wellness root`);
  }
  if (!layout.themeStyles.some(url => url.includes(`/generic-content.css?ver=`) && url.includes(genericFingerprint))) {
    problems.push(`${label} generic-content CSS URL does not identify current bytes ${genericFingerprint}`);
  }
  const photos = await page.evaluate(async () => {
    const imgs = [...document.querySelectorAll('.aznet-theme-wellness01 .wp-block-image img')];
    await Promise.all(imgs.map(img => img.decode().catch(() => {})));
    return imgs.map(img => ({ ok: img.complete && img.naturalWidth > 0, alt: img.alt, url: img.currentSrc }));
  });
  if (photos.length !== 7 || photos.some(img => !img.ok || !img.url.includes('/assets/starter/wellness01/'))) {
    problems.push(`${label} missing WordPress-served demo photos: ${JSON.stringify(photos)}`);
  }
  if (!await page.locator('.aznet-theme-wellness01').getByText('Ảnh minh họa được tạo bằng AI').count()) {
    problems.push(`${label} missing visible AI illustration disclosure`);
  }

  if (errors.length) problems.push(`${label} console JS exceptions: ${errors.join('; ')}`);
  await page.keyboard.press('Tab');
  const focus = await page.evaluate(() => ({ name: document.activeElement?.tagName, visible: document.activeElement !== document.body }));
  if (!focus.visible) problems.push(`${label} keyboard Tab did not focus a control`);
  const axe = await new AxeBuilder({ page }).include('.aznet-theme-wellness01').withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
  if (axe.violations.length) problems.push(`${label} axe violations: ${axe.violations.map(v => v.id + '(' + v.nodes.length + ')').join(', ')}`);
  const screenshot = `${out}/${label}.png`;
  await page.screenshot({ path: screenshot, fullPage: true });
  captures.push({ label, ...info, layout, axeViolations: axe.violations, screenshot });
  await page.close();
}
// Reopen an independent draft Page in the actual Gutenberg editor; check its
// instantiated blocks for parser-invalid content. Draft remains local to CI.
const editor = await browser.newPage({ viewport: { width: 1440, height: 900 } });
await editor.goto(`${base}/wp-login.php`, { waitUntil: 'domcontentloaded' });
await editor.fill('#user_login', process.env.WELLNESS_EDITOR_USER || 'admin');
await editor.fill('#user_pass', process.env.WELLNESS_EDITOR_PASS || '');
await Promise.all([
  editor.waitForNavigation({ waitUntil: 'domcontentloaded' }),
  editor.click('#wp-submit'),
]);
await editor.goto(`${base}/wp-admin/post.php?post=${draftId}&action=edit`, { waitUntil: 'domcontentloaded' });
await editor.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, null, { timeout: 45000 });
const editorState = await editor.evaluate(() => {
  const blocks = window.wp.data.select('core/block-editor').getBlocks();
  let count = 0, invalid = [], names = [];
  function walk(items) { for (const b of items) { count++; names.push(b.name); if (b.isValid === false) invalid.push(b.name); walk(b.innerBlocks || []); } }
  walk(blocks);
  return { count, invalid, rootNames: blocks.map(b=>b.name), customBlocks: names.filter(n=>!n.startsWith('core/')) };
});
if (editorState.count < 25) problems.push(`editor parsed too few native blocks: ${editorState.count}`);
if (editorState.invalid.length) problems.push(`editor invalid blocks: ${editorState.invalid.join(', ')}`);
if (editorState.customBlocks.length) problems.push(`editor non-core blocks: ${editorState.customBlocks.join(', ')}`);
// Exercise the WordPress editorial lifecycle on the disposable DRAFT, not the
// published fixture. Saving and reopening must preserve native block validity.
const roundTripSentinel = 'Wellness 01 Gutenberg roundtrip marker';
await editor.evaluate(async marker => {
  const store = window.wp.data;
  const findParagraph = blocks => {
    for (const block of blocks) {
      if (block.name === 'core/paragraph') return block;
      const nested = findParagraph(block.innerBlocks || []);
      if (nested) return nested;
    }
    return null;
  };
  const paragraph = findParagraph(store.select('core/block-editor').getBlocks());
  if (!paragraph) throw new Error('No editable paragraph in native pattern');
  store.dispatch('core/block-editor').updateBlockAttributes(paragraph.clientId, { content: marker });
  await store.dispatch('core/editor').savePost();
}, roundTripSentinel);
await editor.waitForFunction(() => !window.wp.data.select('core/editor').isSavingPost(), null, { timeout: 45000 });
await editor.reload({ waitUntil: 'domcontentloaded' });
await editor.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, null, { timeout: 45000 });
const roundTrip = await editor.evaluate(marker => {
  const blocks = window.wp.data.select('core/block-editor').getBlocks();
  let valid = true, found = false;
  function walk(list) {
    for (const b of list) {
      if (b.isValid === false) valid = false;
      if (b.name === 'core/paragraph' && String(b.attributes?.content || '').includes(marker)) found = true;
      walk(b.innerBlocks || []);
    }
  }
  walk(blocks);
  return { valid, found, topLevel: blocks.length };
}, roundTripSentinel);
if (!roundTrip.valid || !roundTrip.found) problems.push(`draft editor save/reopen failed: ${JSON.stringify(roundTrip)}`);
await editor.screenshot({ path: `${out}/editor.png`, fullPage: false });
await editor.close();
await browser.close();
fs.writeFileSync(`${out}/verification.json`, JSON.stringify({ captures, editorState, roundTrip, problems }, null, 2));
if (problems.length) { console.error('FAIL: Wellness 01 native WordPress QA\n' + problems.join('\n')); process.exit(1); }
console.log('PASS: Wellness 01 WordPress browser x4 + axe + Gutenberg save/reopen; ' + editorState.count + ' blocks');
