/**
 * Discardable WordPress 6.9 Wellness 01 pattern browser/editor QA.
 * Saves only disposable native drafts; production and provider state stay untouched.
 */
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import { createHash, randomUUID } from 'node:crypto';
import { inspectWellnessLayout } from './wellness01-layout-probe.mjs';

const base = process.env.WELLNESS_BASE_URL || 'http://127.0.0.1:8080';
const pageId = process.env.WELLNESS_PAGE_ID;
const draftId = process.env.WELLNESS_DRAFT_ID;
const blockDraftId = process.env.WELLNESS_BLOCK_DRAFT_ID;
const editorOnly = process.env.WELLNESS_EDITOR_ONLY === '1';
const out = process.env.WELLNESS_EVIDENCE_DIR || '/tmp/wellness01-evidence';
if (!pageId || !draftId || !blockDraftId) throw new Error('WELLNESS_PAGE_ID, WELLNESS_DRAFT_ID and WELLNESS_BLOCK_DRAFT_ID are required');
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ headless: true });
const problems = [];
const captures = [];
// Fresh markers prove this invocation saved; persisted disposable drafts may
// already contain markers from a previous run.
const runToken = randomUUID();
const classicTextMarker = `Wellness 01 Classic Text roundtrip ${runToken}`;
const classicVisualMarker = `Wellness 01 Classic Visual roundtrip ${runToken}`;
const roundTripSentinel = `Wellness 01 Gutenberg roundtrip ${runToken}`;
const genericFingerprint = createHash('sha256').update(fs.readFileSync(new URL('../../assets/css/components/generic-content.css', import.meta.url))).digest('hex').slice(0, 12);
const viewports = [['desktop', 1440, 900], ['tablet-wide', 1024, 768], ['tablet', 768, 1024], ['mobile', 390, 844]];
for (const [label, width, height] of editorOnly ? [] : viewports) {
  const context = await browser.newContext({ viewport: { width, height }, reducedMotion: 'reduce' });
  const page = await context.newPage();
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
  await context.close();
}
fs.writeFileSync(`${out}/frontend-verification.json`, JSON.stringify({ captures, problems }, null, 2));
// D-024/D-029 retain Classic Editor for ordinary Posts/Pages. Test that real
// lifecycle first; native wp_block preserves WordPress's Gutenberg decision.
const editorContext = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const editor = await editorContext.newPage();
const editorLogs = [];
editor.on('console', message => {
  if (['error', 'warning'].includes(message.type())) editorLogs.push(message.text());
});
editor.on('pageerror', error => editorLogs.push(error.message));
await editor.goto(`${base}/wp-login.php`, { waitUntil: 'domcontentloaded' });
await editor.fill('#user_login', process.env.WELLNESS_EDITOR_USER || 'admin');
await editor.fill('#user_pass', process.env.WELLNESS_EDITOR_PASS || '');
await Promise.all([
  editor.waitForNavigation({ waitUntil: 'domcontentloaded' }),
  editor.click('#wp-submit'),
]);
await editor.goto(`${base}/wp-admin/post.php?post=${draftId}&action=edit`, { waitUntil: 'domcontentloaded' });
await editor.locator('#content').waitFor({ state: 'attached' });
let classicSavePhase = 'text';
const classicSaveRequests = [];
editor.on('request', request => {
  if (request.method() === 'POST' && new URL(request.url()).pathname === '/wp-admin/post.php') {
    const form = new URLSearchParams(request.postData() || '');
    // Retain only marker booleans, never nonces, cookies or full form contents.
    classicSaveRequests.push({ phase: classicSavePhase, action: form.get('action'),
      textMarker: (form.get('content') || '').includes(classicTextMarker),
      visualMarker: (form.get('content') || '').includes(classicVisualMarker) });
  }
});
async function selectClassicMode(mode) {
  await editor.waitForLoadState('networkidle');
  await editor.waitForFunction(() => !!window.switchEditors?.go && (
    document.querySelector('#wp-content-wrap')?.classList.contains('html-active')
    || !!window.tinymce?.get('content')?.initialized
  ), null, { timeout: 45000 });
  await editor.locator(`#content-${mode}`).click();
  await editor.locator(`#wp-content-wrap.${mode === 'html' ? 'html' : 'tmce'}-active`).waitFor({ state: 'attached' });
  if (mode === 'html') await editor.locator('#content').waitFor({ state: 'visible' });
  else await editor.waitForFunction(() => !!window.tinymce?.get('content')?.initialized && !window.tinymce.get('content').isHidden(), null, { timeout: 45000 });
}
await selectClassicMode('html');
const classicBefore = await editor.locator('#content').inputValue();
if (classicBefore.includes(classicTextMarker) || classicBefore.includes(classicVisualMarker)) {
  throw new Error('Fresh Classic save markers unexpectedly exist before editing');
}
await editor.locator('#content').fill(`${classicBefore}\n<p>${classicTextMarker}</p>`);
await Promise.all([
  editor.waitForNavigation({ waitUntil: 'domcontentloaded' }),
  editor.locator('#save-post').click(),
]);
await selectClassicMode('html');
const classicTextSaved = await editor.locator('#content').inputValue();
await selectClassicMode('tmce');
const visualBody = editor.frameLocator('#content_ifr').locator('body');
await visualBody.waitFor({ state: 'visible' });
const visualImageCount = await visualBody.locator('img').count();
// Focus a known editable paragraph; a body-centre click can hit a native view.
await visualBody.getByText(classicTextMarker, { exact: true }).click();
await editor.keyboard.press('ControlOrMeta+End');
await editor.keyboard.press('Enter');
await editor.keyboard.type(classicVisualMarker);
const visualMarkerBeforeSave = (await visualBody.innerText()).includes(classicVisualMarker);
const visualDirtyBeforeSave = await editor.evaluate(() => window.tinymce.get('content').isDirty());
const visualSerializedBeforeSave = await editor.evaluate(marker => window.tinymce.get('content').getContent().includes(marker), classicVisualMarker);
classicSavePhase = 'visual';
await Promise.all([
  editor.waitForNavigation({ waitUntil: 'domcontentloaded' }),
  editor.locator('#save-post').click(),
]);
await selectClassicMode('html');
const classicAfter = await editor.locator('#content').inputValue();
const classicRoundTrip = {
  visualMarkerBeforeSave, visualDirtyBeforeSave, visualSerializedBeforeSave, saveRequests: classicSaveRequests,
  textSaved: classicTextSaved.includes(classicTextMarker), visualSaved: classicAfter.includes(classicVisualMarker),
  textRetained: classicAfter.includes(classicTextMarker), visualImages: visualImageCount,
  savedImages: (classicAfter.match(/<img\b/g) || []).length, hasNativeMarkers: classicAfter.includes('<!-- wp:group'),
};
if (!classicRoundTrip.visualMarkerBeforeSave || !classicRoundTrip.visualSerializedBeforeSave
  || !classicSaveRequests.some(request => request.phase === 'text' && request.textMarker)
  || !classicSaveRequests.some(request => request.phase === 'visual' && request.textMarker && request.visualMarker)
  || !classicRoundTrip.textSaved || !classicRoundTrip.visualSaved || !classicRoundTrip.textRetained
  || classicRoundTrip.visualImages !== 7 || classicRoundTrip.savedImages !== 7 || !classicRoundTrip.hasNativeMarkers) {
  problems.push(`Classic Text/Visual roundtrip failed: ${JSON.stringify(classicRoundTrip)}`);
}
fs.writeFileSync(`${out}/classic-verification.json`, JSON.stringify({ classicRoundTrip, problems }, null, 2));
// Classic may normalize native inline styles while saving. Verify the resulting
// WordPress draft's real computed Button colors, independently of block validity.
const classicPreview = await editorContext.newPage();
await classicPreview.goto(`${base}/?page_id=${draftId}&preview=true`, { waitUntil: 'networkidle' });
const classicLayout = await classicPreview.evaluate(inspectWellnessLayout);
const classicFixtureNames = ['Custom white foreground', 'Short white foreground', 'White background stays dark'];
const classicInverseToken = await classicPreview.evaluate(() => {
  const token = getComputedStyle(document.querySelector('.aznet-theme-entry__content')).getPropertyValue('--aznet-theme-on-inverse').trim() || '#fff';
  const context = new OffscreenCanvas(1, 1).getContext('2d');
  context.fillStyle = token;
  context.fillRect(0, 0, 1, 1);
  const [r, g, b, alpha] = context.getImageData(0, 0, 1, 1).data;
  if (alpha !== 255) throw new Error('Expected an opaque inverse foreground token');
  return { token, foreground: `rgb(${r}, ${g}, ${b})` };
});
const classicPresentation = {
  scope: 'Classic-saved draft Button contrast only; no frontend layout claim',
  inverseToken: classicInverseToken,
  themeStyles: classicLayout.themeStyles,
  buttons: classicLayout.buttons.filter(button => classicFixtureNames.includes(button.name)),
};
for (const name of classicFixtureNames) {
  const matches = classicPresentation.buttons.filter(button => button.name === name);
  const shouldBeWhite = name !== 'White background stays dark';
  if (matches.length !== 1 || matches[0].contrast < 4.5
    || (matches[0].foreground === classicInverseToken.foreground) !== shouldBeWhite) {
    problems.push(`Classic-saved Button color regression: ${name}`);
  }
}
if (!classicPresentation.themeStyles.some(url => url.includes(`/generic-content.css?ver=`) && url.includes(genericFingerprint))) {
  problems.push('Classic-saved preview does not load current generic CSS bytes');
}
fs.writeFileSync(`${out}/classic-presentation.json`, JSON.stringify({ classicPresentation, problems }, null, 2));
await classicPreview.close();
await editor.goto(`${base}/wp-admin/post.php?post=${blockDraftId}&action=edit`, { waitUntil: 'domcontentloaded' });
try {
  await editor.waitForFunction(() => window.wp?.data?.select('core/block-editor')?.getBlocks()?.length > 0, null, { timeout: 45000 });
} catch (error) {
  await editor.screenshot({ path: `${out}/editor-startup-failure.png`, fullPage: false });
  fs.writeFileSync(`${out}/editor-startup-failure.json`, JSON.stringify({
    url: editor.url(), title: await editor.title(), body: await editor.locator('body').innerText(), logs: editorLogs,
  }, null, 2));
  throw error;
}
const editorState = await editor.evaluate(() => {
  const blocks = window.wp.data.select('core/block-editor').getBlocks();
  let count = 0, invalid = [], invalidDetails = [], names = [];
  function walk(items) { for (const b of items) {
    count++; names.push(b.name);
    if (b.isValid === false) {
      invalid.push(b.name);
      invalidDetails.push({ name: b.name, attributes: b.attributes, original: b.originalContent,
        expected: window.wp.blocks.getSaveContent(b.name, b.attributes, b.innerBlocks || []) });
    }
    walk(b.innerBlocks || []);
  } }
  walk(blocks);
  return { count, invalid, invalidDetails, rootNames: blocks.map(b=>b.name), customBlocks: names.filter(n=>!n.startsWith('core/')) };
});
if (editorState.count < 25) problems.push(`editor parsed too few native blocks: ${editorState.count}`);
if (editorState.invalid.length) problems.push(`editor invalid blocks: ${editorState.invalid.join(', ')}`);
if (editorState.customBlocks.length) problems.push(`editor non-core blocks: ${editorState.customBlocks.join(', ')}`);
if (editorState.invalid.length) {
  fs.writeFileSync(`${out}/verification.json`, JSON.stringify({ captures, classicRoundTrip, editorState, problems }, null, 2));
  await browser.close();
  throw new Error(`Native wp_block validation RED: ${editorState.invalid.join(', ')}`);
}
// Exercise the WordPress editorial lifecycle on the disposable DRAFT, not the
// published fixture. Saving and reopening must preserve native block validity.
if (await editor.evaluate(marker => window.wp.data.select('core/editor').getEditedPostContent().includes(marker), roundTripSentinel)) {
  throw new Error('Fresh Gutenberg save marker unexpectedly exists before editing');
}
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
  let valid = true, found = false, count = 0, names = [];
  function walk(list) {
    for (const b of list) {
      count++; names.push(b.name);
      if (b.isValid === false) valid = false;
      if (b.name === 'core/paragraph' && String(b.attributes?.content || '').includes(marker)) found = true;
      walk(b.innerBlocks || []);
    }
  }
  walk(blocks);
  return { valid, found, count, topLevel: blocks.length, rootNames: blocks.map(b => b.name), customBlocks: names.filter(n => !n.startsWith('core/')) };
}, roundTripSentinel);
if (!roundTrip.valid || !roundTrip.found || roundTrip.count !== editorState.count
  || JSON.stringify(roundTrip.rootNames) !== JSON.stringify(editorState.rootNames) || roundTrip.customBlocks.length) {
  problems.push(`draft editor save/reopen failed: ${JSON.stringify(roundTrip)}`);
}
await editor.screenshot({ path: `${out}/editor.png`, fullPage: false });
await editor.close();
await editorContext.close();
await browser.close();
fs.writeFileSync(`${out}/verification.json`, JSON.stringify({ mode: editorOnly ? 'editor-only' : 'full', runToken, captures, classicRoundTrip, classicPresentation, editorState, roundTrip, problems }, null, 2));
if (problems.length) { console.error('FAIL: Wellness 01 native WordPress QA\n' + problems.join('\n')); process.exit(1); }
console.log(`PASS: Wellness 01 ${editorOnly ? 'editor-only' : 'WordPress browser x4 + axe'} + Classic Text/Visual + native wp_block Gutenberg save/reopen; ${editorState.count} blocks`);
