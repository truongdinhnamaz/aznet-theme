import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const targetZip = process.env.TARGET_THEME_ZIP || '';
const rollbackZip = process.env.ROLLBACK_THEME_ZIP || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-homepage-1354-runtime-staging';

const TARGET_VERSION = '1.3.54';
const ROLLBACK_VERSION = '1.3.1';
const REQUIRED_SURFACES = ['hero', 'services', 'profile', 'front-page-content', 'process', 'faq', 'final-cta'];

if (!adminUser || !adminPass) throw new Error('Required staging WordPress credentials are unavailable');
fs.mkdirSync(stateDir, { recursive: true });

const state = {
  scope: 'owner-approved-homepage-runtime-staging',
  target: baseUrl,
  target_version: TARGET_VERSION,
  rollback_version: ROLLBACK_VERSION,
  before: {},
  after: {},
  mutation: [],
  rollback: [],
  l3: {},
  l4: {},
  console: [],
  error: null,
};

function writeState() {
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(state, null, 2));
}

async function login(page) {
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
  const textarea = page.locator('textarea[readonly]');
  if ((await textarea.count()) !== 1) throw new Error('System Health support snapshot unavailable');
  return JSON.parse(await textarea.inputValue());
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

  const errorPage = page.locator('#error-page');
  if ((await errorPage.count()) === 1) {
    throw new Error('WordPress returned an error while replacing the Theme');
  }
  await page.waitForTimeout(1500);
}

async function gotoHomepageAdmin(page) {
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=homepage`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 20000 });
}

async function collectAdminHomepage(page, viewport) {
  await page.setViewportSize(viewport);
  await gotoHomepageAdmin(page);

  const library = page.locator('[data-aznet-template-library]');
  if ((await library.count()) !== 1) throw new Error('Template Library is missing in Homepage admin');
  if ((await library.locator('[data-aznet-template-search]').count()) !== 1) throw new Error('Template Library search is missing');
  if ((await library.locator('[data-aznet-template-category]').count()) !== 1) throw new Error('Template Library category filter is missing');

  const map = page.locator('#aznet-theme-homepage-map');
  if ((await map.count()) !== 1) throw new Error('Homepage Map is missing');
  if ((await map.getByRole('heading', { name: 'Trang chủ đang hiển thị' }).count()) !== 1) throw new Error('Homepage Map heading is missing');

  const keys = await map.locator('[data-surface-key]').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('data-surface-key')));
  for (const key of REQUIRED_SURFACES) {
    if (!keys.includes(key)) throw new Error(`Required effective Homepage surface missing: ${key}; actual=${JSON.stringify(keys)}`);
  }

  const expectedOrder = ['hero', 'services', 'profile', 'front-page-content'];
  let previous = -1;
  for (const key of expectedOrder) {
    const index = keys.indexOf(key);
    if (index <= previous) throw new Error(`Homepage primary reading order drift: ${JSON.stringify(keys)}`);
    previous = index;
  }
  if (keys[keys.length - 1] !== 'final-cta') throw new Error(`Final CTA is not the last effective surface: ${JSON.stringify(keys)}`);

  const nativeCard = map.locator('[data-surface-key="front-page-content"]');
  if ((await nativeCard.getByRole('link', { name: 'Chỉnh nội dung trang' }).count()) !== 1) throw new Error('Native Front Page edit action missing');

  const processCard = map.locator('[data-surface-key="process"]');
  if ((await processCard.getByRole('link', { name: 'Sửa các bước' }).count()) !== 1) throw new Error('Process full-content edit action missing');

  const faqCard = map.locator('[data-surface-key="faq"]');
  if ((await faqCard.getByRole('link', { name: 'Sửa câu hỏi' }).count()) !== 1) throw new Error('FAQ full-content edit action missing');

  const advanced = page.locator('#aznet-theme-homepage-sources');
  if ((await advanced.count()) !== 1) throw new Error('Advanced source disclosure missing');
  const advancedLabel = ((await advanced.locator('summary').textContent()) || '').trim();
  if (!advancedLabel.includes('Nguồn & cài đặt nâng cao')) throw new Error(`Advanced source label mismatch: ${advancedLabel}`);

  if ((await map.getByText('READY', { exact: true }).count()) > 0 || (await map.getByText('DRAFT', { exact: true }).count()) > 0) {
    throw new Error('Raw technical statuses leaked into the primary Homepage Map');
  }

  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) throw new Error(`Homepage admin horizontal overflow: ${overflow}px`);

  const firstTab = page.locator('.aznet-theme-control-center .nav-tab').first();
  await firstTab.focus();
  const focus = await firstTab.evaluate((node) => {
    const style = getComputedStyle(node);
    return {
      active: document.activeElement === node,
      outline: style.outlineStyle !== 'none' && Number.parseFloat(style.outlineWidth || '0') > 0,
      shadow: Boolean(style.boxShadow && style.boxShadow !== 'none'),
    };
  });
  if (!focus.active || (!focus.outline && !focus.shadow)) throw new Error(`Homepage admin keyboard focus indicator missing: ${JSON.stringify(focus)}`);

  const axe = await new AxeBuilder({ page }).include('.aznet-theme-control-center').analyze();
  const serious = axe.violations.filter((item) => ['critical', 'serious'].includes(item.impact || ''));
  fs.writeFileSync(path.join(stateDir, `admin-${viewport.width}-axe.json`), JSON.stringify(axe, null, 2));
  if (serious.length) throw new Error(`Homepage admin axe serious/critical: ${serious.map((item) => item.id).join(',')}`);

  await page.screenshot({ path: path.join(stateDir, `admin-${viewport.width}.png`), fullPage: true });
  return { keys, overflow, focus, axe_serious: serious.length };
}

async function collectPublicHomepage(page, viewport) {
  await page.setViewportSize(viewport);
  const response = await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle', timeout: 30000 });
  const status = response?.status() || 0;
  if (status !== 200) throw new Error(`Public Homepage returned HTTP ${status} at ${viewport.width}px`);

  const errorPage = page.locator('#error-page');
  if ((await errorPage.count()) > 0) throw new Error('Public Homepage rendered WordPress error page');

  const data = await page.evaluate(() => ({
    keys: Array.from(document.querySelectorAll('[data-aznet-homepage-surface]')).map((node) => node.getAttribute('data-aznet-homepage-surface')),
    main_count: document.querySelectorAll('main').length,
    overflow: Math.max(0, Math.ceil(document.documentElement.scrollWidth - document.documentElement.clientWidth)),
  }));

  if (data.main_count !== 1) throw new Error(`Public Homepage expected one main landmark, got ${data.main_count}`);
  if (data.overflow > 1) throw new Error(`Public Homepage horizontal overflow: ${data.overflow}px`);
  for (const key of REQUIRED_SURFACES) {
    if (!data.keys.includes(key)) throw new Error(`Public Homepage missing effective surface ${key}: ${JSON.stringify(data.keys)}`);
  }

  const axe = await new AxeBuilder({ page }).include('main').analyze();
  const serious = axe.violations.filter((item) => ['critical', 'serious'].includes(item.impact || ''));
  fs.writeFileSync(path.join(stateDir, `public-${viewport.width}-axe.json`), JSON.stringify(axe, null, 2));
  if (serious.length) throw new Error(`Public Homepage axe serious/critical: ${serious.map((item) => item.id).join(',')}`);

  await page.screenshot({ path: path.join(stateDir, `public-${viewport.width}.png`), fullPage: true });
  return { status, ...data, axe_serious: serious.length };
}

const browser = await chromium.launch({ headless: true });
let targetApplied = false;
let beforeVersion = null;

try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  page.on('console', (msg) => {
    if (msg.type() === 'error') state.console.push({ type: 'console', text: msg.text() });
  });
  page.on('pageerror', (error) => state.console.push({ type: 'pageerror', text: error.message }));

  await login(page);
  const beforeSupport = await supportSnapshot(page);
  beforeVersion = beforeSupport?.report?.theme?.version || null;
  state.before = {
    version: beforeVersion,
    standalone_core: beforeSupport?.report?.standalone_core || null,
  };
  writeState();

  if (![ROLLBACK_VERSION, TARGET_VERSION].includes(beforeVersion)) {
    throw new Error(`Staging precondition drift: expected Theme ${ROLLBACK_VERSION} or ${TARGET_VERSION}, got ${beforeVersion}`);
  }

  if (beforeVersion !== TARGET_VERSION) {
    await replaceThemeFromZip(page, targetZip);
    targetApplied = true;
    state.mutation.push(`theme_replaced:${beforeVersion}->${TARGET_VERSION}`);
  } else {
    state.mutation.push(`theme_already_target:${TARGET_VERSION}`);
  }

  const afterSupport = await supportSnapshot(page);
  const afterVersion = afterSupport?.report?.theme?.version || null;
  if (afterVersion !== TARGET_VERSION) throw new Error(`Target Theme version mismatch: ${afterVersion}`);
  if (afterSupport?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Standalone Core is not ready after staging deployment: ${JSON.stringify(afterSupport?.report?.standalone_core || null)}`);
  }

  state.after = {
    version: afterVersion,
    standalone_core: afterSupport?.report?.standalone_core || null,
  };
  state.l3 = {
    exact_theme_version: true,
    system_health_available: true,
    standalone_core_ready: true,
  };

  const desktopAdmin = await collectAdminHomepage(page, { width: 1440, height: 1000 });
  const desktopPublic = await collectPublicHomepage(page, { width: 1440, height: 1000 });
  if (JSON.stringify(desktopAdmin.keys) !== JSON.stringify(desktopPublic.keys)) {
    throw new Error(`Desktop admin/frontend surface parity mismatch: admin=${JSON.stringify(desktopAdmin.keys)} public=${JSON.stringify(desktopPublic.keys)}`);
  }

  const mobileAdmin = await collectAdminHomepage(page, { width: 390, height: 844 });
  const mobilePublic = await collectPublicHomepage(page, { width: 390, height: 844 });
  if (JSON.stringify(mobileAdmin.keys) !== JSON.stringify(mobilePublic.keys)) {
    throw new Error(`Mobile admin/frontend surface parity mismatch: admin=${JSON.stringify(mobileAdmin.keys)} public=${JSON.stringify(mobilePublic.keys)}`);
  }

  const pageErrors = state.console.filter((item) => item.type === 'pageerror');
  if (pageErrors.length) throw new Error(`Browser page errors detected: ${JSON.stringify(pageErrors)}`);

  state.l4 = {
    desktop_admin: desktopAdmin,
    desktop_public: desktopPublic,
    mobile_admin: mobileAdmin,
    mobile_public: mobilePublic,
    surface_order_parity: true,
    page_errors: 0,
  };

  writeState();
  await context.close();
} catch (error) {
  state.error = error instanceof Error ? error.message : String(error);
  writeState();

  if (targetApplied && beforeVersion === ROLLBACK_VERSION) {
    const rollbackContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const rollbackPage = await rollbackContext.newPage();
    try {
      await login(rollbackPage);
      await replaceThemeFromZip(rollbackPage, rollbackZip);
      const rollbackSupport = await supportSnapshot(rollbackPage);
      const rollbackVersion = rollbackSupport?.report?.theme?.version || null;
      if (rollbackVersion === ROLLBACK_VERSION) {
        state.rollback.push(`theme_restored:${ROLLBACK_VERSION}`);
      } else {
        state.rollback.push(`theme_restore_version_mismatch:${rollbackVersion}`);
      }
    } catch (rollbackError) {
      state.rollback.push(`theme_rollback_failed:${rollbackError instanceof Error ? rollbackError.message : String(rollbackError)}`);
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
