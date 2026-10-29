/**
 * RQA L4 positive control, read-only: an explicit provider-ready swatch works.
 * This does NOT heal or hide the real first-tap-before-init UX failure.
 * Nothing submitted, no commerce cart/order action, no WP admin/session writes.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url = 'https://remquocanh.vn/rem-vai-hoa-van/';
const dir = process.env.RQA_READY_EVIDENCE_DIR || '/tmp/rqa-swatch-ready-l4';
const cases = [
  { name: 'mobile-carolina', value: 'carolina' },
  { name: 'mobile-kate', value: 'kate' },
];
const evidence = { scope: 'RQA installed WVS ready-state POSITIVE CONTROL only; initial tap before initialization remains RED', source: url, head: process.env.GITHUB_SHA || '', cases: [] };
fs.mkdirSync(dir,{recursive:true});
const browser = await chromium.launch({headless:true});
try {
  for (const c of cases) {
    const record = {case:c.name,ready:false,initialReady:false,waitForReadyMs:null,touchPassed:false,quoteStylePassed:false,error:null};
    const context = await browser.newContext({viewport:{width:390,height:844},hasTouch:true,reducedMotion:'reduce'});
    const page = await context.newPage();
    try {
      const response = await page.goto(url,{waitUntil:'domcontentloaded',timeout:55000});
      if (response?.status()!==200) throw new Error('Product public HTTP not 200');
      const swatch = page.locator('#product-70 ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"] li[data-value="'+c.value+'"]');
      await swatch.waitFor({state:'visible',timeout:15000});
      const readiness = () => page.evaluate(() => {
        const form=document.querySelector('#product-70 form.variations_form');
        const ul=form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
        const delegates=ul && window.jQuery?._data(ul,'events')?.click || [];
        return {loaded:Boolean(form?.classList.contains('wvs-loaded')),handler:Boolean(delegates.some(d=>d.namespace==='wvs'&&d.selector?.includes('li.variable-item')))};
      });
      const initial = await readiness();
      record.initialReady = initial.loaded && initial.handler;
      const start=Date.now();
      await page.waitForFunction(() => {
        const form=document.querySelector('#product-70 form.variations_form');
        const ul=form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
        const delegates=ul && window.jQuery?._data(ul,'events')?.click || [];
        return Boolean(form?.classList.contains('wvs-loaded') && delegates.some(d=>d.namespace==='wvs'&&d.selector?.includes('li.variable-item')));
      }, null, {timeout:30000});
      record.waitForReadyMs=Date.now()-start;
      record.ready=true;
      await swatch.tap();
      await page.waitForFunction(style=>document.querySelector('#pa_kieu')?.value===style,c.value,{timeout:12000});
      record.touchPassed = true;
      const quote = page.locator('a[data-choiceguide-quote-target="dat-hang"]').first();
      await quote.waitFor({state:'visible',timeout:12000});
      await quote.click();
      await page.waitForFunction(style=>{
        const input=document.querySelector('#dat-hang input[name="choiceguide_quote_view_attributes"]');
        if(!input) return false;
        try{return JSON.parse(input.value).attribute_pa_kieu===style;}catch{return false;}
      },c.value,{timeout:12000});
      record.quoteStylePassed=true;
      await page.screenshot({path:path.join(dir,c.name+'-ready.png'),fullPage:false});
      console.log('PASS READY-STATE control '+c.name+': touch to native select and quote viewed style, waited '+record.waitForReadyMs+'ms');
    } catch(error) {
      record.error=String(error);
      console.error('FAIL READY-STATE control '+c.name+': '+record.error);
    } finally {
      evidence.cases.push(record);
      await context.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(dir,'ready-control.json'),JSON.stringify(evidence,null,2));
}
const failures=evidence.cases.filter(c=>!c.ready||!c.touchPassed||!c.quoteStylePassed);
console.log('READY-STATE control '+(evidence.cases.length-failures.length)+'/'+evidence.cases.length+' (NOT first-touch acceptance)');
if(failures.length) process.exitCode=1;
