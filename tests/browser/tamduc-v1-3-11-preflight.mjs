import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/lstamduchn-v1-3-11-preflight';

const CURRENT_VERSION = '1.3.10';
const TARGET_VERSION = '1.3.11';
const PHONE_DISPLAY = '024 3716 4123';
const PHONE_HREF = 'tel:+842437164123';
const MENU_NAME = 'Tâm Đức - Liên hệ chính thức';
const UTILITY_LOCATION = 'header-utility';

fs.mkdirSync(stateDir, { recursive: true });
const state = {
  scope: 'read-only-predeploy-preflight',
  target: `${baseUrl}/`,
  mutation: false,
  current_version: CURRENT_VERSION,
  target_version: TARGET_VERSION,
  verification: {},
  blocked: [],
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
  await page.locator('#wp-submit').click();
  await page.waitForLoadState('domcontentloaded', { timeout: 30000 });
  if (!page.url().includes('/wp-admin/')) {
    throw new Error(`WordPress login did not reach wp-admin: ${page.url()}`);
  }
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

async function approvedPhoneMenu(page, location) {
  if (!location.registered) throw new Error(`${UTILITY_LOCATION} is not registered`);
  const menuId = Number(location.menu_id || 0);
  if (!Number.isInteger(menuId) || menuId <= 0) throw new Error(`Invalid menu id: ${location.menu_id}`);
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${menuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const menuName = await page.locator('#menu-name').inputValue();
  if (menuName !== MENU_NAME) throw new Error(`Approved phone menu name drift: ${menuName}`);
  const rows = page.locator('#menu-to-edit .menu-item');
  const items = [];
  for (let i = 0; i < await rows.count(); i += 1) {
    const row = rows.nth(i);
    items.push({
      title: await row.locator('input.edit-menu-item-title').inputValue(),
      href: await row.locator('input.edit-menu-item-url').inputValue(),
    });
  }
  if (items.length !== 1 || items[0]?.title !== PHONE_DISPLAY || items[0]?.href !== PHONE_HREF) {
    throw new Error(`Approved phone menu drift: ${JSON.stringify(items)}`);
  }
  return { menu_id: menuId, menu_name: menuName, items };
}

function assertPublic(item) {
  if (item.status !== 200) throw new Error(`Public HTTP status ${item.status} at ${item.viewport.width}px`);
  if (item.main_count !== 1) throw new Error(`Expected one main landmark at ${item.viewport.width}px`);
  if (item.h1_count !== 1) throw new Error(`Expected one H1 at ${item.viewport.width}px`);
  if (item.overflow > 1) throw new Error(`Horizontal overflow ${item.overflow}px at ${item.viewport.width}px`);
  if (item.logo_count < 1 || !item.logo_src) throw new Error(`Approved logo missing at ${item.viewport.width}px`);
  if (!item.tel_links.some((link) => link.href === PHONE_HREF)) throw new Error(`Approved phone link missing at ${item.viewport.width}px`);
}

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const support = await supportSnapshot(page);
  const version = support?.report?.theme?.version || null;
  if (version !== CURRENT_VERSION) throw new Error(`Precondition drift: expected Theme ${CURRENT_VERSION}, got ${version}`);
  if (support?.report?.standalone_core?.status !== 'ready') {
    throw new Error(`Standalone Core not ready: ${JSON.stringify(support?.report?.standalone_core || null)}`);
  }

  const location = await locationState(page);
  const menu = await approvedPhoneMenu(page, location);
  const desktop = await publicState(page, { width: 1440, height: 1000 });
  const mobile = await publicState(page, { width: 390, height: 844 });
  assertPublic(desktop);
  assertPublic(mobile);
  if (!desktop.tel_links.some((link) => link.href === PHONE_HREF && link.visible)) {
    throw new Error('Desktop approved phone link is not visible');
  }

  state.verification = {
    authenticated: true,
    theme_version_exact: true,
    standalone_core_ready: true,
    header_utility_retained: location.menu_id === String(menu.menu_id),
    phone_menu_exact: true,
    public_desktop_mobile: true,
  };
  state.observed = { support, location, menu, desktop, mobile };
  writeState();
  await context.close();
} catch (error) {
  state.error = error instanceof Error ? error.message : String(error);
  writeState();
  await browser.close();
  console.error(state.error);
  process.exit(1);
}

await browser.close();
console.log(JSON.stringify(state, null, 2));
