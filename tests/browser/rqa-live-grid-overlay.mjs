/** Public read-only Theme CSS candidate proof. Injected style affects only this browser tab. */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
const css=fs.readFileSync('assets/css/components/woocommerce-archive.css','utf8');
const match=css.match(/\.woocommerce ul\.products\[class\*="columns-"\] li\.product\s*\{[^}]+\}/);
if(!match)throw new Error('The exact CSS candidate rule is missing');
const out=process.env.RQA_GRID_QA_DIR||'/tmp/rqa-mobile-grid';
fs.mkdirSync(out,{recursive:true});
const browser=await chromium.launch({headless:true});
let data;
try {
 const context=await browser.newContext({viewport:{width:390,height:844},deviceScaleFactor:2,isMobile:true,hasTouch:true,locale:'vi-VN'});
 const page=await context.newPage();
 const res=await page.goto('https://remquocanh.vn/rem-cuon/',{waitUntil:'domcontentloaded',timeout:65000});
 await page.locator('main#main ul.products li.product').first().waitFor({state:'visible',timeout:40000});
 await page.waitForTimeout(1500);
 const snapshot=async()=>page.evaluate(()=>{
  const grid=document.querySelector('main#main ul.products'),card=grid?.querySelector('li.product');
  if(!grid||!card)return null;
  const gs=getComputedStyle(grid),cs=getComputedStyle(card);
  return {windowWidth:innerWidth,gridWidth:grid.getBoundingClientRect().width,
   columnWidth:Number.parseFloat(gs.gridTemplateColumns),cardWidth:card.getBoundingClientRect().width,
   widthRule:cs.width,float:cs.cssFloat,clear:cs.clear,columns:gs.gridTemplateColumns,
   gridDisplay:gs.display,cardText:card.textContent?.trim()?.slice(0,90)};
 });
 const deployedCustomCss=await page.evaluate(()=>
  (document.querySelector('style#wp-custom-css')?.textContent||'')
    .includes('AZnet Theme RQA scoped Woo archive grid hotfix'));
 const before=await snapshot();
 await page.screenshot({path:path.join(out,'rqa-live-before.png'),fullPage:false});
 await page.addStyleTag({content:match[0]});
 await page.waitForTimeout(250);
 const after=await snapshot();
 await page.screenshot({path:path.join(out,'rqa-live-overlay-after.png'),fullPage:false});
 data={http:res?.status()||0,url:page.url(),before,after,selector:match[0],
  beforeRatio:before?Number((before.cardWidth/before.columnWidth).toFixed(4)):null,
  afterRatio:after?Number((after.cardWidth/after.columnWidth).toFixed(4)):null,
  deployedCustomCss,
  note:'Browser-local candidate overlay for comparative check; no server write.'};
 const prePatch=data.beforeRatio>.4&&data.beforeRatio<.6;
 const alreadyPatched=deployedCustomCss&&data.beforeRatio>=.98&&data.beforeRatio<=1.02;
 data.phase=prePatch?'pre-hotfix-RED':alreadyPatched?'hotfix-live':'unexpected';
 data.pass=data.http===200&&(prePatch||alreadyPatched)&&
  data.afterRatio>=.98&&data.afterRatio<=1.02;
 fs.writeFileSync(path.join(out,'live-overlay-summary.json'),JSON.stringify(data,null,2)+'\n');
 console.log('RQA_LIVE_GRID_OVERLAY '+JSON.stringify(data));
 if(!data.pass)process.exitCode=1;
 await context.close();
}finally{await browser.close();}
