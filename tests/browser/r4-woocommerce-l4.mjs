import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.R4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.R4_STATE_DIR || '/tmp/r4-woocommerce';
const mode = process.env.R4_MODE || 'woo';
const scenario = process.env.R4_SCENARIO || mode;
const catalogPreset = process.env.R4_CATALOG_PRESET || 'grid';
const cardDensity = process.env.R4_CARD_DENSITY || 'balanced';
const productPreset = process.env.R4_PRODUCT_PRESET || 'classic';
const fullSurfaces = process.env.R4_FULL_SURFACES === '1';
const simpleProductId = process.env.R4_SIMPLE_PRODUCT_ID || '';
const outputDir = path.join(stateDir, scenario);
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x800': { width: 320, height: 800 },
};

const cleanViewports = {
  '1440x1000': viewports['1440x1000'],
  '390x844': viewports['390x844'],
};

const summary = {
  mode,
  scenario,
  catalogPreset,
  cardDensity,
  productPreset,
  fullSurfaces,
  pages: [],
};
const failures = [];

function safeName(value) {
  return value.replace(/[^a-z0-9-]+/gi, '-').replace(/^-|-$/g, '').toLowerCase();
}

async function firstKeyboardFocus(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  await page.keyboard.press('Tab');
  return page.evaluate(() => {
    const element = document.activeElement;
    if (!(element instanceof HTMLElement)) return null;
    const rect = element.getBoundingClientRect();
    const style = getComputedStyle(element);
    return {
      className: String(element.className || ''),
      href: element.getAttribute('href'),
      visible: rect.width > 1 && rect.height > 1,
      hasIndicator: (style.outlineStyle !== 'none' && Number.parseFloat(style.outlineWidth || '0') > 0) || (style.boxShadow && style.boxShadow !== 'none'),
    };
  });
}

async function themeWooStyleIds(page) {
  return page.locator('link[rel="stylesheet"][id^="aznet-theme-woocommerce-"]').evaluateAll((nodes) => nodes.map((node) => node.id).sort());
}

async function isKnownWooCheckoutProviderViolation(page, kind, violation) {
  if (kind !== 'checkout' || !['aria-prohibited-attr', 'autocomplete-valid'].includes(violation.id)) return false;
  if (!Array.isArray(violation.nodes) || violation.nodes.length === 0) return false;

  for (const node of violation.nodes) {
    if (!Array.isArray(node.target) || node.target.length !== 1 || typeof node.target[0] !== 'string') return false;
    const selector = node.target[0];
    const locator = page.locator(selector).first();
    if (await locator.count() !== 1) return false;

    const matchesKnownProviderMarkup = await locator.evaluate((element, violationId) => {
      if (!(element instanceof HTMLElement) || !element.closest('.wc-block-checkout')) return false;

      if (violationId === 'aria-prohibited-attr') {
        return element.matches('.wc-block-components-order-summary[aria-live="polite"][aria-label]') ||
          element.matches('.wc-block-components-skeleton__element[aria-live="polite"][aria-label]');
      }

      if (violationId === 'autocomplete-valid') {
        return element.matches('input#email[name="contact_email"][type="email"]') &&
          element.getAttribute('autocomplete') === 'section-contact contact email';
      }

      return false;
    }, violation.id);

    if (!matchesKnownProviderMarkup) return false;
  }

  return true;
}

async function verifyBasePage(page, kind, viewportName, result) {
  await page.locator('main#main').waitFor({ state: 'visible', timeout: 20000 });
  const mainCount = await page.locator('main#main').count();
  if (mainCount !== 1) throw new Error(`${kind}: expected exactly one main#main, got ${mainCount}`);

  result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (result.overflowPx > 1) throw new Error(`${kind}: horizontal overflow ${result.overflowPx}px at ${viewportName}`);

  result.firstFocus = await firstKeyboardFocus(page);
  if (!result.firstFocus?.visible || !result.firstFocus.hasIndicator || !result.firstFocus.className.includes('aznet-theme-skip-link') || result.firstFocus.href !== '#main') {
    throw new Error(`${kind}: first keyboard focus is not visible Theme skip link: ${JSON.stringify(result.firstFocus)}`);
  }

  result.themeWooStyles = await themeWooStyleIds(page);
}

async function verifyKind(page, kind, viewport) {
  if (kind === 'shop') {
    const bodyClass = String((await page.locator('body').getAttribute('class')) || '');
    for (const expected of [`aznet-theme-woo-catalog--${catalogPreset}`, `aznet-theme-woo-card--${cardDensity}`]) {
      if (!bodyClass.split(/\s+/).includes(expected)) throw new Error(`shop: missing body class ${expected}`);
    }
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-archive-css'))) throw new Error('shop: archive stylesheet not scoped onto Shop');
    await page.locator('.woocommerce ul.products li.product').first().waitFor({ state: 'visible', timeout: 20000 });
    for (const selector of ['.woocommerce-loop-product__title', '.price', '.star-rating', '.button']) {
      if (await page.locator(`.woocommerce ul.products li.product ${selector}`).count() < 1) throw new Error(`shop: missing native Woo ${selector}`);
    }
  }

  if (kind === 'product') {
    const bodyClass = String((await page.locator('body').getAttribute('class')) || '');
    const expected = `aznet-theme-woo-product--${productPreset}`;
    if (!bodyClass.split(/\s+/).includes(expected)) throw new Error(`product: missing body class ${expected}`);
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-product-css'))) throw new Error('product: product stylesheet not scoped onto product page');

    const themeTitle = page.locator('main#main .aznet-theme-entry__title').first();
    await themeTitle.waitFor({ state: 'visible', timeout: 20000 });
    if ((await themeTitle.textContent())?.trim() !== 'R4 Variable Product') throw new Error('product: Theme-owned product H1 does not match fixture title');
    if (await page.locator('main#main h1').count() !== 1) throw new Error('product: expected exactly one H1 in main');

    for (const selector of ['.summary .price', 'form.variations_form', '.variations select', '.single_add_to_cart_button']) {
      await page.locator(selector).first().waitFor({ state: 'visible', timeout: 20000 });
    }

    const galleryViewport = page.locator('.woocommerce-product-gallery .flex-viewport').first();
    const galleryImage = page.locator('.woocommerce-product-gallery__image.flex-active-slide img').first();
    await galleryViewport.waitFor({ state: 'visible', timeout: 20000 });
    await galleryImage.waitFor({ state: 'visible', timeout: 20000 });
    const imageMetrics = await galleryImage.evaluate((node) => ({
      naturalWidth: node.naturalWidth,
      naturalHeight: node.naturalHeight,
      width: node.getBoundingClientRect().width,
      height: node.getBoundingClientRect().height,
      viewportWidth: document.documentElement.clientWidth,
    }));
    if (
      imageMetrics.naturalWidth <= 0 ||
      imageMetrics.naturalHeight <= 0 ||
      imageMetrics.width <= 0 ||
      imageMetrics.height <= 0 ||
      imageMetrics.width > imageMetrics.viewportWidth + 1
    ) {
      throw new Error(`product: gallery image invalid ${JSON.stringify(imageMetrics)}`);
    }

    const select = page.locator('.variations select').first();
    const options = await select.locator('option').evaluateAll((nodes) => nodes.map((node) => node.value).filter(Boolean));
    if (options.length < 1) throw new Error('product: native variable select has no selectable variation');
    await select.selectOption(options[0]);
    if ((await select.inputValue()) !== options[0]) throw new Error('product: native variation select did not retain selected value');

    if (productPreset === 'focus') {
      const summaryPosition = await page.locator('.summary').evaluate((node) => getComputedStyle(node).position);
      if (viewport.width >= 768 && summaryPosition !== 'sticky') throw new Error(`Focus summary expected sticky on desktop, got ${summaryPosition}`);
      if (viewport.width < 768 && summaryPosition === 'sticky') throw new Error('Focus summary remained sticky on mobile');
    }
  }

  if (kind === 'cart') {
    await page.locator('.woocommerce-cart-form .shop_table, .wp-block-woocommerce-cart').first().waitFor({ state: 'visible', timeout: 20000 });
    if (await page.locator('.woocommerce-cart-form .shop_table').count() > 0) {
      for (const selector of ['.woocommerce-cart-form .shop_table', '.shop_table .product-name', '.shop_table .product-quantity', '.shop_table .product-subtotal', '.checkout-button']) {
        await page.locator(selector).first().waitFor({ state: 'visible', timeout: 20000 });
      }
    } else {
      for (const selector of ['.wp-block-woocommerce-cart', '.wc-block-cart-items', '.wc-block-cart-item__product', '.wc-block-components-quantity-selector', '.wc-block-cart__submit-button']) {
        await page.locator(selector).first().waitFor({ state: 'visible', timeout: 20000 });
      }
    }
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-cart-css'))) throw new Error('cart: Cart stylesheet not scoped onto Cart');
  }

  if (kind === 'checkout') {
    await page.locator('form.checkout, .wc-block-checkout').first().waitFor({ state: 'visible', timeout: 25000 });
    if (await page.locator('form.checkout').count() > 0) {
      for (const selector of ['form.checkout', '#billing_first_name', '#order_review', '#payment', '#place_order']) {
        await page.locator(selector).first().waitFor({ state: 'visible', timeout: 25000 });
      }
    } else {
      for (const selector of ['.wc-block-checkout', '.wc-block-components-main', '.wc-block-components-sidebar', '.wc-block-components-text-input input', '.wc-block-components-checkout-place-order-button']) {
        await page.locator(selector).first().waitFor({ state: 'visible', timeout: 25000 });
      }
    }
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-checkout-css'))) throw new Error('checkout: Checkout stylesheet not scoped onto Checkout');
  }

  if (kind === 'account') {
    await page.locator('.woocommerce form.login, .woocommerce .woocommerce-form-login').first().waitFor({ state: 'visible', timeout: 20000 });
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-account-css'))) throw new Error('account: Account stylesheet not scoped onto My Account');
  }

  if (kind === 'blocks') {
    await page.locator('.wp-block-woocommerce-product-collection, .wc-block-product').first().waitFor({ state: 'visible', timeout: 25000 });
    if (!((await themeWooStyleIds(page)).includes('aznet-theme-woocommerce-blocks-css'))) throw new Error('blocks: capability-gated Woo Blocks stylesheet missing');
  }

  if (kind === 'generic') {
    const ids = await themeWooStyleIds(page);
    if (ids.length !== 0) throw new Error(`generic: Theme Woo stylesheet leaked onto generic page: ${ids.join(', ')}`);
  }

  if (kind === 'clean') {
    const ids = await themeWooStyleIds(page);
    if (ids.length !== 0) throw new Error(`clean WordPress: Theme Woo stylesheet loaded without Woo: ${ids.join(', ')}`);
    const bodyClass = String((await page.locator('body').getAttribute('class')) || '');
    if (bodyClass.split(/\s+/).some((name) => name.startsWith('aznet-theme-woo-'))) throw new Error(`clean WordPress: Woo presentation class leaked: ${bodyClass}`);
  }
}

async function inspect(browser, kind, route, viewportName, viewport, addCartItem = false) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const failedSubresources = [];
  page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() >= 400 && response.request().resourceType() !== 'document') failedSubresources.push(`${response.status()} ${response.request().resourceType()} ${response.url()}`);
  });

  const result = {
    kind,
    route,
    viewport: viewportName,
    status: 'failed',
    overflowPx: null,
    firstFocus: null,
    themeWooStyles: [],
    axeBlocking: null,
    axeProviderDefects: [],
    error: null,
  };

  try {
    if (addCartItem) {
      if (!simpleProductId) throw new Error(`${kind}: R4_SIMPLE_PRODUCT_ID is required`);
      await page.goto(`${baseUrl}/?add-to-cart=${encodeURIComponent(simpleProductId)}`, { waitUntil: 'networkidle' });
    }

    const response = await page.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`${kind}: expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    await verifyBasePage(page, kind, viewportName, result);
    await verifyKind(page, kind, viewport);

    const axeResults = await new AxeBuilder({ page }).analyze();
    fs.writeFileSync(path.join(axeDir, `${safeName(kind)}-${viewportName}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    const unexpectedBlocking = [];

    for (const violation of blocking) {
      if (await isKnownWooCheckoutProviderViolation(page, kind, violation)) {
        result.axeProviderDefects.push({
          id: violation.id,
          impact: violation.impact,
          nodeCount: violation.nodes.length,
        });
      } else {
        unexpectedBlocking.push(violation);
      }
    }

    result.axeBlocking = unexpectedBlocking.length;
    if (unexpectedBlocking.length > 0) {
      throw new Error(`${kind}: blocking axe violations ${unexpectedBlocking.map((item) => item.id).join(', ')}`);
    }

    if (failedSubresources.length > 0) throw new Error(`${kind}: failed subresources ${failedSubresources.join(' | ')}`);
    if (consoleErrors.length > 0) throw new Error(`${kind}: console errors ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`${kind}: page errors ${pageErrors.join(' | ')}`);

    if (viewportName === '1440x1000' || viewportName === '390x844') {
      await page.screenshot({ path: path.join(screenshotDir, `${safeName(kind)}-${viewportName}.png`), fullPage: true });
    }
    result.status = 'passed';
    console.log(`PASS: R4 ${scenario} ${kind} ${viewportName}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${kind}-${viewportName}: ${result.error}`);
    console.error(`FAIL: R4 ${scenario} ${kind} ${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `${safeName(kind)}-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.pages.push(result);
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  if (mode === 'clean') {
    for (const [viewportName, viewport] of Object.entries(cleanViewports)) {
      for (const route of ['/', '/r4-generic/', '/r4-clean-post/']) {
        await inspect(browser, 'clean', route, viewportName, viewport, false);
      }
    }
  } else {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspect(browser, 'shop', '/shop/', viewportName, viewport, false);
      await inspect(browser, 'product', '/product/r4-variable-product/', viewportName, viewport, false);
      if (fullSurfaces) {
        await inspect(browser, 'cart', '/cart/', viewportName, viewport, true);
        await inspect(browser, 'checkout', '/checkout/', viewportName, viewport, true);
        await inspect(browser, 'account', '/my-account/', viewportName, viewport, false);
      }
    }

    if (fullSurfaces) {
      for (const viewportName of ['1440x1000', '390x844']) {
        const viewport = viewports[viewportName];
        await inspect(browser, 'blocks', '/r4-blocks-fixture/', viewportName, viewport, false);
        await inspect(browser, 'generic', '/r4-generic/', viewportName, viewport, false);
      }
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length > 0) throw new Error(`R4 ${scenario} browser matrix failed:\n${failures.join('\n')}`);
console.log(`PASS: R4 ${scenario} browser matrix`);
