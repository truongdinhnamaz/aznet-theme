import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.INDUSTRIAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outDir = process.env.INDUSTRIAL01_OUT_DIR || '/tmp/industrial01-pilot';
fs.mkdirSync(outDir, { recursive: true });

const viewports = {
  desktop: { width: 1440, height: 1000 },
  tablet: { width: 900, height: 1000 },
  mobile: { width: 390, height: 844 },
  compact: { width: 320, height: 800 },
};

async function assertNoOverflow(page, label) {
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) throw new Error(`${label}: horizontal overflow ${overflow}px`);
  return overflow;
}

async function assertA11y(page, label) {
  const axe = await new AxeBuilder({ page }).analyze();
  fs.writeFileSync(path.join(outDir, `${label}-axe.json`), JSON.stringify(axe, null, 2));
  const serious = axe.violations.filter((v) => ['critical', 'serious'].includes(v.impact || ''));
  if (serious.length) throw new Error(`${label}: axe critical/serious violations ${serious.map((v) => v.id).join(', ')}`);
  return axe.violations.length;
}

const browser = await chromium.launch({ headless: true });
const summary = {};

try {
  for (const [name, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    await page.goto(baseUrl + '/', { waitUntil: 'networkidle' });

    const bodyClass = (await page.locator('body').getAttribute('class')) || '';
    if (!bodyClass.includes('aznet-theme-preset--industrial-01')) throw new Error(`${name}: Industrial 01 body preset missing`);

    const presetCss = await page.locator('link[href*="/assets/css/presets/industrial-01.css"]').count();
    const homepageCss = await page.locator('link[href*="/assets/css/components/homepage-industrial-01.css"]').count();
    if (presetCss !== 1 || homepageCss !== 1) throw new Error(`${name}: Industrial 01 scoped assets missing`);

    const homepage = page.locator('.aznet-theme-homepage--industrial-01');
    await homepage.waitFor({ state: 'visible', timeout: 20000 });
    for (const selector of [
      '.aznet-theme-industrial01-hero',
      '.aznet-theme-industrial01-categories',
      '.aznet-theme-industrial01-products',
      '.aznet-theme-industrial01-cta',
    ]) {
      if (await homepage.locator(selector).count() !== 1) throw new Error(`${name}: homepage section missing ${selector}`);
    }

    const categories = await homepage.locator('.aznet-theme-industrial01-category-card').count();
    const products = await homepage.locator('.aznet-theme-industrial01-product-card').count();
    if (categories < 4) throw new Error(`${name}: expected at least 4 category cards, got ${categories}`);
    if (products < 6) throw new Error(`${name}: expected at least 6 product cards, got ${products}`);

    // Bounded native WooCommerce catalogue gallery (seven terms; eighth grid
    // position is an editorial navigation tile, never a fabricated product).
    const categorySection = homepage.locator('.aznet-theme-industrial01-categories');
    const gallery = categorySection.locator('.aznet-theme-industrial01-grid--categories');
    const categoryLinks = gallery.locator('a.aznet-theme-industrial01-category-card');
    const introCard = gallery.locator('.aznet-theme-industrial01-categories__intro-card');
    if (await categoryLinks.count() !== 7 || await introCard.count() !== 1) {
      throw new Error(name + ': seven WordPress-owned category links plus one introductory tile are required');
    }
    if (!(await introCard.innerText()).includes('07 danh mục')) {
      throw new Error(name + ': source term count not shown correctly');
    }
    const hrefs = await categoryLinks.evaluateAll((items) => items.map((item) => item.href));
    if (new Set(hrefs).size !== 7 || hrefs.some((href) => !href.includes('/product-category/'))) {
      throw new Error(name + ': native WooCommerce category permalink missing or duplicated');
    }
    if (await gallery.locator('.aznet-theme-industrial01-category-card__veil').count() !== 7) {
      throw new Error(name + ': accessible title gradient overlay missing');
    }
    const galleryCols = await gallery.evaluate(
      (node) => getComputedStyle(node).gridTemplateColumns.split(/\s+/).filter(Boolean).length
    );
    const expectedGalleryCols = viewport.width > 1024 ? 4 : viewport.width < 368 ? 1 : 2;
    if (galleryCols !== expectedGalleryCols) {
      throw new Error(name + ': category gallery columns ' + galleryCols + ', expected ' + expectedGalleryCols);
    }
    const galleryMediaCount = await gallery.locator('.aznet-theme-industrial01-category-card__media img').count();
    if (galleryMediaCount !== 7) {
      throw new Error(name + ': all WooCommerce-provided category thumbnails must remain present');
    }
    const galleryHeading = await categorySection.locator('#aznet-industrial01-categories-title').innerText();
    if (!galleryHeading.includes('Danh mục thiết bị')) {
      throw new Error(name + ': Theme preset category heading changed unexpectedly');
    }
    const isolatedGalleryCapture = await page.addStyleTag({
      content: '.aznet-theme-site-header { visibility: hidden !important; }'
    });
    await categorySection.screenshot({ path: path.join(outDir, name + '-categories.png') });
    await isolatedGalleryCapture.evaluate((node) => node.remove());


    // WordPress runtime: mapped native Page blocks drive Solutions presentation.
    const solutions = homepage.locator('#aznet-homepage-industrial-solutions');
    if (await solutions.count() !== 1) throw new Error(name + ': mapped Solutions section missing');
    const cards = solutions.locator('.aznet-theme-industrial01-solutions__card');
    const descriptions = solutions.locator('.aznet-theme-industrial01-solutions__card-description');
    if (await cards.count() !== 4 || await descriptions.count() !== 4) {
      throw new Error(name + ': missing source-owned Solutions cards/descriptions');
    }
    const solutionCopy = await descriptions.allTextContents();
    if (!solutionCopy.some((value) => value.includes('sai số, năng suất'))) {
      throw new Error(name + ': native Page situational description not projected');
    }
    const solutionImage = solutions.locator('img.aznet-theme-industrial01-solutions__image');
    if (await solutionImage.count() !== 1) throw new Error(name + ': WordPress Solutions image missing');
    const loaded = await solutionImage.evaluate((element) => element.complete && element.naturalWidth > 0);
    if (!loaded) throw new Error(name + ': native WordPress Solutions image did not load');
    const links = await cards.evaluateAll((elements) => elements.map((element) => element.getAttribute('href') || ''));
    if (links.some((href) => !href.endsWith('/giai-phap/#tinh-huong'))) {
      throw new Error(name + ': Solutions links do not use WordPress Page anchor');
    }
    const columns = await solutions.locator('.aznet-theme-industrial01-solutions__grid')
      .evaluate((element) => getComputedStyle(element).gridTemplateColumns.split(/\s+/).filter(Boolean).length);
    const desiredColumns = viewport.width <= 390 ? 1 : 2;
    if (columns !== desiredColumns) {
      throw new Error(name + ': Solutions grid column count ' + columns + ', expected ' + desiredColumns);
    }
    const solutionsTitle = await solutions.locator('h2#aznet-industrial01-solutions-title').textContent();
    if (solutionsTitle?.trim() !== 'Giải pháp') throw new Error(name + ': WordPress Page title not retained');
    // Isolate section for visual QA; sticky shell is restored immediately.
    const isolatedCapture = await page.addStyleTag({ content: '.aznet-theme-site-header { visibility: hidden !important; }' });
    await solutions.screenshot({ path: path.join(outDir, name + '-solutions.png') });
    await isolatedCapture.evaluate((element) => element.remove());

    await assertNoOverflow(page, name + '-home');
    await assertA11y(page, name + '-home');
    await page.screenshot({ path: path.join(outDir, `${name}-home.png`), fullPage: true });

    await page.goto(baseUrl + '/shop/', { waitUntil: 'networkidle' });
    const shopBody = (await page.locator('body').getAttribute('class')) || '';
    if (!shopBody.includes('aznet-theme-preset--industrial-01')) throw new Error(`${name}: Shop lost Industrial 01 preset`);
    const shopProducts = await page.locator('.woocommerce ul.products li.product').count();
    if (shopProducts < 6) throw new Error(`${name}: expected at least 6 Shop products, got ${shopProducts}`);
    await assertNoOverflow(page, name + '-shop');
    await assertA11y(page, name + '-shop');
    await page.screenshot({ path: path.join(outDir, `${name}-shop.png`), fullPage: true });

    await page.goto(baseUrl + '/product/industrial-motor-01/', { waitUntil: 'networkidle' });
    const productBody = (await page.locator('body').getAttribute('class')) || '';
    if (!productBody.includes('aznet-theme-preset--industrial-01')) throw new Error(`${name}: Product lost Industrial 01 preset`);
    const title = page.locator('main#main h1.product_title.entry-title');
    await title.waitFor({ state: 'visible', timeout: 20000 });
    if ((await title.innerText()).trim() !== 'Industrial Motor 01') throw new Error(`${name}: native Woo product title mismatch`);
    if (await page.locator('.woocommerce-product-gallery').count() !== 1) throw new Error(`${name}: native Woo gallery missing`);
    if (await page.locator('.summary').count() !== 1) throw new Error(`${name}: native Woo summary missing`);
    await assertNoOverflow(page, name + '-product');
    await assertA11y(page, name + '-product');
    await page.screenshot({ path: path.join(outDir, `${name}-product.png`), fullPage: true });

    summary[name] = { categories, products, shopProducts };
    await context.close();
  }

  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log(JSON.stringify(summary, null, 2));
} finally {
  await browser.close();
}
