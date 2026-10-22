/**
 * D-060 real WordPress public Page geometry with exact candidate CSS overlaid.
 * No draft auth token, DOM mutation, WordPress write, or provider data access.
 * The overlay proves candidate L4; it is not production Theme release proof.
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const out='/tmp/d060-about-v2-browser';
mkdirSync(out,{recursive:true});
const results=[],issues=[];
const widths=[1920,1600,1440,1280,1024,951,950,768,640,390,360];
const url='https://aznet.vn/gioi-thieu';
const browser=await chromium.launch({headless:true});
try {
  for (const width of widths) {
    const context=await browser.newContext({viewport:{width,height:900},reducedMotion:'reduce',deviceScaleFactor:1});
    const page=await context.newPage();
    try{
      const response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});
      if (response?.status()!==200) throw Error('Public Page URL HTTP '+response?.status());
      const publicAssetCount=await page.locator('link[rel="stylesheet"][href*="about-showcase-v2.css"]').count();
      if(publicAssetCount!==0) issues.push({width,issues:['V2 CSS unexpectedly loaded on released production Theme before overlay']});
      await page.addStyleTag({path:'assets/css/presets/digital-01.css'});
      await page.addStyleTag({path:'assets/css/components/page.css'});
      await page.addStyleTag({path:'assets/css/components/about-showcase-v2.css'});
      await page.evaluate(()=>document.fonts.ready);
      const r=await page.evaluate(()=>{
        const select=s=>document.querySelector(s);
        const main=select('.aznet-theme-page__content-inner');
        const sections=main?[...main.children].filter(e=>e.classList?.contains('wp-block-group')):[];
        const hero=select('.aznet-theme-about-showcase');
        const figure=hero?.querySelector('figure');
        const h1=hero?.querySelector('h1');
        const nav=sections[1]?.querySelector('p');
        const story=select('#az-story');
        const storyArt=story?.querySelector('figure');
        const storyTitle=story?.querySelector('h2');
        const values=select('#az-values');
        const valueH2=values?.querySelector('h2');
        const team=select('#az-team');
        const teamH2=team?.querySelector('h2');
        const cards=select('#az-mission')?.querySelectorAll('.wp-block-columns > .wp-block-column');
        const rect=e=>{if(!e)return null;const x=e.getBoundingClientRect();return {x:+x.x.toFixed(2),y:+x.y.toFixed(2),width:+x.width.toFixed(2),height:+x.height.toFixed(2),right:+x.right.toFixed(2),bottom:+x.bottom.toFixed(2)}};
        const color=e=>e?getComputedStyle(e).color:null;
        const sectionRects=sections.map(rect);
        const ids=sections.map(s=>s.id).filter(Boolean);
        const anchorHrefs=nav?[...nav.querySelectorAll('a[href^="#"]')].map(a=>a.getAttribute('href')):[];
        const textLinks=[...main.querySelectorAll('a[href]')].map(a=>a.getAttribute('href'));
        return {status:'rendered',pageId:select('article#post-18897')?.id||null,bodyClass:document.body.className.slice(0,250),
          viewport:innerWidth,scrollWidth:document.documentElement.scrollWidth,
          rootGroups:sections.length,sections:sectionRects,ids,
          h1Count:main.querySelectorAll('h1').length,hero:rect(hero),heroMode:hero?getComputedStyle(hero).display:null,
          heroTitle:rect(h1),heroTitleColor:color(h1),heroFigure:rect(figure),
          heroVisual:figure?.querySelector('svg[role="img"]')?.getAttribute('aria-label')||null,
          nav:rect(nav),navScrollWidth:nav?.scrollWidth,navClientWidth:nav?.clientWidth,navAnchors:anchorHrefs,
          storyTitle:rect(storyTitle),storyArt:rect(storyArt),
          missionCards:cards?.length||0,processCards:select('#az-process')?.querySelectorAll('.wp-block-column').length||0,
          valuesH2:color(valueH2),teamH2:color(teamH2),
          svgCount:main.querySelectorAll('svg[role="img"]').length,
          relevantLinks:textLinks.filter(x=>x.startsWith('#az-')||x.startsWith('tel:')||x.startsWith('https://zalo.me/')),
          hasShortcodeText:/\[section(?:\s|\])/i.test(main.innerText),
          headerOverflow:select('.aznet-theme-site-header')?.scrollWidth > select('.aznet-theme-site-header')?.clientWidth+3,
          menuTriggerVisible:(()=>{const t=select('[data-aznet-theme-nav-trigger]');return !!t&&t.getBoundingClientRect().width>0})()
        };
      });
      const errs=[];
      const almost=(a,b,tol=3)=>typeof a==='number'&&Math.abs(a-b)<=tol;
      if(r.pageId!=='post-18897')errs.push('Wrong WordPress Page identity');
      if(r.rootGroups!==9)errs.push('Not nine native Page sections');
      if(r.h1Count!==1)errs.push('H1 duplicated or missing');
      if(!r.hero||!almost(r.hero.x,0)||!almost(r.hero.width,width,4))errs.push('Hero off-canvas / constrained');
      if(!r.heroVisual||r.svgCount!==2)errs.push('Source-owned SVG illustration missing');
      if(r.heroTitleColor!=='rgb(255, 255, 255)')errs.push('Hero heading illegible on inverse');
      if(!['rgb(255, 255, 255)','rgb(247, 248, 250)'].includes(r.valuesH2))errs.push('Values heading illegible: '+r.valuesH2);
      if(!['rgb(255, 255, 255)','rgb(247, 248, 250)'].includes(r.teamH2))errs.push('Team heading illegible: '+r.teamH2);
      if(r.scrollWidth>width+3)errs.push('Page horizontal overflow '+r.scrollWidth+'>'+width);
      if(r.headerOverflow)errs.push('Header horizontal overflow');
      if(r.sections.some(s=>!almost(s.x,0)||!almost(s.width,width,4)))errs.push('Section is not full width');
      if(r.navAnchors.length!==7||r.navAnchors.some(x=>!r.ids.includes(x.slice(1))))errs.push('Native anchor navigation broken');
      if(r.missionCards!==2||r.processCards!==5)errs.push('Native WordPress cards missing');
      if(r.hasShortcodeText)errs.push('Unexpanded legacy shortcode shown');
      if(width>950&&r.heroFigure&&r.heroTitle&&r.heroFigure.x < r.heroTitle.right - 10)errs.push('Desktop Hero illustration is not to right of text');
      if(width<=950&&r.heroFigure&&r.heroTitle&&r.heroFigure.y<r.heroTitle.bottom)errs.push('Mobile Hero illustration is not below copy');
      if(width<=390&&!r.menuTriggerVisible)errs.push('Native mobile Header menu is missing');
      if(width>=360&&width<=390&&r.navScrollWidth<=r.navClientWidth)errs.push('Mobile section navigation should be horizontally scrollable');
      if(errs.length)issues.push({width,issues:errs});
      results.push({width,...r,issues:errs});
      console.log('ABOUT_V2_MEASURE '+JSON.stringify({width,err:errs,hero:r.hero,title:r.heroTitle,figure:r.heroFigure,scrollWidth:r.scrollWidth,navWidth:r.navClientWidth,navScroll:r.navScrollWidth,valuesH2:r.valuesH2,teamH2:r.teamH2}));
      if([1600,1024,768,390,360].includes(width))
        await page.screenshot({path:join(out,'aznet-about-v2-'+width+'.png'),fullPage:true});
      if(width===390) {
        const trigger=page.locator('[data-aznet-theme-nav-trigger]').first();
        const panel=page.locator('[data-aznet-theme-nav-panel]').first();
        await trigger.click();
        if(await trigger.getAttribute('aria-expanded')!=='true'||!(await panel.isVisible()))issues.push({width,issues:['Native mobile Header menu does not open']});
        await page.keyboard.press('Escape');
        if(await trigger.getAttribute('aria-expanded')!=='false')issues.push({width,issues:['Escape does not dismiss mobile Header menu']});
      }
    }catch(e){issues.push({width,issues:[String(e)]});}
    finally{await context.close();}
  }
  // Explicit other-surface isolation: Homepage must not adopt V2 layout.
  const context=await browser.newContext({viewport:{width:1440,height:900}});
  const page=await context.newPage();
  try{
    const res=await page.goto('https://aznet.vn/',{waitUntil:'domcontentloaded',timeout:45000});
    await page.addStyleTag({path:'assets/css/components/about-showcase-v2.css'});
    const isolation=await page.evaluate(()=>({status:document.readyState,showcase:!!document.querySelector('.aznet-theme-about-showcase'),hero:!!document.querySelector('.aznet-digital01__hero'),h1:document.querySelectorAll('h1').length,overflow:document.documentElement.scrollWidth>innerWidth+3}));
    console.log('ISOLATION '+JSON.stringify(isolation));
    if(res?.status()!==200||isolation.showcase||!isolation.hero||isolation.h1!==1)issues.push({surface:'homepage',issues:['CSS not isolated to explicitly opted-in Page']});
  }catch(e){issues.push({surface:'homepage',issues:[String(e)]})}
  finally{await context.close()}
}finally{await browser.close()}
writeFileSync(join(out,'evidence.json'),JSON.stringify({url,commit:process.env.GITHUB_SHA,results,issues},null,2));
console.log('ABOUT_V2_SUMMARY '+JSON.stringify({widths:results.length,issues},null,2));
if(issues.length)process.exit(1);
console.log('PASS: D-060 public WordPress Page 18897 V2 candidate layout across 11 widths and Homepage isolation');
