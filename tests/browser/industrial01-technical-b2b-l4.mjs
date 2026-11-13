/**
 * Opt-in Industrial 01 technical B2B Homepage verification on real WordPress.
 * Runs only against a disposable local CI fixture, never production.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.INDUSTRIAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outDir = path.join(process.env.INDUSTRIAL01_OUT_DIR || '/tmp/industrial01-pilot', 'technical-b2b');
fs.mkdirSync(outDir, { recursive: true });

const cases = [
  { name: 'desktop', width: 1440, height: 900, columns: 3 },
  { name: 'tablet', width: 768, height: 1024, columns: 2 },
  { name: 'mobile', width: 390, height: 844, columns: 1 },
];

const browser = await chromium.launch({ headless: true });
const summary = [];
try {
  for (const view of cases) {
    const context = await browser.newContext({ viewport: { width: view.width, height: view.height } });
    const page = await context.newPage();
    const response = await page.goto(baseUrl + '/', { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(view.name + ': Front Page must return HTTP 200');

    const themeRoot = page.locator('.aznet-theme-homepage--industrial-01-technical-b2b');
    if (await themeRoot.count() !== 1) throw new Error(view.name + ': selected technical variant absent from Theme Composer');

    const source = await themeRoot.evaluate(root => {
      const grid = root.querySelector('.aznet-theme-industrial01-grid--products');
      const cards = [...root.querySelectorAll('.aznet-theme-industrial01-product-card')];
      const hero = root.querySelector('.aznet-theme-industrial01-hero');
      const products = root.querySelector('.aznet-theme-industrial01-products');
      const solutions = root.querySelector('.aznet-theme-industrial01-solutions');
      const h1s = [...document.querySelectorAll('main#main h1')].map(x => x.textContent.trim());
      const links = cards.map(card => card.querySelector('h3 a')?.href || '');
      const display = grid ? getComputedStyle(grid) : null;
      const heroImage = hero?.querySelector('.aznet-theme-industrial01-hero__image');
      const productImage = cards[0]?.querySelector('.aznet-theme-industrial01-product-card__media img');
      const mediaBox = cards[0]?.querySelector('.aznet-theme-industrial01-product-card__media');
      const mediaRect = mediaBox?.getBoundingClientRect();
      return {
        h1s, cards: cards.length, links,
        columns: display?.gridTemplateColumns.split(/\s+/).filter(Boolean).length || 0,
        productsBeforeSolutions: Boolean(products && solutions && (products.compareDocumentPosition(solutions) & Node.DOCUMENT_POSITION_FOLLOWING)),
        heroImagePresent: Boolean(heroImage && heroImage.complete && heroImage.naturalWidth > 0),
        productImageFit: productImage ? getComputedStyle(productImage).objectFit : null,
        mediaRatio: mediaRect ? mediaRect.width / mediaRect.height : null,
        homepageCss: [...document.querySelectorAll('link[rel="stylesheet"]')].some(link => link.href.includes('homepage-industrial-01.css')),
        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
      };
    });
    if (source.h1s.length !== 1 || !source.h1s[0]) throw new Error(view.name + ': expected one meaningful H1');
    if (source.cards !== 8 || source.links.some(link => !link.startsWith(baseUrl + '/product/'))) {
      throw new Error(view.name + ': WooCommerce authoritative product cards or links changed: ' + JSON.stringify(source));
    }
    if (source.columns !== view.columns) throw new Error(view.name + ': expected ' + view.columns + ' columns; got ' + source.columns);
    if (!source.productsBeforeSolutions) throw new Error(view.name + ': native Solutions Page must follow Woo product discovery');
    if (!source.homepageCss) throw new Error(view.name + ': scoped Industrial 01 CSS not enqueued');
    if (!source.heroImagePresent || source.productImageFit !== 'cover') throw new Error(view.name + ': source WordPress media unavailable');
    if (!source.mediaRatio || Math.abs(source.mediaRatio - (4/3)) > 0.08) {
      throw new Error(view.name + ': product visual must have 4:3 framing');
    }
    if (source.overflow > 1) throw new Error(view.name + ': horizontal overflow ' + source.overflow + 'px');

    const a11y = await new AxeBuilder({ page }).analyze();
    const serious = a11y.violations.filter(v => ['critical', 'serious'].includes(v.impact || ''));
    fs.writeFileSync(path.join(outDir, view.name + '-axe.json'), JSON.stringify({
      violations: a11y.violations,
      passes: a11y.passes.length,
      incomplete: a11y.incomplete.length,
    }, null, 2));
    if (serious.length) throw new Error(view.name + ': axe serious/critical violations ' + serious.map(v => v.id).join(','));

    await page.screenshot({ path: path.join(outDir, view.name + '.png'), fullPage: true });
    summary.push({ viewport: view.name, width: view.width, columns: source.columns, productCards: source.cards, h1: source.h1s[0], overflow: source.overflow, a11yViolations: a11y.violations.map(v => ({ id: v.id, impact: v.impact })) });
    await context.close();
  }

  fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(summary, null, 2));
  console.log('PASS: Industrial 01 technical B2B WordPress L4 390/768/1440; exact native product links, responsive, no overflow, axe severe 0');
} finally {
  await browser.close();
}
