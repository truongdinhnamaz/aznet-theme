import { chromium } from 'playwright';

const baseUrl = (process.env.R4_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const browser = await chromium.launch({ headless: true });
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
const consoleErrors = [];
const pageErrors = [];

page.on('console', (message) => {
  if (message.type() === 'error') consoleErrors.push(message.text());
});
page.on('pageerror', (error) => pageErrors.push(error.message));

try {
  await page.goto(`${baseUrl}/product/r4-variable-product/`, { waitUntil: 'networkidle' });
  await page.waitForTimeout(1500);
  await page.locator('.woocommerce-product-gallery').first().waitFor({ state: 'attached', timeout: 20000 });

  const diagnostic = await page.locator('.woocommerce-product-gallery').first().evaluate((root) => {
    const wrapper = root.querySelector('.woocommerce-product-gallery__wrapper');
    const viewport = root.querySelector('.flex-viewport');
    const rootStyle = getComputedStyle(root);
    const wrapperStyle = wrapper ? getComputedStyle(wrapper) : null;
    const rootRect = root.getBoundingClientRect();
    const wrapperRect = wrapper?.getBoundingClientRect();
    const jq = window.jQuery;

    return {
      documentReadyState: document.readyState,
      rootInlineStyle: root.getAttribute('style'),
      rootOpacity: rootStyle.opacity,
      rootDisplay: rootStyle.display,
      rootVisibility: rootStyle.visibility,
      rootBox: { width: rootRect.width, height: rootRect.height },
      wrapperDisplay: wrapperStyle?.display ?? null,
      wrapperVisibility: wrapperStyle?.visibility ?? null,
      wrapperOpacity: wrapperStyle?.opacity ?? null,
      wrapperBox: wrapperRect ? { width: wrapperRect.width, height: wrapperRect.height } : null,
      flexViewportCount: root.querySelectorAll('.flex-viewport').length,
      flexActiveSlideCount: root.querySelectorAll('.flex-active-slide').length,
      imageCount: root.querySelectorAll('.woocommerce-product-gallery__image img').length,
      imageComplete: Array.from(root.querySelectorAll('.woocommerce-product-gallery__image img')).map((img) => ({
        complete: img.complete,
        naturalWidth: img.naturalWidth,
        naturalHeight: img.naturalHeight,
      })),
      jqueryPresent: typeof jq === 'function',
      flexsliderPresent: Boolean(jq && typeof jq.fn?.flexslider === 'function'),
      wcGalleryPluginPresent: Boolean(jq && typeof jq.fn?.wc_product_gallery === 'function'),
      productGalleryDataPresent: Boolean(jq && jq(root).data('product_gallery')),
      singleProductParamsPresent: typeof window.wc_single_product_params !== 'undefined',
      flexsliderEnabledParam: window.wc_single_product_params?.flexslider_enabled ?? null,
      resourceScripts: performance.getEntriesByType('resource')
        .map((entry) => entry.name)
        .filter((url) => url.includes('single-product') || url.includes('flexslider')),
      viewportPresent: Boolean(viewport),
    };
  });

  console.log(`R4_GALLERY_DIAGNOSTIC=${JSON.stringify({ diagnostic, consoleErrors, pageErrors })}`);
} finally {
  await context.close();
  await browser.close();
}
