import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

// QA-only A/B control for issue #455. Both isolated WordPress installations
// use identical saved native R2 pattern content and the same Classic Editor UI.
// This does not replace the retained R2 frontend/editor/a11y acceptance suite.
const baseUrl = (process.env.R2_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.R2_AB_DIR || '/tmp/r2-woo-editor-ab';
const fixtureId = process.env.R2_FIXTURE_ID || '';
const arm = process.env.R2_AB_ARM || '';
const repeat = process.env.R2_AB_REPEAT || '';
if (!fixtureId || !['aznet-theme', 'twentytwentyfive'].includes(arm) || !['1', '2'].includes(repeat)) {
  throw new Error('Missing or unsupported R2 A/B fixture, theme arm or repeat');
}
fs.mkdirSync(outputDir, { recursive: true });

const summary = {
  arm,
  repeat,
  fixtureId,
  fixtureSha: process.env.R2_FIXTURE_SHA || null,
  browser: 'Chromium',
  viewport: '1440x1000',
  editor: { status: 'failed', mode: null, patternMarkupPresent: false, blockEditorPresent: null, error: null },
  wooAdminRequests: [],
  pageErrors: [],
};
const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
page.on('request', (request) => {
  if (request.url().includes('/wp-json/wc-admin/')) {
    summary.wooAdminRequests.push({ method: request.method(), endpoint: new URL(request.url()).pathname });
  }
});
page.on('pageerror', (error) => summary.pageErrors.push(error.message));

try {
  const login = await page.goto(baseUrl + '/wp-login.php', { waitUntil: 'domcontentloaded' });
  if (!login || login.status() !== 200) throw new Error('WordPress login did not return HTTP 200');
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('r2-browser-password');
  await Promise.all([
    page.waitForURL(/wp-admin/),
    page.locator('#wp-submit').click(),
  ]);

  // Both arms visit the same real WooCommerce admin UI before opening the
  // native Page editor. Earlier A/B artifacts had ZERO wc-admin requests,
  // unlike the historical SIGSEGV runs, making that first control weak.
  // We visit only normal admin screens; no private REST calls or data writes.
  const wooAdminPage = await page.goto(
    baseUrl + '/wp-admin/admin.php?page=wc-admin', { waitUntil: 'domcontentloaded' },
  );
  summary.wooAdminPageStatus = wooAdminPage?.status() ?? null;
  if (!wooAdminPage || wooAdminPage.status() !== 200) {
    throw new Error('WooCommerce admin UI unavailable: HTTP ' + summary.wooAdminPageStatus);
  }
  await page.waitForTimeout(3500);
  summary.wooAdminRequestsBeforeEditor = summary.wooAdminRequests.length;

  const editUrl = baseUrl + '/wp-admin/post.php?post=' + fixtureId + '&action=edit';
  const response = await page.goto(editUrl, { waitUntil: 'domcontentloaded' });
  if (!response || response.status() !== 200) {
    throw new Error('WordPress editor did not return HTTP 200: ' + (response?.status() ?? 'missing'));
  }
  await page.locator('#post').waitFor({ state: 'visible', timeout: 30000 });
  await page.locator('#content').waitFor({ state: 'attached', timeout: 30000 });
  const content = await page.locator('#content').inputValue();
  summary.editor.mode = 'classic';
  summary.editor.patternMarkupPresent =
    content.includes('aznet-theme-pattern--hero-split') &&
    content.includes('aznet-theme-pattern--content-faq');
  summary.editor.blockEditorPresent =
    await page.locator('.edit-post-layout, .interface-interface-skeleton').count();
  if (!summary.editor.patternMarkupPresent) {
    throw new Error('Classic Editor lost representative native R2 block content');
  }
  if (summary.editor.blockEditorPresent !== 0) {
    throw new Error('Native Page unexpectedly exposed Block Editor');
  }
  // Preserve the original Classic Editor assertions; then validate that this
  // experiment really exercised the Woo admin request workload instead of
  // silently claiming an unrelated editor-only PASS.
  await page.waitForTimeout(1000);
  if (summary.wooAdminRequests.length < 1) {
    throw new Error('A/B INCONCLUSIVE: no WooCommerce admin REST requests observed');
  }
  await page.screenshot({ path: path.join(outputDir, 'editor-classic.png'), fullPage: true });
  summary.editor.status = 'passed';
  console.log('PASS: R2 Woo editor A/B ' + arm + ' repeat ' + repeat);
} catch (error) {
  summary.editor.error = error instanceof Error ? error.message : String(error);
  await page.screenshot({ path: path.join(outputDir, 'editor-classic-failure.png'), fullPage: true }).catch(() => {});
  console.error('RED: R2 Woo editor A/B ' + arm + ' repeat ' + repeat + ': ' + summary.editor.error);
} finally {
  fs.writeFileSync(path.join(outputDir, 'editor-summary.json'), JSON.stringify(summary, null, 2));
  await context.close();
  await browser.close();
}
if (summary.editor.status !== 'passed') process.exitCode = 1;
