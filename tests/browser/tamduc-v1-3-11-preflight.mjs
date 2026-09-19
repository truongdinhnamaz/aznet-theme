import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-v1-3-11-preflight';
const expectedLiveVersion = process.env.EXPECTED_LIVE_VERSION || '1.3.1';

const PHONE_DISPLAY = '024 3716 4123';
const PHONE_HREF = 'tel:+842437164123';
const MENU_NAME = 'Tâm Đức - Liên hệ chính thức';
const UTILITY_LOCATION = 'header-utility';
const THEME_DIR = 'themes/aznet-theme';
const MAX_READ_BYTES = 65536;
const ALLOWED_EXTENSIONS = new Set(['php', 'css', 'js', 'json', 'html', 'txt', 'svg']);

fs.mkdirSync(stateDir, { recursive: true });
const rollbackRoot = path.join(stateDir, 'rollback', 'aznet-theme');
fs.mkdirSync(rollbackRoot, { recursive: true });

const state = {
  scope: 'read-only-predeploy-preflight',
  target: `${baseUrl}/`,
  mutation: false,
  expected_live_version: expectedLiveVersion,
  before_state: {},
  backup: {},
  verification: {},
  blocked: [],
  error: null,
};

function writeJson(name, value) {
  fs.writeFileSync(path.join(stateDir, name), JSON.stringify(value, null, 2));
}

function writeState() {
  writeJson('summary.json', state);
}

function sha256(value) {
  return crypto.createHash('sha256').update(value).digest('hex');
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

async function restNonce(context) {
  const response = await context.request.get(`${baseUrl}/wp-admin/admin-ajax.php?action=rest-nonce`);
  if (!response.ok()) throw new Error(`REST nonce request failed: ${response.status()}`);
  const nonce = (await response.text()).trim();
  if (!nonce || nonce === '-1') throw new Error('REST nonce unavailable');
  return nonce;
}

async function wpvibeJson(context, nonce, method, route, data = null) {
  const url = `${baseUrl}/wp-json/wpvibe/v1${route}`;
  const options = {
    headers: {
      'X-WP-Nonce': nonce,
      'Accept': 'application/json',
    },
  };
  if (data !== null) options.data = data;
  const response = method === 'GET'
    ? await context.request.get(url, options)
    : await context.request.post(url, options);
  const text = await response.text();
  if (!response.ok()) throw new Error(`WPVibe REST ${method} ${route} failed ${response.status()}: ${text.slice(0, 500)}`);
  return JSON.parse(text);
}

async function backupLiveTheme(context, nonce) {
  const listing = await wpvibeJson(
    context,
    nonce,
    'GET',
    `/file/list?scope=wp-content&directory=${encodeURIComponent(THEME_DIR)}`
  );
  const files = Array.isArray(listing.files) ? listing.files : listing.result?.files;
  const totalFiles = Number(listing.total_files ?? listing.result?.total_files ?? files?.length ?? 0);
  if (!Array.isArray(files) || files.length === 0 || totalFiles !== files.length) {
    throw new Error(`Live Theme listing invalid: total=${totalFiles}, files=${files?.length ?? 0}`);
  }

  const manifest = [];
  for (const file of files) {
    if (file.type !== 'file') throw new Error(`Unsupported live Theme entry type: ${JSON.stringify(file)}`);
    if (!file.path.startsWith(`${THEME_DIR}/`)) throw new Error(`Unexpected live Theme path: ${file.path}`);
    const extension = String(file.extension || '').toLowerCase();
    if (!ALLOWED_EXTENSIONS.has(extension)) throw new Error(`Unsupported rollback file extension: ${file.path}`);
    if (Number(file.size || 0) > MAX_READ_BYTES) throw new Error(`Rollback file exceeds safe read size: ${file.path} (${file.size})`);

    const payload = await wpvibeJson(context, nonce, 'POST', '/file/read', {
      scope: 'wp-content',
      path: file.path,
    });
    const content = typeof payload.content === 'string' ? payload.content : payload.result?.content;
    if (typeof content !== 'string') throw new Error(`Live Theme file content unavailable: ${file.path}`);
    if (Buffer.byteLength(content) !== Number(file.size)) {
      throw new Error(`Live Theme file byte mismatch: ${file.path} expected=${file.size} actual=${Buffer.byteLength(content)}`);
    }

    const relative = file.path.slice(`${THEME_DIR}/`.length);
    const target = path.join(rollbackRoot, relative);
    fs.mkdirSync(path.dirname(target), { recursive: true });
    fs.writeFileSync(target, content, 'utf8');
    manifest.push({
      path: relative,
      bytes: Buffer.byteLength(content),
      sha256: sha256(content),
    });
  }

  manifest.sort((a, b) => a.path.localeCompare(b.path));
  writeJson('live-theme-manifest.json', manifest);

  const styleEntry = manifest.find((entry) => entry.path === 'style.css');
  if (!styleEntry) throw new Error('Live Theme style.css missing from rollback snapshot');
  const style = fs.readFileSync(path.join(rollbackRoot, 'style.css'), 'utf8');
  const versionMatch = style.match(/^Version:\s*(.+)$/m);
  const version = versionMatch?.[1]?.trim() || null;

  return {
    file_count: manifest.length,
    total_bytes: manifest.reduce((sum, entry) => sum + entry.bytes, 0),
    version,
    style_sha256: styleEntry.sha256,
  };
}

function assertPublic(item) {
  if (item.status !== 200) throw new Error(`Public HTTP status ${item.status} at ${item.viewport.width}px`);
  if (item.main_count !== 1) throw new Error(`Expected one main landmark at ${item.viewport.width}px, got ${item.main_count}`);
  if (item.h1_count !== 1) throw new Error(`Expected one H1 at ${item.viewport.width}px, got ${item.h1_count}`);
  if (item.overflow > 1) throw new Error(`Horizontal overflow ${item.overflow}px at ${item.viewport.width}px`);
  if (item.logo_count < 1 || !item.logo_src) throw new Error(`Approved logo missing at ${item.viewport.width}px`);
  if (!item.tel_links.some((link) => link.href === PHONE_HREF)) throw new Error(`Approved phone link missing at ${item.viewport.width}px`);
}

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const beforeSupport = await supportSnapshot(page);
  const beforeVersion = beforeSupport?.report?.theme?.version || null;
  const standalone = beforeSupport?.report?.standalone_core || null;
  const location = await locationState(page);
  const approvedMenu = await resolveApprovedPhoneMenuFromLocation(page, location);
  const desktop = await publicState(page, { width: 1440, height: 1000 });
  const mobile = await publicState(page, { width: 390, height: 844 });

  if (beforeVersion !== expectedLiveVersion) {
    throw new Error(`Precondition drift: expected live Theme ${expectedLiveVersion}, got ${beforeVersion}`);
  }
  if (standalone?.status !== 'ready') {
    throw new Error(`Precondition drift: Standalone Core not ready: ${JSON.stringify(standalone)}`);
  }
  if (!location.registered || location.menu_id !== String(approvedMenu.menu_id)) {
    throw new Error(`Precondition drift: ${UTILITY_LOCATION} assignment mismatch`);
  }
  assertPublic(desktop);
  assertPublic(mobile);
  if (!desktop.tel_links.some((link) => link.href === PHONE_HREF && link.visible)) {
    throw new Error('Desktop approved phone link is not visible');
  }

  const nonce = await restNonce(context);
  const backup = await backupLiveTheme(context, nonce);
  if (backup.version !== expectedLiveVersion) {
    throw new Error(`Rollback snapshot version drift: expected ${expectedLiveVersion}, got ${backup.version}`);
  }

  state.before_state = {
    theme_version: beforeVersion,
    standalone_core: standalone,
    header_utility: location,
    approved_phone_menu: approvedMenu,
    public_matrix: { desktop, mobile },
  };
  state.backup = backup;
  state.verification = {
    authenticated: true,
    current_theme_exact: true,
    standalone_core_ready: true,
    header_utility_retained: true,
    phone_menu_exact: true,
    public_desktop_mobile: true,
    rollback_snapshot_complete: true,
  };
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
