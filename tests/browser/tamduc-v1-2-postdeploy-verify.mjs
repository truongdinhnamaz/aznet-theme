import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-v1-2-postdeploy-verify';
const TARGET_VERSION = '1.2.0';
const PHONE_DISPLAY = '024 3716 4123';
const PHONE_HREF = 'tel:+842437164123';
const MENU_NAME = 'T\u00e2m \u0110\u1ee9c - Li\u00ean h\u1ec7 ch\u00ednh th\u1ee9c';
const UTILITY_LOCATION = 'header-utility';

fs.mkdirSync(stateDir, { recursive: true });

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

async function locationState(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const select = page.locator(`select[name="menu-locations[${UTILITY_LOCATION}]"]`);
  if ((await select.count()) !== 1) return { registered: false, menu_id: null };
  return { registered: true, menu_id: await select.inputValue() };
}

async function approvedMenuFromLocation(page, location) {
  if (!location.registered) throw new Error(`${UTILITY_LOCATION} is not registered`);
  const menuId = Number(location.menu_id || 0);
  if (!Number.isInteger(menuId) || menuId <= 0) throw new Error(`Invalid ${UTILITY_LOCATION} menu id: ${location.menu_id}`);

  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${menuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const menuNameField = page.locator('#menu-name');
  if ((await menuNameField.count()) !== 1) throw new Error(`Menu ${menuId} edit surface unavailable`);
  const menu_name = await page.locator('#menu-name').inputValue();
  if (menu_name !== MENU_NAME) throw new Error(`Approved phone menu name drift: ${menu_name}`);

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
  return { menu_id: menuId, menu_name, item_count: items.length, items };
}

async function publicCase(browser, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const pageErrors = [];
  const consoleErrors = [];
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
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
  await context.close();
  return { viewport, status, page_errors: pageErrors, console_errors: consoleErrors, ...data };
}

const browser = await chromium.launch({ headless: true });
try {
  const adminContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const adminPage = await adminContext.newPage();
  await login(adminPage);
  const support = await supportSnapshot(adminPage);
  const location = await locationState(adminPage);
  const menu = await approvedMenuFromLocation(adminPage, location);
  await adminContext.close();

  const version = support?.report?.theme?.version || null;
  const standalone = support?.report?.standalone_core || null;
  if (version !== TARGET_VERSION) throw new Error(`Expected Theme ${TARGET_VERSION}, got ${version}`);
  if (standalone?.status !== 'ready') throw new Error(`Standalone Core not ready: ${JSON.stringify(standalone)}`);
  if (!location.registered || location.menu_id !== String(menu.menu_id)) throw new Error(`Header utility location mismatch: ${JSON.stringify(location)}`);

  const desktop = await publicCase(browser, { width: 1440, height: 1000 });
  const mobile = await publicCase(browser, { width: 390, height: 844 });
  for (const item of [desktop, mobile]) {
    if (item.status !== 200) throw new Error(`HTTP ${item.status} at ${item.viewport.width}px`);
    if (item.main_count !== 1 || item.h1_count !== 1) throw new Error(`Landmark/heading failure at ${item.viewport.width}px`);
    if (item.overflow > 1) throw new Error(`Horizontal overflow ${item.overflow}px at ${item.viewport.width}px`);
    if (item.logo_count < 1 || !item.logo_src) throw new Error(`Logo missing at ${item.viewport.width}px`);
    if (!item.tel_links.some((link) => link.href === PHONE_HREF)) throw new Error(`Phone link missing at ${item.viewport.width}px`);
    if (item.page_errors.length || item.console_errors.length) throw new Error(`Browser error at ${item.viewport.width}px: ${JSON.stringify({ page_errors: item.page_errors, console_errors: item.console_errors })}`);
  }
  if (!desktop.tel_links.some((link) => link.href === PHONE_HREF && link.visible)) throw new Error('Desktop phone link is not visible');

  const summary = {
    target: baseUrl,
    theme_version: version,
    standalone_core: standalone,
    approved_phone_menu: menu,
    header_utility: location,
    public_matrix: { desktop, mobile },
    mutation: false,
    result: 'PASS',
  };
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary, null, 2));
} finally {
  await browser.close();
}
