/**
 * RQA read-only cross-category Woo archive CSS regression.
 * Uses exact candidate CSS in browser tabs; no production writes.
 */
import fs from 'node:fs';
import path from 'node:path';
import {chromium} from 'playwright';
const css=fs.readFileSync('assets/css/components/woocommerce-archive.css','utf8');
const rule=css.match(/\.woocommerce ul\.products\[class\*="columns-"\] li\.product\s*\{[^}]+\}/)?.[0];
if(!rule)throw new Error('Candidate CSS rule is missing from work branch');
const targets=[
 {slug:'rem-vai',expectedCount:4},
 {slug:'rem-cau-vong',expectedCount:3},
];
const widths=[390,768];
const directory=process.env.RQA_GRID_QA_DIR||'/tmp/rqa-mobile-grid';
fs.mkdirSync(directory,{recursive:true});
const browser=await chromium.launch({headless:true});
const rows=[];
try{
 for(const target of targets){
  for(const width of widths){
   const page=await browser.newPage({viewport:{width,height:900},locale:'vi-VN'});
   let value={slug:target.slug,width,pass:false,error:null};
   try{
    const url='https://remquocanh.vn/'+target.slug+'/';
    const response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:70000});
    const grid=page.locator('main#main ul.products').first();
    await grid.waitFor({state:'visible',timeout:45000});
    await page.addStyleTag({content:rule});
    await page.waitForTimeout(220);
    const p=await page.evaluate(()=>{
      const ul=document.querySelector('main#main ul.products');
      const items=[...ul.querySelectorAll('li.product')];
      const tracks=getComputedStyle(ul).gridTemplateColumns.split(/\s+/).map(parseFloat);
      return {columns:tracks.length,widths:items.map(node=>node.getBoundingClientRect().width),
        ratios:items.map((node,i)=>node.getBoundingClientRect().width/tracks[i%tracks.length]),
        links:items.map(node=>node.querySelector('a.woocommerce-loop-product__link')?.getAttribute('href')||'')};
    });
    const linksValid=p.links.every(link=>link.startsWith('https://remquocanh.vn/'));
    const primary=page.locator('main#main ul.products li.product a.woocommerce-loop-product__link').first();
    await primary.focus();
    await page.keyboard.press('Tab');
    const keyboardNext=await page.evaluate(()=>{
      const el=document.activeElement;
      return {tag:el?.tagName,inGrid:!!el?.closest('main#main ul.products'),
        href:el?.getAttribute('href')||null};
    });
    const pass=response?.status()===200&&p.widths.length===target.expectedCount&&
      p.ratios.every(v=>v>=.975&&v<=1.025)&&linksValid&&
      keyboardNext.tag==='A'&&keyboardNext.inGrid;
    value={slug:target.slug,width,pass,status:response?.status(),
      products:p.widths.length,columns:p.columns,
      cardRatios:p.ratios.map(x=>+x.toFixed(4)),linksValid,keyboardNext};
    await page.screenshot({path:path.join(directory,target.slug+'-'+width+'.png'),fullPage:false});
   }catch(e){value.error=String(e.stack||e).slice(0,800);}
   rows.push(value);console.log('RQA_OTHER_CATEGORIES '+JSON.stringify(value));
   await page.close();
  }
 }
}finally{await browser.close();}
fs.writeFileSync(path.join(directory,'other-categories-matrix.json'),JSON.stringify(rows,null,2)+'\n');
const pass=rows.length===targets.length*widths.length&&rows.every(x=>x.pass);
console.log('RQA_OTHER_CATEGORIES_STATUS '+(pass?'PASS':'FAIL'));
if(!pass)process.exitCode=1;
