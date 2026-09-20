import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.LSTAMDUCHN_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const targetZip = process.env.TARGET_THEME_ZIP || '';
const rollbackZip = process.env.ROLLBACK_THEME_ZIP || '';
const stateDir = process.env.LSTAMDUCHN_STATE_DIR || 'lstamduchn-v1-3-20-selfhosted-deploy';

const TARGET_VERSION = '1.3.20';
const PREVIOUS_VERSION = '1.3.10';

fs.mkdirSync(stateDir, { recursive: true });

const state = {
  scope: 'owner-approved-v1.3.20-selfhosted-production-deployment',
  target: baseUrl,
  target_version: TARGET_VERSION,
  previous_version: PREVIOUS_VERSION,
  before_state: {},
  after_state: {},
  mutations: [],
  rollback: [],
  verification: {},
  error: null,
};

function writeState() {
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(state, null, 2));
}

async function login(page) {
  if (!adminUser || !adminPass) throw new Error('Required pilot credentials are unavailable');
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 45000 });
  await page.locator('#user_login').fill(adminUser);
  await page.locator('#user_pass').fill(adminPass);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 45000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function supportSnapshot(page) {
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=system-health`, { waitUntil: 'domcontentloaded', timeout: 45000 });
  const box = page.locator('textarea[readonly]');
  if ((await box.count()) !== 1) throw new Error('System Health support snapshot unavailable');
  return JSON.parse(await box.inputValue());
}

async function menuLocations(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 45000 });
  const selects = page.locator('select[name^="menu-locations["]');
  const locations = {};
  for (let i = 0; i < await selects.count(); i += 1) {
    const select = selects.nth(i);
    const name = await select.getAttribute('name');
    const match = name?.match(/^menu-locations\[(.+)\]$/);
    if (!match) continue;
    locations[match[1]] = await select.inputValue();
  }
  return locations;
}

async function publicState(page, width, height) {
  await page.setViewportSize({ width, height });
  const response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 45000 });
  return await page.evaluate((status) => {
    const root = document.documentElement;
    const body = document.body;
    return {
      status,
      main_count: document.querySelectorAll('main').length,
      h1_count: document.querySelectorAll('h1').length,
      overflow: Math.max(0, Math.ceil(Math.max(root.scrollWidth, body?.scrollWidth || 0) - root.clientWidth)),
      logo_count: document.querySelectorAll('img.aznet-theme-site-header__logo, img.custom-logo').length,
      tel_hrefs: Array.from(document.querySelectorAll('a[href^="tel:"]')).map((node) => node.getAttribute('href') || '').filter(Boolean),
      title: document.title,
    };
  }, response?.status() || 0);
}

function assertPublic(label, item) {
  if (item.status !== 200) throw new Error(`${label} HTTP status ${item.status}`);
  if (item.main_count !== 1) throw new Error(`${label} expected one main landmark, got ${item.main_count}`);
  if (item.h1_count !== 1) throw new Error(`${label} expected one H1, got ${item.h1_count}`);
  if (item.overflow > 1) throw new Error(`${label} horizontal overflow ${item.overflow}px`);
}

function sameObject(a, b) {
  return JSON.stringify(a) === JSON.stringify(b);
}

async function openUploadThemeForm(page) {
  await page.goto(`${baseUrl}/wp-admin/theme-install.php?upload`, { waitUntil: 'domcontentloaded', timeout: 45000 });
  const input = page.locator('#themezip');
  if ((await input.count()) === 1 && await input.isVisible()) return input;
  const toggle = page.locator('.upload-view-toggle').first();
  if ((await toggle.count()) === 1) await toggle.click();
  await input.waitFor({ state: 'visible', timeout: 15000 });
  return input;
}

async function replaceThemeFromZip(page, zipPath) {
  if (!zipPath || !fs.existsSync(zipPath)) throw new Error(`Theme package missing: ${zipPath}`);
  const input = await openUploadThemeForm(page);
  await input.setInputFiles(zipPath);
  const submit = page.locator('#install-theme-submit');
  await submit.waitFor({ state: 'visible', timeout: 15000 });
  await submit.click();
  await page.waitForLoadState('domcontentloaded', { timeout: 45000 });

  const overwrite = page.locator('.update-from-upload-overwrite');
  if ((await overwrite.count()) === 1) {
    await overwrite.click();
    await page.waitForLoadState('domcontentloaded', { timeout: 45000 });
  }
  await page.waitForTimeout(2000);
}

const browser = await chromium.launch({ headless: true });
let targetApplied = false;
let beforeVersion = null;
let beforeMenus = {};
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const beforeSupport = await supportSnapshot(page);
  beforeVersion = beforeSupport?.report?.theme?.version || null;
  beforeMenus = await menuLocations(page);
  const beforeDesktop = await publicState(page, 1440, 1000);
  const beforeMobile = await publicState(page, 390, 844);
  assertPublic('pre-deploy desktop', beforeDesktop);
  assertPublic('pre-deploy mobile', beforeMobile);

  state.before_state = {
    theme_version: beforeVersion,
    standalone_core: beforeSupport?.report?.standalone_core || null,
    menu_locations: beforeMenus,
    public: { desktop: beforeDesktop, mobile: beforeMobile },
  };
  writeState();

  if (![PREVIOUS_VERSION, TARGET_VERSION].includes(beforeVersion)) {
    throw new Error(`Precondition drift: expected Theme ${PREVIOUS_VERSION} or ${TARGET_VERSION}, got ${beforeVersion}`);
  }
  if (beforeSupport?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Precondition drift: Standalone Core not ready: ${JSON.stringify(beforeSupport?.report?.standalone_core || null)}`);
  }

  if (beforeVersion === PREVIOUS_VERSION) {
    await replaceThemeFromZip(page, targetZip);
    targetApplied = true;
    state.mutations.push(`theme_replaced:${PREVIOUS_VERSION}->${TARGET_VERSION}`);
  } else {
    state.mutations.push(`theme_already_target:${TARGET_VERSION}`);
  }

  const afterSupport = await supportSnapshot(page);
  const afterVersion = afterSupport?.report?.theme?.version || null;
  const afterMenus = await menuLocations(page);
  const afterDesktop = await publicState(page, 1440, 1000);
  const afterMobile = await publicState(page, 390, 844);
  assertPublic('post-deploy desktop', afterDesktop);
  assertPublic('post-deploy mobile', afterMobile);

  if (afterVersion !== TARGET_VERSION) throw new Error(`Target Theme version verification failed: ${afterVersion}`);
  if (afterSupport?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Standalone Core not ready after deployment: ${JSON.stringify(afterSupport?.report?.standalone_core || null)}`);
  }
  if (!sameObject(beforeMenus, afterMenus)) {
    throw new Error(`Menu location drift: before=${JSON.stringify(beforeMenus)} after=${JSON.stringify(afterMenus)}`);
  }

  state.after_state = {
    theme_version: afterVersion,
    standalone_core: afterSupport?.report?.standalone_core || null,
    menu_locations: afterMenus,
    public: { desktop: afterDesktop, mobile: afterMobile },
  };
  state.verification = {
    theme_version_exact: true,
    standalone_core_ready: true,
    menu_locations_retained: true,
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
      const rollbackMenus = await menuLocations(rollbackPage);
      const rollbackDesktop = await publicState(rollbackPage, 1440, 1000);
      const rollbackMobile = await publicState(rollbackPage, 390, 844);
      assertPublic('rollback desktop', rollbackDesktop);
      assertPublic('rollback mobile', rollbackMobile);

      if (rollbackVersion === PREVIOUS_VERSION) {
        state.rollback.push(`theme_restored:${PREVIOUS_VERSION}`);
      } else {
        state.rollback.push(`theme_restore_version_mismatch:${rollbackVersion}`);
      }
      if (sameObject(beforeMenus, rollbackMenus)) {
        state.rollback.push('menu_locations_retained');
      } else {
        state.rollback.push(`menu_location_rollback_drift:${JSON.stringify(rollbackMenus)}`);
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
