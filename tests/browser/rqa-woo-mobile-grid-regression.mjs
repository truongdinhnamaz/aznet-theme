/**
 * L2 browser cascade contract for Theme-owned Woo archive card presentation.
 * Minimal native WooCommerce smallscreen CSS compatibility fixture, NOT plugin source.
 * No external service, provider storage or live WordPress mutation.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const themeCss=fs.readFileSync('assets/css/components/woocommerce-archive.css','utf8');
const out=process.env.RQA_GRID_QA_DIR||'/tmp/rqa-mobile-grid';
fs.mkdirSync(out,{recursive:true});
const nativeWooFixture=[
  '.woocommerce ul.products li.product {width:22.05%;float:left;clear:none;margin:0 3.8% 2.992em 0;}',
  '@media (max-width:768px) {',
  '  .woocommerce ul.products[class*=columns-] li.product {width:48%;float:left;clear:both;margin:0 0 2.992em;}',
  '  .woocommerce ul.products[class*=columns-] li.product:nth-child(2n) {float:right;clear:none!important;}',
  '}'
].join('\n');
const fixture='<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1">'+
  '<style>html{box-sizing:border-box}*,*::before,*::after{box-sizing:inherit}body{margin:0}'+
  'body.woocommerce main#main {width:min(calc(100% - 36px),1280px);margin-inline:auto}'+
  nativeWooFixture+'\n'+themeCss+'</style></head>'+
  '<body class="woocommerce aznet-theme-woo-catalog--grid aznet-theme-woo-card--balanced">'+
  '<main id="main"><ul class="products columns-4">'+
  '<li class="product"><a href="#">Rèm cuốn chống cháy</a></li>'+
  '<li class="product"><a href="#">Rèm cuốn xuyên sáng</a></li>'+
  '<li class="product"><a href="#">Rèm cuốn cản sáng</a></li>'+
  '</ul></main></body></html>';
const viewports=[390,768,1280];
const browser=await chromium.launch({headless:true});
const checks=[];
try {
  for(const width of viewports){
    const page=await browser.newPage({viewport:{width,height:844}});
    await page.setContent(fixture,{waitUntil:'load'});
    const state=await page.evaluate(()=>{
      const grid=document.querySelector('ul.products');
      const card=grid.querySelector('li.product');
      const g=getComputedStyle(grid),c=getComputedStyle(card);
      return {
        gridDisplay:g.display,columns:g.gridTemplateColumns,
        gridWidth:grid.getBoundingClientRect().width,
        cardWidth:card.getBoundingClientRect().width,
        cardFloat:c.cssFloat,cardClear:c.clear,
        token:getComputedStyle(document.body).getPropertyValue('--aznet-theme-woo-catalog-columns').trim()
      };
    });
    const colWidth=Number.parseFloat(state.columns);
    const ratio=state.cardWidth/colWidth;
    const pass=state.gridDisplay==='grid'&&ratio>=.98&&ratio<=1.02;
    checks.push({width,pass,ratio:Number(ratio.toFixed(4)),...state});
    await page.screenshot({path:path.join(out,String(width)+'.png'),fullPage:false});
    console.log('RQA_WOO_GRID '+JSON.stringify(checks[checks.length-1]));
    await page.close();
  }
}finally{await browser.close();}
fs.writeFileSync(path.join(out,'summary.json'),JSON.stringify(checks,null,2)+'\n');
const failed=checks.filter(x=>!x.pass);
if(failed.length){
  console.error('RED: mobile archive cards do not fill CSS Grid columns at '+failed.map(x=>x.width).join(','));
  process.exitCode=1;
}else console.log('GREEN: archive cards fill CSS Grid cells at all three viewport widths');
