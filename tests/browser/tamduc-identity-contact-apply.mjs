import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const logoPath = process.env.TAMDUC_LOGO_PATH || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-identity-contact';

const PHONE_DISPLAY = '024 3716 4123';
const PHONE_HREF = 'tel:+842437164123';
const MENU_NAME = 'Tâm Đức - Liên hệ chính thức';
const LOGO_FILENAME = 'logo-official.jpg';

fs.mkdirSync(stateDir, { recursive: true });

const state = {
  scope: 'owner-approved-wordpress-identity-contact',
  before_state: {},
  after_state: {},
  mutations: [],
  rollback: [],
  blocked: [],
  error: null,
};

async function nativeClick(locator) {
  await locator.evaluate((element) => element.click());
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

async function publicState(page) {
  await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  return page.evaluate(() => ({
    logo_count: document.querySelectorAll('img.custom-logo').length,
    logo_src: document.querySelector('img.custom-logo')?.getAttribute('src') || '',
    tel_links: Array.from(document.querySelectorAll('a[href^="tel:"]')).map((node) => ({
      href: node.getAttribute('href') || '',
      text: (node.textContent || '').trim(),
    })),
  }));
}

async function restNonce(page) {
  await page.goto(`${baseUrl}/wp-admin/post-new.php?post_type=page`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const nonce = await page.evaluate(() => window.wpApiSettings?.nonce || '');
  if (!nonce) throw new Error('WordPress REST nonce unavailable');
  return nonce;
}

async function uploadLogo(page, nonce) {
  if (!logoPath || !fs.existsSync(logoPath)) throw new Error(`Owner-approved ${LOGO_FILENAME} payload missing`);
  const response = await page.request.post(`${baseUrl}/wp-json/wp/v2/media`, {
    headers: {
      'X-WP-Nonce': nonce,
      'Content-Disposition': `attachment; filename="tam-duc-ha-noi-${LOGO_FILENAME}"`,
      'Content-Type': 'image/jpeg',
    },
    data: fs.readFileSync(logoPath),
    timeout: 30000,
  });
  if (!response.ok()) throw new Error(`Logo upload failed: ${response.status()} ${await response.text()}`);
  const payload = await response.json();
  const mediaId = Number(payload?.id || 0);
  if (!mediaId) throw new Error('Logo upload returned no media id');
  return mediaId;
}

async function setCustomLogo(page, mediaId) {
  await page.goto(`${baseUrl}/wp-admin/customize.php?autofocus[control]=custom_logo`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.waitForFunction(() => Boolean(window.wp?.customize), null, { timeout: 30000 });
  const result = await page.evaluate((id) => {
    const setting = window.wp.customize('custom_logo');
    if (!setting) return { ok: false, reason: 'custom_logo setting unavailable' };
    setting.set(id);
    return { ok: true, value: setting.get() };
  }, mediaId);
  if (!result.ok) throw new Error(result.reason);
  const save = page.locator('#save');
  await save.waitFor({ state: 'visible', timeout: 15000 });
  await page.waitForFunction(() => {
    const button = document.querySelector('#save');
    return button && !button.disabled;
  }, null, { timeout: 15000 });
  await save.click();
  await page.waitForFunction(() => Boolean(window.wp?.customize?.state?.('saved')?.get?.()), null, { timeout: 30000 });
}

async function menuInventory(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const options = page.locator('#select-menu-to-edit option');
  const menus = [];
  for (let i = 0; i < await options.count(); i += 1) {
    const option = options.nth(i);
    menus.push({ value: await option.getAttribute('value'), label: (await option.innerText()).trim() });
  }
  return menus;
}

async function createPhoneMenu(page) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=0`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('#menu-name').fill(MENU_NAME);
  await Promise.all([
    page.waitForURL(/nav-menus\.php.*menu=\d+/, { timeout: 30000 }),
    nativeClick(page.locator('#save_menu_header')),
  ]);
  const menuId = new URL(page.url()).searchParams.get('menu');
  if (!menuId || !/^\d+$/.test(menuId)) throw new Error('Created menu id unavailable');

  const customSection = page.locator('#add-custom-links');
  const title = customSection.locator('.accordion-section-title');
  if ((await title.getAttribute('aria-expanded')) !== 'true') await title.click();
  await customSection.locator('#custom-menu-item-url').fill(PHONE_HREF);
  await customSection.locator('#custom-menu-item-name').fill(PHONE_DISPLAY);
  await customSection.locator('#submit-customlinkdiv').click();
  const item = page.locator('#menu-to-edit .menu-item').filter({ hasText: PHONE_DISPLAY });
  await item.waitFor({ state: 'visible', timeout: 15000 });
  await nativeClick(page.locator('#save_menu_header'));
  await page.waitForLoadState('domcontentloaded');
  return Number(menuId);
}

async function phoneMenuState(page, menuId) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${menuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const item = page.locator('#menu-to-edit .menu-item').filter({ hasText: PHONE_DISPLAY });
  const count = await item.count();
  let href = '';
  if (count > 0) {
    const urlField = item.first().locator('input.edit-menu-item-url');
    href = (await urlField.count()) ? await urlField.inputValue() : '';
  }
  return { menu_id: menuId, item_count: count, href };
}

async function maybeAssignHeaderUtility(page, menuId) {
  await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const select = page.locator('select[name="menu-locations[header-utility]"]');
  if ((await select.count()) !== 1) return { registered: false, assigned: false };
  const before = await select.inputValue();
  if (before !== String(menuId)) {
    await select.selectOption(String(menuId));
    const submit = page.locator('input[type="submit"].button-primary').first();
    await submit.click();
    await page.waitForLoadState('domcontentloaded');
  }
  const after = await page.locator('select[name="menu-locations[header-utility]"]').inputValue();
  return { registered: true, assigned: after === String(menuId), before };
}

async function rollbackCustomLogo(page) {
  try {
    await setCustomLogo(page, 0);
    state.rollback.push('custom_logo_restored_to_0');
  } catch (error) {
    state.rollback.push(`custom_logo_rollback_failed:${error.message}`);
  }
}

async function deleteUploadedMedia(page, nonce, mediaId) {
  try {
    const response = await page.request.delete(`${baseUrl}/wp-json/wp/v2/media/${mediaId}?force=true`, {
      headers: { 'X-WP-Nonce': nonce },
      timeout: 30000,
    });
    state.rollback.push(response.ok() ? `media_deleted:${mediaId}` : `media_delete_failed:${response.status()}`);
  } catch (error) {
    state.rollback.push(`media_delete_failed:${error.message}`);
  }
}

async function deleteCreatedMenu(page, menuId) {
  try {
    await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${menuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    page.once('dialog', (dialog) => dialog.accept());
    const link = page.locator('.menu-delete');
    if ((await link.count()) === 1) {
      await link.click();
      await page.waitForLoadState('domcontentloaded');
      state.rollback.push(`menu_deleted:${menuId}`);
    }
  } catch (error) {
    state.rollback.push(`menu_delete_failed:${error.message}`);
  }
}

const browser = await chromium.launch({ headless: true });
let mediaId = 0;
let menuId = 0;
let nonce = '';
let logoApplied = false;
let menuCreated = false;
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  const support = await supportSnapshot(page);
  const beforePublic = await publicState(page);
  const beforeMenus = await menuInventory(page);
  state.before_state = {
    theme_version: support?.report?.theme?.version || null,
    custom_logo: support?.report?.standalone_core?.logo ?? null,
    public: beforePublic,
    menus: beforeMenus,
  };

  if (state.before_state.custom_logo !== false) throw new Error(`Precondition drift: custom_logo expected false, got ${state.before_state.custom_logo}`);
  if (beforePublic.tel_links.some((item) => item.href === PHONE_HREF)) throw new Error(`Precondition drift: ${PHONE_HREF} already public`);
  if (beforeMenus.some((item) => item.label === MENU_NAME)) throw new Error(`Precondition drift: menu already exists: ${MENU_NAME}`);

  nonce = await restNonce(page);
  mediaId = await uploadLogo(page, nonce);
  state.mutations.push(`media_uploaded:${mediaId}:${LOGO_FILENAME}`);

  await setCustomLogo(page, mediaId);
  logoApplied = true;
  state.mutations.push(`custom_logo:${mediaId}`);

  menuId = await createPhoneMenu(page);
  menuCreated = true;
  state.mutations.push(`menu_created:${menuId}:${MENU_NAME}`);
  state.mutations.push(`phone_link:${PHONE_DISPLAY}:${PHONE_HREF}`);

  const headerUtility = await maybeAssignHeaderUtility(page, menuId);
  if (headerUtility.registered && headerUtility.assigned) {
    state.mutations.push(`header_utility_assigned:${menuId}`);
  } else if (!headerUtility.registered) {
    state.blocked.push('header_utility_rendering: active Theme does not register header-utility; menu retained WordPress-owned for later compatible Theme deployment');
  }

  const menu = await phoneMenuState(page, menuId);
  const afterPublic = await publicState(page);
  state.after_state = { menu, header_utility: headerUtility, public: afterPublic };

  if (menu.item_count !== 1 || menu.href !== PHONE_HREF) throw new Error(`Phone menu verification failed: ${JSON.stringify(menu)}`);
  if (afterPublic.logo_count < 1 || !afterPublic.logo_src) throw new Error('Public custom logo did not render after save');
  if (headerUtility.registered && !afterPublic.tel_links.some((item) => item.href === PHONE_HREF)) throw new Error('Registered header utility did not expose official phone publicly');

  await page.screenshot({ path: path.join(stateDir, 'homepage-after-1440.png'), fullPage: true });
  await context.close();
} catch (error) {
  state.error = error instanceof Error ? error.message : String(error);
  const context = browser.contexts()[0];
  const pages = context?.pages() || [];
  const page = pages[0];
  if (page) {
    if (menuCreated && menuId) await deleteCreatedMenu(page, menuId);
    if (logoApplied) await rollbackCustomLogo(page);
    if (mediaId && nonce) await deleteUploadedMedia(page, nonce, mediaId);
  }
  process.exitCode = 1;
} finally {
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(state, null, 2));
  await browser.close();
}

if (process.exitCode !== 1) console.log('PASS: Tâm Đức official logo and phone applied through WordPress-owned surfaces');
