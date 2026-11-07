/**
 * Rèm Quốc Anh /rem-cuon/ — source-owner CLS probe, public read-only.
 * Candidate CSS is intercepted in THIS Chromium tab only, never written to WP.
 * Separate causal evidence from the verified Theme grid-card width fix.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const source = fs.readFileSync('assets/css/components/woocommerce-archive.css', 'utf8');
const rule = source.match(/\.woocommerce ul\.products\[class\*="columns-"\] li\.product\s*\{[^}]+\}/)?.[0];
if (!rule) throw new Error('Theme CSS candidate missing: check PR #506 source');
const out = process.env.RQA_CLS_TRACE_DIR || '/tmp/rqa-cls-owner-trace';
fs.mkdirSync(out, { recursive: true });
const results = [];
const browser = await chromium.launch({headless:true});
try {
  for(const mode of ['current','browser-overlay']){
    const context=await browser.newContext({
      viewport:{width:390,height:844},deviceScaleFactor:2,
      isMobile:true,hasTouch:true,locale:'vi-VN',
    });
    const page=await context.newPage();
    const runtimeErrors=[];
    page.on('pageerror',e=>runtimeErrors.push(String(e.message).slice(0,250)));
    const cdp=await context.newCDPSession(page);
    await cdp.send('Network.enable');
    await cdp.send('Network.emulateNetworkConditions',{
      offline:false,latency:150,downloadThroughput:200000,uploadThroughput:93750
    });
    await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
    let injected=false;
    if(mode==='browser-overlay'){
      await page.route('**/assets/css/components/woocommerce-archive.css?**',async route=>{
        const response=await route.fetch();
        const currentCss=await response.text();
        injected=true;
        await route.fulfill({response,body:currentCss+'\n/* local CSS candidate */\n'+rule+'\n'});
      });
    }
    await page.addInitScript(()=>{
      const state={shifts:[],geometry:[],mutations:[],errors:[]};
      window.__rqaClsTrace=state;
      const ident=el=>{
        if(!(el instanceof Element))return null;
        return (el.tagName.toLowerCase()+(el.id?'#'+el.id:'')+
          [...el.classList].slice(0,3).map(c=>'.'+c).join('')).slice(0,190);
      };
      const snapshot=why=>{
        if(state.geometry.length>=180)return;
        const grid=document.querySelector('main#main ul.products');
        const first=grid?.querySelector('li.product');
        const g=grid?.getBoundingClientRect(),f=first?.getBoundingClientRect();
        state.geometry.push({time:Math.round(performance.now()),why,
          ready:document.readyState,gridPresent:!!grid,
          grid:g?{x:Math.round(g.x),y:Math.round(g.y),w:Math.round(g.width),h:Math.round(g.height),display:getComputedStyle(grid).display}:null,
          first:f?{w:Math.round(f.width),h:Math.round(f.height),float:getComputedStyle(first).cssFloat}:null});
      };
      try {
        new PerformanceObserver(list=>{
          for(const x of list.getEntries()){
            state.shifts.push({time:Math.round(x.startTime),value:+x.value.toFixed(6),
              recentInput:x.hadRecentInput,
              sources:(x.sources||[]).slice(0,10).map(v=>({
                node:ident(v.node),
                old:v.previousRect?{x:Math.round(v.previousRect.x),y:Math.round(v.previousRect.y),
                  w:Math.round(v.previousRect.width),h:Math.round(v.previousRect.height)}:null,
                current:v.currentRect?{x:Math.round(v.currentRect.x),y:Math.round(v.currentRect.y),
                  w:Math.round(v.currentRect.width),h:Math.round(v.currentRect.height)}:null
              }))
            });snapshot('layout-shift');
          }
        }).observe({type:'layout-shift',buffered:true});
      }catch(e){state.errors.push('LayoutObserver '+e.message);}
      try{
        const observer=new MutationObserver(records=>{
          for(const r of records){
            if(state.mutations.length>=75)break;
            const el=r.target;
            const relevant=el instanceof Element &&
              (el.closest('main#main')||el.matches('main#main'));
            const found=Array.from(r.addedNodes).some(n=>n instanceof Element &&
              (n.matches('ul.products')||n.querySelector('ul.products')));
            const removed=Array.from(r.removedNodes).some(n=>n instanceof Element &&
              (n.matches('ul.products')||n.querySelector('ul.products')));
            const styled=relevant&&r.type==='attributes' &&
              (el.matches('ul.products,li.product,main#main')||el.matches('[data-choiceguide-filter-instance]'));
            if(found||removed||styled){
              state.mutations.push({time:Math.round(performance.now()),type:r.type,
                element:ident(el),attribute:r.attributeName,addedGrid:found,removedGrid:removed});
              snapshot('mutation');
            }
          }
        });
        observer.observe(document,{attributes:true,childList:true,subtree:true,
          attributeFilter:['class','style','hidden']});
      }catch(e){state.errors.push('MutationObserver '+e.message);}
      const start=()=>{
        snapshot('DOMContentLoaded');
        const started=performance.now();
        const id=setInterval(()=>{
          snapshot('interval');
          if(performance.now()-started>=10000)clearInterval(id);
        },150);
      };
      if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});
      else start();
      window.addEventListener('load',()=>snapshot('load'),{once:true});
    });
    let report={mode,error:null};
    try{
      const response=await page.goto('https://remquocanh.vn/rem-cuon/',{waitUntil:'domcontentloaded',timeout:70000});
      await page.waitForLoadState('load',{timeout:90000}).catch(()=>{});
      await page.waitForTimeout(6500);
      report=await page.evaluate(()=>{
        const state=window.__rqaClsTrace||{};
        const nav=performance.getEntriesByType('navigation')[0];
        const resources=performance.getEntriesByType('resource')
          .filter(r=>/\.(css|js)(\?|$)/.test(r.name))
          .map(r=>({file:r.name.split('/').at(-1)?.split('?')[0],
            start:Math.round(r.startTime),end:Math.round(r.responseEnd),bytes:r.transferSize||0}));
        const grid=document.querySelector('main#main ul.products');
        const first=grid?.querySelector('li.product');
        const gr=grid?.getBoundingClientRect(),fr=first?.getBoundingClientRect();
        return {...state,title:document.title,nav:{
          domContentLoaded:Math.round(nav?.domContentLoadedEventStart||0),
          loaded:Math.round(nav?.loadEventEnd||0)},
          assets:resources,gridWidth:gr?.width||0,
          columnWidths:grid?getComputedStyle(grid).gridTemplateColumns:null,
          firstWidth:fr?.width||0,firstFloat:first?getComputedStyle(first).cssFloat:null};
      });
      report.status=response?.status()||0;
      report.mode=mode;
      report.injected=injected;
      report.ratio=report.columnWidths?+(report.firstWidth/parseFloat(report.columnWidths)).toFixed(4):null;
      report.cls=+(report.shifts||[]).filter(x=>!x.recentInput)
        .reduce((sum,x)=>sum+x.value,0).toFixed(5);
      report.errors=[...(report.errors||[]),...runtimeErrors];
      await page.screenshot({path:path.join(out,mode+'.png'),fullPage:false}).catch(()=>{});
    }catch(e){report={mode,error:String(e.stack||e).slice(0,1500)};}
    fs.writeFileSync(path.join(out,mode+'.json'),JSON.stringify(report,null,2)+'\n');
    const brief={mode,status:report.status||0,injected:!!report.injected,ratio:report.ratio??null,
      cls:report.cls??null,shiftTimes:report.shifts?.map(x=>({time:x.time,value:x.value})),
      mutations:report.mutations?.length||0,mutatedGrid:(report.mutations||[]).filter(x=>x.addedGrid||x.removedGrid),
      firstSamples:report.geometry?.slice(0,8),errors:report.errors||[],error:report.error||null};
    results.push(brief);
    console.log('RQA_CLS_OWNER '+JSON.stringify(brief));
    await context.close();
  }
}finally{await browser.close();}
fs.writeFileSync(path.join(out,'summary.json'),JSON.stringify(results,null,2)+'\n');
const valid=results.length===2 && results.every(x=>x.status===200 && x.shiftTimes && x.firstSamples?.length) && results[1].injected;
console.log('RQA_CLS_CAPTURE '+(valid?'PASS':'BLOCKED'));
if(!valid)process.exitCode=1;
