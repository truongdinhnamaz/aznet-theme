/**
 * AZnet Theme L4 consumer-only fail-soft smoke: quote contact remains usable
 * if JavaScript is disabled. Production source, customer data and orders are
 * never changed. Variation/swatch semantics remain Woo/WVS owner-owned.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const host = 'https://remquocanh.vn';
const destination = process.env.RQA_NOJS_EVIDENCE_DIR || '/tmp/rqa-quote-nojs-l4';
const products = [
  { id: 70, slug: 'rem-vai-hoa-van', type: 'variable' },
  { id: 522, slug: 'rem-cau-vong-can-sang', type: 'simple' }
];
const viewports = [
  {name:'desktop',width:1440,height:900},
  {name:'mobile',width:390,height:844}
];
const result = {
  scope: 'RQA public L4 no-JavaScript read-only quote CONTACT fail-soft, not commerce or variation business-state certification',
  head:process.env.GITHUB_SHA||'',
  pages:[]
};
fs.mkdirSync(destination,{recursive:true});

const browser=await chromium.launch({headless:true});
try {
  for(const viewport of viewports){
    const context=await browser.newContext({
      javascriptEnabled:false,viewport:{width:viewport.width,height:viewport.height}
    });
    try{
      for(const product of products){
        const row={
          product:product.id,viewport:viewport.name,http:null,quoteSectionVisible:false,
          phoneLinkVisible:false,zaloLinkVisible:false,
          titleVisible:false,nativeCartVisible:null,
          nativeStyleSelectPresent:null,nativeStyleSelectVisible:null,
          nativeStyleOptions:[],swatchesVisible:null,
          contactFailSoft:false,styleChoiceWithoutJS:null,
          error:null
        };
        const page=await context.newPage();
        try {
          const response=await page.goto(host+'/'+product.slug+'/',{waitUntil:'domcontentloaded',timeout:55000});
          row.http=response?.status()||null;
          if(row.http!==200) throw new Error('HTTP '+row.http+' on public product');
          const root=page.locator('#product-'+product.id);
          await root.waitFor({state:'attached',timeout:12000});
          row.titleVisible=await root.locator('h1').first().isVisible();
          const quote=page.locator('#dat-hang[data-choiceguide-conversion-action="quote"]').first();
          row.quoteSectionVisible=await quote.isVisible();
          const tel=quote.locator('a[href^="tel:"]');
          const zalo=quote.locator('a[href^="https://zalo.me/"]');
          row.phoneLinkVisible=await tel.count()>0 && await tel.first().isVisible();
          row.zaloLinkVisible=await zalo.count()>0 && await zalo.first().isVisible();
          row.nativeCartVisible=await root.locator('button.single_add_to_cart_button:visible, a.add_to_cart_button:visible').count();
          if(product.type==='variable'){
            const select=root.locator('select#pa_kieu');
            row.nativeStyleSelectPresent=await select.count()>0;
            if(row.nativeStyleSelectPresent){
              row.nativeStyleSelectVisible=await select.isVisible();
              row.nativeStyleOptions=(await select.locator('option').evaluateAll(options=>
                options.map(el=>({value:el.value,label:el.textContent.trim()}))
              )).filter(o=>o.value);
            }
            row.swatchesVisible=await root.locator('ul.variable-items-wrapper li.variable-item:visible').count();
            row.styleChoiceWithoutJS=Boolean(row.nativeStyleSelectVisible && row.nativeStyleOptions.length>=5);
          }
          row.contactFailSoft=row.titleVisible&&row.quoteSectionVisible&&row.phoneLinkVisible&&row.zaloLinkVisible&&row.nativeCartVisible===0;
          if(!row.contactFailSoft) throw new Error('Public quote CONTACT fail-soft unavailable without JS');
          await page.screenshot({path:path.join(destination,viewport.name+'-'+product.id+'.png'),fullPage:false});
          console.log('PASS NO-JS contact '+product.id+' '+viewport.name+'; source-owned sample option fallback='+row.styleChoiceWithoutJS);
        }catch(error){
          row.error=String(error);
          console.error('FAIL NO-JS contact '+product.id+' '+viewport.name+': '+row.error);
          try{await page.screenshot({path:path.join(destination,viewport.name+'-'+product.id+'-fail.png'),fullPage:false});}catch{}
        }finally{
          result.pages.push(row);
          await page.close();
        }
      }
    }finally{await context.close();}
  }
}finally{
  await browser.close();
  const failures=result.pages.filter(x=>!x.contactFailSoft||x.error);
  const noJsStyle=result.pages.filter(x=>x.product===70&&x.styleChoiceWithoutJS);
  result.summary={contactPassed:result.pages.length-failures.length,total:result.pages.length,variableStyleFallbackVerified:noJsStyle.length,variableStyleViewportCount:2};
  fs.writeFileSync(path.join(destination,'nojs-fallback.json'),JSON.stringify(result,null,2));
  console.log('RQA NO-JS CONTACT',JSON.stringify(result.summary),'style-selection is separate provider-owned observation');
  if(failures.length) process.exitCode=1;
}