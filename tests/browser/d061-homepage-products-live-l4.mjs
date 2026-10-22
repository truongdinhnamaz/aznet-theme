import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url = process.env.D061_PUBLIC_URL || 'https://aznet.vn/';
const out = process.env.D061_EVIDENCE_DIR || '/tmp/d061-homepage-evidence';
fs.mkdirSync(out, { recursive: true });
const summary = { source: url, head_sha: process.env.D061_HEAD_SHA || '', layer: 'L4 actual public HTML with candidate CSS overlay, NO deployment', cases: [], failures: [] };

const selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__products > .wp-block-group.aznet-digital01__section';
const stylesheet = fs.readFileSync('assets/css/components/homepage-digital-01.css', 'utf8');
const start = stylesheet.indexOf(selector + ' {');
if (start === -1) throw new Error('D-061 candidate selector missing');
const end = stylesheet.indexOf('}', start);
const override = stylesheet.substring(start, end + 1);
if (!override.includes('width:min(var(--d01-max),calc(100% - 48px))') ||
    !override.includes('padding-block:0') ||
    !override.includes('margin-inline:auto!important')) {
  throw new Error('D-061 candidate declaration mismatch');
}

function assert(ok, reason) { if (!ok) throw new Error(reason); }
function approx(a, b, tolerance = 2.5) { return Number.isFinite(a) && Math.abs(a-b) <= tolerance; }
const widths = [
  {name:'mobile-360',width:360,height:800},
  {name:'mobile-390',width:390,height:844},
  {name:'tablet-768',width:768,height:1024},
  {name:'desktop-1024',width:1024,height:900},
  {name:'desktop-1440',width:1440,height:900},
  {name:'wide-1920',width:1920,height:1000}
];

async function geometry(page) {
  return await page.evaluate(() => {
    const section = document.querySelector('.aznet-digital01__products');
    const inner = document.querySelector('.aznet-digital01__products > .wp-block-group.aznet-digital01__section');
    const heading = inner?.querySelector('h2');
    const grid = inner?.querySelector('.aznet-digital01__products-grid');
    const cards = [...(grid?.querySelectorAll(':scope > .aznet-digital01__product') || [])];
    const box = el => {
      if (!el) return null;
      const r=el.getBoundingClientRect();
      return {left:r.left,right:r.right,top:r.top,width:r.width,height:r.height};
    };
    return {
      viewport: document.documentElement.clientWidth,
      scrollWidth: document.documentElement.scrollWidth,
      bodyPreset: document.body.classList.contains('aznet-theme-preset--digital-01'),
      section:box(section), inner:box(inner), heading:box(heading), grid:box(grid),
      cards:cards.map(box),
      paddingTop:inner ? getComputedStyle(inner).paddingTop : null,
      paddingBottom:inner ? getComputedStyle(inner).paddingBottom : null,
      gridColumns:grid ? getComputedStyle(grid).gridTemplateColumns : null,
      headingText:heading?.textContent?.trim().slice(0,120) || '',
      outerHero:box(document.querySelector('.aznet-digital01__hero')),
      header:box(document.querySelector('.aznet-theme-site-header'))
    };
  });
}

const browser=await chromium.launch({headless:true});
try {
  for (const v of widths) {
    const context=await browser.newContext({viewport:{width:v.width,height:v.height}, deviceScaleFactor:1, reducedMotion:'reduce'});
    const page=await context.newPage();
    const result={name:v.name,viewport:v.width,passed:false};
    const jsErrors=[];
    page.on('pageerror',error=>jsErrors.push(error.message));
    try {
      let response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});
      if (response?.status()!==200) {
        // Public WAF/network occasionally returns 520; one bounded retry.
        response=await page.reload({waitUntil:'domcontentloaded',timeout:45000});
      }
      assert(response?.status()===200,'public HTML unavailable; HTTP '+(response?.status()||'none'));
      await page.locator('.aznet-digital01__products > .wp-block-group.aznet-digital01__section').waitFor({state:'attached',timeout:20000});
      await page.waitForLoadState('load',{timeout:20000}).catch(()=>{});
      const before=await geometry(page);
      assert(before.bodyPreset,'Digital 01 visual preset absent');
      assert(before.inner&&before.heading&&before.grid&&before.cards.length===2,'native WordPress Products HTML changed: '+JSON.stringify(before));
      await page.addStyleTag({content:override});
      const after=await geometry(page);
      const expectedWidth=Math.min(1240,v.width-48);
      const expectedLeft=(v.width-expectedWidth)/2;
      assert(approx(after.inner.width,expectedWidth,3),'container width incorrect: '+JSON.stringify({expectedWidth,actual:after.inner.width}));
      assert(approx(after.inner.left,expectedLeft,3),'container not centered: '+JSON.stringify({expectedLeft,actual:after.inner.left}));
      assert(approx(after.inner.right,expectedLeft+expectedWidth,3),'container right edge wrong');
      assert(approx(parseFloat(after.paddingTop),0,0.5)&&approx(parseFloat(after.paddingBottom),0,0.5),'double section vertical padding retained: '+after.paddingTop+'/'+after.paddingBottom);
      assert(approx(after.heading.left,after.inner.left,4),'heading not aligned with inner container');
      assert(approx(after.grid.left,after.inner.left,4),'grid not aligned with heading/inner container');
      assert(after.cards.every(r=>r.left>=after.inner.left-3&&r.right<=after.inner.right+3),'card clipped/overflowed inner container');
      assert(after.cards.every(r=>r.left>=-1&&r.right<=v.width+1),'card outside viewport');
      assert(after.scrollWidth<=Math.max(before.scrollWidth,v.width+1),'candidate introduced horizontal overflow');
      assert(approx(after.outerHero?.left,before.outerHero?.left,1),'candidate shifted unrelated Hero');
      assert(approx(after.header?.left,before.header?.left,1),'candidate shifted unrelated Header');
      await page.locator('.aznet-digital01__products').screenshot({path:path.join(out,'products-'+v.name+'.png'),animations:'disabled',timeout:30000});
      if(v.width===390||v.width===1440){await page.screenshot({path:path.join(out,'page-'+v.name+'.png'),fullPage:true,animations:'disabled',timeout:45000});}
      result.passed=true;
      result.before=before;result.after=after;result.jsErrors=jsErrors;
      console.log('PASS D-061 '+v.name+' inner '+Math.round(after.inner.left)+'..'+Math.round(after.inner.right)+' width='+Math.round(after.inner.width)+' padding=0; Products cards=2');
    } catch(e) {
      result.failure=String(e);
      summary.failures.push(v.name+': '+String(e));
      console.error('FAIL D-061 '+v.name+' '+String(e));
      await page.screenshot({path:path.join(out,'failed-'+v.name+'.png'),fullPage:false,timeout:12000}).catch(()=>{});
    } finally {
      summary.cases.push(result);
      await context.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(out,'summary.json'),JSON.stringify(summary,null,2));
}
if(summary.failures.length){console.error('D-061 L4 FAIL: '+summary.failures.join(' | '));process.exitCode=1;}
else console.log('D-061 L4 PASS: 6 viewport candidate CSS overlays on actual public WordPress Products HTML; NOT Theme release.');
