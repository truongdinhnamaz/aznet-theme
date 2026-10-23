/**
 * WordPress-clean read-only browser/a11y smoke on exact preview ZIP installation.
 * Runs only on local CI server at 127.0.0.1. No production traffic or writes.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const root=process.env.RQA_STAGING_DIR;
const postId=process.env.RQA_SENTINEL_POST_ID;
if(!root||!postId||!/^[0-9]+$/.test(postId))throw Error('Isolated staging environment missing');
const output=path.join(root,'browser');
fs.mkdirSync(output,{recursive:true});
const targets=[
 {key:'home',path:'/'},
 {key:'post',path:'/?p='+postId},
];
const viewports=[390,768,1280];
const browser=await chromium.launch({headless:true});
const results=[];
try{
 for(const target of targets){
  for(const width of viewports){
   const context=await browser.newContext({viewport:{width,height:900},locale:'vi-VN',reducedMotion:'reduce'});
   const page=await context.newPage();
   const errors=[];
   page.on('pageerror',e=>errors.push(String(e.message).slice(0,180)));
   const entry={page:target.key,width,pass:false,error:null};
   try {
    const response=await page.goto('http://127.0.0.1:8080'+target.path,{waitUntil:'load',timeout:40000});
    const dom=await page.evaluate(()=>{
      const main=document.querySelector('#main');
      const body=document.body;
      return {
        title:document.title,
        hasMain:Boolean(main),
        hasHeader:Boolean(document.querySelector('.aznet-theme-site-header')),
        hasFooter:Boolean(document.querySelector('.aznet-theme-site-footer')),
        viewport:window.innerWidth,
        documentWidth:document.documentElement.scrollWidth,
        horizontalOverflow:document.documentElement.scrollWidth>window.innerWidth+2,
        leakingWooOrConvertflowAssets:[...document.querySelectorAll('link[rel=stylesheet],script[src]')]
          .map(x=>x.getAttribute('href')||x.getAttribute('src')||'')
          .filter(x=>/woocommerce-archive\.css|choiceguide|convertflow/i.test(x)),
        focusableLinks:[...document.querySelectorAll('a[href]')]
          .filter(x=>x.getClientRects().length>0).length,
        bodyClass:body.className.slice(0,300)
      };
    });
    let accessible={violations:[],incomplete:0,error:null};
    try {
      const audit=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa']).analyze();
      accessible={
        violations:audit.violations.map(v=>({
          rule:v.id,impact:v.impact,
          selectors:v.nodes.slice(0,5).flatMap(n=>n.target)
        })),
        incomplete:audit.incomplete.length,error:null
      };
    }catch(e){accessible.error=String(e.message||e).slice(0,400);}
    const first=page.locator('a[href]:visible').first();
    await first.focus();
    await page.keyboard.press('Tab');
    const focus=await page.evaluate(()=>{
      const active=document.activeElement;
      return {tag:active?.tagName,inDocument:active!==document.body&&active!==document.documentElement};
    });
    Object.assign(entry,dom,{status:response?.status(),a11y:accessible,focus,jsErrors:errors});
    const blocking=accessible.violations.filter(x=>['critical','serious'].includes(x.impact));
    entry.pass=response?.status()===200&&dom.hasHeader&&dom.hasFooter&&dom.hasMain&&
      !dom.horizontalOverflow&&dom.leakingWooOrConvertflowAssets.length===0&&
      dom.focusableLinks>=2&&focus.inDocument&&
      blocking.length===0&&!accessible.error&&errors.length===0;
    await page.screenshot({path:path.join(output,target.key+'-'+width+'.png'),fullPage:false});
   }catch(e){entry.error=String(e.stack||e).slice(0,850);}
   results.push(entry);
   console.log('RQA_CLEAN_WP_BROWSER '+JSON.stringify(entry));
   await context.close();
  }
 }
}finally{await browser.close();}
const pass=results.length===6&&results.every(x=>x.pass);
fs.writeFileSync(path.join(output,'summary.json'),JSON.stringify({pass,cases:results},null,2)+'\n');
console.log('RQA_CLEAN_WP_BROWSER_STATUS '+(pass?'PASS':'FAIL')+' '+results.filter(x=>x.pass).length+'/6');
if(!pass)process.exitCode=1;
