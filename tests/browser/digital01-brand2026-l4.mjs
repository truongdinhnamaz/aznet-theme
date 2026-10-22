import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import path from 'node:path';

const base = process.env.DIGITAL01_BASE_URL || 'http://127.0.0.1:8080';
const evidenceDir = process.env.DIGITAL01_STATE_DIR || '/tmp/digital01-brand';
fs.mkdirSync(evidenceDir, {recursive:true});
const browser = await chromium.launch({headless:true});
const cases = [
  {name:'desktop', width:1440, height:950, columns:4},
  {name:'tablet', width:820, height:1024, columns:2},
  {name:'mobile', width:390, height:844, columns:1},
];
const summary = [];
let failed = false;
for (const test of cases) {
  const context = await browser.newContext({viewport:{width:test.width,height:test.height},deviceScaleFactor:1});
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror',err=>errors.push(String(err)));
  await page.goto(base,{waitUntil:'networkidle',timeout:90000});
  const data = await page.evaluate(() => {
    const query = x=>document.querySelector(x);
    const sel = '.aznet-digital01--brand-2026';
    const root = query(sel);
    const hero = query(sel+' .aznet-digital01__hero');
    const h1 = query(sel+' h1');
    const cards = [...document.querySelectorAll(sel+' .aznet-digital01__service')];
    const services = query(sel+' .aznet-digital01__services-grid');
    const logo = query('.aznet-theme-site-header__brand img');
    const header = query('.aznet-theme-site-header');
    const art = query(sel+' .aznet-digital01__hero-art');
    const style = element => element ? getComputedStyle(element) : null;
    const resources = performance.getEntriesByType('resource').map(x=>x.name);
    const row = {
      variant:!!root,
      bodyClass:document.body.className,
      logo:logo?.getAttribute('src')||null,
      headerBackground:style(header)?.backgroundColor||null,
      heroBackground:style(hero)?.backgroundImage||null,
      h1:h1?.textContent?.trim()||null,
      h1Color:style(h1)?.color||null,
      cards:cards.length,
      serviceColumns:style(services)?.gridTemplateColumns?.split(' ').filter(Boolean).length||0,
      serviceBox:services?.getBoundingClientRect()?.toJSON()||null,
      artVisible:!!art && style(art)?.display!=='none',
      artBox:art?.getBoundingClientRect()?.toJSON()||null,
      links:[...document.querySelectorAll(sel+' a')].map(x=>({text:x.textContent.trim().slice(0,40),href:x.getAttribute('href')})).slice(0,10),
      headerNav:[...document.querySelectorAll('.aznet-theme-site-header__utility-menu a')].map(a=>a.textContent.trim()),
      scrollWidth:document.documentElement.scrollWidth,
      viewportWidth:window.innerWidth,
      bodyHeight:document.documentElement.scrollHeight,
      brandCSS:resources.some(x=>x.includes('homepage-digital-01-brand-2026.css')),
      consultationBackground: style(query('.aznet-theme-site-header__utility-menu > li:last-child > a'))?.backgroundColor||null,
      submenuLastBackground: style(query('.aznet-theme-site-header__utility-menu .sub-menu > li:last-child > a'))?.backgroundColor||null,
    };
    return row;
  });
  const axe = data.variant
    ? await new AxeBuilder({page}).include('.aznet-digital01--brand-2026').withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa']).analyze()
    : {violations: []};
  const violations = axe.violations.map(v=>({id:v.id,impact:v.impact,nodes:v.nodes.map(n=>({target:n.target,summary:n.failureSummary})).slice(0,6)}));
  const problems = [];
  if(!data.variant)problems.push('missing WordPress-owned brand-2026 opt-in class');
  if(!data.logo)problems.push('native WordPress custom logo missing');
  if(data.headerBackground!=='rgb(255, 255, 255)')problems.push('header not white');
  if(data.h1Color!=='rgb(11, 35, 77)')problems.push('hero title color not navy');
  if(!data.h1?.includes('tăng trưởng thực sự'))problems.push('target Hero heading missing');
  if(data.cards!==4)problems.push('expected four native service cards');
  if(data.serviceColumns!==test.columns)problems.push('wrong responsive service columns '+data.serviceColumns+' expected '+test.columns);
  if(!data.brandCSS)problems.push('branded stylesheet not loaded');
  if (test.width >= 1100) {
    if (data.consultationBackground !== 'rgb(198, 33, 33)')
      problems.push('last top-level consultation item not styled as red CTA');
    if (data.submenuLastBackground === null)
      problems.push('nested WordPress submenu missing from fixture');
    else if (data.submenuLastBackground === 'rgb(198, 33, 33)')
      problems.push('nested submenu last item incorrectly styled as red CTA');
  }
  if(!data.artVisible || !data.artBox?.width)problems.push('hero art not visually laid out');
  if(data.scrollWidth>data.viewportWidth+2)problems.push('horizontal overflow '+data.scrollWidth+'> '+data.viewportWidth);
  if(violations.length)problems.push('WCAG tagged violations: '+violations.map(x=>x.id).join(','));
  if(errors.length)problems.push('JavaScript errors '+errors.join(','));

  // The bright branded Header must remain legible after the native mobile menu opens.
  // Unlike the Hero's scoped content, navigation lives in the Theme-owned site shell.
  if (test.width <= 900) {
    const trigger = page.locator('[data-aznet-theme-nav-trigger]');
    if (await trigger.count() !== 1) {
      problems.push('native mobile navigation trigger missing');
    } else {
      await trigger.click();
      const mobileMenu = await page.evaluate(() => {
        const p = document.querySelector('[data-aznet-theme-nav-panel]');
        const a = p?.querySelector('.aznet-theme-site-header__utility-menu a');
        const panelColor = p ? getComputedStyle(p).backgroundColor : null;
        const linkColor = a ? getComputedStyle(a).color : null;
        const lum = color => {
          const x = (color?.match(/[0-9]+(?:\\.[0-9]+)?/g)||[]).slice(0,3).map(Number).map(v=>v/255);
          if(x.length!==3)return 0;
          const y=x.map(v=>v<=.04045?v/12.92:((v+.055)/1.055)**2.4);
          return .2126*y[0]+.7152*y[1]+.0722*y[2];
        };
        const hi = Math.max(lum(panelColor),lum(linkColor));
        const lo = Math.min(lum(panelColor),lum(linkColor));
        return {open:p&&!p.hidden,expanded:document.querySelector('[data-aznet-theme-nav-trigger]')?.getAttribute('aria-expanded'),
          panelColor,linkColor,contrast:(hi+.05)/(lo+.05),
          childVisible:(() => {
            const child=p?.querySelector('.aznet-theme-site-header__utility-menu .sub-menu a');
            return !!child && getComputedStyle(child).visibility==='visible' && child.getClientRects().length>0;
          })()};
      });
      data.mobileMenu = mobileMenu;
      if (!mobileMenu.open || mobileMenu.expanded!=='true') problems.push('native mobile menu not interactive');
      if (!mobileMenu.childVisible) problems.push('native nested WordPress mobile utility menu items not visible');
      if (!mobileMenu.linkColor || mobileMenu.contrast < 4.5)
        problems.push('mobile header link contrast '+mobileMenu.contrast?.toFixed(2)+' < 4.5');
      await page.keyboard.press('Escape');
    }
  }
  if (test.width >= 1100) {
    const dropParent = page.locator('.aznet-theme-site-header__utility-menu > li.menu-item-has-children').first();
    if (await dropParent.count() !== 1) {
      problems.push('native Desktop utility dropdown trigger missing');
    } else {
      await dropParent.hover();
      const dropdown = await page.evaluate(() => {
        const ul = document.querySelector('.aznet-theme-site-header__utility-menu > li.menu-item-has-children > .sub-menu');
        if (!ul) return null;
        const style = getComputedStyle(ul), bounds = ul.getBoundingClientRect();
        return {visibility:style.visibility, opacity:style.opacity, background:style.backgroundColor,
          box:bounds.toJSON()};
      });
      data.dropdown = dropdown;
      if (!dropdown || dropdown.visibility !== 'visible' || Number(dropdown.opacity) < .95
          || dropdown.background !== 'rgb(255, 255, 255)'
          || !(dropdown.box?.width > 150))
        problems.push('native Desktop dropdown not visually accessible with white background on hover');
    }
  }
  const result={...test,observed:data,violations,problems};
  summary.push(result);
  await page.screenshot({path:path.join(evidenceDir,'brand-2026-'+test.name+'.png'),fullPage:true});
  console.log('BRAND_2026_L4 '+JSON.stringify(result));
  if(problems.length)failed=true;
  await context.close();
}
// Verify the second, non-home WordPress-owned Landing Page renders the same
// opt-in presentation without adding a duplicate native Page-title H1.
const landingId = Number(fs.readFileSync(path.join(evidenceDir,'landing-id.txt'),'utf8').trim());
if(!Number.isInteger(landingId) || landingId <= 0){
  console.error('FAIL: Native Landing Page fixture missing'); failed=true;
} else {
  for(const view of [{name:'landing-desktop',width:1440,height:950},{name:'landing-mobile',width:390,height:844}]){
    const context = await browser.newContext({viewport:{width:view.width,height:view.height},deviceScaleFactor:1});
    const page = await context.newPage();
    await page.goto(base+'/?page_id='+landingId,{waitUntil:'networkidle',timeout:90000});
    const data = await page.evaluate(() => ({
      pageClass: document.body.className,
      branded: !!document.querySelector('.aznet-digital01--brand-2026'),
      h1Count: document.querySelectorAll('h1').length,
      h1Text: document.querySelector('h1')?.textContent?.trim(),
      extraPageHeader: !!document.querySelector('.aznet-theme-page__header'),
      CSSActive: performance.getEntriesByType('resource').some(r=>r.name.includes('homepage-digital-01-brand-2026.css')),
      scrollWidth:document.documentElement.scrollWidth,
      viewportWidth:window.innerWidth
    }));
    const problems = [];
    if(!data.pageClass.includes('page-template-landing')) problems.push('native Page landing template not selected');
    if(!data.branded || !data.CSSActive) problems.push('opt-in presentation missing from native Page route');
    if(data.h1Count!==1 || data.h1Text!=='Xây dựng website tăng trưởng thực sự')
      problems.push('native Landing Page H1 incorrectly duplicated or missing');
    if(data.extraPageHeader) problems.push('extra native Page header breaks Hero-first composition');
    if(data.scrollWidth>data.viewportWidth+2) problems.push('horizontal overflow');
    const outcome={...view,observed:data,problems};
    summary.push(outcome);
    console.log('BRAND_2026_LANDING '+JSON.stringify(outcome));
    await page.screenshot({path:path.join(evidenceDir,'brand-2026-'+view.name+'.png'),fullPage:true});
    if(problems.length)failed=true;
    await context.close();
  }
}
fs.writeFileSync(path.join(evidenceDir,'brand-2026-l4.json'),JSON.stringify(summary,null,2));
await browser.close();
if(failed){console.error('FAIL: WordPress + browser branded design fixture; see BRAND_2026_L4 rows');process.exit(1);}
console.log('PASS: WordPress 6.9 + responsive desktop/tablet/mobile, native logo, focus/contrast WCAG checks');
