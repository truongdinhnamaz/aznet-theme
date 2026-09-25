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
  '782x900': { width: 782, height: 900 },
  '390x844': { width: 390, height: 844 },
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

async function verifyHomepageHeroEditingBridge(page, viewportName) {
  await gotoCenter(page, 'homepage');
  const presetForm = page.locator('form.aznet-theme-panel').filter({ has: page.getByRole('heading', { name: 'Mẫu trang chủ' }) });
  const templateLibrary = presetForm.locator('[data-aznet-template-library]');
  if (await templateLibrary.count() !== 1) throw new Error('Template Library root missing');
  if (await templateLibrary.locator('[data-aznet-template-search]').count() !== 1) throw new Error('Template Library search missing');
  if (await templateLibrary.locator('[data-aznet-template-category]').count() !== 1) throw new Error('Template Library category filter missing');
  const lawPreset = presetForm.locator('input[name="aznet_theme_settings[homepage_preset]"][value="law-01"]');
  if (await lawPreset.count() !== 1) throw new Error('Law 01 Template Library card missing');
  await lawPreset.check();
  await Promise.all([
    page.waitForURL(/updated=1/, { timeout: 20000 }),
    presetForm.getByRole('button', { name: 'Lưu mẫu trang chủ' }).click(),
  ]);

  await gotoCenter(page, 'homepage');
  if (await page.locator('form.aznet-theme-homepage-hero-library').count()) throw new Error('Hero Library must not render inline inside Homepage Map');
  const designLink = page.getByRole('link', { name: 'Sửa Hero' }).first();
  if (await designLink.count() !== 1) throw new Error('Homepage Hero row must expose exactly one Sửa Hero path');
  const designHref = await designLink.getAttribute('href');
  if (!designHref || !designHref.includes('section=hero-library')) throw new Error('Sửa Hero does not route to dedicated Hero Library: ' + designHref);

  await designLink.click();
  await page.waitForURL(/section=hero-library/, { timeout: 20000 });
  const libraryForm = page.locator('form.aznet-theme-homepage-hero-library');
  if (await libraryForm.count() !== 1) throw new Error('Dedicated Hero Library form missing');
  if (await libraryForm.locator('input[name="homepage_hero_variant"]').count() !== 4) throw new Error('Hero Library must expose exactly four bounded variants');
  const backLink = page.getByRole('link', { name: /Quay lại Trang chủ/ }).first();
  if (await backLink.count() !== 1) throw new Error('Hero Library back-to-Homepage action missing');
  const backHref = await backLink.getAttribute('href');
  if (!backHref || !backHref.includes('section=homepage')) throw new Error('Hero Library back link mismatch: ' + backHref);
  return await checkLayoutAndA11y(page, viewportName + '-hero-library-' + (expectWoo ? 'woo' : 'clean'));
}

async function verifyHomepageTeamAuthoring(page, viewportName) {
  await gotoCenter(page, 'homepage');
  const sources = page.locator('#aznet-theme-homepage-sources');
  if (!(await sources.evaluate((node) => node.hasAttribute('open')))) await sources.locator('summary').click();
  const sourceForm = sources.locator('form.aznet-theme-panel').filter({ has: page.getByRole('heading', { name: 'Nguồn nội dung' }) });
  const teamSelect = sourceForm.locator('select[name="aznet_theme_settings[homepage_law01_team_page]"]');
  if (await teamSelect.count() !== 1) throw new Error('Law 01 Team source selector missing');
  await teamSelect.selectOption({ label: 'R5 Team Parent' });
  await Promise.all([
    page.waitForURL(/updated=1/, { timeout: 20000 }),
    sourceForm.getByRole('button', { name: 'Lưu nguồn nội dung' }).click(),
  ]);
  await gotoCenter(page, 'homepage');
  const team = page.locator('#homepage-team');
  if (await team.count() !== 1) throw new Error('Homepage Team authoring panel missing');
  const names = (await team.locator('[data-team-member-name]').allTextContents()).map((value) => value.trim());
  const expected = ['R5 Team Member A', 'R5 Team Member B', 'R5 Team Member C', 'R5 Team Member D'];
  if (JSON.stringify(names) !== JSON.stringify(expected)) throw new Error('Homepage Team member order mismatch: ' + JSON.stringify(names));
  if (await team.getByText('Thêm nhân sự', { exact: true }).count() !== 1) throw new Error('Thêm nhân sự action missing');
  if (await team.getByRole('link', { name: 'Xem tất cả trên website' }).count() !== 1) throw new Error('Xem tất cả trên website action missing');
  const firstQuickEdit = team.locator('.aznet-theme-homepage-quick-edit-panel').first();
  await firstQuickEdit.locator('summary').click();
  if (await firstQuickEdit.locator('input[name="homepage_source_title"]').inputValue() !== expected[0]) throw new Error('Team quick edit title is not WordPress child Page title');
  if (!(await firstQuickEdit.locator('textarea[name="homepage_source_excerpt"]').inputValue()).includes('Vai trò A')) throw new Error('Team quick edit role is not WordPress child Page excerpt');
  if (await firstQuickEdit.locator('input[name="homepage_featured_image_id"]').count() !== 1) throw new Error('Team quick edit portrait field missing');
  const publicPage = await page.context().newPage();
  try {
    await publicPage.goto(baseUrl + '/', { waitUntil: 'networkidle' });
    const publicNames = (await publicPage.locator('.aznet-theme-law01-profile__members .aznet-theme-team-card__name').allTextContents()).map((value) => value.trim());
    if (JSON.stringify(publicNames) !== JSON.stringify(names)) {
      throw new Error('Team admin/Homepage member order mismatch: admin=' + JSON.stringify(names) + ' public=' + JSON.stringify(publicNames));
    }
    if (await publicPage.locator('.aznet-theme-law01-profile__members .aznet-theme-team-card__media img').count() !== 0) {
      throw new Error('R5 text-only Team fixture unexpectedly rendered portrait media');
    }
  } finally {
    await publicPage.close();
  }

  const addDetails = team.locator('.aznet-theme-homepage-team-create');
  await addDetails.locator('summary').focus();
  if (!(await addDetails.locator('summary').evaluate((node) => document.activeElement === node))) throw new Error('Thêm nhân sự is not keyboard focusable');
  return await checkLayoutAndA11y(page, viewportName + '-homepage-team-' + (expectWoo ? 'woo' : 'clean'));
}
async function verifyHomepageMap(page, viewportName) {
  await gotoCenter(page, 'homepage');
  const map = page.locator('#aznet-theme-homepage-map');
  if (await map.count() !== 1) throw new Error('Homepage Map is missing for Law 01');
  if ((await map.getByRole('heading', { name: 'Trang chủ đang hiển thị' }).count()) !== 1) throw new Error('Homepage Map heading missing');

  const adminKeys = await map.locator('[data-surface-key]').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('data-surface-key')));
  if (!adminKeys.length || adminKeys[0] !== 'hero') throw new Error('Homepage Map does not start with effective Hero surface: ' + JSON.stringify(adminKeys));
  if (await map.getByText('READY', { exact: true }).count()) throw new Error('raw READY leaked into primary Homepage Map');
  if (await map.getByText('DRAFT', { exact: true }).count()) throw new Error('raw DRAFT leaked into primary Homepage Map');
  if (await map.getByRole('link', { name: 'Đổi nguồn' }).count()) throw new Error('Đổi nguồn must stay in advanced settings');

  const advanced = page.locator('#aznet-theme-homepage-sources');
  if (await advanced.count() !== 1) throw new Error('Homepage advanced source disclosure missing');
  if (!((await advanced.locator('summary').textContent()) || '').includes('Nguồn & cài đặt nâng cao')) throw new Error('Homepage advanced source disclosure label mismatch');

  const publicPage = await page.context().newPage();
  try {
    await publicPage.goto(baseUrl + '/', { waitUntil: 'networkidle' });
    const publicKeys = await publicPage.locator('[data-aznet-homepage-surface]').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('data-aznet-homepage-surface')));
    if (JSON.stringify(adminKeys) !== JSON.stringify(publicKeys)) {
      throw new Error('Homepage admin/frontend surface order mismatch: admin=' + JSON.stringify(adminKeys) + ' public=' + JSON.stringify(publicKeys));
    }
    for (const key of adminKeys) {
      const card = map.locator('[data-surface-key="' + key + '"]');
      if (await card.getByRole('link', { name: 'Xem trên trang chủ' }).count() !== 1) {
        throw new Error('Homepage Map deep link missing for ' + key);
      }
    }
  } finally {
    await publicPage.close();
  }

  return await checkLayoutAndA11y(page, viewportName + '-homepage-map-' + (expectWoo ? 'woo' : 'clean'));
}

async function verifySystemHealth(page) {
  await gotoCenter(page, 'system-health');
  const text = await page.locator('.aznet-theme-control-center').innerText();
  for (const needle of ['environment / wordpress', 'theme / version', 'Standalone Core', 'READY', 'Optional Integrations', 'WooCommerce', 'ConvertFlow', 'Support Snapshot']) {
    if (!text.includes(needle)) throw new Error(`System Health missing ${needle}`);
  }
  const expectedWooText = expectWoo ? 'Available' : 'Not present';
  const wooRow = page.locator('tr', { hasText: 'WooCommerce' });
  if (!(await wooRow.count()) || !(await wooRow.innerText()).includes(expectedWooText)) throw new Error(`System Health WooCommerce status missing ${expectedWooText}`);
  const convertFlowRow = page.locator('tr', { hasText: 'ConvertFlow' });
  if (!(await convertFlowRow.count()) || !(await convertFlowRow.innerText()).includes('Unknown')) throw new Error('System Health ConvertFlow status must remain Unknown');

  const snapshot = page.locator('textarea[readonly]');
  const parsed = JSON.parse(await snapshot.inputValue());
  const expectedWoo = expectWoo ? 'available' : 'not_present';
  if (parsed.product !== 'aznet-theme' || parsed.report?.standalone_core?.status !== 'ready') throw new Error('Support Snapshot Core markers invalid');
  if (parsed.report?.optional_integrations?.woocommerce !== expectedWoo) throw new Error(`Support Snapshot WooCommerce status mismatch: ${parsed.report?.optional_integrations?.woocommerce}`);
  if (parsed.report?.optional_integrations?.convertflow !== 'unknown') throw new Error('Support Snapshot ConvertFlow marker invalid');
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
      const homepageHero = await verifyHomepageHeroEditingBridge(page, viewportName);
      const homepageTeam = await verifyHomepageTeamAuthoring(page, viewportName);
      const homepageMap = await verifyHomepageMap(page, viewportName);
      await verifySystemHealth(page);
      const layout = await checkLayoutAndA11y(page, viewportName + '-' + (expectWoo ? 'woo' : 'clean'));
      results.push({ viewportName, homepageHero, homepageTeam, homepageMap, ...layout });
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
