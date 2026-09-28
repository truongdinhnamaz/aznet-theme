import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.INDUSTRIAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outDir = process.env.INDUSTRIAL01_OUT_DIR || '/tmp/industrial01-pilot';
fs.mkdirSync(outDir, { recursive: true });

const viewports = {
  desktop: { width: 1440, height: 1000 },
  mobile: { width: 390, height: 844 },
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
