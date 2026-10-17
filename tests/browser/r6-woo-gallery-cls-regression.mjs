/**
 * R6 regression of Theme-owned native Woo single-product presentation.
 * Browser-only and read-only. No WooCommerce gallery engine override.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const base=(process.env.R6_BASE_URL||'http://127.0.0.1:8080').replace(/\/$/,'');
if(base!=='http://127.0.0.1:8080')throw new Error('Only the local R6 WordPress fixture is allowed');
const stateDir=process.env.R6_STATE_DIR||'/tmp/r6-performance';
const outDir=path.join(stateDir,'woo-gallery-cls');
fs.mkdirSync(outDir,{recursive:true});
const targets=[{name:'390x844',width:390,height:844},{name:'1440x900',width:1440,height:900}];
const browser=await chromium.launch({headless:true});
const cases=[],failures=[];
try{
  for(const vp of targets){
    const context=await browser.newContext({viewport:{width:vp.width,height:vp.height}});
    const page=await context.newPage();
    const errors=[];
    const result={viewport:vp.name,status:0,cls:null,thumbChanged:null,error:null};
    page.on('pageerror',e=>{if(errors.length<8)errors.push(String(e.message).slice(0,180));});
    try{
      await page.addInitScript(()=>{
        const state={cls:0,layoutShiftAvailable:false,maxSlideWidth:0,maxGalleryHeight:0,samples:0};
        globalThis.__r6GalleryClsRegression=state;
        try{
          new PerformanceObserver(list=>{
            for(const entry of list.getEntries())if(!entry.hadRecentInput)state.cls+=entry.value;
          }).observe({type:'layout-shift',buffered:true});
          state.layoutShiftAvailable=true;
        }catch{}
        const tick=()=>{
          const gallery=document.querySelector('.single-product #main .woocommerce-product-gallery');
          const slide=gallery?.querySelector('.woocommerce-product-gallery__image');
          if(gallery){
            state.maxGalleryHeight=Math.max(state.maxGalleryHeight,gallery.getBoundingClientRect().height);
            if(slide)state.maxSlideWidth=Math.max(state.maxSlideWidth,slide.getBoundingClientRect().width);
            state.samples++;
          }
          if(performance.now()<4500)requestAnimationFrame(tick);
        };
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',tick,{once:true});
        else tick();
      });
      const response=await page.goto(base+'/product/r4-simple-product/',{waitUntil:'networkidle',timeout:35000});
      result.status=response?.status()||0;
      await page.waitForTimeout(4400);
      const data=await page.evaluate(()=>{
        const gallery=document.querySelector('.single-product #main .woocommerce-product-gallery');
        const viewport=gallery?.querySelector('.flex-viewport');
        const active=gallery?.querySelector('.woocommerce-product-gallery__image.flex-active-slide img')||
                     gallery?.querySelector('.woocommerce-product-gallery__image img');
        const state=globalThis.__r6GalleryClsRegression||{};
        return {
          cls:Number((state.cls||0).toFixed(5)),
          maxSlideWidth:Number((state.maxSlideWidth||0).toFixed(2)),
          maxGalleryHeight:Number((state.maxGalleryHeight||0).toFixed(2)),
          samples:state.samples||0,
          observerAvailable:Boolean(state.layoutShiftAvailable),
          galleryWidth:gallery?.getBoundingClientRect().width||0,
          viewportWidth:viewport?.getBoundingClientRect().width||0,
          viewportHeight:viewport?.getBoundingClientRect().height||0,
          activeDecoded:Boolean(active?.complete&&active?.naturalWidth>0),
          thumbnailCount:gallery?.querySelectorAll('.flex-control-thumbs img').length||0,
          overflowPx:document.documentElement.scrollWidth-document.documentElement.clientWidth,
        };
      });
      Object.assign(result,data);
      if(result.thumbnailCount>=2){
        const thumbs=page.locator('.woocommerce-product-gallery .flex-control-thumbs img');
        await thumbs.nth(1).click({timeout:5000});
        await page.waitForTimeout(400);
        result.thumbChanged=(await thumbs.evaluateAll(xs=>xs.findIndex(x=>x.classList.contains('flex-active'))))===1;
      }
      if(result.status!==200)throw new Error('Product returned HTTP '+result.status);
      if(!result.observerAvailable)throw new Error('LayoutShift unsupported');
      if(result.samples<3)throw new Error('Gallery geometry unavailable');
      if(result.cls>=0.1)throw new Error('Mobile/desktop gallery CLS regression '+result.cls+' >= 0.1');
      if(!result.activeDecoded||!(result.viewportWidth>0&&result.viewportHeight>0))throw new Error('Native Woo slider image/viewport missing');
      if(result.thumbChanged!==true)throw new Error('Native Woo thumbnail navigation failed');
      if(result.overflowPx>1)throw new Error('Horizontal overflow '+result.overflowPx);
      if(errors.length)throw new Error('Browser errors: '+errors.join(';'));
    }catch(e){
      result.error=String(e.message||e).slice(0,260);
      failures.push(vp.name+': '+result.error);
      await page.screenshot({path:path.join(outDir,vp.name+'-failure.png'),fullPage:false}).catch(()=>{});
    }finally{
      cases.push(result);
      console.log(JSON.stringify({viewport:result.viewport,cls:result.cls,maxSlideWidth:result.maxSlideWidth,maxGalleryHeight:result.maxGalleryHeight,thumbnail:result.thumbChanged,error:result.error}));
      await context.close();
    }
  }
}finally{await browser.close();}
fs.writeFileSync(path.join(outDir,'summary.json'),JSON.stringify({cases,failures},null,2)+'\n');
if(failures.length){console.error(failures.join('\n'));process.exit(1);}
console.log('PASS: R6 native Woo gallery CLS, image and thumbnail regression 2/2');
