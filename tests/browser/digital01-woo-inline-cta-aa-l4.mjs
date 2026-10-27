import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

// Public read-only Woo L4 after Brand Light changed the native Front Page.
// Real Woo buttons now belong to the published Shop archive, not the editorial
// Homepage. Exercise the Theme's legacy optional inline-CTA selector with
// browser-local ancestor classes only; never click or update WooCommerce.
const url = process.env.DIGITAL01_PUBLIC_URL || 'https://aznet.vn/';
const shopUrl = new URL('/san-pham/', url).href;
const outDir = process.env.DIGITAL01_EVIDENCE_DIR || '/tmp/digital01-woo-inline-cta-aa';
const css = fs.readFileSync('assets/css/components/homepage-digital-01.css', 'utf8');
const selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__service .woocommerce a.add_to_cart_button';
const start = css.indexOf(selector + ' {');
if (start < 0) throw new Error('Candidate scoped Woo button foreground CSS missing');
const end = css.indexOf('}', start);
if (end < 0) throw new Error('Candidate scoped Woo button CSS block unterminated');
const candidateRule = css.slice(start, end + 1);

const output = {
  head: process.env.GITHUB_SHA || '',
  target: shopUrl,
  homepage: url,
  scope: 'published native Woo Shop public DOM; browser-local CSS opt-in and candidate overlay; neither installed Homepage Woo nor actual provider L5 certification',
  viewports: [],
  failures: [],
};
fs.mkdirSync(outDir, { recursive: true });

function luminosity(rgb) {
  return rgb.map((x) => {
    const c = x / 255;
    return c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4;
  }).reduce((sum, x, i) => sum + x * [0.2126, 0.7152, 0.0722][i], 0);
}
function channels(color) {
  const m = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
  if (!m) throw new Error('Unexpected browser CSS color ' + color);
  return m.slice(1, 4).map(Number);
}
function contrast(color, background) {
  const x = luminosity(channels(color));
  const y = luminosity(channels(background));
  return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05);
}

const browser = await chromium.launch({ headless: true });
try {
  for (const width of [390, 768, 1440]) {
    const context = await browser.newContext({
      viewport: { width, height: 900 },
      deviceScaleFactor: 1,
      reducedMotion: 'reduce',
    });
    const page = await context.newPage();
    const result = { width, pass: false };
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    try {
      let response = await page.goto(shopUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });
      if (response?.status() !== 200) response = await page.reload({ waitUntil: 'domcontentloaded', timeout: 45000 });
      if (response?.status() !== 200) throw new Error('Public Woo Shop status ' + String(response?.status()));

      const button = page.locator('main ul.products li.product a.add_to_cart_button').first();
      await button.waitFor({ state: 'visible', timeout: 15000 });
      const read = async () => button.evaluate((element) => {
        const style = getComputedStyle(element);
        return {
          color: style.color,
          background: style.backgroundColor,
          href: element.href,
          label: element.textContent.trim(),
          productId: element.getAttribute('data-product_id'),
          quantity: element.getAttribute('data-quantity'),
          ariaLabel: element.getAttribute('aria-label'),
          preset: document.body.classList.contains('aznet-theme-preset--digital-01'),
          scrollWidth: document.documentElement.scrollWidth,
          clientWidth: document.documentElement.clientWidth,
        };
      });

      const before = await read();
      if (!before.preset || !before.href || !before.label || !before.productId || !before.background) {
        throw new Error('Public Woo button missing native identity or Digital 01 preset');
      }
      const beforeRatio = contrast(before.color, before.background);
      const optIn = await button.evaluate((el) => {
        const product = el.closest('li.product');
        const list = product?.closest('ul.products');
        const main = list?.closest('main');
        if (!main || !list || !product) return false;
        // Existing button remains WordPress/Woo-owned; class changes only
        // affect this disposable Chromium DOM. No cart action or new data.
        main.classList.add('aznet-digital01__service');
        list.classList.add('woocommerce');
        return true;
      });
      if (!optIn) throw new Error('Native Woo Shop product structure missing');
      await page.addStyleTag({ content: candidateRule });
      const after = await read();
      const afterRatio = contrast(after.color, after.background);
      if (afterRatio < 4.5) throw new Error('Candidate Woo button AA ' + afterRatio.toFixed(2) + ':1 below 4.5:1');

      if (before.href !== after.href || before.label !== after.label ||
          before.productId !== after.productId || before.quantity !== after.quantity ||
          before.ariaLabel !== after.ariaLabel) {
        throw new Error('Theme presentation changed WooCommerce native button contract');
      }
      if (after.scrollWidth > Math.max(before.scrollWidth, width + 2)) {
        throw new Error('Candidate causes new horizontal overflow');
      }

      await button.hover();
      const hover = await read();
      const hoverRatio = contrast(hover.color, hover.background);
      if (hoverRatio < 4.5) throw new Error('Woo button hover AA ' + hoverRatio.toFixed(2) + ':1 below 4.5:1');

      await button.focus();
      const focus = await read();
      const focusRatio = contrast(focus.color, focus.background);
      if (focusRatio < 4.5) throw new Error('Woo button focus AA ' + focusRatio.toFixed(2) + ':1 below 4.5:1');
      if (errors.length) throw new Error('Page JS errors: ' + errors.join(' | '));

      result.before = before;
      result.after = after;
      result.beforeRatio = Number(beforeRatio.toFixed(2));
      result.afterRatio = Number(afterRatio.toFixed(2));
      result.hoverRatio = Number(hoverRatio.toFixed(2));
      result.focusRatio = Number(focusRatio.toFixed(2));
      await button.screenshot({ path: path.join(outDir, 'woo-inline-cta-' + width + '.png'), animations: 'disabled' });
      result.pass = true;
      console.log('PASS: Digital 01 Woo inline CTA ' + width + 'px before=' + beforeRatio.toFixed(2) +
        ':1 after=' + afterRatio.toFixed(2) + ':1 hover=' + hoverRatio.toFixed(2) +
        ':1 focus=' + focusRatio.toFixed(2) + ':1; native Woo identity unchanged');
    } catch (error) {
      result.error = error instanceof Error ? error.message : String(error);
      output.failures.push(width + ': ' + result.error);
      console.error('FAIL: Digital 01 Woo inline CTA ' + width + 'px: ' + result.error);
    } finally {
      output.viewports.push(result);
      await context.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(output, null, 2));
}
if (output.failures.length) process.exitCode = 1;
else console.log('PASS: Digital 01 Woo Shop native CTA AA 3/3 (public native buttons + synthetic optional Theme opt-in, not production Homepage Woo)');
