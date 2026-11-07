/**
 * Production-only, read-only post-hotfix verification for RQA.
 * No browser-local CSS injections and no WordPress changes.
 * Checks deployed native WordPress Additional CSS, actual computed Woo Grid
 * card widths, all category cards, valid links and one keyboard continuation.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const targets=[
  {slug:'rem-cuon',expected:3},
  {slug:'rem-vai',expected:4},
  {slug:'rem-cau-vong',expected:3},
];
const widths=[390,768,1280];
const output=process.env.RQA_PRODUCTION_QA_DIR||'/tmp/rqa-production-css-qa';
fs.mkdirSync(output,{recursive:true});
const browser=await chromium.launch({headless:true});
const rows=[];
try{
  for(const target of targets){
    for(const width of widths){
      const page=await browser.newPage({
        viewport:{width,height:900},
        locale:'vi-VN',
      });
      const row={slug:target.slug,width,pass:false,error:null};
      try{
        const response=await page.goto('https://remquocanh.vn/'+target.slug+'/',{
          waitUntil:'domcontentloaded',timeout:75000
        });
        await page.locator('main#main ul.products li.product').first()
          .waitFor({state:'visible',timeout:45000});
        await page.waitForTimeout(150);
        const result=await page.evaluate(()=>{
          const css=document.querySelector('style#wp-custom-css')?.textContent||'';
          const grid=document.querySelector('main#main ul.products');
          const cards=[...(grid?.querySelectorAll('li.product')||[])];
          const tracks=(getComputedStyle(grid).gridTemplateColumns||'')
            .split(/\s+/).map(parseFloat).filter(Number.isFinite);
          const actual=cards.map((node,i)=>{
            const style=getComputedStyle(node);
            return {
              cardWidth:node.getBoundingClientRect().width,
              trackWidth:tracks[i%tracks.length]||0,
              float:style.cssFloat,
              clear:style.clear,
              url:node.querySelector('a.woocommerce-loop-product__link')?.getAttribute('href')||'',
            };
          });
          return {
            title:document.title,
            cssTag:css.includes('AZnet Theme RQA scoped Woo archive grid hotfix'),
            cssRule:css.includes('.woocommerce ul.products[class*="columns-"] li.product'),
            products:cards.length,
            columns:tracks.length,
            widths:actual.map(x=>({
              ratio:+(x.cardWidth/(x.trackWidth||1)).toFixed(4),
              width:+x.cardWidth.toFixed(2),track:+x.trackWidth.toFixed(2),
              float:x.float,clear:x.clear
            })),
            linksValid:actual.every(x=>x.url.startsWith('https://remquocanh.vn/')),
          };
        });
        const first=page.locator('main#main ul.products li.product a.woocommerce-loop-product__link').first();
        await first.focus();
        await page.keyboard.press('Tab');
        const keyboard=await page.evaluate(()=>{
          const el=document.activeElement;
          return {tag:el?.tagName,inGrid:!!el?.closest('main#main ul.products')};
        });
        Object.assign(row,result,{status:response?.status()||null,keyboard});
        row.pass=response?.status()===200&&result.cssTag&&result.cssRule&&
          result.products===target.expected&&result.linksValid&&result.columns>=1&&
          result.widths.every(x=>x.ratio>=0.975&&x.ratio<=1.025)&&
          keyboard.tag==='A'&&keyboard.inGrid;
        await page.screenshot({
          path:path.join(output,target.slug+'-'+width+'.png'),
          fullPage:false,timeout:20000
        }).catch(()=>{});
      }catch(e){row.error=String(e.stack||e).slice(0,900);}
      rows.push(row);
      console.log('RQA_PRODUCTION_CSS '+JSON.stringify(row));
      await page.close();
    }
  }
}finally{await browser.close();}
fs.writeFileSync(path.join(output,'summary.json'),JSON.stringify(rows,null,2)+'\n');
const pass=rows.length===9&&rows.every(x=>x.pass);
console.log('RQA_PRODUCTION_CSS_STATUS '+(pass?'PASS':'FAIL')+' '+rows.filter(x=>x.pass).length+'/9');
if(!pass)process.exitCode=1;
