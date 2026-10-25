/**
 * Non-destructive public-browser experiment, RQA pilot only.
 *
 * A/B: untouched published HTML vs a browser-memory copy with the core Woo
 * variation script's `defer` removed. The test does NOT alter
 * remquocanh.vn, install extensions, post a quote, add to cart, or change
 * authoritative product state. It reports observations; it does NOT certify
 * either script strategy as a source-owner-approved fix.
 *
 * The already-existing unaltered first-touch RED test remains the real gate.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url = 'https://remquocanh.vn/rem-vai-hoa-van/';
const dir = process.env.RQA_DEFER_EVIDENCE_DIR || '/tmp/rqa-wvs-defer-l4';
const cases = [
  { mode: 'production-unaltered', patchDefer: false },
  { mode: 'browser-local-no-defer', patchDefer: true }
];
const styles = ['carolina', 'kate', 'donatella'];
const rounds = 2;
const evidence = {
  scope: 'Hypothesis A/B public browser only — NOT a production fix or pass gate',
  url,
  explanation: 'The second arm changes Woo variation script attributes only in the in-memory page response served to this test browser',
  testCases: []
};
fs.mkdirSync(dir, {recursive: true});
const browser = await chromium.launch({headless:true});
let invalid = false;
try {
  for (const testCase of cases) {
    for (const style of styles) {
      for (let run = 1; run <= rounds; run++) {
        const row = {
          mode: testCase.mode, style, run, patchedHtml:false, originalDefer:null,
          initialHandlerReady:null, initialWvsLoaded:null, selectedAfterTap:null,
          handlerReadyAfterTap:null, firstTouchSelected:null, error:null
        };
        const context = await browser.newContext({
          viewport:{width:390,height:844}, hasTouch:true, reducedMotion:'reduce'
        });
        const page = await context.newPage();
        try {
          if (testCase.patchDefer) {
            await page.route(url, async route => {
              const response = await route.fetch();
              const html = await response.text();
              const re = /<script\b[^>]*\bid="wc-add-to-cart-variation-js"[^>]*><\/script>/i;
              const match = html.match(re);
              if (!match || !/\bdefer\b/.test(match[0])) {
                throw new Error('Expected genuine deferred Woo variation script tag missing');
              }
              row.originalDefer = true;
              const tag = match[0]
                .replace(/\sdata-wp-strategy="defer"/, '')
                .replace(/\sdefer(?=\s|>)/, '');
              if (tag.includes(' defer') || tag.includes('data-wp-strategy="defer"')) {
                throw new Error('Browser-local script tag patch incomplete');
              }
              row.patchedHtml = true;
              await route.fulfill({response, body:html.replace(re,tag)});
            });
          }
          const response = await page.goto(url,{waitUntil:'domcontentloaded',timeout:55000});
          if (response?.status() !== 200) throw new Error('Public product HTTP not 200');
          const swatch = page.locator(
            '#product-70 ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"] li[data-value="' + style + '"]'
          );
          await swatch.waitFor({state:'visible',timeout:15000});
          const before = await page.evaluate(() => {
            const form = document.querySelector('#product-70 form.variations_form');
            const ul = form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
            const handlers = ul && window.jQuery?._data(ul,'events')?.click || [];
            const wc = document.getElementById('wc-add-to-cart-variation-js');
            return {
              handler:Boolean(handlers.some(h=>h.namespace==='wvs'&&h.selector?.includes('li.variable-item'))),
              loaded:Boolean(form?.classList.contains('wvs-loaded')),
              scriptDefer:Boolean(wc?.defer),
              selected:document.querySelector('#pa_kieu')?.value||''
            };
          });
          row.initialHandlerReady = before.handler;
          row.initialWvsLoaded = before.loaded;
          if (!testCase.patchDefer) row.originalDefer = before.scriptDefer;
          if (testCase.patchDefer && (!row.patchedHtml || before.scriptDefer)) {
            throw new Error('Modified HTML was not applied to the in-memory browser response');
          }
          if (!testCase.patchDefer && !before.scriptDefer) {
            throw new Error('Production control unexpectedly lacks deferred Woo script');
          }
          await swatch.tap();
          try {
            await page.waitForFunction(value =>
              document.querySelector('#pa_kieu')?.value===value, style,{timeout:1800});
          } catch {
            // A lost first interaction is data, not an infrastructure failure.
          }
          row.selectedAfterTap = await page.locator('#pa_kieu').inputValue();
          row.firstTouchSelected = row.selectedAfterTap === style;
          row.handlerReadyAfterTap = await page.evaluate(() => {
            const ul = document.querySelector('#product-70 ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
            const handlers = ul && window.jQuery?._data(ul,'events')?.click || [];
            return Boolean(handlers.some(h=>h.namespace==='wvs'&&h.selector?.includes('li.variable-item')));
          });
          console.log('OBSERVE',JSON.stringify(row));
        } catch(error) {
          row.error=String(error);
          invalid=true;
          console.error('INVALID DIAGNOSTIC',JSON.stringify(row));
        } finally {
          evidence.testCases.push(row);
          await context.close();
        }
      }
    }
  }
} finally {
  await browser.close();
  const stats = cases.map(c => {
    const casesForMode=evidence.testCases.filter(r=>r.mode===c.mode);
    const valid=casesForMode.filter(r=>!r.error);
    return {
      mode:c.mode, valid:valid.length, total:casesForMode.length,
      readyBefore:valid.filter(r=>r.initialHandlerReady).length,
      firstTouchSelected:valid.filter(r=>r.firstTouchSelected).length
    };
  });
  evidence.stats=stats;
  fs.writeFileSync(path.join(dir,'script-strategy-hypothesis.json'),JSON.stringify(evidence,null,2));
  console.log('BROWSER-ONLY HYPOTHESIS A/B',JSON.stringify(stats));
  console.log('IMPORTANT: any positive outcome is diagnostic only; real first-touch RED remains open');
}
if (invalid) process.exitCode=1;
