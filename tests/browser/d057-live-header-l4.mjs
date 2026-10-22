/**
 * D-057, read-only public-browser acceptance probe of the existing WordPress
 * About page. Loads the PR's exact CSS after the public production baseline.
 * Never writes to WordPress or depends on a private preview token.
 */
import { chromium } from 'playwright';
import { readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const url = 'https://aznet.vn/gioi-thieu';
const candidateCSS = readFileSync('assets/css/presets/digital-01.css', 'utf8');
const outDir = '/tmp/d057-live-visual';
mkdirSync(outDir, { recursive: true });
const widths = [1920, 1600, 1501, 1500, 1440, 1366, 1280, 1101, 1100, 1024, 981, 980, 768, 390, 360];
const errors = [];
const records = [];
const browser = await chromium.launch({ headless: true });
try {
  const probe = async (width, override) => {
    const ctx = await browser.newContext({viewport: {width, height: 850}, reducedMotion:'reduce'});
    const page = await ctx.newPage();
    page.setDefaultTimeout(12000);
    try {
      const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
      if (response?.status() !== 200) throw Error('Public URL status '+response?.status());
      await page.locator('.aznet-theme-site-header').first().waitFor({state:'visible'});
      if (override) await page.addStyleTag({content:candidateCSS});
      await page.evaluate(() => document.fonts.ready);
      const measurements = await page.evaluate(() => {
        const head = document.querySelector('.aznet-theme-site-header');
        const inner = head?.querySelector('.aznet-theme-site-header__inner');
        const brand = head?.querySelector('.aznet-theme-site-header__brand');
        const logo = head?.querySelector('.aznet-theme-site-header__logo');
        const copy = head?.querySelector('.aznet-theme-site-header__brand-copy');
        const actions = head?.querySelector('.aznet-theme-site-header__inner > .aznet-theme-site-header__actions');
        const desktopNav = head?.querySelector('.aznet-theme-site-header__inner > .aznet-theme-site-header__nav');
        const utilNav = actions?.querySelector('.aznet-theme-site-header__utility-nav');
        const utilLinks = [...(utilNav?.querySelectorAll('a')??[])];
        const mobile = head?.querySelector('.aznet-theme-site-header__mobile');
        const trigger = mobile?.querySelector('[data-aznet-theme-nav-trigger]');
        function rect(el) {
          if (!el) return null;
          const r=el.getBoundingClientRect(); const st=getComputedStyle(el);
          return {x: +r.x.toFixed(1), y:+r.y.toFixed(1), w:+r.width.toFixed(1), h:+r.height.toFixed(1),
            visible:r.width>0&&r.height>0&&st.display!=='none'&&st.visibility!=='hidden',
            display:st.display};
        }
        function intersection(a,b) {
          if (!a?.visible||!b?.visible) return false;
          return Math.min(a.x+a.w,b.x+b.w)-Math.max(a.x,b.x)>2 &&
                 Math.min(a.y+a.h,b.y+b.h)-Math.max(a.y,b.y)>2;
        }
        const R = {header:rect(head),inner:rect(inner),brand:rect(brand),logo:rect(logo),copy:rect(copy),
          desktopNav:rect(desktopNav),actions:rect(actions),utilityNav:rect(utilNav),mobile:rect(mobile),trigger:rect(trigger),
          utilityLinks:utilLinks.map(x=>({text:x.textContent.trim(),...rect(x)}))};
        const linkOverlaps=[];
        for(let i=0;i<R.utilityLinks.length;i++){
          for(let j=i+1;j<R.utilityLinks.length;j++){
            if(intersection(R.utilityLinks[i], R.utilityLinks[j]))linkOverlaps.push([R.utilityLinks[i].text,R.utilityLinks[j].text]);
          }
        }
        return {url:location.href,theme:document.body.className.match(/wp-theme-\S+/)?.[0]||null,
          fullWidth:inner?.getBoundingClientRect().width,
          horizontalScroll:document.documentElement.scrollWidth > window.innerWidth + 3,
          headerOverflow:!!head && head.scrollWidth > head.clientWidth + 3,
          logoLoaded:!!logo && logo.complete && logo.naturalWidth>0,
          brandActionsOverlap:intersection(R.brand,R.actions),brandMobileOverlap:intersection(R.brand,R.mobile),
          linksOverlap:linkOverlaps,regions:R};
      });
      // Diagnose the independently observed WordPress Page 18897 Hero display defect.
      // These measurements are NOT fixed by Header CSS alone.
      const heroDiag = await page.evaluate(() => {
        const e=document.querySelector('.aznet-theme-page-hero');
        const h=e?.querySelector('h1');
        const r=x=>x?{x:Math.round(x.getBoundingClientRect().x),y:Math.round(x.getBoundingClientRect().y),
          w:Math.round(x.getBoundingClientRect().width),h:Math.round(x.getBoundingClientRect().height)}:null;
        const st=x=>{if(!x)return null; const c=getComputedStyle(x);return {
          display:c.display,width:c.width,maxWidth:c.maxWidth,minWidth:c.minWidth,
          backgroundColor:c.backgroundColor,color:c.color,
          paddingLeft:c.paddingLeft,paddingRight:c.paddingRight,
          marginLeft:c.marginLeft,position:c.position,
          transform:c.transform,gridTemplateColumns:c.gridTemplateColumns,
          fontSize:c.fontSize,opacity:c.opacity,
        }};
        const ancestry=[];for(let p=e, n=0;p&&n<10;p=p.parentElement,n++)
          ancestry.push({tag:p.tagName,cls:(p.className?.baseVal??p.className??'').toString().slice(0,170),
            rect:r(p),style:st(p)});
        return {rect:r(e),h1:r(h),h1Style:st(h),
          introStyle:st(e?.querySelector('p')),ancestry,
          bodyWidth:document.body.getBoundingClientRect().width,
          viewport:innerWidth,htmlWidth:document.documentElement.getBoundingClientRect().width};
      });
      measurements.hero=heroDiag;
      if ([1600, 1366, 390].includes(width)) console.log('HERO_DIAG '+JSON.stringify({
        width,scenario:override?'candidate':'production',...heroDiag}));
      if (override) {
        if (heroDiag.rect && heroDiag.rect.w < width * .90)
          errors.push({width,issues:['About Hero only occupies '+heroDiag.rect.w+' of '+width+' px viewport']});
      }
      const snap = [1600, 1500, 1366, 980, 390].includes(width);
      if (snap) await page.screenshot({path:join(outDir, 'about-'+width+(override?'-candidate':'-production')+'.png')});
      if (override) {
        const e = [];
        if (!measurements.regions.logo?.visible) e.push('WordPress image logo not visible');
        if (measurements.regions.copy?.visible) e.push('Redundant brand text still visible beside custom logo');
        if (measurements.brandActionsOverlap) e.push('Brand overlaps desktop actions');
        if (measurements.brandMobileOverlap) e.push('Brand overlaps mobile disclosure');
        if (measurements.linksOverlap.length) e.push('Utility nav links overlap: '+JSON.stringify(measurements.linksOverlap));
        if (measurements.headerOverflow) e.push('Header horizontal overflow');
        if (width>1500) {
          if (measurements.regions.desktopNav?.visible) e.push('Secondary primary desktop menu still visible');
          if (!measurements.regions.utilityNav?.visible) e.push('Expected utility navigation missing');
        } else {
          if (measurements.regions.desktopNav?.visible) e.push('Primary desktop nav not hidden at compact width');
          if (measurements.regions.actions?.visible) e.push('Full actions not hidden at compact width');
          if (!measurements.regions.trigger?.visible) e.push('Accessible native menu trigger missing');
        }
        if (e.length) errors.push({width, issues:e});
        if ([1500, 1366, 980, 390].includes(width) && measurements.regions.trigger?.visible) {
          const trigger = page.locator('[data-aznet-theme-nav-trigger]').first();
          const panel = page.locator('[data-aznet-theme-nav-panel]').first();
          await trigger.click();
          if (await trigger.getAttribute('aria-expanded')!=='true' || !(await panel.isVisible()))
            errors.push({width, issues:['Mobile menu click did not open accessible panel']});
          await page.keyboard.press('Escape');
          if (await trigger.getAttribute('aria-expanded')!=='false' || (await panel.isVisible()))
            errors.push({width, issues:['Escape did not close accessible panel']});
        }
      }
      records.push({width, scenario:override?'candidate':'production', ...measurements});
      console.log('MEASURE '+JSON.stringify({width,scenario:override?'candidate':'production',
        theme:measurements.theme,brandActionsOverlap:measurements.brandActionsOverlap,
        brandMobileOverlap:measurements.brandMobileOverlap,utilityOverlaps:measurements.linksOverlap.length,
        headerOverflow:measurements.headerOverflow,horizontalScroll:measurements.horizontalScroll,
        logoLoaded:measurements.logoLoaded,heroWidth:measurements.hero?.rect?.w,heroH1Color:measurements.hero?.h1Style?.color,desktopNav:measurements.regions.desktopNav?.visible,
        mobileTrigger:measurements.regions.trigger?.visible,utilityNav:measurements.regions.utilityNav?.visible}));
    } catch(e) {
      errors.push({width, scenario:override?'candidate':'production', issues:[String(e)]});
    } finally {await ctx.close();}
  };
  for(const w of [1600,1366,390]) await probe(w,false);
  for(const w of widths) await probe(w,true);
} finally {
  await browser.close();
}
writeFileSync(join(outDir,'measurements.json'),JSON.stringify({url,commit:process.env.GITHUB_SHA,records,errors},null,2));
console.log('PROBE_SUMMARY '+JSON.stringify({samples:records.length,errors,baseline:records.filter(x=>x.scenario==='production').map(x=>({
  width:x.width,brandActionsOverlap:x.brandActionsOverlap,linkOverlaps:x.linksOverlap.length,desktopNavVisible:x.regions.desktopNav?.visible}))},null,2));
if(errors.length)process.exit(1);
console.log('PASS: D-057 real public WordPress Header geometry, focus/keyboard controls and responsive breakpoints (candidate CSS overlaid)');
