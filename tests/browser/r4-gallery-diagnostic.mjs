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
    const activeSlide = root.querySelector('.woocommerce-product-gallery__image.flex-active-slide');
    const activeLink = activeSlide?.querySelector('a');
    const activeImage = activeSlide?.querySelector('img');
    const rootStyle = getComputedStyle(root);

    const box = (node) => {
      if (!node) return null;
      const rect = node.getBoundingClientRect();
      const style = getComputedStyle(node);
      return {
        inlineStyle: node.getAttribute('style'),
        width: rect.width,
        height: rect.height,
        display: style.display,
        position: style.position,
        float: style.float,
        overflow: style.overflow,
        opacity: style.opacity,
        visibility: style.visibility,
        marginTop: style.marginTop,
        marginBottom: style.marginBottom,
        paddingTop: style.paddingTop,
        paddingBottom: style.paddingBottom,
      };
    };

    const jq = window.jQuery;
    return {
      documentReadyState: document.readyState,
      root: box(root),
      viewport: box(viewport),
      wrapper: box(wrapper),
      activeSlide: box(activeSlide),
      activeLink: box(activeLink),
      activeImage: box(activeImage),
      rootOpacity: rootStyle.opacity,
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
      flexsliderOptions: window.wc_single_product_params?.flexslider ?? null,
      resourceScripts: performance.getEntriesByType('resource')
        .map((entry) => entry.name)
        .filter((url) => url.includes('single-product') || url.includes('flexslider')),
    };
  });

  const evidence = { diagnostic, consoleErrors, pageErrors };
  console.log(`R4_GALLERY_DIAGNOSTIC=${JSON.stringify(evidence)}`);

  if (
    diagnostic.rootOpacity === '0' ||
    !diagnostic.productGalleryDataPresent ||
    !diagnostic.viewport || diagnostic.viewport.height <= 0 ||
    !diagnostic.activeSlide || diagnostic.activeSlide.height <= 0 ||
    !diagnostic.activeImage || diagnostic.activeImage.height <= 0 ||
    consoleErrors.length > 0 ||
    pageErrors.length > 0
  ) {
    throw new Error(`R4 native Woo gallery initialization incomplete: ${JSON.stringify(evidence)}`);
  }
} finally {
  await context.close();
  await browser.close();
}
