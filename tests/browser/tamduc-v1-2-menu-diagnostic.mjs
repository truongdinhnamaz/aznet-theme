import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-v1-2-menu-diagnostic';
const knownMenuId = 15;
const utilityLocation = 'header-utility';

fs.mkdirSync(stateDir, { recursive: true });
const summary = { target: baseUrl, mutation: false, support: null, plain_menu_screen: null, locations: null, direct_menu_15: null, errors: [] };
const write = () => fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));

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

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);

  try {
    await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=system-health`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const box = page.locator('textarea[readonly]');
    summary.support = (await box.count()) === 1 ? JSON.parse(await box.inputValue()) : { unavailable: true };
  } catch (error) {
    summary.errors.push(`support:${error.message}`);
  }
  write();

  try {
    await page.goto(`${baseUrl}/wp-admin/nav-menus.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const options = page.locator('#select-menu-to-edit option');
    const optionRows = [];
    for (let i = 0; i < await options.count(); i += 1) {
      const option = options.nth(i);
      optionRows.push({ value: await option.getAttribute('value'), label: (await option.innerText()).trim(), selected: await option.isChecked().catch(() => false) });
    }
    summary.plain_menu_screen = {
      url: page.url(),
      title: await page.title(),
      select_count: await page.locator('#select-menu-to-edit').count(),
      option_count: optionRows.length,
      options: optionRows,
      menu_name_count: await page.locator('#menu-name').count(),
      menu_name_value: (await page.locator('#menu-name').count()) ? await page.locator('#menu-name').inputValue() : null,
    };
  } catch (error) {
    summary.errors.push(`plain_menu:${error.message}`);
  }
  write();

  try {
    await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=locations`, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const select = page.locator(`select[name="menu-locations[${utilityLocation}]"]`);
    const selectedValue = (await select.count()) === 1 ? await select.inputValue() : null;
    const selectedText = selectedValue !== null
      ? await select.locator(`option[value="${selectedValue}"]`).innerText().catch(() => null)
      : null;
    summary.locations = {
      url: page.url(),
      select_count: await select.count(),
      selected_value: selectedValue,
      selected_text: selectedText?.trim() || null,
    };
  } catch (error) {
    summary.errors.push(`locations:${error.message}`);
  }
  write();

  try {
    await page.goto(`${baseUrl}/wp-admin/nav-menus.php?action=edit&menu=${knownMenuId}`, { waitUntil: 'domcontentloaded', timeout: 30000 });
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
    summary.direct_menu_15 = {
      url: page.url(),
      menu_name_count: await page.locator('#menu-name').count(),
      menu_name: (await page.locator('#menu-name').count()) ? await page.locator('#menu-name').inputValue() : null,
      item_count: items.length,
      items,
      error_notice: await page.locator('.notice-error, .error').allInnerTexts().catch(() => []),
    };
  } catch (error) {
    summary.errors.push(`direct_menu_15:${error.message}`);
  }
  write();
  await context.close();
} catch (error) {
  summary.errors.push(`fatal:${error.message}`);
  write();
  process.exitCode = 1;
} finally {
  await browser.close();
}

console.log(JSON.stringify(summary, null, 2));
