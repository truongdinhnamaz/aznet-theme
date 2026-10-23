/**
 * RQA live responsive QA of Theme-owned Woo card-width candidate.
 * CSS is injected only into isolated browser tabs; production is read-only.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const css=fs.readFileSync('assets/css/components/woocommerce-archive.css','utf8');
const candidate=css.match(/\.woocommerce ul\.products\[class\*="columns-"\] li\.product\s*\{[^}]+\}/)?.[0];
if(!candidate)throw new Error('Missing exact PR #506 CSS candidate');
const widths=[360,768,1024,1366];
const directory=process.env.RQA_GRID_QA_DIR||'/tmp/rqa-mobile-grid';
fs.mkdirSync(directory,{recursive:true});
const browser=await chromium.launch({headless:true});
const rows=[];
try{
  for(const width of widths){
    const page=await browser.newPage({viewport:{width,height:900}});
    const response=await page.goto('https://remquocanh.vn/rem-cuon/',{
      waitUntil:'domcontentloaded',timeout:70000
    });
    await page.locator('main#main ul.products li.product').first()
      .waitFor({state:'visible',timeout:45000});
    await page.waitForTimeout(300);
    const inspect=async()=>page.evaluate(()=>{
      const ul=document.querySelector('main#main ul.products');
      const cards=Array.from(ul?.querySelectorAll('li.product')||[]);
      const tracks=getComputedStyle(ul).gridTemplateColumns.split(/\s+/).map(x=>parseFloat(x)).filter(Number.isFinite);
      const rect=ul.getBoundingClientRect();
      return {viewport:innerWidth,gridWidth:rect.width,tracks,
        cards:cards.map(node=>{
          const cs=getComputedStyle(node),br=node.getBoundingClientRect();
          return {width:br.width,cssFloat:cs.cssFloat,clear:cs.clear,
            link:node.querySelector('a.woocommerce-loop-product__link')?.getAttribute('href')||''};
        })
      };
    });
    const before=await inspect();
    await page.addStyleTag({content:candidate});
    await page.waitForTimeout(150);
    const after=await inspect();
    await page.screenshot({
      path:path.join(directory,'live-card-grid-'+width+'.png'),fullPage:false
    });
    const ratios=after.cards.map((card,i)=>card.width/(after.tracks[i%after.tracks.length]||1));
    const pass=response?.status()===200&&after.cards.length===3&&
      ratios.every(v=>v>=.975&&v<=1.025)&&
      after.cards.every(c=>c.cssFloat==='none'&&c.clear==='none')&&
      after.cards.every(c=>c.link.startsWith('https://remquocanh.vn/'))&&
      after.tracks.length>=1;
    const row={width,pass,status:response?.status(),columns:after.tracks.length,
      beforeWidth:before.cards[0]?.width,afterWidth:after.cards[0]?.width,
      trackWidth:after.tracks[0],ratios:ratios.map(v=>+v.toFixed(3)),
      linksValid:after.cards.every(c=>c.link.startsWith('https://remquocanh.vn/')),
      method:'browser-local CSS injection; no WordPress write'};
    rows.push(row);
    console.log('RQA_GRID_LIVE_MATRIX '+JSON.stringify(row));
    await page.close();
  }
}finally{await browser.close();}
fs.writeFileSync(path.join(directory,'live-responsive-matrix.json'),JSON.stringify(rows,null,2)+'\n');
const ok=rows.length===widths.length&&rows.every(r=>r.pass);
console.log('RQA_GRID_LIVE_MATRIX_STATUS '+(ok?'PASS':'FAIL'));
if(!ok)process.exitCode=1;
