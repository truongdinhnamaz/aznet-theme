/**
 * RQA public presentation L4: real WVS keyboard activation when ready.
 *
 * Theme consumer QA only. Woo owns choices, WVS owns event handling, ConvertFlow
 * owns the quote view. No source changes, customer data, form submit, cart,
 * checkout, phone or Zalo navigation. No WVS private state mutation.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url='https://remquocanh.vn/rem-vai-hoa-van/';
const dir=process.env.RQA_KEYBOARD_EVIDENCE_DIR || '/tmp/rqa-keyboard-l4';
const options=['carolina','donatella','karl','kate'];
const cases=['desktop','mobile'].flatMap(viewport=>
  options.map((style,index)=>({viewport,style,key:index%2===0?'Enter':'Space'}))
);
const evidence={
  scope:'RQA public keyboard input L4 after WVS readiness; not first-interaction timing or backend purchase certification',
  url,head:process.env.GITHUB_SHA||'',cases:[]
};
fs.mkdirSync(dir,{recursive:true});
const browser=await chromium.launch({headless:true});
try{
  for(const c of cases){
    const row={
      viewport:c.viewport,style:c.style,key:c.key,ready:false,
      accessibleRole:null,tabIndex:null,keyboardFocus:false,
      selectedValue:null,ariaChecked:null,quoteStyle:null,
      focusOutline:null,ok:false,error:null
    };
    const context=await browser.newContext({
      viewport:c.viewport==='desktop'?{width:1440,height:900}:{width:390,height:844},
      hasTouch:c.viewport==='mobile',reducedMotion:'reduce'
    });
    const page=await context.newPage();
    try{
      const resp=await page.goto(url,{waitUntil:'domcontentloaded',timeout:55000});
      if(resp?.status()!==200) throw new Error('Public product HTTP '+resp?.status());
      const swatch=page.locator('#product-70 ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"] li[data-value="'+c.style+'"]');
      await swatch.waitFor({state:'visible',timeout:15000});
      await page.waitForFunction(()=>{
        const form=document.querySelector('#product-70 form.variations_form');
        const ul=form?.querySelector('ul.variable-items-wrapper[data-attribute_name="attribute_pa_kieu"]');
        const events=ul && window.jQuery?._data(ul,'events') || {};
        return Boolean(
          form?.classList.contains('wvs-loaded') &&
          events.click?.some(h=>h.namespace==='wvs') &&
          events.keydown?.some(h=>h.namespace==='wvs')
        );
      },null,{timeout:30000});
      row.ready=true;
      row.accessibleRole=await swatch.getAttribute('role');
      row.tabIndex=await swatch.getAttribute('tabindex');
      if(row.accessibleRole!=='radio'||row.tabIndex===null||Number(row.tabIndex)<0)
        throw new Error('Swatch has no keyboard-focusable radio semantics');
      await swatch.focus();
      row.keyboardFocus=await swatch.evaluate(el=>document.activeElement===el);
      row.focusOutline=await swatch.evaluate(el=>{
        const s=getComputedStyle(el);return {style:s.outlineStyle,width:s.outlineWidth};
      });
      if(!row.keyboardFocus) throw new Error('Swatch focus not reached via focusable control');
      await page.keyboard.press(c.key);
      await page.waitForFunction(style=>document.querySelector('#pa_kieu')?.value===style,c.style,{timeout:10000});
      row.selectedValue=await page.locator('#pa_kieu').inputValue();
      row.ariaChecked=await swatch.getAttribute('aria-checked');
      if(row.ariaChecked!=='true') throw new Error('WVS radio aria-checked did not reflect keyboard selection');
      const quote=page.locator('a[data-choiceguide-quote-target="dat-hang"]').first();
      await quote.waitFor({state:'visible',timeout:12000});
      await quote.click();
      await page.waitForFunction(style=>{
        const input=document.querySelector('#dat-hang input[name="choiceguide_quote_view_attributes"]');
        if(!input)return false;
        try{return JSON.parse(input.value).attribute_pa_kieu===style;}catch{return false;}
      },c.style,{timeout:12000});
      row.quoteStyle=c.style;
      row.ok=true;
      console.log('PASS KEYBOARD',c.viewport,c.style,c.key,'selection and quote viewed style');
    }catch(error){
      row.error=String(error);
      console.error('FAIL KEYBOARD',c.viewport,c.style,c.key,row.error);
      try{await page.screenshot({path:path.join(dir,c.viewport+'-'+c.style+'-fail.png'),fullPage:false});}catch{}
    }finally{
      evidence.cases.push(row);
      await context.close();
    }
  }
}finally{
  await browser.close();
  const failed=evidence.cases.filter(x=>!x.ok);
  evidence.summary={total:evidence.cases.length,passed:evidence.cases.length-failed.length,failed:failed.length};
  fs.writeFileSync(path.join(dir,'keyboard-quote.json'),JSON.stringify(evidence,null,2));
  console.log('RQA KEYBOARD L4',JSON.stringify(evidence.summary));
  if(failed.length)process.exitCode=1;
}