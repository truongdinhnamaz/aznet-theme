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
  if(!data.artVisible || !data.artBox?.width)problems.push('hero art not visually laid out');
  if(data.scrollWidth>data.viewportWidth+2)problems.push('horizontal overflow '+data.scrollWidth+'> '+data.viewportWidth);
  if(violations.length)problems.push('WCAG tagged violations: '+violations.map(x=>x.id).join(','));
  if(errors.length)problems.push('JavaScript errors '+errors.join(','));
  const result={...test,observed:data,violations,problems};
  summary.push(result);
  await page.screenshot({path:path.join(evidenceDir,'brand-2026-'+test.name+'.png'),fullPage:true});
  console.log('BRAND_2026_L4 '+JSON.stringify(result));
  if(problems.length)failed=true;
  await context.close();
}
fs.writeFileSync(path.join(evidenceDir,'brand-2026-l4.json'),JSON.stringify(summary,null,2));
await browser.close();
if(failed){console.error('FAIL: WordPress + browser branded design fixture; see BRAND_2026_L4 rows');process.exit(1);}
console.log('PASS: WordPress 6.9 + responsive desktop/tablet/mobile, native logo, focus/contrast WCAG checks');
