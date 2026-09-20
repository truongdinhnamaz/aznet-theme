import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.LSTAMDUCHN_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.LSTAMDUCHN_STATE_DIR || '/tmp/lstamduchn-v1-3-20-preflight';
const proxyServer = process.env.LSTAMDUCHN_PROXY_SERVER || '';

fs.mkdirSync(stateDir, { recursive: true });

const state = {
  scope: 'owner-approved-v1.3.20-production-preflight-readonly',
  target: baseUrl,
  authenticated: false,
  theme: null,
  standalone_core: null,
  installed_themes: [],
  menu_locations: {},
  public: {},
  proxy: proxyServer || null,
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
  state.authenticated = true;
}

async function supportSnapshot(page) {
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=system-health`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const box = page.locator('textarea[readonly]');
  if ((await box.count()) !== 1) throw new Error('System Health support snapshot unavailable');
  return JSON.parse(await box.inputValue());
}

async function themeInventory(page) {
  await page.goto(`${baseUrl}/wp-admin/themes.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  return await page.locator('.theme').evaluateAll((nodes) => nodes.map((node) => ({
    slug: node.getAttribute('data-slug') || '',
    active: node.classList.contains('active'),
    name: (node.querySelector('.theme-name')?.textContent || '').trim(),
  })));
}

async function menuLocations(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 30000 });
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
  const response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  return await page.evaluate((status) => {
    const root = document.documentElement;
    const body = document.body;
    return {
      status,
      main_count: document.querySelectorAll('main').length,
      h1_count: document.querySelectorAll('h1').length,
      overflow: Math.max(0, Math.ceil(Math.max(root.scrollWidth, body?.scrollWidth || 0) - root.clientWidth)),
      logo_count: document.querySelectorAll('img.aznet-theme-site-header__logo, img.custom-logo').length,
      tel_links: Array.from(document.querySelectorAll('a[href^="tel:"]')).map((node) => ({
        href: node.getAttribute('href') || '',
        text: (node.textContent || '').trim(),
        visible: Boolean(node.getClientRects().length),
      })),
      title: document.title,
    };
  }, response?.status() || 0);
}

const browser = await chromium.launch({
  headless: true,
  ...(proxyServer ? { proxy: { server: proxyServer } } : {}),
});
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const support = await supportSnapshot(page);
  state.theme = support?.report?.theme || null;
  state.standalone_core = support?.report?.standalone_core || null;
  state.installed_themes = await themeInventory(page);
  state.menu_locations = await menuLocations(page);
  state.public.desktop = await publicState(page, 1440, 1000);
  state.public.mobile = await publicState(page, 390, 844);

  if (!state.theme?.version) throw new Error('Active AZnet Theme version unavailable');
  if (state.standalone_core?.status !== 'ready') throw new Error(`Standalone Core not ready: ${JSON.stringify(state.standalone_core)}`);
  for (const [label, item] of Object.entries(state.public)) {
    if (item.status !== 200) throw new Error(`${label} HTTP status ${item.status}`);
    if (item.main_count !== 1) throw new Error(`${label} expected one main landmark, got ${item.main_count}`);
    if (item.h1_count !== 1) throw new Error(`${label} expected one H1, got ${item.h1_count}`);
    if (item.overflow > 1) throw new Error(`${label} horizontal overflow ${item.overflow}px`);
  }

  writeState();
  console.log(JSON.stringify(state, null, 2));
  await context.close();
} catch (error) {
  state.error = error instanceof Error ? error.message : String(error);
  writeState();
  console.error(state.error);
  process.exitCode = 1;
} finally {
  await browser.close();
}
