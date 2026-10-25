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
    const consoleIssues=[];
    page.on('pageerror',e=>{if(errors.length<8)errors.push(String(e.message).slice(0,260));});
    page.on('console',message=>{
      if(!['error','warning'].includes(message.type())||consoleIssues.length>=12)return;
      const location=message.location();
      consoleIssues.push({
        type:message.type(),
        text:message.text().slice(0,350),
        source:location.url?location.url.replace(/\\?.*$/,'').slice(0,230):null,
        line:location.lineNumber??null
      });
    });
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
        const grid=document.querySelector('.woocommerce ul.products');
        const card=grid?.querySelector('li.product');
        const gs=grid?getComputedStyle(grid):null;
        const cs=card?getComputedStyle(card):null;
        const matched=[];
        if(card)for(const sheet of document.styleSheets){
          let rules;
          try{rules=sheet.cssRules;}catch{continue;}
          const walk=list=>{
            for(const rule of list){
              if(rule.cssRules){
                if(rule.conditionText && rule.media &&
                  !matchMedia(rule.conditionText).matches)continue;
                walk(rule.cssRules);
              }else if(rule.selectorText && rule.style &&
                (rule.style.width||rule.style.float||rule.style.clear)){
                let applies=false;
                try{applies=card.matches(rule.selectorText);}catch{}
                if(applies && matched.length<70)
                  matched.push({
                    source:sheet.href?.replace(/\?.*$/,'')||'inline',
                    selector:rule.selectorText.slice(0,220),
                    width:rule.style.width||null,
                    float:rule.style.float||null,
                    clear:rule.style.clear||null
                  });
              }
            }
          };
          walk(rules);
        }
        const catalogGeometry={
          windowWidth:window.innerWidth,
          devicePixelRatio:window.devicePixelRatio,
          viewportWidth:window.visualViewport?.width||null,
          columnsToken:getComputedStyle(document.body).getPropertyValue('--aznet-theme-woo-catalog-columns').trim(),
          grid:grid?{
            width:Math.round(grid.getBoundingClientRect().width),
            display:gs.display,templateColumns:gs.gridTemplateColumns
          }:null,
          card:card?{
            width:Math.round(card.getBoundingClientRect().width),
            cssWidth:cs.width,float:cs.cssFloat,clear:cs.clear,
            marginRight:cs.marginRight
          }:null,
          rules:matched,
          domContentLoadedMs:Math.round(performance.getEntriesByType('navigation')[0]?.domContentLoadedEventStart||0),
          firstPaintMs:Math.round(performance.getEntriesByType('paint')[0]?.startTime||0)
        };
        // Attribution only: a square intentionally cropped with object-fit
        // cover is not automatically a stretched/distorted source image.
        // Capture both declared metadata and actual loaded intrinsic ratio.
        const imageAspects=[...document.querySelectorAll('main img')].map(img=>{
          const rect=img.getBoundingClientRect();
          const style=getComputedStyle(img);
          if(!img.complete||!img.naturalWidth||!img.naturalHeight||
            rect.width<12||rect.height<12||style.display==='none'||
            style.visibility==='hidden')return null;
          const sourceRatio=img.naturalWidth/img.naturalHeight;
          const displayRatio=rect.width/rect.height;
          const difference=Math.abs(displayRatio/sourceRatio-1);
          const cropMode=['cover','contain','scale-down','none'].includes(style.objectFit);
          const channel=img.closest('[class*="choiceguide-"]')?'provider-choiceguide':
            img.closest('.woocommerce-product-gallery')?'woocommerce-gallery':
            img.closest('.woocommerce ul.products')?'woocommerce-catalog':
            'wordpress-theme-other';
          const publicPath=(()=>{
            try{return new URL(img.currentSrc||img.src,location.href).pathname.slice(0,250);}
            catch{return '(unresolved)';}
          })();
          return {
            path:publicPath,alt:img.alt.slice(0,110),
            widthAttr:img.getAttribute('width'),heightAttr:img.getAttribute('height'),
            naturalWidth:img.naturalWidth,naturalHeight:img.naturalHeight,
            displayWidth:Math.round(rect.width*10)/10,
            displayHeight:Math.round(rect.height*10)/10,
            cssWidth:style.width,cssHeight:style.height,
            objectFit:style.objectFit,objectPosition:style.objectPosition,
            ratioDifferencePercent:Math.round(difference*1000)/10,
            intentionalCropOrContain:cropMode,
            distortionSuspect:difference>0.08&&!cropMode,
            channel
          };
        }).filter(Boolean).sort((a,b)=>b.ratioDifferencePercent-a.ratioDifferencePercent);
        return {
          imageAspects:imageAspects.slice(0,60),
          title:document.title,
          heading:document.querySelector('main h1')?.textContent?.trim()||null,
          bodyClass:document.body?.className?.slice(0,400),
          catalogGeometry,
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
        consoleIssues,
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
        consoleIssues:result.consoleIssues||[],
        images:{
          analyzed:result.imageAspects?.length??0,
          suspects:result.imageAspects?.filter(i=>i.distortionSuspect).slice(0,16)||[],
          cropCandidates:result.imageAspects?.filter(i=>i.intentionalCropOrContain&&i.ratioDifferencePercent>8).slice(0,12)||[],
          bySurface:(result.imageAspects||[]).reduce((acc,i)=>{acc[i.channel]=(acc[i.channel]||0)+1;return acc;},{})
        },
        catalog:result.catalogGeometry?{token:result.catalogGeometry.columnsToken,grid:result.catalogGeometry.grid,card:result.catalogGeometry.card,rules:result.catalogGeometry.rules.slice(-10)}:null,
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
