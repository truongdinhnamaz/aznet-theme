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
  { label: 'desktop-brooke', width: 1440, height: 900, touch: false, value: 'brooke', id: '74' },
  { label: 'desktop-carolina', width: 1440, height: 900, touch: false, value: 'carolina', id: '78' },
  { label: 'desktop-donatella', width: 1440, height: 900, touch: false, value: 'donatella', id: '77' },
  { label: 'desktop-karl', width: 1440, height: 900, touch: false, value: 'karl', id: '75' },
  { label: 'desktop-kate', width: 1440, height: 900, touch: false, value: 'kate', id: '76' },
  { label: 'mobile-brooke', width: 390, height: 844, touch: true, value: 'brooke', id: '74' },
  { label: 'mobile-carolina', width: 390, height: 844, touch: true, value: 'carolina', id: '78' },
  { label: 'mobile-donatella', width: 390, height: 844, touch: true, value: 'donatella', id: '77' },
  { label: 'mobile-karl', width: 390, height: 844, touch: true, value: 'karl', id: '75' },
  { label: 'mobile-kate', width: 390, height: 844, touch: true, value: 'kate', id: '76' }
];
const expectedStyles = ['brooke', 'carolina', 'donatella', 'karl', 'kate'];
const evidence = { scope: 'RQA public storefront consumer presentation L4 read-only', themeVersion: 'live installed (not inferred from candidate)', source: site, head: process.env.GITHUB_SHA || '', catalog: [], selections: [], note: 'No quote sent, no order/cart created. Backend purchase guard and Rank Math Twitter price are separate known owner gates.' };
fs.mkdirSync(dir, { recursive: true });

const browser = await chromium.launch({headless: true});
try {
  const context = await browser.newContext({viewport: {width: 1440,height: 900}, reducedMotion: 'reduce'});
  for (const p of products) {
    const record = {id: p.id, slug: p.slug, ok: false, quoteFound: false, nativeCartDom: null, nativeCartVisible: null, twitterPriceZero: null, error: null};
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
      record.nativeCartDom = await buyButtons.count();
      record.nativeCartVisible = await root.locator('button.single_add_to_cart_button:visible, a.add_to_cart_button:visible').count();
      if (record.nativeCartVisible !== 0) throw new Error('Native Add-to-cart button is visibly interactive in quote-only product');
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
    const caseRecord = { label: c.label, style: c.value, expectedVariation: c.id, ok: false, swatches: [], selectedVariation: null, selectedAttribute: null, diagnostics: null, error: null };
    const ctx = await browser.newContext({viewport: {width:c.width,height:c.height},hasTouch:c.touch, reducedMotion:'reduce'});
    const page = await ctx.newPage();
    try {
      const response = await page.goto(site + '/rem-vai-hoa-van/', {waitUntil:'domcontentloaded',timeout:55000});
      if (response?.status() !== 200) throw new Error('Public variable product HTTP not 200');
      await page.waitForLoadState('load',{timeout:20000});
      const swatches = page.locator('#product-70 .variable-items-wrapper[data-attribute_name="attribute_pa_kieu"] li[data-value]');
      await swatches.first().waitFor({state:'visible',timeout:20000});
      caseRecord.swatches = await swatches.evaluateAll(els=>els.map(el=>el.getAttribute('data-value')));
      if (expectedStyles.some(s=>!caseRecord.swatches.includes(s)) || caseRecord.swatches.length !== 5)
        throw new Error('One or more of five published visual style choices unavailable');
      // Public browser-local event telemetry: captures pointer target and
      // hit-testing without changing server state or submitting any request.
      if (c.touch) await page.evaluate(() => {
        window.__rqaTapEvents = [];
        ['pointerdown','pointerup','touchstart','touchend','click','change'].forEach(type => {
          document.addEventListener(type, event => {
            const target = event.target instanceof Element ? event.target : null;
            const swatch = target?.closest('.variable-items-wrapper li[data-value]');
            window.__rqaTapEvents.push({
              type,
              tag: target?.tagName || '',
              cls: String(target?.className || '').slice(0,90),
              swatch: swatch?.getAttribute('data-value') || '',
              pointerType: event.pointerType || '',
              trusted: event.isTrusted,
              selected: document.querySelector('#pa_kieu')?.value || ''
            });
          },true);
        });
      });
      // Bound each listener to this isolated browser page; no site-side script
      // is changed. Compare capture/bubble propagation into WVS delegated ul.
      if (c.touch) await page.evaluate(style => {
        const ul = document.querySelector('#product-70 .variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
        const li = ul?.querySelector('li[data-value="' + style + '"]');
        const sel = document.querySelector('#pa_kieu');
        const capture = (scope, stage, e) => window.__rqaTapEvents.push({
          type:e.type,scope,stage,phase:e.eventPhase,selected:sel?.value||'',
          swatch:e.target?.closest?.('li[data-value]')?.getAttribute('data-value')||'',
          defaultPrevented:e.defaultPrevented,
          cancelBubble:e.cancelBubble
        });
        for (const [scope, el] of [['ul',ul],['li',li],['document',document]]) {
          if (!el) continue;
          el.addEventListener('click', e=>capture(scope,'capture',e),true);
          el.addEventListener('click', e=>capture(scope,'bubble',e),false);
        }
        sel?.addEventListener('change',e=>capture('native-select','change',e),true);
      }, c.value);
      if (c.touch) caseRecord.beforeTap = await page.evaluate(() => {
        const form=document.querySelector('#product-70 form.variations_form');
        const ul=form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
        const click=ul&&window.jQuery?._data(ul,'events')?.click||[];
        return {
          formWvsLoaded:Boolean(form?.classList.contains('wvs-loaded')),
          selected:document.querySelector('#pa_kieu')?.value||'',
          clickDelegates:click.map(e=>({selector:e.selector||'',namespace:e.namespace||''})),
          conversionRuntime:Boolean(window.ChoiceGuideConversionRuntime)
        };
      });
      const swatch = swatches.filter({hasText: c.value[0].toUpperCase() + c.value.slice(1)});
      if (c.touch) await swatch.tap(); else await swatch.click();
      if (c.touch) caseRecord.afterTap = await page.evaluate(() => ({
        select:document.querySelector('#pa_kieu')?.value||'',
        selectedSwatch:document.querySelector('#product-70 .variable-items-wrapper li.selected')?.getAttribute('data-value')||'',
        events:(window.__rqaTapEvents||[]).slice(-40)
      }));
      await page.waitForFunction(style => document.querySelector('#pa_kieu')?.value === style, c.value, {timeout:12000});
      const opener = page.locator('a[data-choiceguide-quote-target="dat-hang"]').first();
      await opener.waitFor({state:'visible',timeout:12000});
      await opener.click();
      // A quote is a request, not an order. Woo add-to-cart fields are removed
      // by the provider's quote UI; native variation ID may legitimately be
      // absent. Assert that the view's style reaches the quote form instead.
      await page.waitForFunction(style => {
        const input = document.querySelector('#dat-hang input[name="choiceguide_quote_view_attributes"]');
        if (!input) return false;
        try { return JSON.parse(input.value).attribute_pa_kieu === style; }
        catch { return false; }
      }, c.value, {timeout:15000});
      caseRecord.selectedVariation = await page.locator('#dat-hang input[name="choiceguide_quote_view_variation_id"]').inputValue();
      caseRecord.selectedAttribute = await page.locator('#pa_kieu').inputValue();
      if (caseRecord.selectedVariation && caseRecord.selectedVariation !== c.id)
        throw new Error('Quote view context maps selected style to a different Woo variation');
      if (!(await page.locator('#dat-hang').isVisible())) throw new Error('Quote section hidden after opening');
      await page.screenshot({path:path.join(dir,c.label+'.png'),fullPage:false});
      caseRecord.ok = true;
      console.log('PASS selected style quote context ' + c.label + ' -> attribute ' + c.value + ', view variation ID ' + (caseRecord.selectedVariation || '(empty)'));
    } catch (error) {
      caseRecord.error=String(error);
      try {
        caseRecord.diagnostics = await page.evaluate((style) => {
          const selected = document.querySelector('#product-70 .variable-items-wrapper li.selected');
          const target = document.querySelector('#product-70 .variable-items-wrapper li[aria-checked="true"]');
          const form = document.querySelector('#product-70 form.variations_form');
          const targetSwatch = document.querySelector('#product-70 .variable-items-wrapper li[data-value="' + style + '"]') || target;
          const rect=targetSwatch?.getBoundingClientRect();
          const center=rect ? {x:Math.round(rect.left+rect.width/2),y:Math.round(rect.top+rect.height/2)} : null;
          const hit=center ? document.elementFromPoint(center.x,center.y) : null;
          return {
            readyState:document.readyState, hasJQuery:Boolean(window.jQuery),
            nativeSelectValue:document.querySelector('#pa_kieu')?.value||'',
            selectedSwatch:selected?.getAttribute('data-value')||'',
            selectedVariationId:form?.querySelector('[name="variation_id"]')?.value||'',
            quoteVariationId:document.querySelector('#dat-hang input[name="choiceguide_quote_view_variation_id"]')?.value||'',
            quoteAttributes:document.querySelector('#dat-hang input[name="choiceguide_quote_view_attributes"]')?.value||'',
            quoteLabels:document.querySelector('#dat-hang input[name="choiceguide_quote_view_labels"]')?.value||'',
            targetSwatchExists:Boolean(target), variationFormExists:Boolean(form),
            hitTestAtHoveredCenter: center ? {center,tag:hit?.tagName||'',cls:String(hit?.className||'').slice(0,120),swatch:hit?.closest('.variable-items-wrapper li[data-value]')?.getAttribute('data-value')||''} : null,
            touchEvents:(window.__rqaTapEvents||[]).slice(-25),
            currentWvsState:(()=>{
              const ul=form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
              const click=ul&&window.jQuery?._data(ul,'events')?.click||[];
              return {formWvsLoaded:Boolean(form?.classList.contains('wvs-loaded')),clickDelegates:click.map(e=>({selector:e.selector||'',namespace:e.namespace||''}))};
            })()
          };
        }, c.value);
      } catch {}
      try {await page.screenshot({path:path.join(dir,c.label+'-fail.png'),fullPage:false});} catch {}
      if (c.touch) {
        const retrySwatch = page.locator('#product-70 .variable-items-wrapper li[data-value="' + c.value + '"]');
        try {
          await retrySwatch.tap({timeout:4000});
          await page.waitForFunction(style => document.querySelector('#pa_kieu')?.value === style, c.value, {timeout:5000});
          caseRecord.secondTouch = {worked:true, selectedStyle:await page.locator('#pa_kieu').inputValue()};
        } catch (touchErr) {
          caseRecord.secondTouch = {worked:false,error:String(touchErr)};
        }
        if (!caseRecord.secondTouch.worked) {
          try {
            await retrySwatch.click({timeout:4000});
            await page.waitForFunction(style => document.querySelector('#pa_kieu')?.value === style, c.value, {timeout:5000});
            caseRecord.mouseClickFallback = {selectedStyle: await page.locator('#pa_kieu').inputValue(), worked: true};
          } catch (fallbackErr) {
            caseRecord.mouseClickFallback = {worked:false,error:String(fallbackErr)};
          }
        }
      }
      console.error('FAIL selected style quote context ' + c.label + ': ' + caseRecord.error + ' | beforeTap ' + JSON.stringify(caseRecord.beforeTap) + ' | diagnostics ' + JSON.stringify(caseRecord.diagnostics) + ' | secondTouch ' + JSON.stringify(caseRecord.secondTouch) + ' | mouseFallback ' + JSON.stringify(caseRecord.mouseClickFallback));
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
console.log('RQA consumer presentation L4: '+evidence.catalog.filter(x=>x.ok).length+'/10 products; '+evidence.selections.filter(x=>x.ok).length+'/10 style selections; FAIL '+failures.length);
if (failures.length) process.exitCode=1;