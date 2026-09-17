import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const targetZip = process.env.TARGET_THEME_ZIP || '';
const rollbackZip = process.env.ROLLBACK_THEME_ZIP || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-v1-3-production-deploy';

const TARGET_VERSION = '1.3.0';
const PREVIOUS_VERSION = '1.2.0';
const PHONE_DISPLAY = '024 3716 4123';
const PHONE_HREF = 'tel:+842437164123';
const MENU_NAME = 'T\u00e2m \u0110\u1ee9c - Li\u00ean h\u1ec7 ch\u00ednh th\u1ee9c';
const UTILITY_LOCATION = 'header-utility';

fs.mkdirSync(stateDir, { recursive: true });

const state = {
  scope: 'owner-approved-production-theme-deployment',
  target: `${baseUrl}/`,
  target_version: TARGET_VERSION,
  previous_version: PREVIOUS_VERSION,
  before_state: {},
  after_state: {},
  mutations: [],
  rollback: [],
  blocked: [],
  verification: {},
  error: null,
};

function writeState() {
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(state, null, 2));
}

async function login(page) {
  if (!adminUser || !adminPass) throw new Error('Required pilot credentials are unavailable');
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('#user_login').fill(adminUser);
  await page.locator('#user_pass').fill(adminPass);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 30000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function supportSnapshot(page) {
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=system-health`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const box = page.locator('textarea[readonly]');
  if ((await box.count()) !== 1) throw new Error('System Health support snapshot unavailable');
  return JSON.parse(await box.inputValue());
}

async function publicState(page, viewport) {
  await page.setViewportSize(viewport);
  const response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  const status = response?.status() || 0;
  const data = await page.evaluate(() => {
    const root = document.documentElement;
    const body = document.body;
    const logoSelector = 'img.aznet-theme-site-header__logo, img.custom-logo';
    const logo = document.querySelector(logoSelector);
    const telLinks = Array.from(document.querySelectorAll('a[href^="tel:"]')).map((node) => ({
      href: node.getAttribute('href') || '',
      text: (node.textContent || '').trim(),
      visible: Boolean(node.getClientRects().length),
    }));
    return {
      main_count: document.querySelectorAll('main').length,
      h1_count: document.querySelectorAll('h1').length,
      h1_text: (document.querySelector('h1')?.textContent || '').trim(),
      overflow: Math.max(0, Math.ceil(Math.max(root.scrollWidth, body?.scrollWidth || 0) - root.clientWidth)),
      logo_count: document.querySelectorAll(logoSelector).length,
      logo_src: logo?.getAttribute('src') || '',
      tel_links: telLinks,
    };
  });
  return { viewport, status, ...data };
}

async function locationState(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const select = page.locator(`select[name="menu-locations[${UTILITY_LOCATION}]"]`);
  if ((await select.count()) !== 1) return { registered: false, menu_id: null };
  return { registered: true, menu_id: await select.inputValue() };
}

async function resolveApprovedPhoneMenuFromLocation(page, location) {
  if (!location.registered) throw new Error(`${UTILITY_LOCATION} is not registered`);
  const menuId = Number(location.menu_id || 0);
  if (!Number.isInteger(menuId) || menuId <= 0) throw new Error(`Invalid ${UTILITY_LOCATION} menu id: ${location.menu_id}`);

  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${menuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const menuNameField = page.locator('#menu-name');
  if ((await menuNameField.count()) !== 1) throw new Error(`Menu ${menuId} edit surface unavailable`);
  const menuName = await menuNameField.inputValue();
  if (menuName !== MENU_NAME) throw new Error(`Approved phone menu name drift: ${menuName}`);

  const rows = page.locator('#menu-to-edit .menu-item');
  const items = [];
  for (let index = 0; index < await rows.count(); index += 1) {
    const row = rows.nth(index);
    const titleField = row.locator('input.edit-menu-item-title');
    const urlField = row.locator('input.edit-menu-item-url');
    items.push({
      title: (await titleField.count()) ? await titleField.inputValue() : '',
      href: (await urlField.count()) ? await urlField.inputValue() : '',
    });
  }
  const exact = items.length === 1 && items[0]?.title === PHONE_DISPLAY && items[0]?.href === PHONE_HREF;
  if (!exact) throw new Error(`Approved phone menu drift: ${JSON.stringify(items)}`);
  return { menu_id: menuId, menu_name: menuName, item_count: items.length, items };
}

async function openUploadThemeForm(page) {
  await page.goto(`${baseUrl}/wp-admin/theme-install.php?upload`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const input = page.locator('#themezip');
  if ((await input.count()) === 1 && await input.isVisible()) return input;
  const toggle = page.locator('.upload-view-toggle').first();
  if ((await toggle.count()) === 1) await toggle.click();
  await input.waitFor({ state: 'visible', timeout: 10000 });
  return input;
}

async function replaceThemeFromZip(page, zipPath) {
  if (!zipPath || !fs.existsSync(zipPath)) throw new Error(`Theme package missing: ${zipPath}`);
  const input = await openUploadThemeForm(page);
  await input.setInputFiles(zipPath);
  const submit = page.locator('#install-theme-submit');
  await submit.waitFor({ state: 'visible', timeout: 10000 });
  await submit.click();
  await page.waitForLoadState('domcontentloaded');

  const overwrite = page.locator('.update-from-upload-overwrite');
  if ((await overwrite.count()) === 1) {
    await overwrite.click();
    await page.waitForLoadState('domcontentloaded');
  }

  await page.waitForTimeout(1500);
}

async function verifyPublicMatrix(page) {
  const desktop = await publicState(page, { width: 1440, height: 1000 });
  const mobile = await publicState(page, { width: 390, height: 844 });
  for (const item of [desktop, mobile]) {
    if (item.status !== 200) throw new Error(`Public HTTP status ${item.status} at ${item.viewport.width}px`);
    if (item.main_count !== 1) throw new Error(`Expected one main landmark at ${item.viewport.width}px, got ${item.main_count}`);
    if (item.h1_count !== 1) throw new Error(`Expected one H1 at ${item.viewport.width}px, got ${item.h1_count}`);
    if (item.overflow > 1) throw new Error(`Horizontal overflow ${item.overflow}px at ${item.viewport.width}px`);
    if (item.logo_count < 1 || !item.logo_src) throw new Error(`Approved logo missing at ${item.viewport.width}px`);
    if (!item.tel_links.some((link) => link.href === PHONE_HREF)) throw new Error(`Approved phone link missing at ${item.viewport.width}px`);
  }
  if (!desktop.tel_links.some((link) => link.href === PHONE_HREF && link.visible)) {
    throw new Error('Desktop phone link is not visible');
  }
  return { desktop, mobile };
}

const browser = await chromium.launch({ headless: true });
let targetApplied = false;
let beforeVersion = null;
let approvedMenu = null;
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const beforeSupport = await supportSnapshot(page);
  beforeVersion = beforeSupport?.report?.theme?.version || null;
  const beforePublic = await publicState(page, { width: 1440, height: 1000 });
  const previousLocation = await locationState(page);
  approvedMenu = await resolveApprovedPhoneMenuFromLocation(page, previousLocation);
  const precondition_header_utility_retained = previousLocation.registered
    && previousLocation.menu_id === String(approvedMenu.menu_id);

  state.before_state = {
    theme_version: beforeVersion,
    standalone_core: beforeSupport?.report?.standalone_core || null,
    public: beforePublic,
    approved_phone_menu: approvedMenu,
    header_utility: previousLocation,
  };
  state.verification.precondition_header_utility_retained = precondition_header_utility_retained;
  writeState();

  if (![PREVIOUS_VERSION, TARGET_VERSION].includes(beforeVersion)) {
    throw new Error(`Precondition drift: expected Theme ${PREVIOUS_VERSION} or ${TARGET_VERSION}, got ${beforeVersion}`);
  }
  if (beforeSupport?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Precondition drift: Standalone Core not ready: ${JSON.stringify(beforeSupport?.report?.standalone_core || null)}`);
  }
  if (!precondition_header_utility_retained) {
    throw new Error(`Precondition drift: ${UTILITY_LOCATION} is not retained on the approved menu`);
  }
  if (beforePublic.status !== 200 || beforePublic.main_count !== 1 || beforePublic.h1_count !== 1 || beforePublic.overflow > 1) {
    throw new Error(`Pre-deploy public baseline failed: ${JSON.stringify(beforePublic)}`);
  }
  if (beforePublic.logo_count < 1 || !beforePublic.logo_src) throw new Error('Pre-deploy approved logo is missing');
  if (!beforePublic.tel_links.some((link) => link.href === PHONE_HREF)) throw new Error('Pre-deploy approved phone link is missing');

  if (beforeVersion === PREVIOUS_VERSION) {
    await replaceThemeFromZip(page, targetZip);
    targetApplied = true;
    state.mutations.push(`theme_replaced:${PREVIOUS_VERSION}->${TARGET_VERSION}`);
  } else {
    state.mutations.push(`theme_already_target:${TARGET_VERSION}`);
  }

  const afterSupport = await supportSnapshot(page);
  const afterVersion = afterSupport?.report?.theme?.version || null;
  if (afterVersion !== TARGET_VERSION) throw new Error(`Target Theme version verification failed: ${afterVersion}`);
  if (afterSupport?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Standalone Core not ready after deployment: ${JSON.stringify(afterSupport?.report?.standalone_core || null)}`);
  }

  const finalLocation = await locationState(page);
  if (!finalLocation.registered || finalLocation.menu_id !== String(approvedMenu.menu_id)) {
    throw new Error(`Header utility assignment drift after deployment: ${JSON.stringify(finalLocation)}`);
  }
  const finalMenu = await resolveApprovedPhoneMenuFromLocation(page, finalLocation);
  const matrix = await verifyPublicMatrix(page);

  state.after_state = {
    theme_version: afterVersion,
    standalone_core: afterSupport?.report?.standalone_core || null,
    header_utility: finalLocation,
    approved_phone_menu: finalMenu,
    public_matrix: matrix,
  };
  state.verification = {
    ...state.verification,
    theme_version_exact: true,
    standalone_core_ready: true,
    phone_menu_exact: true,
    header_utility_retained: true,
    public_desktop_mobile: true,
  };
  writeState();
  await context.close();
} catch (error) {
  state.error = error instanceof Error ? error.message : String(error);
  writeState();

  if (targetApplied && beforeVersion === PREVIOUS_VERSION) {
    const rollbackContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const rollbackPage = await rollbackContext.newPage();
    try {
      await login(rollbackPage);
      await replaceThemeFromZip(rollbackPage, rollbackZip);
      const rollbackSupport = await supportSnapshot(rollbackPage);
      const rollbackVersion = rollbackSupport?.report?.theme?.version || null;
      const rollbackLocation = await locationState(rollbackPage);
      const rollbackMenu = await resolveApprovedPhoneMenuFromLocation(rollbackPage, rollbackLocation);
      if (rollbackVersion === PREVIOUS_VERSION) {
        state.rollback.push(`theme_restored:${PREVIOUS_VERSION}`);
      } else {
        state.rollback.push(`theme_restore_version_mismatch:${rollbackVersion}`);
      }
      if (approvedMenu && rollbackLocation.menu_id === String(approvedMenu.menu_id) && rollbackMenu.menu_id === approvedMenu.menu_id) {
        state.rollback.push('header_utility_retained');
      } else {
        state.rollback.push(`header_utility_rollback_drift:${JSON.stringify(rollbackLocation)}`);
      }
    } catch (rollbackError) {
      state.rollback.push(`theme_rollback_failed:${rollbackError.message}`);
    }
    await rollbackContext.close();
    writeState();
  }

  await browser.close();
  console.error(state.error);
  process.exit(1);
}

await browser.close();
console.log(JSON.stringify(state, null, 2));
