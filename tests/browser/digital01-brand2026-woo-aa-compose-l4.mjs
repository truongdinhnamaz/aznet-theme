import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

/**
 * QA-only WooCommerce composability probe after the published Front Page
 * changed from a Woo inline service card to WordPress-authored Brand Light.
 *
 * Source-owned native Woo product buttons are read from the public Shop
 * archive, not inferred from editorial Homepage cards. Only ephemeral browser
 * classes opt those existing Woo elements into Theme presentation CSS. This
 * never clicks Add to Cart, mutates the site, or claims actual L5 integration.
 */
const site = process.env.DIGITAL01_PUBLIC_URL || 'https://aznet.vn/';
const shopUrl = new URL('/san-pham/', site).href;
const baseFile = 'assets/css/components/homepage-digital-01.css';
const brandFile = 'assets/css/components/homepage-digital-01-brand-2026.css';
const mainCSS = fs.readFileSync(baseFile, 'utf8');
const brandCSS = fs.readFileSync(brandFile, 'utf8');
const out = process.env.DIGITAL01_STATE_DIR || '/tmp/digital01-brand';
fs.mkdirSync(out, { recursive: true });
if (!mainCSS.includes('.aznet-digital01__service .woocommerce a.add_to_cart_button {color:#192e3b;}')) {
  throw new Error('Current-main native Woo Add-to-Cart contrast regression CSS is missing');
}
if (!brandCSS.includes('.aznet-digital01--brand-2026') ||
    !brandCSS.includes('.aznet-digital01__service a')) {
  throw new Error('Brand Light scoped link styling is missing');
}

function channels(rgb) {
  const match = rgb.match(/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/);
  if (!match) throw new Error('Unsupported computed CSS color ' + rgb);
  return match.slice(1, 4).map(Number);
}
function luminance(rgb) {
  return channels(rgb)
    .map(x => x / 255)
    .map(v => v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4))
    .reduce((a, x, i) => a + x * [0.2126, 0.7152, 0.0722][i], 0);
}
function ratio(fg, bg) {
  const x = luminance(fg);
  const y = luminance(bg);
  return (Math.max(x, y) + .05) / (Math.min(x, y) + .05);
}
const result = {
  sourceCommit: process.env.GITHUB_SHA || 'local',
  homepage: site,
  url: shopUrl,
  disclosure: 'Actual published Woo Shop HTML with exact candidate CSS + browser-local opt-in; L4 synthetic composition only, NOT provider L5 or Theme deployment',
  cases: [],
  failures: [],
};
const browser = await chromium.launch({ headless: true });
for (const width of [390, 768, 1440]) {
  const context = await browser.newContext({ viewport: { width, height: 900 }, reducedMotion: 'reduce' });
  const page = await context.newPage();
  const data = { width, status: 'failed' };
  const errors = [];
  page.on('pageerror', e => errors.push(e.message));
  try {
    const response = await page.goto(shopUrl, { waitUntil: 'domcontentloaded', timeout: 45000 });
    if (!response || response.status() !== 200) throw new Error('Public Woo Shop HTTP ' + (response?.status() ?? 'unavailable'));
    const button = page.locator('main ul.products li.product a.add_to_cart_button').first();
    await button.waitFor({ state: 'visible', timeout: 15000 });

    const state = () => button.evaluate(el => {
      const css = getComputedStyle(el);
      return {
        color: css.color,
        background: css.backgroundColor,
        text: el.textContent.trim(),
        href: el.getAttribute('href'),
        productId: el.getAttribute('data-product_id'),
        quantity: el.getAttribute('data-quantity'),
        ariaLabel: el.getAttribute('aria-label'),
        scrollWidth: document.documentElement.scrollWidth,
        clientWidth: document.documentElement.clientWidth,
      };
    });
    const before = await state();
    if (!before.href || !before.text || !before.productId ||
        before.background === 'rgba(0, 0, 0, 0)') {
      throw new Error('Live Woo button missing public-safe identity or opaque background');
    }

    const host = await button.evaluate(el => {
      if (!document.body.classList.contains('aznet-theme-preset--digital-01')) return false;
      const product = el.closest('li.product');
      const list = product?.closest('ul.products');
      const main = list?.closest('main');
      if (!main || !list || !product) return false;
      // Browser-local opt-in only: main > list > li is a valid ancestor
      // chain for Brand > Service > Woo. No native product identity changes.
      main.classList.add('aznet-digital01--brand-2026');
      list.classList.add('aznet-digital01__service');
      product.classList.add('woocommerce');
      return true;
    });
    if (!host) throw new Error('Woo Shop native product structure or Digital 01 body preset missing');

    // Match WordPress dependency ordering: native Digital 01 CSS before opt-in
    // Brand Light CSS. Real WooCommerce stylesheet and live HTML are retained.
    await page.addStyleTag({ content: mainCSS });
    await page.addStyleTag({ content: brandCSS });
    const after = await state();
    const current = ratio(after.color, after.background);
    if (current < 4.5) throw new Error('Brand+Woo normal contrast ' + current.toFixed(2) + ':1 below AA 4.5:1');

    await button.hover();
    const hover = await state();
    const hoverRatio = ratio(hover.color, hover.background);
    if (hoverRatio < 4.5) throw new Error('Brand+Woo hover contrast ' + hoverRatio.toFixed(2) + ':1 below AA 4.5:1');

    await button.focus();
    const focus = await state();
    const focusRatio = ratio(focus.color, focus.background);
    if (focusRatio < 4.5) throw new Error('Brand+Woo focus contrast ' + focusRatio.toFixed(2) + ':1 below AA 4.5:1');

    for (const key of ['href', 'text', 'productId', 'quantity', 'ariaLabel']) {
      if (before[key] !== after[key] || before[key] !== hover[key] || before[key] !== focus[key]) {
        throw new Error('Theme-only CSS altered native Woo ' + key);
      }
    }
    if (after.scrollWidth > Math.max(before.scrollWidth, width + 2)) {
      throw new Error('Brand opt-in caused new horizontal overflow');
    }
    if (errors.length) throw new Error('Page JavaScript error: ' + errors.join(' | '));

    await button.screenshot({ path: path.join(out, 'brand-woo-aa-' + width + '.png'), animations: 'disabled' });
    Object.assign(data, {
      status: 'passed',
      before,
      after,
      normalRatio: Number(current.toFixed(2)),
      hoverRatio: Number(hoverRatio.toFixed(2)),
      focusRatio: Number(focusRatio.toFixed(2)),
    });
    console.log('PASS: Brand Light + Woo native CTA ' + width +
      'px normal=' + current.toFixed(2) + ':1 hover=' + hoverRatio.toFixed(2) +
      ':1 focus=' + focusRatio.toFixed(2) + ':1');
  } catch (error) {
    data.error = error instanceof Error ? error.message : String(error);
    result.failures.push({ width, error: data.error });
    console.error('RED: Brand Light + Woo native CTA ' + width + 'px: ' + data.error);
  } finally {
    result.cases.push(data);
    await context.close();
  }
}
await browser.close();
fs.writeFileSync(path.join(out, 'brand-woo-aa-summary.json'), JSON.stringify(result, null, 2));
if (result.failures.length) process.exitCode = 1;
else console.log('PASS: Brand+Woo candidate 3/3 public Shop CSS opt-in simulations; not actual Homepage Woo button or L5');
