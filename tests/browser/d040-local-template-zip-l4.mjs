import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const base = (process.env.D040_L4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const output = process.env.D040_L4_OUT_DIR || '/tmp/d040-local-template-l4';
const fixtureZip = path.join(output, 'curtain-01-1.0.2-browser.zip');
fs.mkdirSync(output, { recursive: true });

// Reuse the actual bundled Curtain 01 manifest and CSS; test only the local ZIP
// upload/rollback UI, not an invented external Distribution Service contract.
const makeFixturePhp = String.raw`
$root = getenv('GITHUB_WORKSPACE');
$zipPath = getenv('D040_L4_ZIP');
$m = require $root . '/inc/theme/templates/curtain-01/manifest.php';
$m['version'] = '1.0.2';
$m['assets']['homepage'] = [ $m['assets']['homepage'][0] ];
$cssPath = 'assets/css/components/homepage-curtain-01.css';
$css = file_get_contents($root . '/' . $cssPath);
if (!is_string($css) || '' === $css) { fwrite(STDERR, 'Fixture CSS missing'); exit(2); }
$p = [
  'package_contract_version' => 1,
  'requires' => ['theme' => '1.3.110', 'wordpress' => '6.9', 'php' => '8.1'],
  'files' => [$cssPath => hash('sha256', $css)],
];
$zip = new ZipArchive();
if (true !== $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) { exit(3); }
$zip->addFromString('manifest.json', json_encode($m, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
$zip->addFromString('package.json', json_encode($p, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
$zip->addFromString($cssPath, $css);
$zip->close();
`;
execFileSync('php', ['-r', makeFixturePhp], {
  env: { ...process.env, D040_L4_ZIP: fixtureZip },
  stdio: 'pipe',
});
const exactSha = crypto.createHash('sha256').update(fs.readFileSync(fixtureZip)).digest('hex');
const browser = await chromium.launch({ headless: true });
const errors = [];
const page = await browser.newPage({ viewport: { width: 390, height: 844 } });
page.on('pageerror', error => errors.push(String(error)));
function assert(condition, message) {
  if (!condition) throw new Error('D040 L4: ' + message);
}
const outputRecord = { commit: process.env.GITHUB_SHA || '', fixtureSha256: exactSha, viewports: {}, installed: false, rolledBack: false, shaRejected: false, accessibility: null };
try {
  await page.goto(base + '/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('local-test-only-password');
  await Promise.all([
    page.waitForURL(url => !url.pathname.endsWith('/wp-login.php'), { timeout: 20000 }),
    page.locator('#wp-submit').click(),
  ]);
  const adminUrl = base + '/wp-admin/admin.php?page=aznet-theme&section=homepage';
  await page.goto(adminUrl, { waitUntil: 'domcontentloaded' });
  const panel = page.locator('.aznet-theme-local-template-packages');
  await panel.waitFor({ state: 'visible', timeout: 20000 });

  for (const [name, viewport] of Object.entries({ mobile: { width: 390, height: 844 }, narrow: { width: 320, height: 720 } })) {
    await page.setViewportSize(viewport);
    const geometry = await page.evaluate(() => ({
      viewport: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
      panelWidth: document.querySelector('.aznet-theme-local-template-packages')?.getBoundingClientRect().width ?? 0,
    }));
    assert(geometry.scrollWidth - geometry.viewport <= 1, name + ' overflow: ' + JSON.stringify(geometry));
    outputRecord.viewports[name] = geometry;
    await panel.screenshot({ path: path.join(output, name + '-package-manager.png') });
  }
  await page.setViewportSize({ width: 390, height: 844 });
  const a11y = await new AxeBuilder({ page }).include('.aznet-theme-local-template-packages').analyze();
  const serious = a11y.violations.filter(v => ['serious','critical'].includes(v.impact || ''));
  outputRecord.accessibility = { serious: serious.length, ids: serious.map(v => v.id) };
  assert(serious.length === 0, 'package manager serious accessibility: ' + JSON.stringify(serious.map(v => v.id)));

  const upload = panel.locator('form').first();
  assert(await upload.count() === 1, 'upload form missing');
  await upload.locator('input[name="template_package_zip"]').setInputFiles(fixtureZip);
  await upload.locator('input[name="template_package_sha256"]').fill(exactSha);
  await upload.locator('input[name="template_install_confirm"]').check();
  await Promise.all([
    page.waitForURL(url => url.searchParams.get('template_package_result') === 'installed', { timeout: 20000 }),
    upload.locator('input[type="submit"]').click(),
  ]);
  const installed = page.locator('.aznet-theme-local-template-packages__installed');
  assert(await installed.count() === 1, 'installed package missing from UI');
  assert((await installed.innerText()).includes('curtain-01') && (await installed.innerText()).includes('1.0.2'), 'installed version label mismatch');
  outputRecord.installed = true;
  await page.locator('.aznet-theme-local-template-packages').screenshot({ path: path.join(output, 'installed-package.png') });

  const rollback = installed.locator('form');
  await rollback.locator('input[name="template_rollback_confirm"]').check();
  await Promise.all([
    page.waitForURL(url => url.searchParams.get('template_package_result') === 'rolled_back', { timeout: 20000 }),
    rollback.locator('input[type="submit"]').click(),
  ]);
  assert(await page.locator('.aznet-theme-local-template-packages__installed').count() === 0, 'rollback must restore bundled template');
  outputRecord.rolledBack = true;

  const retry = page.locator('.aznet-theme-local-template-packages form').first();
  await retry.locator('input[name="template_package_zip"]').setInputFiles(fixtureZip);
  await retry.locator('input[name="template_package_sha256"]').fill('0'.repeat(64));
  await retry.locator('input[name="template_install_confirm"]').check();
  await Promise.all([
    page.waitForURL(url => url.searchParams.get('template_package_result') === 'zip_integrity', { timeout: 20000 }),
    retry.locator('input[type="submit"]').click(),
  ]);
  assert(await page.locator('.aznet-theme-local-template-packages__installed').count() === 0, 'bad ZIP hash activated invalid version');
  outputRecord.shaRejected = true;
  assert(errors.length === 0, 'uncaught page errors: ' + JSON.stringify(errors));
  fs.writeFileSync(path.join(output, 'summary.json'), JSON.stringify(outputRecord, null, 2));
  console.log('PASS D040 L4: real WP-admin ZIP upload, version display, rollback, SHA rejection, 390/320 responsive and axe');
} finally {
  await browser.close();
}
