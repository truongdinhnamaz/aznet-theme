import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const base = (process.env.D043_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const variant = process.env.D043_TEMPLATE || 'curtain';
const dest = process.env.D043_STATE_DIR || '/tmp/d043-browser';
const passwd = process.env.D043_ADMIN_PASSWORD || 'd043-provisioning-password';
const template = {
  curtain: { preset: 'curtain-01', blueprint: 'curtain01-v1', label: 'Rèm 01', unique: 'Bộ sưu tập' },
  industrial: { preset: 'industrial-01', blueprint: 'industrial01-v1', label: 'Industrial 01', unique: 'Giải pháp' },
}[variant];
if (!template) throw new Error('Invalid D043 template fixture');
fs.mkdirSync(dest, { recursive: true });
const errors = [];
async function assertAxe(page, name) {
  const a = await new AxeBuilder({ page }).analyze();
  fs.writeFileSync(path.join(dest, name + '.axe.json'), JSON.stringify(a, null, 2));
  const bad = a.violations.filter(v => ['critical', 'serious'].includes(v.impact));
  if (bad.length) throw new Error(name + ' axe: ' + bad.map(v => v.id + ':' + v.impact).join(','));
}
const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await context.newPage();
  page.on('pageerror', e => errors.push(e.message));
  await page.goto(base + '/wp-login.php', { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill(passwd);
  await Promise.all([page.waitForURL(/wp-admin/), page.locator('#wp-submit').click()]);
  await page.goto(base + '/wp-admin/admin.php?page=aznet-theme&section=provisioning&step=1', { waitUntil: 'networkidle' });
  await page.getByRole('heading', { name: 'Kiểm tra website' }).waitFor();
  const select = page.locator('#aznet-theme-provision-blueprint');
  const selected = await select.inputValue();
  if (selected !== template.blueprint) throw new Error(variant + ' selected ' + selected + ', expected ' + template.blueprint);
  if ((await select.locator('option:checked').textContent())?.trim() !== template.label) throw new Error('Blueprint label incorrect');
  if (await page.locator('.aznet-theme-provision-unavailable').count()) throw new Error('Registered recipe incorrectly unavailable');
  await assertAxe(page, variant + '-step1');

  await Promise.all([page.waitForURL(/step=2/), page.getByRole('button', { name: 'Tiếp tục' }).click()]);
  await page.getByRole('heading', { name: 'Thiết lập được khuyến nghị' }).waitFor();
  const cards = await page.locator('.aznet-theme-provision-recommendation').count();
  if (cards !== 8) throw new Error('Expected six Pages + two Categories, got ' + cards);
  if ((await page.locator('input[name="blueprint"]').inputValue()) !== template.blueprint) throw new Error('Blueprint changed across steps');
  const t = await page.locator('.aznet-theme-provisioning').innerText();
  if (!t.includes(template.unique) || !t.includes('WordPress-native') || !t.includes('Không tạo sản phẩm')) throw new Error('Template-specific content/ownership warning missing');
  if (await page.locator('input[name^="starter_posts["]').count()) throw new Error('Law starter post UI leaked');
  await assertAxe(page, variant + '-step2');
  await page.screenshot({ path: path.join(dest, variant + '-step2.png'), fullPage: true });

  await Promise.all([page.waitForURL(/step=3/), page.getByRole('button', { name: 'Dùng các thiết lập được khuyến nghị' }).click()]);
  await page.getByRole('heading', { name: 'Xem trước thay đổi' }).waitFor();
  const plan = await page.locator('.aznet-theme-provisioning').innerText();
  for (const required of ['CREATE Page', 'CREATE Category', 'SET Front Page', 'SET Homepage preset → ' + template.preset]) {
    if (!plan.includes(required)) throw new Error('Missing plan operation: ' + required);
  }
  if (plan.includes('MAP Homepage Content Map') || plan.includes('SET starter Site Title')) throw new Error('Cross-template mutation in plan');
  const confirm = page.locator('input[name="confirm_plan"]');
  if (await confirm.isChecked()) throw new Error('Plan confirmation was already checked');
  await assertAxe(page, variant + '-step3');
  await confirm.check();
  await Promise.all([page.waitForURL(/step=4/), page.getByRole('button', { name: 'Áp dụng thiết lập' }).click()]);
  await page.getByRole('heading', { name: 'Thiết lập hoàn tất — Website đã sẵn sàng để chỉnh nội dung.' }).waitFor({ timeout: 30000 });
  await assertAxe(page, variant + '-step4');
  await page.screenshot({ path: path.join(dest, variant + '-step4.png'), fullPage: true });

  await page.goto(base + '/', { waitUntil: 'networkidle' });
  if (!await page.locator('.aznet-theme-site-header').count()) throw new Error('Theme header missing after setup');
  if (await page.locator('body.wp-theme-aznet-theme').count() !== 1) throw new Error('Unexpected active Theme');
  if (errors.length) throw new Error('Browser runtime errors: ' + errors.join(' | '));
  fs.writeFileSync(path.join(dest, variant + '-summary.json'), JSON.stringify({ template, cards, confirmed: true, axeBlocking: 0, pageErrors: errors }, null, 2));
  await context.close();
} finally {
  await browser.close();
}
console.log('PASS: D-043 ' + variant + ' browser wizard, confirm, native apply + frontend smoke');
