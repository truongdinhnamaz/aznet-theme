import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.WOO_L3_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const l3StateDir = process.env.WOO_L3_STATE_DIR || '/tmp/woo-l3';
const outputDir = process.env.WOO_L4_STATE_DIR || '/tmp/woo-l4';
const stateFile = path.join(l3StateDir, 'runtime-ids.env');
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');

function parseEnvFile(file) {
  const result = {};
  const text = fs.readFileSync(file, 'utf8');
  for (const line of text.split(/\r?\n/)) {
    if (!line || line.startsWith('#')) continue;
    const index = line.indexOf('=');
    if (index < 1) continue;
    result[line.slice(0, index)] = line.slice(index + 1);
  }
  return result;
}

function ensurePositiveId(ids, key) {
  const value = Number.parseInt(ids[key], 10);
  if (!Number.isInteger(value) || value <= 0) {
    throw new Error(`missing/invalid runtime id: ${key}`);
  }
  return value;
}

async function visibleAndEnabled(page, selector, label) {
  const locator = page.locator(selector).first();
  await locator.waitFor({ state: 'visible', timeout: 15000 });
  if (await locator.isDisabled().catch(() => false)) {
    throw new Error(`${label} is disabled: ${selector}`);
  }
}

async function assertKeyboardFocusVisible(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  for (let attempt = 0; attempt < 60; attempt += 1) {
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const element = document.activeElement;
      const main = document.querySelector('main');
      if (!(element instanceof HTMLElement) || !main || !main.contains(element)) return null;

      const rect = element.getBoundingClientRect();
      const style = window.getComputedStyle(element);
      const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
      const hasOutline = style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent';
      const hasShadow = style.boxShadow && style.boxShadow !== 'none';

      return {
        tag: element.tagName.toLowerCase(),
        id: element.id || null,
        className: typeof element.className === 'string' ? element.className : null,
        visible: rect.width > 0 && rect.height > 0,
        hasIndicator: Boolean(hasOutline || hasShadow),
      };
    });

    if (focus?.visible && focus.hasIndicator) return focus;
  }

  throw new Error('keyboard Tab did not reach a visibly focused element inside main');
}

fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

if (!fs.existsSync(stateFile)) throw new Error(`missing L3 runtime state: ${stateFile}`);
const ids = parseEnvFile(stateFile);
const PRODUCT_ID = ensurePositiveId(ids, 'PRODUCT_ID');
const SHOP_PAGE_ID = ensurePositiveId(ids, 'SHOP_PAGE_ID');
const CART_PAGE_ID = ensurePositiveId(ids, 'CART_PAGE_ID');
const CHECKOUT_PAGE_ID = ensurePositiveId(ids, 'CHECKOUT_PAGE_ID');
const ACCOUNT_PAGE_ID = ensurePositiveId(ids, 'ACCOUNT_PAGE_ID');

const viewports = {
  desktop: { width: 1440, height: 1000 },
  mobile: { width: 390, height: 844 },
};

const surfaces = [
  {
    name: 'product',
    url: `/?post_type=product&p=${PRODUCT_ID}`,
    landmark: '.woocommerce div.product',
    interactive: 'button.single_add_to_cart_button',
  },
  {
    name: 'archive',
    url: `/?page_id=${SHOP_PAGE_ID}`,
    landmark: '.woocommerce ul.products',
    interactive: 'ul.products li.product a',
  },
  {
    name: 'cart',
    url: `/?page_id=${CART_PAGE_ID}`,
    landmark: '.woocommerce-cart-form',
    interactive: 'a.checkout-button',
  },
  {
    name: 'checkout',
    url: `/?page_id=${CHECKOUT_PAGE_ID}`,
    landmark: 'form.checkout',
    interactive: '#billing_first_name',
  },
  {
    name: 'account',
    url: `/?page_id=${ACCOUNT_PAGE_ID}`,
    landmark: '.woocommerce',
    interactive: '#username',
  },
];

const blockingImpacts = ['critical', 'serious'];
const summary = {
  baseUrl,
  blockingImpacts,
  cases: [],
};
const failures = [];
const browser = await chromium.launch({ headless: true });

try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();

    try {
      const addResponse = await page.goto(`${baseUrl}/?add-to-cart=${PRODUCT_ID}`, { waitUntil: 'networkidle' });
      if (!addResponse || addResponse.status() >= 400) {
        throw new Error(`failed to establish Woo cart session for ${viewportName}`);
      }

      for (const surface of surfaces) {
        const caseName = `${viewportName}-${surface.name}`;
        const screenshotPath = path.join(screenshotDir, `${caseName}.png`);
        const axePath = path.join(axeDir, `${caseName}.json`);
        const caseResult = {
          viewport: viewportName,
          width: viewport.width,
          height: viewport.height,
          surface: surface.name,
          status: 'failed',
          overflowPx: null,
          blockingAxeViolations: null,
          focus: null,
          error: null,
        };

        try {
          const response = await page.goto(`${baseUrl}${surface.url}`, { waitUntil: 'networkidle' });
          if (!response || response.status() >= 400) {
            throw new Error(`HTTP failure for ${caseName}`);
          }

          await page.locator(surface.landmark).first().waitFor({ state: 'visible', timeout: 15000 });
          await visibleAndEnabled(page, surface.interactive, `${caseName} interactive control`);

          const overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
          caseResult.overflowPx = overflowPx;
          if (overflowPx > 1) throw new Error(`horizontal overflow: ${overflowPx}px`);

          caseResult.focus = await assertKeyboardFocusVisible(page);

          const axeResults = await new AxeBuilder({ page }).analyze();
          fs.writeFileSync(axePath, JSON.stringify(axeResults, null, 2));
          const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
          caseResult.blockingAxeViolations = blocking.length;
          if (blocking.length > 0) {
            throw new Error(`blocking axe violations: ${blocking.map((item) => `${item.id}:${item.impact}`).join(', ')}`);
          }

          caseResult.status = 'passed';
          console.log(`PASS: ${caseName}`);
        } catch (error) {
          caseResult.error = error instanceof Error ? error.message : String(error);
          failures.push(`${caseName}: ${caseResult.error}`);
          console.error(`FAIL: ${caseName}: ${caseResult.error}`);
        } finally {
          await page.screenshot({ path: screenshotPath, fullPage: true }).catch((error) => {
            failures.push(`${caseName}: screenshot failed: ${error.message}`);
          });
          summary.cases.push(caseResult);
        }
      }
    } finally {
      await context.close();
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'browser-summary.json'), JSON.stringify(summary, null, 2));

if (summary.cases.length !== 10) {
  failures.push(`expected 10 browser cases, got ${summary.cases.length}`);
}

if (failures.length > 0) {
  throw new Error(`Woo L4 browser verification failed:\n${failures.join('\n')}`);
}

console.log('PASS: Woo L4 browser/visual/a11y automated verification 10/10');
