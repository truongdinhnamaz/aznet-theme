/**
 * RQA pilot public consumer L4. This test observes production storefront UI
 * using browser-local interactions only. It NEVER submits a form, follows a
 * purchase/phone/Zalo link, authenticates, or changes WordPress/source state.
 *
 * Domain owners: WooCommerce holds product & variation state; ConvertFlow owns
 * quote context. AZnet Theme asserts consumer-facing presentation only.
 * This is not a certification of backend checkout denial or provider L5.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const site = 'https://remquocanh.vn';
const dir = process.env.RQA_QUOTE_EVIDENCE_DIR || '/tmp/rqa-quote-l4';
const products = [
  { id: 70, slug: 'rem-vai-hoa-van' },
  { id: 522, slug: 'rem-cau-vong-can-sang' },
  { id: 611, slug: 'rem-vai-don-sac' },
  { id: 610, slug: 'rem-vai-2-lop' },
  { id: 608, slug: 'rem-voan' },
  { id: 607, slug: 'rem-cuon-chong-chay' },
  { id: 606, slug: 'rem-cuon-xuyen-sang' },
  { id: 605, slug: 'rem-cuon-can-sang' },
  { id: 604, slug: 'rem-cau-vong-chong-chay' },
  { id: 603, slug: 'rem-cau-vong-xuyen-sang' }
];
const styleCases = [
  { label: 'desktop-karl', width: 1440, height: 900, touch: false, value: 'karl', id: '75' },
  { label: 'mobile-kate', width: 390, height: 844, touch: true, value: 'kate', id: '76' }
];
const expectedStyles = ['brooke', 'carolina', 'donatella', 'karl', 'kate'];
const evidence = { scope: 'RQA public storefront consumer presentation L4 read-only', themeVersion: 'live installed (not inferred from candidate)', source: site, head: process.env.GITHUB_SHA || '', catalog: [], selections: [], note: 'No quote sent, no order/cart created. Backend purchase guard and Rank Math Twitter price are separate known owner gates.' };
fs.mkdirSync(dir, { recursive: true });

const browser = await chromium.launch({headless: true});
try {
  const context = await browser.newContext({viewport: {width: 1440,height: 900}, reducedMotion: 'reduce'});
  for (const p of products) {
    const record = {id: p.id, slug: p.slug, ok: false, quoteFound: false, nativeCart: null, twitterPriceZero: null, error: null};
    const page = await context.newPage();
    try {
      const response = await page.goto(site + '/' + p.slug + '/', {waitUntil:'domcontentloaded',timeout: 55000});
      if (response?.status() !== 200) throw new Error('Public product HTTP status ' + String(response?.status()));
      const root = page.locator('#product-' + p.id);
      await root.waitFor({state: 'attached', timeout: 15000});
      const quote = page.locator('#dat-hang[data-choiceguide-conversion-action="quote"]').first();
      if (!(await quote.count()) || !(await quote.isVisible())) throw new Error('Supported ConvertFlow quote section not visible');
      if (!(await quote.locator('[data-choiceguide-quote-canonical]').count())) throw new Error('Canonical quote root missing');
      if (!(await quote.locator('a[href^="tel:"]').count())) throw new Error('Quote phone channel missing');
      if (!(await quote.locator('a[href^="https://zalo.me/"]').count())) throw new Error('Quote Zalo channel missing');
      record.quoteFound = true;
      const buyButtons = root.locator('button.single_add_to_cart_button, a.add_to_cart_button');
      record.nativeCart = await buyButtons.count();
      if (record.nativeCart !== 0) throw new Error('Native Add-to-cart button visible in quote-only product presentation');
      const meta = await page.locator('meta[name="twitter:data1"]').first().getAttribute('content').catch(() => '');
      record.twitterPriceZero = String(meta || '').includes('0');
      record.ok = true;
      console.log('PASS public quote presentation product #' + p.id);
    } catch (error) {
      record.error = String(error);
      console.error('FAIL public quote presentation product #' + p.id + ': ' + record.error);
    } finally {
      evidence.catalog.push(record);
      await page.close();
    }
  }
  await context.close();

  for (const c of styleCases) {
    const caseRecord = { label: c.label, style: c.value, expectedVariation: c.id, ok: false, swatches: [], selectedVariation: null, selectedAttribute: null, error: null };
    const ctx = await browser.newContext({viewport: {width:c.width,height:c.height},hasTouch:c.touch, reducedMotion:'reduce'});
    const page = await ctx.newPage();
    try {
      const response = await page.goto(site + '/rem-vai-hoa-van/', {waitUntil:'domcontentloaded',timeout:55000});
      if (response?.status() !== 200) throw new Error('Public variable product HTTP not 200');
      const swatches = page.locator('#product-70 .variable-items-wrapper[data-attribute_name="attribute_pa_kieu"] li[data-value]');
      await swatches.first().waitFor({state:'visible',timeout:20000});
      caseRecord.swatches = await swatches.evaluateAll(els=>els.map(el=>el.getAttribute('data-value')));
      if (expectedStyles.some(s=>!caseRecord.swatches.includes(s)) || caseRecord.swatches.length !== 5)
        throw new Error('One or more of five published visual style choices unavailable');
      await swatches.filter({hasText: c.value[0].toUpperCase() + c.value.slice(1)}).click();
      await page.waitForFunction(style => document.querySelector('#pa_kieu')?.value === style, c.value, {timeout:12000});
      const opener = page.locator('a[data-choiceguide-quote-target="dat-hang"]').first();
      await opener.waitFor({state:'visible',timeout:12000});
      await opener.click();
      await page.waitForFunction(expected => document.querySelector('#dat-hang input[name="choiceguide_quote_view_variation_id"]')?.value === expected, c.id, {timeout:15000});
      caseRecord.selectedVariation = await page.locator('#dat-hang input[name="choiceguide_quote_view_variation_id"]').inputValue();
      caseRecord.selectedAttribute = await page.locator('#pa_kieu').inputValue();
      const attributesText = await page.locator('#dat-hang input[name="choiceguide_quote_view_attributes"]').inputValue();
      let attrs={}; try { attrs=JSON.parse(attributesText); } catch {}
      if (attrs.attribute_pa_kieu !== c.value) throw new Error('Selected style not copied into quote context attributes');
      if (!(await page.locator('#dat-hang').isVisible())) throw new Error('Quote section hidden after opening');
      await page.screenshot({path:path.join(dir,c.label+'.png'),fullPage:false});
      caseRecord.ok = true;
      console.log('PASS selected style quote context ' + c.label + ' -> variation ' + c.id);
    } catch (error) {
      caseRecord.error=String(error);
      console.error('FAIL selected style quote context ' + c.label + ': ' + caseRecord.error);
    } finally {
      evidence.selections.push(caseRecord);
      await ctx.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(dir,'evidence.json'),JSON.stringify(evidence,null,2));
}
const failures=[...evidence.catalog,...evidence.selections].filter(p=>!p.ok);
console.log('RQA consumer presentation L4: '+evidence.catalog.filter(x=>x.ok).length+'/10 products; '+evidence.selections.filter(x=>x.ok).length+'/2 style selections; FAIL '+failures.length);
if (failures.length) process.exitCode=1;