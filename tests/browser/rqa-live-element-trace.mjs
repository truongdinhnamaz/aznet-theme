/**
 * RQA production read-only L4 diagnostic.
 * Exact public URLs only. No login, clicks, mutations or private API access.
 * Collects layout-shift source elements and axe accessibility offending nodes.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const out = process.env.RQA_TRACE_DIR || '/tmp/rqa-live-l4';
const pages = [
  {name:'archive-rem-cuon',url:'https://remquocanh.vn/rem-cuon/',heading:'Rèm Cuốn'},
  {name:'product-rem-vai-hoa-van',url:'https://remquocanh.vn/rem-vai-hoa-van/',heading:'Rèm vải hoa văn'},
];
fs.mkdirSync(out,{recursive:true});
function sessionScore(items) {
  let best=0,sum=0,first=-1,previous=-1;
  for (const item of items.slice().sort((a,b)=>a.time-b.time)) {
    if (first<0 || item.time-previous>1000 || item.time-first>5000) {first=item.time;sum=0;}
    sum+=item.value;best=Math.max(best,sum);previous=item.time;
  }
  return Number(best.toFixed(5));
}
const browser=await chromium.launch({headless:true});
const summary=[];
try {
  for(const target of pages) {
    const context=await browser.newContext({
      viewport:{width:390,height:844},deviceScaleFactor:2,
      isMobile:true,hasTouch:true,locale:'vi-VN',reducedMotion:'no-preference'
    });
    const page=await context.newPage();
    const errors=[];
    page.on('pageerror',e=>{if(errors.length<8)errors.push(String(e.message).slice(0,260));});
    let result={name:target.name,url:target.url,valid:false,error:null};
    try {
      const cdp=await context.newCDPSession(page);
      await cdp.send('Network.enable');
      await cdp.send('Network.emulateNetworkConditions',{
        offline:false,latency:150,downloadThroughput:200000,uploadThroughput:93750
      });
      await cdp.send('Emulation.setCPUThrottlingRate',{rate:4});
      await page.addInitScript(()=>{
        const state={entries:[],geometry:[],observer:false,error:null};
        window.__rqaReadOnlyTrace=state;
        function selector(node) {
          if(!(node instanceof Element))return null;
          const parts=[];
          for(let el=node;el && parts.length<5;el=el.parentElement) {
            let piece=el.tagName.toLowerCase();
            if(el.id){piece+='#'+CSS.escape(el.id);parts.unshift(piece);break;}
            const names=[...el.classList].filter(x=>x.length<60).slice(0,2);
            if(names.length)piece+=names.map(x=>'.'+CSS.escape(x)).join('');
            parts.unshift(piece);
          }
          return parts.join(' > ').slice(0,300);
        }
        try {
          new PerformanceObserver(list=>{
            for(const entry of list.getEntries()){
              if(entry.hadRecentInput)continue;
              state.entries.push({
                time:Math.round(entry.startTime),value:Number(entry.value.toFixed(6)),
                sources:(entry.sources||[]).slice(0,12).map(source=>({
                  selector:selector(source.node),
                  old:source.previousRect?{x:Math.round(source.previousRect.x),
                    y:Math.round(source.previousRect.y),
                    width:Math.round(source.previousRect.width),
                    height:Math.round(source.previousRect.height)}:null,
                  next:source.currentRect?{x:Math.round(source.currentRect.x),
                    y:Math.round(source.currentRect.y),
                    width:Math.round(source.currentRect.width),
                    height:Math.round(source.currentRect.height)}:null
                }))
              });
            }
          }).observe({type:'layout-shift',buffered:true});
          state.observer=true;
        }catch(e){state.error=String(e);}
        const tracked=['.aznet-theme-site-header','.woocommerce-products-header',
          '.choiceguide-filter','.woocommerce ul.products','.woocommerce-product-gallery',
          '.summary','.site-main','.aznet-theme-site-footer'];
        const prev=new Map();
        let last=-100;
        function tick(t){
          if(t-last>=100){
            last=t;
            for(const name of tracked){
              const node=document.querySelector(name);if(!node)continue;
              const r=node.getBoundingClientRect();
              const val={top:Math.round(r.top),height:Math.round(r.height),width:Math.round(r.width)};
              const old=prev.get(name);
              if(!old||Math.abs(old.top-val.top)>=3||Math.abs(old.height-val.height)>=3)
                state.geometry.push({time:Math.round(t),selector:name,...val});
              prev.set(name,val);
            }
          }
          if(t<10000)requestAnimationFrame(tick);
        }
        if(document.readyState==='loading')
          document.addEventListener('DOMContentLoaded',()=>requestAnimationFrame(tick),{once:true});
        else requestAnimationFrame(tick);
      });
      const response=await page.goto(target.url,{waitUntil:'domcontentloaded',timeout:60000});
      await page.waitForLoadState('load',{timeout:100000}).catch(()=>{});
      await page.waitForTimeout(4500);
      const runtime=await page.evaluate(()=>{
        const state=window.__rqaReadOnlyTrace||{};
        const resources=performance.getEntriesByType('resource').map(r=>({
          url:r.name.replace(/\?.*$/,''),decodedBytes:r.decodedBodySize||0,
          transferredBytes:r.transferSize||0,durationMs:Math.round(r.duration)
        })).sort((a,b)=>b.decodedBytes-a.decodedBytes);
        return {
          title:document.title,
          heading:document.querySelector('main h1')?.textContent?.trim()||null,
          bodyClass:document.body?.className?.slice(0,400),
          observer:Boolean(state.observer),observerError:state.error||null,
          shifts:state.entries||[],geometry:(state.geometry||[]).slice(0,200),
          largestResources:resources.slice(0,15),resourceCount:resources.length
        };
      });
      let axe={violations:[],incomplete:[],error:null};
      try {
        const audit=await new AxeBuilder({page})
          .withRules(['color-contrast','label-content-name-mismatch']).analyze();
        const compact=v=>({
          id:v.id,impact:v.impact,help:v.help,
          nodes:v.nodes.slice(0,15).map(node=>({
            target:node.target,html:node.html?.slice(0,700),
            summary:node.failureSummary?.slice(0,450),
            checks:node.any?.slice(0,4).map(c=>({id:c.id,data:c.data}))
          }))
        });
        axe={violations:audit.violations.map(compact),
          incomplete:audit.incomplete.map(compact),error:null};
      }catch(e){axe.error=String(e.message||e).slice(0,450);}
      result={
        ...result,status:response?.status()||0,...runtime,
        cls:sessionScore(runtime.shifts),axe,pageErrors:errors,
        observedAt:new Date().toISOString(),
      };
      result.valid=result.status===200 && result.observer &&
        Boolean(result.heading?.toLowerCase().includes(target.heading.toLowerCase()));
      await page.screenshot({path:path.join(out,target.name+'.png'),
        fullPage:false,timeout:20000}).catch(()=>{});
    }catch(e){result.error=String(e.stack||e).slice(0,1400);}
    finally{
      fs.writeFileSync(path.join(out,target.name+'.json'),JSON.stringify(result,null,2)+'\n');
      summary.push({
        name:result.name,valid:result.valid,http:result.status||null,
        cls:result.cls??null,shiftCount:result.shifts?.length??0,
        sources:result.shifts?.flatMap(s=>s.sources.map(v=>v.selector))
          .filter(Boolean).slice(0,16)||[],
        axeViolations:result.axe?.violations.map(v=>({
          id:v.id,targets:v.nodes.map(n=>n.target).slice(0,12)
        }))||[],axeError:result.axe?.error||null,
        error:result.error,
      });
      await context.close();
    }
  }
}finally{await browser.close();}
const invalid=summary.filter(v=>!v.valid).length;
fs.writeFileSync(path.join(out,'summary.json'),
  JSON.stringify({tests:summary,invalid},null,2)+'\n');
for(const row of summary)console.log('RQA_L4_TRACE '+JSON.stringify(row));
console.log('RQA_L4_STATUS valid='+String(summary.length-invalid)+'/'+String(summary.length));
if(invalid)process.exitCode=1;
