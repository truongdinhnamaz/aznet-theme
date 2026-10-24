/**
 * READ-ONLY RQA public ConvertFlow/Woo DOM + browser-local AZnet 1.3.97 CSS.
 * No site writes, no admin, no plugin-source copy. NOT full candidate L5.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const SITE = 'https://remquocanh.vn';
const OUT = process.env.RQA_OVERLAY_EVIDENCE_DIR || '/tmp/rqa-v1397-overlay';
const root = path.resolve(import.meta.dirname, '..', '..');
const css = fs.readFileSync(path.join(root, 'assets/css/components/woocommerce-archive.css'), 'utf8');
const style = fs.readFileSync(path.join(root, 'style.css'), 'utf8');
const rulePattern = /\.woocommerce\s+ul\.products\[class\*=["']columns-["']\]\s+li\.product\s*\{[^}]*\}/g;
function need(ok, why) { if (!ok) throw new Error('RQA_OVERLAY_FAIL: ' + why); }
need(/^Version:\s*1\.3\.97\s*$/m.test(style), 'exact 1.3.97 candidate required');
need((css.match(rulePattern) || []).length === 1, 'candidate Woo columns fix present exactly once');
fs.mkdirSync(OUT, { recursive: true });
const browser = await chromium.launch({ headless: true });
const observations = [];
async function navigate(page, url) {
  const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
  need(response?.status() === 200, 'GET ' + url + ' must return 200');
  await page.locator('body.wp-theme-aznet-theme').waitFor({ state: 'attached' });
  need(await page.locator('link#choiceguide-theme-integration-css').count() === 1, 'real ConvertFlow public bridge CSS');
  need(await page.locator('link#aznet-theme-convertflow-contract-css').count() === 1, 'Theme-owned public bridge CSS');
}
async function measure(page) {
  return page.evaluate(() => {
    const grid = document.querySelector('.woocommerce ul.products[class*="columns-"]');
    if (!grid) return { error: 'grid absent' };
    const cards = [...grid.querySelectorAll(':scope > li.product')];
    if (!cards.length) return { error: 'cards absent' };
    const s = getComputedStyle(grid);
    const n = s.gridTemplateColumns.split(/\s+/).filter(Boolean).length;
    const gap = parseFloat(s.columnGap) || 0;
    const track = (grid.getBoundingClientRect().width - (n - 1) * gap) / n;
    const rect = cards[0].getBoundingClientRect();
    return {
      gridDisplay: s.display, columns: n, cards: cards.length,
      ratio: Math.round(rect.width / track * 1000) / 1000,
      overflow: grid.scrollWidth - grid.clientWidth,
      links: cards.map(el => el.querySelector('a.woocommerce-loop-product__link')?.href || ''),
    };
  });
}
async function clientOnlyRemoveHotfix(page) {
  return page.locator('style#wp-custom-css').evaluate(el => {
    const re = /\.woocommerce\s+ul\.products\[class\*=["']columns-["']\]\s+li\.product\s*\{[^}]*\}/g;
    const original = el.textContent || '';
    const matches = original.match(re) || [];
    if (matches.length !== 1) throw new Error('Expected exact live WP Custom CSS rule');
    for (const declaration of ['float: none;', 'clear: none;', 'width: auto;', 'margin: 0;']) {
      if (!matches[0].includes(declaration)) throw new Error('Unexpected native CSS hotfix ' + declaration);
    }
    el.textContent = original.replace(matches[0], '');
    return matches[0];
  });
}
try {
  for (const width of [390, 768, 1280]) {
    const context = await browser.newContext({ viewport: { width, height: 850 }, locale: 'vi-VN' });
    const page = await context.newPage();
    await navigate(page, SITE + '/san-pham/');
    need(await page.locator('[data-choiceguide-filter-role="auto"]').count() >= 1, 'real provider catalog filter at ' + width);
    need(await page.locator('[data-choiceguide-filter-result-summary][aria-live="polite"]').count() >= 1, 'provider live result summary');
    const baseline = await measure(page);
    need(!baseline.error && baseline.gridDisplay === 'grid' && baseline.cards >= 2, 'real Woo catalog cards at ' + width);
    await clientOnlyRemoveHotfix(page);
    const without = await measure(page);
    if (width === 390) {
      need(without.ratio < 0.90, 'RED expects undersized cards without client-only hotfix; ratio=' + without.ratio);
      console.log('RQA_LIVE_PROVIDER_RED ratio=' + without.ratio);
      await page.screenshot({path: path.join(OUT, 'shop-390-RED-browser-only.png'), fullPage: true});
    }
    await page.addStyleTag({content: css});
    const green = await measure(page);
    need(green.ratio >= 0.94 && green.overflow <= 2, 'GREEN candidate cards must fill grid and avoid overflow width=' + width + ' ratio=' + green.ratio + ' overflow=' + green.overflow);
    need(green.cards === baseline.cards && JSON.stringify(green.links) === JSON.stringify(baseline.links), 'provider data/links unchanged');
    need(await page.locator('[data-choiceguide-filter-role="auto"]').count() >= 1, 'provider catalog filter retained');
    if (width === 390 || width === 768) {
      await page.screenshot({path: path.join(OUT, 'shop-' + width + '-GREEN-browser-only.png'), fullPage: true});
    }
    observations.push({width, baseline, without, green});
    console.log('RQA_LIVE_PROVIDER_GREEN width=' + width + ' ratio=' + green.ratio + ' cards=' + green.cards);
    await context.close();
  }
  const context = await browser.newContext({viewport: {width:390,height:850}, locale:'vi-VN'});
  const page = await context.newPage();
  await navigate(page, SITE + '/rem-vai-don-sac/');
  const nav = page.locator('[data-choiceguide-sticky-submenu]');
  const cta = page.locator('a.choiceguide-quote-button--canonical');
  need(await nav.count() === 1 && await cta.count() === 1, 'actual provider Journey and quote-first CTA');
  const before = {href: await cta.getAttribute('href'), label: (await cta.innerText()).trim(), links: await nav.locator('[data-choiceguide-journey-nav]').count()};
  need(before.href?.startsWith('#') && before.links > 0, 'valid public provider projection');
  await page.addStyleTag({content:css});
  const after = {href: await cta.getAttribute('href'), label:(await cta.innerText()).trim(), links: await nav.locator('[data-choiceguide-journey-nav]').count()};
  need(JSON.stringify(before) === JSON.stringify(after), 'candidate CSS leaves provider-owned CTA/links unchanged');
  await page.screenshot({path:path.join(OUT, 'product-390-provider-preserved.png'),fullPage:true});
  observations.push({product: {before,after}});
  await context.close();
  console.log('RQA_LIVE_PROVIDER_CSS_OVERLAY_PASS');
} finally {
  await browser.close();
  fs.writeFileSync(path.join(OUT,'observations.json'),JSON.stringify({
    scope:'public live provider DOM on Theme 1.3.92, browser-local 1.3.97 CSS overlay',
    actualTheme1397L5:false, productionWrite:false, backupTest:false, observations
  },null,2)+'\n');
}
