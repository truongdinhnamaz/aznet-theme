/**
 * One-off, public, read-only native Woo gallery A/B.
 * Never writes to WordPress, provider data or Theme production assets.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import { parsePilotTargets } from './r6-public-pilot-vitals.mjs';
import { installR6VitalsObserver } from './r6-vitals-diagnostics.mjs';
import { installR6LayoutTimeline } from './r6-layout-timeline.mjs';
import { cssForR6Trial, parseR6TrialVariants, parseR6Viewports } from './r6-slide-sizing-css.mjs';

const urls=parsePilotTargets(process.env.R6_PILOT_URLS,process.env.R6_PILOT_ALLOWED_HOST);
const trials=parseR6TrialVariants(process.env.R6_AB_TRIALS);
const viewports=parseR6Viewports(process.env.R6_AB_VIEWPORTS);
const outDir=process.env.R6_AB_OUTPUT_DIR||'/tmp/r6-performance/slide-container-ab';
const all=[];
const browser=await chromium.launch({headless:true});
try {
  for(const target of urls) {
    for(const vp of viewports) {
      for(const trial of trials) {
        const context=await browser.newContext({viewport:{width:vp.width,height:vp.height},deviceScaleFactor:1,colorScheme:'light'});
        const page=await context.newPage();
        const result={route:new URL(target).pathname,viewport:vp.label,trial,httpStatus:null,error:null,warnings:[]};
        try {
          const css=cssForR6Trial(trial);
          if(css) {
            await page.addInitScript((rule)=>{
              const install=()=>{
                if(!document.head) return false;
                if(!document.querySelector('style[data-r6-ab-slide]')) {
                  const node=document.createElement('style');
                  node.setAttribute('data-r6-ab-slide','true');
                  node.textContent=rule;
                  document.head.appendChild(node);
                }
                return true;
              };
              if(!install()) {
                const observer=new MutationObserver(()=>{if(install())observer.disconnect();});
                observer.observe(document,{childList:true,subtree:true});
              }
            },css);
          }
          await page.addInitScript({content:'('+installR6VitalsObserver.toString()+')();'});
          await page.addInitScript({content:'('+installR6LayoutTimeline.toString()+')();'});
          const pageErrors=[];
          page.on('pageerror',e=>{if(pageErrors.length<8)pageErrors.push(String(e.message).slice(0,180));});
          const response=await page.goto(target,{waitUntil:'domcontentloaded',timeout:45000});
          result.httpStatus=response?.status()||0;
          await page.waitForLoadState('load',{timeout:30000}).catch(()=>{});
          await page.waitForTimeout(5800);
          const state=await page.evaluate(()=>{
            const timeline=globalThis.__aznetR6LayoutTimeline||{};
            const shifts=(globalThis.__aznetR6Vitals||{}).layoutShifts||[];
            const samples=timeline.samples||[];
            const peak=(name,field)=>Math.max(0,...samples.map(s=>s.nodes?.[name]?.rect?.[field]||0));
            const gallery=document.querySelector('.single-product #main .woocommerce-product-gallery');
            const slide=gallery?.querySelector('.woocommerce-product-gallery__image');
            const viewport=gallery?.querySelector('.flex-viewport');
            const img=gallery?.querySelector('.woocommerce-product-gallery__image.flex-active-slide img')||
                      gallery?.querySelector('.woocommerce-product-gallery__image img');
            const rect=el=>el?{w:Number(el.getBoundingClientRect().width.toFixed(2)),h:Number(el.getBoundingClientRect().height.toFixed(2))}:null;
            const css=el=>el?{maxWidth:getComputedStyle(el).maxWidth,maxInlineSize:getComputedStyle(el).maxInlineSize,display:getComputedStyle(el).display}:null;
            const firstShift=shifts.find(s=>!s.hadRecentInput)?.startTimeMs;
            return {
              totalCLS:Number(shifts.filter(s=>!s.hadRecentInput).reduce((v,s)=>v+s.value,0).toFixed(5)),
              shiftCount:shifts.length,
              firstLayoutShiftMs:firstShift??null,
              peakGalleryHeight:peak('gallery','height'),
              peakSlideWidth:peak('slide','width'),
              peakImageWidth:peak('mainImage','width'),
              peakSummaryY:peak('summary','y'),
              peakViewportHeight:peak('viewport','height'),
              mutationCount:timeline.mutations?.length||0,
              sampleCount:samples.length,
              timelineActive:Boolean(timeline.availability?.layoutShift&&samples.length),
              styleInjected:Boolean(document.querySelector('style[data-r6-ab-slide]')),
              slideCSS:css(slide),galleryCSS:css(gallery),
              finalGallery:rect(gallery),finalViewport:rect(viewport),
              finalSlide:rect(slide),finalImage:rect(img),
              finalImageDecoded:Boolean(img?.complete&&img?.naturalWidth>0&&img?.naturalHeight>0),
              finalOverflowPx:Math.max(0,document.documentElement.scrollWidth-document.documentElement.clientWidth),
              thumbsCount:gallery?.querySelectorAll('.flex-control-thumbs img').length||0,
              activeThumbBefore:[...(gallery?.querySelectorAll('.flex-control-thumbs img')||[])].findIndex(x=>x.classList.contains('flex-active')),
              shiftWindow:samples.filter(s=>firstShift&&Math.abs(s.tMs-firstShift)<300).slice(0,12).map(s=>({
                tMs:s.tMs,galleryH:s.nodes.gallery?.rect?.height,viewportH:s.nodes.viewport?.rect?.height,
                slideW:s.nodes.slide?.rect?.width,imageW:s.nodes.mainImage?.rect?.width,summaryY:s.nodes.summary?.rect?.y,
              })),
            };
          });
          Object.assign(result,state);
          result.pageErrors=pageErrors;
          if(result.thumbsCount>=2) {
            const thumbs=page.locator('.woocommerce-product-gallery .flex-control-thumbs img');
            try {
              await thumbs.nth(1).click({timeout:5000});
              await page.waitForTimeout(400);
              result.activeThumbAfter=await thumbs.evaluateAll(xs=>xs.findIndex(x=>x.classList.contains('flex-active')));
              result.thumbnailClickPassed=result.activeThumbAfter===1;
            } catch(e) {
              result.thumbnailClickPassed=false;
              result.warnings.push('Thumbnail unavailable: '+String(e.message).slice(0,100));
            }
          } else result.thumbnailClickPassed=null;
          fs.mkdirSync(outDir,{recursive:true});
          const safeName=result.route.replace(/[^a-z0-9]+/gi,'-').slice(0,45);
          await page.screenshot({path:path.join(outDir,safeName+'-'+vp.label+'-'+trial+'.png'),animations:'disabled'});
          if(result.httpStatus!==200||!result.timelineActive||!result.finalImageDecoded||
             !(result.finalViewport?.w>0&&result.finalViewport?.h>0)||result.finalOverflowPx>1||
             (trial!=='control'&&!result.styleInjected)||result.thumbnailClickPassed===false) {
            result.error='Public gallery read-only QA gate failed';
          }
        } catch(e) {result.error=String(e.message||e).slice(0,200);}
        finally {
          all.push(result);
          console.log(JSON.stringify({route:result.route,viewport:result.viewport,trial:result.trial,CLS:result.totalCLS,peakSlide:result.peakSlideWidth,peakGallery:result.peakGalleryHeight,thumb:result.thumbnailClickPassed,error:result.error}));
          await context.close();
        }
      }
    }
  }
} finally {await browser.close();}
fs.mkdirSync(outDir,{recursive:true});
fs.writeFileSync(path.join(outDir,'summary.json'),JSON.stringify({measuredAtUtc:new Date().toISOString(),cases:all},null,2)+'\n');
if(all.some(x=>x.error))process.exitCode=1;
