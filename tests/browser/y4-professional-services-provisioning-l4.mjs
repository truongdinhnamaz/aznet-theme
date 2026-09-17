import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.Y4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.Y4_STATE_DIR || '/tmp/y4-browser';
const adminPassword = process.env.Y4_ADMIN_PASSWORD || 'y4-provisioning-password';
const screenshots = path.join(stateDir, 'screenshots');
const axeDir = path.join(stateDir, 'axe');
fs.mkdirSync(screenshots, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const blockingImpacts = new Set(['critical', 'serious']);
const summary = { admin: {}, public: [] };

async function assertAxe(page, label) {
  const result = await new AxeBuilder({ page }).analyze();
  fs.writeFileSync(path.join(axeDir, `${label}.json`), JSON.stringify(result, null, 2));
  const blocking = result.violations.filter((violation) => blockingImpacts.has(violation.impact));
  if (blocking.length > 0) {
    throw new Error(`${label}: blocking axe violations ${blocking.map((v) => `${v.id}:${v.impact}`).join(', ')}`);
  }
}

async function assertDocumentBoundary(page, label) {
  if (await page.locator('main#main').count() !== 1) throw new Error(`${label}: expected exactly one main#main`);
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) throw new Error(`${label}: horizontal overflow ${overflow}px`);
  return overflow;
}

const browser = await chromium.launch({ headless: true });
try {
  const adminContext = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await adminContext.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill(adminPassword);
  await Promise.all([
    page.waitForURL(/wp-admin/),
    page.locator('#wp-submit').click(),
  ]);

  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=provisioning&step=1`, { waitUntil: 'networkidle' });
  await page.getByRole('heading', { name: 'Kiểm tra website' }).waitFor();
  const blueprint = page.locator('#aznet-theme-provision-blueprint');
  await blueprint.selectOption('professional-services-v1');
  if ((await blueprint.inputValue()) !== 'professional-services-v1') throw new Error('Professional Services blueprint was not selected');
  await assertAxe(page, 'y4-admin-step1');

  await Promise.all([
    page.waitForURL(/step=2.*blueprint=professional-services-v1|blueprint=professional-services-v1.*step=2/),
    page.getByRole('button', { name: 'Tiếp tục' }).click(),
  ]);
  await page.getByRole('heading', { name: 'Thiết lập được khuyến nghị' }).waitFor();
  const cards = await page.locator('.aznet-theme-provision-recommendation').count();
  if (cards !== 5) throw new Error(`expected five Professional Services recommendation cards, got ${cards}`);
  const selectedBlueprint = page.locator('input[name="blueprint"]');
  if ((await selectedBlueprint.inputValue()) !== 'professional-services-v1') throw new Error('step 2 lost the selected Professional Services blueprint');
  for (const title of ['Giải pháp chuyên nghiệp cho nhu cầu của bạn', 'Giới thiệu', 'Dịch vụ', 'Đội ngũ', 'Liên hệ']) {
    if (await page.locator('.aznet-theme-provision-recommendation h4', { hasText: title }).count() !== 1) {
      throw new Error(`step 2 missing visible recommendation title: ${title}`);
    }
  }
  const step2Text = await page.locator('.aznet-theme-provisioning').innerText();
  if (!step2Text.includes('không tạo dữ liệu chuyên ngành')) throw new Error('Professional Services ownership warning missing');
  if (await page.locator('input[name^="starter_posts["]').count() !== 0) throw new Error('generic Professional Services wizard exposed Law starter Posts');
  await assertAxe(page, 'y4-admin-step2');
  await page.screenshot({ path: path.join(screenshots, 'y4-admin-step2.png'), fullPage: true });

  await Promise.all([
    page.waitForURL(/step=3/),
    page.getByRole('button', { name: 'Dùng các thiết lập được khuyến nghị' }).click(),
  ]);
  await page.getByRole('heading', { name: 'Xem trước thay đổi' }).waitFor();
  const planText = await page.locator('.aznet-theme-provisioning').innerText();
  for (const expected of ['Sẽ tạo mới', 'CREATE Page', 'SET Front Page', 'SET Homepage preset → off']) {
    if (!planText.includes(expected)) throw new Error(`review plan missing ${expected}`);
  }
  if (planText.includes('MAP Homepage Content Map')) throw new Error('generic Professional Services plan leaked Law01 homepage mapping');
  const confirm = page.locator('input[name="confirm_plan"]');
  if (await confirm.isChecked()) throw new Error('plan confirmation must not be prechecked');
  await assertAxe(page, 'y4-admin-step3');
  await confirm.check();

  await Promise.all([
    page.waitForURL(/step=4/),
    page.getByRole('button', { name: 'Áp dụng thiết lập' }).click(),
  ]);
  await page.getByRole('heading', { name: 'Thiết lập hoàn tất — Website đã sẵn sàng để chỉnh nội dung.' }).waitFor({ timeout: 30000 });
  const completionText = await page.locator('.aznet-theme-provisioning').innerText();
  if (/Launch Ready/i.test(completionText)) throw new Error('Y4 admin UI must not label provisioning success Launch Ready');
  await assertAxe(page, 'y4-admin-step4');
  await page.screenshot({ path: path.join(screenshots, 'y4-admin-step4.png'), fullPage: true });
  if (consoleErrors.length > 0 || pageErrors.length > 0) throw new Error(`admin browser errors: ${[...consoleErrors, ...pageErrors].join(' | ')}`);
  summary.admin = { recommendationCards: cards, confirmationRequired: true, launchReadyLabel: false, axeBlocking: 0 };
  await adminContext.close();

  const routes = [
    ['home', '/', 'Giải pháp chuyên nghiệp cho nhu cầu của bạn'],
    ['about', '/gioi-thieu/', 'Giới thiệu'],
    ['services', '/dich-vu/', 'Dịch vụ'],
    ['team', '/doi-ngu/', 'Đội ngũ'],
    ['contact', '/lien-he/', 'Liên hệ'],
  ];
  const viewports = {
    '1440x1000': { width: 1440, height: 1000 },
    '390x844': { width: 390, height: 844 },
  };

  for (const [viewportName, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const publicPage = await context.newPage();
    const errors = [];
    publicPage.on('console', (message) => { if (message.type() === 'error') errors.push(message.text()); });
    publicPage.on('pageerror', (error) => errors.push(error.message));

    for (const [role, route, heading] of routes) {
      const response = await publicPage.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
      if (!response || response.status() !== 200) throw new Error(`${viewportName} ${role}: HTTP ${response?.status()}`);
      const h1 = publicPage.getByRole('heading', { level: 1, name: heading, exact: true });
      await h1.waitFor({ state: 'visible', timeout: 20000 });
      if (await publicPage.locator('h1').count() !== 1) throw new Error(`${viewportName} ${role}: expected exactly one H1`);
      const overflow = await assertDocumentBoundary(publicPage, `${viewportName} ${role}`);
      if (role !== 'home' && await publicPage.locator('.aznet-theme-page').count() !== 1) throw new Error(`${viewportName} ${role}: shared Page presentation missing`);
      await assertAxe(publicPage, `y4-${role}-${viewportName}`);
      await publicPage.screenshot({ path: path.join(screenshots, `y4-${role}-${viewportName}.png`), fullPage: true });
      summary.public.push({ role, viewport: viewportName, status: response.status(), h1: heading, overflow, axeBlocking: 0 });
    }

    if (errors.length > 0) throw new Error(`${viewportName}: public browser errors ${errors.join(' | ')}`);
    await context.close();
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(stateDir, 'y4-browser-summary.json'), JSON.stringify(summary, null, 2));
console.log('PASS: Y4 Professional Services confirmed wizard + five public Page surfaces L4');
