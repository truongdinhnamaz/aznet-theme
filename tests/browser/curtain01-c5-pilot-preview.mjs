import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.CURTAIN01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outDir = process.env.CURTAIN01_OUT_DIR || '/tmp/curtain01-c5';
fs.mkdirSync(outDir, { recursive: true });

const viewports = {
  desktop: { width: 1440, height: 1000 },
  tablet: { width: 1024, height: 900 },
  mobile: { width: 390, height: 844 },
  narrow: { width: 320, height: 780 },
};

const browser = await chromium.launch({ headless: true });
const results = {};

async function assertA11y(page, label) {
  const axe = await new AxeBuilder({ page }).analyze();
  const serious = axe.violations.filter((item) => ['critical', 'serious'].includes(item.impact || ''));
  fs.writeFileSync(path.join(outDir, `${label}-axe.json`), JSON.stringify(axe, null, 2));
  if (serious.length) {
    const detail = serious.map((item) => ({
      id: item.id,
      impact: item.impact,
      nodes: item.nodes.slice(0, 6).map((node) => ({
        target: node.target,
        html: node.html,
        failureSummary: node.failureSummary,
      })),
    }));
    throw new Error(`${label}: axe serious/critical violations: ${JSON.stringify(detail)}`);
  }
  return { violations: axe.violations.length, serious: serious.length };
}

async function assertNoOverflow(page, label) {
  const probe = await page.evaluate(() => {
    const viewport = document.documentElement.clientWidth;
    const delta = document.documentElement.scrollWidth - viewport;
    const offenders = [...document.querySelectorAll('body *')]
      .map((node) => {
        const rect = node.getBoundingClientRect();
        const style = getComputedStyle(node);
        const parent = node.parentElement;
        const parentRect = parent ? parent.getBoundingClientRect() : null;
        return {
          tag: node.tagName.toLowerCase(),
          id: node.id || '',
          className: typeof node.className === 'string' ? node.className : '',
          left: Math.round(rect.left),
          right: Math.round(rect.right),
          width: Math.round(rect.width),
          cssWidth: style.width,
          cssMaxWidth: style.maxWidth,
          display: style.display,
          minWidth: style.minWidth,
          parentClass: parent && typeof parent.className === 'string' ? parent.className : '',
          parentWidth: parentRect ? Math.round(parentRect.width) : null,
        };
      })
      .filter((item) => item.right > viewport + 1 || item.left < -1 || item.width > viewport + 1)
      .sort((a, b) => Math.max(b.right - viewport, b.width - viewport) - Math.max(a.right - viewport, a.width - viewport))
      .slice(0, 12);
    return { delta, viewport, scrollWidth: document.documentElement.scrollWidth, offenders };
  });
  if (probe.delta > 1) throw new Error(`${label}: horizontal overflow ${probe.delta}px; ${JSON.stringify(probe)}`);
  return probe.delta;
}

async function verifyHomepage(viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  await page.goto(baseUrl + '/', { waitUntil: 'networkidle' });

  const bodyClass = await page.locator('body').getAttribute('class') || '';
  if (!bodyClass.includes('aznet-theme-preset--curtain-01')) {
    throw new Error(`${viewportName}: Curtain 01 visual preset body class missing`);
  }

  const required = [
    '.aznet-theme-homepage--curtain-01',
    '.aznet-theme-curtain01-hero',
    '.aznet-theme-curtain01-about',
    '.aznet-theme-curtain01-category-showcase',
    '.aznet-theme-curtain01-catalogue',
    '.aznet-theme-curtain01-knowledge',
    '.aznet-theme-curtain01-final-cta',
    '.aznet-theme-site-header',
    '.aznet-theme-site-footer',
  ];
  for (const selector of required) {
    if (await page.locator(selector).count() !== 1) throw new Error(`${viewportName}: expected one ${selector}`);
  }

  const nativeSentinel = page.locator('#curtain01-native-body');
  if (await nativeSentinel.count() !== 1) throw new Error(`${viewportName}: native Front Page content boundary sentinel missing`);

  const cinematicHero = page.locator('.aznet-theme-curtain01-hero[data-aznet-curtain-cinematic]');
  const cinematicSlides = cinematicHero.locator('[data-aznet-curtain-slide]');
  const cinematicDots = cinematicHero.locator('.aznet-theme-curtain01-hero__dot');
  if (await cinematicSlides.count() !== 3) throw new Error(`${viewportName}: expected 3 cinematic Hero slides`);
  if (await cinematicDots.count() !== 3) throw new Error(`${viewportName}: expected 3 cinematic Hero controls`);
  const cinematicDotRects = await cinematicDots.evaluateAll((nodes) => nodes.map((node) => {
    const rect = node.getBoundingClientRect();
    return { width: rect.width, height: rect.height };
  }));
  if (cinematicDotRects.some(({ width, height }) => width < 44 || height < 44)) {
    throw new Error(`${viewportName}: cinematic Hero controls must provide at least 44x44px pointer targets; ${JSON.stringify(cinematicDotRects)}`);
  }
  if (await cinematicSlides.filter({ has: page.locator('.is-active') }).count() > 0) {
    throw new Error(`${viewportName}: invalid nested active-state probe`);
  }
  if (!(await cinematicSlides.nth(0).getAttribute('class') || '').includes('is-active')) {
    throw new Error(`${viewportName}: first cinematic Hero slide must start active`);
  }

  const cinematicGeometry = await cinematicHero.evaluate((hero) => {
    const stage = hero.querySelector('.aznet-theme-curtain01-hero__slide-stage');
    const slides = [...hero.querySelectorAll('[data-aznet-curtain-slide]')];
    const heroRect = hero.getBoundingClientRect();
    const stageRect = stage ? stage.getBoundingClientRect() : null;
    return {
      heroWidth: heroRect.width,
      stageWidth: stageRect ? stageRect.width : 0,
      slideWidths: slides.map((slide) => slide.getBoundingClientRect().width),
    };
  });
  if (cinematicGeometry.stageWidth < cinematicGeometry.heroWidth * 0.95) {
    throw new Error(`${viewportName}: cinematic Hero stage collapsed; ${JSON.stringify(cinematicGeometry)}`);
  }
  if (cinematicGeometry.slideWidths.some((width) => width < cinematicGeometry.heroWidth * 0.95)) {
    throw new Error(`${viewportName}: cinematic Hero slide collapsed; ${JSON.stringify(cinematicGeometry)}`);
  }
  await cinematicDots.nth(1).click();
  await page.waitForTimeout(80);
  if (!(await cinematicSlides.nth(1).getAttribute('class') || '').includes('is-active')) {
    throw new Error(`${viewportName}: cinematic Hero control did not activate slide 2`);
  }
  const heroHeadingHiddenBySlide = await page.locator('.aznet-theme-curtain01-hero h1').evaluate((heading) => Boolean(heading.closest('[aria-hidden="true"]')));
  if (heroHeadingHiddenBySlide) {
    throw new Error(`${viewportName}: cinematic transition must not remove the semantic Hero heading from the accessibility tree`);
  }

  if (viewportName === 'desktop') {
    const headingSelectors = [
      '.aznet-theme-curtain01-about__content h2',
      '.aznet-theme-curtain01-catalogue .aznet-theme-curtain01-section-heading h2',
      '.aznet-theme-curtain01-knowledge .aznet-theme-curtain01-section-heading h2',
      '.aznet-theme-curtain01-final-cta h2',
    ];

    for (const selector of headingSelectors) {
      const probe = await page.locator(selector).evaluate((heading) => {
        const style = getComputedStyle(heading);
        const parent = heading.parentElement;
        const parentWidth = parent ? parent.getBoundingClientRect().width : heading.getBoundingClientRect().width;
        const renderedHeight = heading.getBoundingClientRect().height;
        const lineHeight = Number.parseFloat(style.lineHeight);
        const meter = document.createElement('span');
        meter.textContent = heading.textContent || '';
        meter.style.position = 'absolute';
        meter.style.visibility = 'hidden';
        meter.style.whiteSpace = 'nowrap';
        meter.style.font = style.font;
        meter.style.letterSpacing = style.letterSpacing;
        meter.style.wordSpacing = style.wordSpacing;
        meter.style.maxWidth = 'none';
        document.body.appendChild(meter);
        const singleLineWidth = meter.getBoundingClientRect().width;
        meter.remove();

        return {
          text: (heading.textContent || '').trim(),
          parentWidth,
          renderedHeight,
          lineHeight,
          singleLineWidth,
        };
      });

      const fitsSingleLine = probe.singleLineWidth <= probe.parentWidth - 2;
      const renderedOnMultipleLines = Number.isFinite(probe.lineHeight) && probe.renderedHeight > probe.lineHeight * 1.35;
      if (fitsSingleLine && renderedOnMultipleLines) {
        throw new Error(`${viewportName}: heading wrapped despite available row width: ${selector}; ${JSON.stringify(probe)}`);
      }
    }
  }

  const visibleText = (await page.locator('body').innerText()).toLowerCase();
  for (const forbidden of ['[section', '[ux_', '[row', '[col', '[blog_posts', '[ux_products']) {
    if (visibleText.includes(forbidden)) throw new Error(`${viewportName}: legacy Flatsome shortcode leaked visibly: ${forbidden}`);
  }

  const categoryShowcaseCards = await page.locator('.aznet-theme-curtain01-category-showcase__card').count();
  if (categoryShowcaseCards !== 4) throw new Error(`${viewportName}: expected 4 category showcase cards, got ${categoryShowcaseCards}`);

  const categoryShowcaseImages = await page.locator('.aznet-theme-curtain01-category-showcase__card img').count();
  if (categoryShowcaseImages !== 4) throw new Error(`${viewportName}: expected 4 category showcase images, got ${categoryShowcaseImages}`);

    const productCards = await page.locator('.aznet-theme-curtain01-product-card').count();
  if (productCards !== 6) throw new Error(`${viewportName}: expected 6 Homepage product cards, got ${productCards}`);

  const knowledgeCards = await page.locator('.aznet-theme-curtain01-knowledge-card').count();
  if (knowledgeCards !== 3) throw new Error(`${viewportName}: expected 3 knowledge cards, got ${knowledgeCards}`);

  const categoryChips = await page.locator('.aznet-theme-curtain01-category-chip').count();
  if (categoryChips < 3 || categoryChips > 4) throw new Error(`${viewportName}: unexpected product category chip count ${categoryChips}`);

  const styles = await page.locator('link[rel="stylesheet"]').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('href') || ''));
  if (!styles.some((href) => href.includes('/assets/css/presets/curtain-01.css'))) throw new Error(`${viewportName}: site-wide Curtain 01 preset asset missing`);
  if (!styles.some((href) => href.includes('/assets/css/components/homepage-curtain-01.css'))) throw new Error(`${viewportName}: Homepage Curtain 01 asset missing`);
  if (styles.some((href) => href.toLowerCase().includes('flatsome'))) throw new Error(`${viewportName}: Flatsome stylesheet loaded in migration candidate`);

  const scripts = await page.locator('script[src]').evaluateAll((nodes) => nodes.map((node) => node.getAttribute('src') || ''));
  if (scripts.some((src) => src.toLowerCase().includes('flatsome'))) throw new Error(`${viewportName}: Flatsome script loaded in migration candidate`);

  const overflow = await assertNoOverflow(page, viewportName + '-home');
  const a11y = await assertA11y(page, viewportName + '-home');
  await page.screenshot({ path: path.join(outDir, `${viewportName}-home.png`), fullPage: true });

  results[viewportName] = { categoryShowcaseCards, productCards, knowledgeCards, categoryChips, overflow, a11y };
  await context.close();
}

for (const [name, viewport] of Object.entries(viewports)) {
  await verifyHomepage(name, viewport);
}

const context = await browser.newContext({ viewport: viewports.desktop });
const page = await context.newPage();

await page.goto(baseUrl + '/san-pham/', { waitUntil: 'networkidle' });
if (!(await page.locator('body').getAttribute('class') || '').includes('aznet-theme-preset--curtain-01')) {
  throw new Error('shop: Curtain 01 visual preset missing');
}
const shopProducts = await page.locator('.woocommerce ul.products li.product').count();
if (shopProducts < 6) throw new Error(`shop: expected Woo products, got ${shopProducts}`);
await assertNoOverflow(page, 'shop');
results.shop = { products: shopProducts, a11y: await assertA11y(page, 'shop') };
await page.screenshot({ path: path.join(outDir, 'shop.png'), fullPage: true });

const productResponse = await page.goto(baseUrl + '/product/rem-vai-don-sac/', { waitUntil: 'networkidle' });
const productHeadings = page.locator('body.single-product main h1');
if (await productHeadings.count() !== 1 || (await productHeadings.first().innerText()).trim() !== 'Rèm vải đơn sắc') {
  const diagnostic = {
    status: productResponse ? productResponse.status() : null,
    url: page.url(),
    title: await page.title(),
    bodyClass: await page.locator('body').getAttribute('class') || '',
    h1: await page.locator('h1').allTextContents(),
    mainText: (await page.locator('main').count()) ? (await page.locator('main').innerText()).slice(0, 1200) : '',
  };
  throw new Error(`product: native Woo product heading missing or unexpected; ${JSON.stringify(diagnostic)}`);
}
if (!(await page.locator('body').getAttribute('class') || '').includes('aznet-theme-preset--curtain-01')) {
  throw new Error('product: Curtain 01 visual preset missing');
}
await assertNoOverflow(page, 'product');
results.product = { a11y: await assertA11y(page, 'product') };
await page.screenshot({ path: path.join(outDir, 'product.png'), fullPage: true });

await context.close();
await browser.close();

fs.writeFileSync(path.join(outDir, 'summary.json'), JSON.stringify(results, null, 2));
console.log(JSON.stringify(results, null, 2));
