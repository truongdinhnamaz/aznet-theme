import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.U0_L4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputDir = process.env.U0_L4_STATE_DIR || '/tmp/u0-l4';
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
const blockingImpacts = ['critical', 'serious'];

fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  desktop: { width: 1440, height: 1000 },
  compact: { width: 1024, height: 768 },
};

const requiredText = [
  'AZnet Theme',
  'Logo',
  'Primary Menu',
  'WooCommerce',
  'Lưu thiết lập nền',
  'Đặt lại thiết lập AZnet Theme',
];

async function login(page) {
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill('aznet-runtime');
  await page.locator('#user_pass').fill('aznet-runtime-pass');
  await Promise.all([
    page.waitForURL((url) => url.pathname.startsWith('/wp-admin/'), { timeout: 15000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function assertKeyboardFocusVisible(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  // keyboard Tab must eventually reach an actionable Control Center element.
  for (let attempt = 0; attempt < 100; attempt += 1) {
    await page.keyboard.press('Tab');
    const state = await page.evaluate(() => {
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
        text: (element.textContent || '').trim().slice(0, 100),
        visible: rect.width > 0 && rect.height > 0,
        hasIndicator: Boolean(hasOutline || hasShadow),
      };
    });

    if (state?.visible && state.hasIndicator) return state;
  }

  throw new Error('keyboard Tab did not reach a visibly focused Control Center action');
}

const summary = { baseUrl, blockingImpacts, cases: [] };
const failures = [];
const browser = await chromium.launch({ headless: true });

try {
  for (const [name, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
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
      await login(page);
      const response = await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme`, { waitUntil: 'networkidle' });
      if (!response || response.status() >= 400) throw new Error(`Control Center HTTP status ${response?.status() ?? 'none'}`);

      await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 15000 });
      for (const text of requiredText) {
        await page.getByText(text, { exact: false }).first().waitFor({ state: 'visible', timeout: 10000 });
      }

      const overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
      result.overflowPx = overflowPx;
      if (overflowPx > 1) throw new Error(`horizontal overflow: ${overflowPx}px`);

      result.focus = await assertKeyboardFocusVisible(page);

      const axeResults = await new AxeBuilder({ page })
        .include('.aznet-theme-control-center')
        .analyze();
      fs.writeFileSync(path.join(axeDir, `${name}.json`), JSON.stringify(axeResults, null, 2));
      const blocking = axeResults.violations.filter((violation) => blockingImpacts.includes(violation.impact));
      result.blockingAxeViolations = blocking.length;
      if (blocking.length > 0) {
        throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);
      }

      result.status = 'passed';
      console.log(`PASS: ${name} Control Center browser/a11y`);
    } catch (error) {
      result.error = error instanceof Error ? error.message : String(error);
      failures.push(`${name}: ${result.error}`);
      console.error(`FAIL: ${name}: ${result.error}`);
    } finally {
      await page.screenshot({ path: path.join(screenshotDir, `control-center-${viewport.width}x${viewport.height}.png`), fullPage: true }).catch((error) => {
        failures.push(`${name}: screenshot failed: ${error.message}`);
      });
      summary.cases.push(result);
      await context.close();
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));

if (summary.cases.length !== 2) failures.push(`expected 2 browser cases, got ${summary.cases.length}`);
if (failures.length > 0) throw new Error(`U0 Control Center L4 failed:\n${failures.join('\n')}`);
console.log('PASS: U0 Control Center L4 browser/visual/a11y 2/2');
