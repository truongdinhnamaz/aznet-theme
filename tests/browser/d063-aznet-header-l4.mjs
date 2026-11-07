/**
 * D-063: read-only public AZnet Header geometry and stylesheet provenance.
 * Validates served WP Rocket CSS against current Theme fix; never mutates WordPress.
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const output='/tmp/d063-aznet-header';
mkdirSync(output,{recursive:true});
const failures=[], rows=[];
const browser=await chromium.launch({headless:true});
const widths=[1705,1600,1501,1500,1440,1366,1321,1320,1280,1024,980,390];
function visible(r){return !!r&&r.w>0&&r.h>0&&r.display!=='none'&&r.visibility!=='hidden';}
function intersects(a,b){return visible(a)&&visible(b)&&Math.min(a.x+a.w,b.x+b.w)-Math.max(a.x,b.x)>2&&Math.min(a.y+a.h,b.y+b.h)-Math.max(a.y,b.y)>2;}
try{
  for (const surface of ['about','home']){
    for(const width of surface==='home'?[1705,1440,390]:widths){
      const context=await browser.newContext({viewport:{width,height:930},reducedMotion:'reduce',deviceScaleFactor:1});
      const page=await context.newPage();
      try{
        const response=await page.goto(surface==='about'?'https://aznet.vn/gioi-thieu':'https://aznet.vn/',{waitUntil:'domcontentloaded',timeout:45000});
        if(response?.status()!==200)throw Error('Public HTTP '+response?.status());
        await page.locator('.aznet-theme-site-header').waitFor({state:'visible'});
        const diagnostic=await page.evaluate(async()=>{
          const link=document.querySelector('#aznet-theme-preset-digital-01-css');
          const url=link?.href||null;let hasRule=false,codeStatus=null,codeLength=0,checkError=null;
          if(url){try{const res=await fetch(url,{cache:'no-store'});codeStatus=res.status;const css=await res.text();codeLength=css.length;hasRule=css.includes('min-width:1501px')&&css.includes('max-width:1500px');}catch(e){checkError=String(e);}}
          return {presetCSS:url,codeStatus,codeLength,hasRule,checkError};
        });
        if(surface==='about'&&[1705,1366,390].includes(width)){
          const baseline=await page.evaluate(()=>{
            const h=document.querySelector('.aznet-theme-site-header');
            const nav=h.querySelector('.aznet-theme-site-header__inner > .aznet-theme-site-header__nav');
            const util=h.querySelector('.aznet-theme-site-header__utility-nav');
            const mob=h.querySelector('[data-aznet-theme-nav-trigger]');
            const v=e=>!!e&&e.getBoundingClientRect().width>0&&getComputedStyle(e).display!=='none';
            return {nav:v(nav),utility:v(util),mobile:v(mob),headerOverflow:h.scrollWidth>h.clientWidth+3};
          });
          console.log('BASELINE '+JSON.stringify({surface,width,...baseline,diagnostic}));
          await page.screenshot({path:join(output,surface+'-'+width+'-before.png')});
        }
        await page.evaluate(()=>document.fonts.ready);
        const m=await page.evaluate(()=>{
          const h=document.querySelector('.aznet-theme-site-header'), inner=h?.querySelector('.aznet-theme-site-header__inner');
          const by=s=>h?.querySelector(s);
          const rect=e=>{if(!e)return null;const r=e.getBoundingClientRect(),st=getComputedStyle(e);
            return {x:r.x,y:r.y,w:r.width,h:r.height,display:st.display,visibility:st.visibility}};
          const nav=by('.aznet-theme-site-header__inner > .aznet-theme-site-header__nav');
          const act=by('.aznet-theme-site-header__inner > .aznet-theme-site-header__actions');
          const util=by('.aznet-theme-site-header__utility-nav');
          const links=[...(util?.querySelectorAll('a')||[])].map(x=>({text:x.textContent.trim(),...rect(x)}));
          const e={brand:rect(by('.aznet-theme-site-header__brand')),nav:rect(nav),actions:rect(act),
            util:rect(util),mobile:rect(by('.aznet-theme-site-header__mobile')),
            trigger:rect(by('[data-aznet-theme-nav-trigger]')),
            search:rect(act?.querySelector('.aznet-theme-site-header__search')),
            account:rect(act?.querySelector('.aznet-theme-site-header__utility--account')),
            cart:rect(act?.querySelector('.aznet-theme-site-header__utility--cart')),links};
          return {regions:e,width:innerWidth,scrollWidth:document.documentElement.scrollWidth,
            headerOverflow:h.scrollWidth>h.clientWidth+3,headerHeight:h.getBoundingClientRect().height,
            logo:!!by('.aznet-theme-site-header__logo')&&by('.aznet-theme-site-header__logo').complete,
            pageClass:document.body.className};
        });
        const e=[], R=m.regions;
        if(!m.pageClass.includes('aznet-theme-preset--digital-01'))e.push('Incorrect visual preset');
        if(!diagnostic.hasRule||diagnostic.codeStatus!==200)e.push('Served preset stylesheet is stale or inaccessible: '+JSON.stringify(diagnostic));
        if(!m.logo)e.push('Logo missing or not loaded');
        if(m.headerOverflow)e.push('Header horizontal overflow');
        if(m.scrollWidth>width+3)e.push('Page horizontal overflow');
        if(intersects(R.brand,R.actions)||intersects(R.brand,R.mobile))e.push('Logo/navigation collision');
        for(let i=0;i<R.links.length;i++)for(let j=i+1;j<R.links.length;j++)if(intersects(R.links[i],R.links[j]))e.push('Utility links overlap '+R.links[i].text+' / '+R.links[j].text);
        if(width>=1501){
          if(visible(R.nav))e.push('Duplicate primary menu visible at desktop width');
          if(!visible(R.util))e.push('Site utility navigation missing');
          // Retain existing search and account features unless a separately approved decision removes them.
          if(surface==='about'&&!visible(R.search))e.push('Inner-page desktop search removed by candidate');
          if(surface==='about'&&!visible(R.account))e.push('Inner-page desktop account link removed by candidate');
        }else{
          if(visible(R.nav)||visible(R.actions))e.push('Full desktop navigation rendered in compact viewport');
          if(!visible(R.trigger))e.push('Accessible mobile menu trigger unavailable');
        }
        if(e.length)failures.push({surface,width,errors:e});
        rows.push({surface,width,diagnostic,measurements:m,errors:e});
        console.log('D063_MEASURE '+JSON.stringify({surface,width,cssFresh:diagnostic.hasRule,presetCss:diagnostic.presetCSS,headerOverflow:m.headerOverflow,scrollWidth:m.scrollWidth,nav:visible(R.nav),utility:visible(R.util),mobile:visible(R.trigger),search:visible(R.search),account:visible(R.account),errors:e}));
        if([1705,1440,1366,1280,390].includes(width))await page.screenshot({path:join(output,surface+'-'+width+'-after-cache-purge.png')});
        if(visible(R.trigger)&&[1500,1280,390].includes(width)){
          const trigger=page.locator('[data-aznet-theme-nav-trigger]');
          const panel=page.locator('[data-aznet-theme-nav-panel]');
          await trigger.click();
          if(await trigger.getAttribute('aria-expanded')!=='true'||!await panel.isVisible())failures.push({surface,width,errors:['Disclosure did not open']});
          await page.keyboard.press('Escape');
          if(await trigger.getAttribute('aria-expanded')!=='false'||await panel.isVisible())failures.push({surface,width,errors:['Escape did not close disclosure']});
        }
      }catch(err){failures.push({surface,width,errors:[String(err)]});}
      finally{await context.close();}
    }
  }
}finally{await browser.close();}
writeFileSync(join(output,'measurements.json'),JSON.stringify({commit:process.env.GITHUB_SHA,rows,failures},null,2));
console.log('D063_SUMMARY '+JSON.stringify({tested:rows.length,failures},null,2));
if(failures.length)process.exit(1);
console.log('PASS: AZnet live preset CSS freshness, Header no overflow, retained utilities, mobile keyboard interaction');
