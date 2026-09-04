import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.U0_L4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.U0_L4_STATE_DIR || '/tmp/u0-l4';
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');

fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x768': { width: 1024, height: 768 },
};

const summary = {
  baseUrl,
  cases: [],
};
const failures = [];

async function login(page) {
  const response = await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  if (!response || response.status() >= 400) throw new Error('login page HTTP failure');

  await page.locator('#user_login').fill('aznet-runtime');
  await page.locator('#user_pass').fill('aznet-runtime-pass');
  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.locator('#wp-submit').click(),
  ]);

  if (page.url().includes('wp-login.php')) throw new Error('administrator login did not complete');
}

async function assertVisibleControlCenter(page) {
  await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 15000 });
  await page.locator('.aznet-theme-control-center h1').filter({ hasText: 'AZnet Theme' }).waitFor({ state: 'visible' });

  for (const heading of ['Logo', 'Primary Menu', 'WooCommerce']) {
    await page.locator('.aznet-theme-control-center h2').filter({ hasText: heading }).first().waitFor({ state: 'visible' });
  }

  await page.getByRole('button', { name: 'Lưu thiết lập nền' }).waitFor({ state: 'visible' });
  await page.getByRole('button', { name: 'Đặt lại thiết lập AZnet Theme' }).waitFor({ state: 'visible' });
}

async function assertKeyboardFocusVisible(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  for (let attempt = 0; attempt < 100; attempt += 1) {
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const root = document.querySelector('.aznet-theme-control-center');
      const element = document.activeElement;
      if (!(root instanceof HTMLElement) || !(element instanceof HTMLElement) || !root.contains(element)) return null;

      const rect = element.getBoundingClientRect();
      const style = window.getComputedStyle(element);
      const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
      const hasOutline = style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent';
      const hasShadow = Boolean(style.boxShadow && style.boxShadow !== 'none');

      return {
        tag: element.tagName.toLowerCase(),
        text: element.textContent?.trim().slice(0, 80) || element.getAttribute('value') || '',
        visible: rect.width > 0 && rect.height > 0,
        hasIndicator: Boolean(hasOutline || hasShadow),
      };
    });

    if (focus?.visible && focus.hasIndicator) return focus;
  }

  throw new Error('keyboard Tab did not reach a visibly focused Control Center action');
}

async function verifySaveReset(page) {
  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme`, { waitUntil: 'domcontentloaded' });
  await assertVisibleControlCenter(page);

  await Promise.all([
    page.waitForURL(/aznet_theme_notice=saved/, { timeout: 15000 }),
    page.getByRole('button', { name: 'Lưu thiết lập nền' }).click(),
  ]);
  await page.locator('.notice-success').filter({ hasText: 'Đã lưu thiết lập nền AZnet Theme.' }).waitFor({ state: 'visible' });

  await page.getByRole('checkbox', { name: /Tôi xác nhận đặt lại thiết lập presentation của AZnet Theme/ }).check();
  await Promise.all([
    page.waitForURL(/aznet_theme_notice=reset/, { timeout: 15000 }),
    page.getByRole('button', { name: 'Đặt lại thiết lập AZnet Theme' }).click(),
  ]);
  await page.locator('.notice-success').filter({ hasText: 'Đã đặt lại thiết lập AZnet Theme.' }).waitFor({ state: 'visible' });
}

const browser = await chromium.launch({ headless: true });

try {
  const context = await browser.newContext({ viewport: viewports['1440x1000'] });
  const page = await context.newPage();

  try {
    await login(page);
    await verifySaveReset(page);

    for (const [name, viewport] of Object.entries(viewports)) {
      const result = {
        viewport: name,
        width: viewport.width,
        height: viewport.height,
        status: 'failed',
        overflowPx: null,
        blockingAxeViolations: null,
        focus: null,
        error: null,
      };

      try {
        await page.setViewportSize(viewport);
        const response = await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme`, { waitUntil: 'networkidle' });
        if (!response || response.status() >= 400) throw new Error(`Control Center HTTP failure at ${name}`);
        await assertVisibleControlCenter(page);

        const overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
        result.overflowPx = overflowPx;
        if (overflowPx > 1) throw new Error(`horizontal overflow: ${overflowPx}px`);

        result.focus = await assertKeyboardFocusVisible(page);

        const axeResults = await new AxeBuilder({ page })
          .include('.aznet-theme-control-center')
          .analyze();
        fs.writeFileSync(path.join(axeDir, `control-center-${name}.json`), JSON.stringify(axeResults, null, 2));
        const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
        result.blockingAxeViolations = blocking.length;
        if (blocking.length > 0) {
          throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);
        }

        await page.screenshot({
          path: path.join(screenshotDir, `control-center-${name}.png`),
          fullPage: true,
        });

        result.status = 'passed';
        console.log(`PASS: control-center-${name}`);
      } catch (error) {
        result.error = error instanceof Error ? error.message : String(error);
        failures.push(`${name}: ${result.error}`);
        console.error(`FAIL: control-center-${name}: ${result.error}`);
        await page.screenshot({
          path: path.join(screenshotDir, `control-center-${name}-failure.png`),
          fullPage: true,
        }).catch(() => {});
      } finally {
        summary.cases.push(result);
      }
    }
  } finally {
    await context.close();
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));

if (summary.cases.length !== 2) failures.push(`expected 2 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`U0 L4 browser verification failed:\n${failures.join('\n')}`);

console.log('PASS: U0 Control Center browser/visual/a11y automated verification 2/2');
