import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.R5_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const adminUser = process.env.R5_ADMIN_USER || 'admin';
const adminPass = process.env.R5_ADMIN_PASS || '';
const stateDir = process.env.R5_STATE_DIR || '/tmp/r5-control-center';
const expectWoo = process.env.R5_EXPECT_WOO === '1';
const outputDir = path.join(stateDir, expectWoo ? 'woo' : 'clean');
fs.mkdirSync(outputDir, { recursive: true });

if (!adminPass) throw new Error('R5_ADMIN_PASS is required');

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x768': { width: 1024, height: 768 },
};

const results = [];
const failures = [];

async function login(page) {
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(adminUser);
  await page.locator('#user_pass').fill(adminPass);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 20000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function gotoCenter(page, section = 'overview') {
  const suffix = section === 'overview' ? '' : `&section=${section}`;
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme${suffix}`, { waitUntil: 'domcontentloaded' });
  await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 20000 });
}

async function checkLayoutAndA11y(page, label) {
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) throw new Error(`${label}: horizontal overflow ${overflow}px`);

  const tab = page.locator('.aznet-theme-control-center .nav-tab').first();
  await tab.focus();
  const focus = await tab.evaluate((node) => {
    const style = getComputedStyle(node);
    return {
      active: document.activeElement === node,
      outline: style.outlineStyle !== 'none' && Number.parseFloat(style.outlineWidth || '0') > 0,
      shadow: Boolean(style.boxShadow && style.boxShadow !== 'none'),
    };
  });
  if (!focus.active || (!focus.outline && !focus.shadow)) {
    throw new Error(`${label}: visible keyboard focus indicator missing ${JSON.stringify(focus)}`);
  }

  const axe = await new AxeBuilder({ page }).include('.aznet-theme-control-center').analyze();
  const serious = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact || ''));
  fs.writeFileSync(path.join(outputDir, `${label}-axe.json`), JSON.stringify(axe, null, 2));
  if (serious.length) {
    throw new Error(`${label}: axe critical/serious violations ${serious.map((item) => item.id).join(', ')}`);
  }

  return { overflow, focus };
}

async function verifyNativeLinks(page) {
  const hrefs = await page.locator('.aznet-theme-grid .aznet-theme-card').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('href') || ''));
  if (!hrefs.some((href) => href.includes('customize.php?autofocus%5Bcontrol%5D=custom_logo') || href.includes('customize.php?autofocus[control]=custom_logo'))) {
    throw new Error('overview: native Custom Logo link missing');
  }
  if (!hrefs.some((href) => href.includes('/wp-admin/nav-menus.php'))) throw new Error('overview: native Menu link missing');
  if (!hrefs.some((href) => href.includes('/wp-admin/post-new.php?post_type=page'))) throw new Error('overview: native Page/Patterns link missing');
}

async function saveQuickSetup(page) {
  await gotoCenter(page);
  const form = page.locator('form.aznet-theme-panel').filter({ has: page.getByRole('heading', { name: 'Quick Setup' }) });
  await form.locator('select[name="aznet_theme_settings[visual_preset]"]').selectOption('editorial');
  await form.locator('select[name="aznet_theme_settings[header_preset]"]').selectOption('compact');
  await Promise.all([
    page.waitForURL(/page=aznet-theme.*updated=1|updated=1.*page=aznet-theme/, { timeout: 20000 }),
    form.getByRole('button', { name: 'Lưu Quick Setup' }).click(),
  ]);

  await gotoCenter(page, 'design');
  if (await page.locator('select[name="aznet_theme_settings[visual_preset]"]').inputValue() !== 'editorial') throw new Error('Quick Setup did not persist visual preset');
  await gotoCenter(page, 'header');
  if (await page.locator('select[name="aznet_theme_settings[header_preset]"]').inputValue() !== 'compact') throw new Error('Quick Setup did not persist header preset');
}

async function saveHeaderBooleans(page) {
  await gotoCenter(page, 'header');
  const search = page.locator('input[type="checkbox"][name="aznet_theme_settings[header_search]"]');
  const utilities = page.locator('input[type="checkbox"][name="aznet_theme_settings[header_utilities]"]');
  await search.uncheck();
  await utilities.check();
  await Promise.all([
    page.waitForURL(/updated=1/, { timeout: 20000 }),
    page.getByRole('button', { name: 'Lưu thiết lập' }).click(),
  ]);
  await gotoCenter(page, 'header');
  if (await search.isChecked()) throw new Error('Header search false value did not persist');
  if (!(await utilities.isChecked())) throw new Error('Header utilities true value did not persist');
}

async function exportImport(page, viewportName) {
  await gotoCenter(page);
  const exportForm = page.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_theme_export_settings"]') });
  const [download] = await Promise.all([
    page.waitForEvent('download'),
    exportForm.getByRole('button', { name: 'Xuất JSON' }).click(),
  ]);
  const downloadPath = await download.path();
  if (!downloadPath) throw new Error('Export did not produce a downloadable file');
  const exported = JSON.parse(fs.readFileSync(downloadPath, 'utf8'));
  if (exported.product !== 'aznet-theme' || exported.schema_version !== 1 || !exported.settings) throw new Error('Export payload markers invalid');
  for (const forbidden of ['password', 'secret', 'api_key', 'token', 'product_data', 'journey', 'rootprofile', 'cart', 'order']) {
    if (JSON.stringify(exported).toLowerCase().includes(`"${forbidden}"`)) throw new Error(`Export leaked forbidden key ${forbidden}`);
  }

  const importPath = path.join(outputDir, `${viewportName}-import.json`);
  fs.writeFileSync(importPath, JSON.stringify({
    product: 'aznet-theme',
    schema_version: 1,
    theme_version: exported.theme_version,
    settings: {
      ...exported.settings,
      visual_preset: 'commerce',
      header_preset: 'standard',
      header_search: true,
      unknown_key: 'must-drop',
    },
  }));

  await gotoCenter(page);
  const importForm = page.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_theme_import_settings"]') });
  await importForm.locator('input[type="file"]').setInputFiles(importPath);
  await Promise.all([
    page.waitForURL(/imported=1/, { timeout: 20000 }),
    importForm.getByRole('button', { name: 'Nhập JSON' }).click(),
  ]);
  await gotoCenter(page, 'design');
  if (await page.locator('select[name="aznet_theme_settings[visual_preset]"]').inputValue() !== 'commerce') throw new Error('Imported visual preset did not persist');
  await gotoCenter(page, 'header');
  if (await page.locator('select[name="aznet_theme_settings[header_preset]"]').inputValue() !== 'standard') throw new Error('Imported header preset did not persist');
  if (!(await page.locator('input[type="checkbox"][name="aznet_theme_settings[header_search]"]').isChecked())) throw new Error('Imported boolean setting did not persist');
}

async function resetSettings(page) {
  await gotoCenter(page);
  const resetForm = page.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_theme_reset_settings"]') });
  const resetButton = resetForm.getByRole('button', { name: 'Đặt lại' });
  const confirm = resetForm.locator('input[type="checkbox"][name="confirm_reset"]');
  if (await confirm.isChecked()) throw new Error('Reset confirmation must start unchecked');
  await confirm.check();
  await Promise.all([
    page.waitForURL(/reset=1/, { timeout: 20000 }),
    resetButton.click(),
  ]);
  await gotoCenter(page, 'design');
  if (await page.locator('select[name="aznet_theme_settings[visual_preset]"]').inputValue() !== 'default') throw new Error('Reset did not restore default visual preset');
  await gotoCenter(page, 'header');
  if (await page.locator('select[name="aznet_theme_settings[header_preset]"]').inputValue() !== 'standard') throw new Error('Reset did not restore default header preset');
}

async function verifySystemHealth(page) {
  await gotoCenter(page, 'system-health');
  const text = await page.locator('.aznet-theme-control-center').innerText();
  for (const needle of ['environment / wordpress', 'theme / version', 'capabilities / convertflow', 'unknown', 'Support Snapshot']) {
    if (!text.includes(needle)) throw new Error(`System Health missing ${needle}`);
  }
  const snapshot = page.locator('textarea[readonly]');
  const snapshotValue = await snapshot.inputValue();
  const parsed = JSON.parse(snapshotValue);
  if (parsed.product !== 'aznet-theme' || parsed.report?.capabilities?.convertflow !== 'unknown') throw new Error('Support Snapshot markers invalid');
}

async function verifyCommerceCapability(page) {
  await gotoCenter(page);
  const commerceCount = await page.locator('.nav-tab', { hasText: 'Commerce' }).count();
  if (expectWoo && commerceCount !== 1) throw new Error('Commerce tab missing with public Woo capability');
  if (!expectWoo && commerceCount !== 0) throw new Error('Commerce tab visible without Woo capability');
  if (expectWoo) {
    await gotoCenter(page, 'commerce');
    await page.locator('select[name="aznet_theme_settings[woo_catalog_preset]"]').selectOption('compact-grid');
    await Promise.all([
      page.waitForURL(/updated=1/, { timeout: 20000 }),
      page.getByRole('button', { name: 'Lưu thiết lập' }).click(),
    ]);
    await gotoCenter(page, 'commerce');
    if (await page.locator('select[name="aznet_theme_settings[woo_catalog_preset]"]').inputValue() !== 'compact-grid') throw new Error('Commerce setting did not persist');
  }
}

const browser = await chromium.launch({ headless: true });
try {
  const authContext = await browser.newContext({ viewport: viewports['1440x1000'] });
  const authPage = await authContext.newPage();
  await login(authPage);
  const authState = await authContext.storageState();
  await authContext.close();

  for (const [viewportName, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport, storageState: authState });
    const page = await context.newPage();
    try {
      await gotoCenter(page);
      if ((await page.locator('h1').first().textContent())?.trim() !== 'AZnet Theme') throw new Error('Control Center heading missing');
      if (await page.locator('#toplevel_page_aznet-theme').count() !== 1) throw new Error('AZnet Theme admin menu missing');
      if (await page.locator('link#aznet-theme-control-center-css').count() !== 1) throw new Error('Control Center stylesheet missing on its screen');
      await verifyNativeLinks(page);
      await verifyCommerceCapability(page);
      await saveQuickSetup(page);
      await saveHeaderBooleans(page);
      await exportImport(page, viewportName);
      await resetSettings(page);
      await verifySystemHealth(page);
      const layout = await checkLayoutAndA11y(page, `${viewportName}-${expectWoo ? 'woo' : 'clean'}`);
      results.push({ viewportName, ...layout });
    } catch (error) {
      failures.push(`${viewportName}: ${error instanceof Error ? error.message : String(error)}`);
      await page.screenshot({ path: path.join(outputDir, `${viewportName}-failure.png`), fullPage: true }).catch(() => {});
    } finally {
      await context.close();
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify({ expectWoo, results, failures }, null, 2));
if (failures.length) {
  console.error(failures.join('\n'));
  process.exit(1);
}
console.log(`PASS: R5 authenticated Control Center ${expectWoo ? 'Woo' : 'clean'} matrix ${results.length}/${results.length}`);
