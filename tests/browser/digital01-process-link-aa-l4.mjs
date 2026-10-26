import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url = process.env.DIGITAL01_PUBLIC_URL || 'https://aznet.vn/';
const out = process.env.DIGITAL01_EVIDENCE_DIR || '/tmp/digital01-process-link-evidence';
const source = fs.readFileSync('assets/css/components/homepage-digital-01.css', 'utf8');
const selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__process .aznet-digital01__text-link > a';
const start = source.indexOf(selector + ' {');
if (start < 0) throw new Error('Candidate Process anchor rule missing from real source');
const end = source.indexOf('}', start);
if (end < 0) throw new Error('Candidate Process anchor rule not terminated');
const scopedCSS = source.slice(start, end + 1);
const result = { head: process.env.GITHUB_SHA || '', source: url, scope: 'PUBLIC browser-local CSS overlay only; NO production mutation', cases: [], failures: [] };
fs.mkdirSync(out, { recursive: true });

const luminance = (rgb) => rgb.map(v => {
  const n = v / 255;
  return n <= 0.04045 ? n / 12.92 : ((n + 0.055) / 1.055) ** 2.4;
}).reduce((total, n, i) => total + n * [0.2126,0.7152,0.0722][i], 0);
function channels(cssColor) {
  const m = cssColor.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)/);
  if (!m) throw new Error('Unexpected computed CSS color: ' + cssColor);
  return m.slice(1,4).map(Number);
}
function contrast(fg,bg) {
  const a = luminance(channels(fg)); const b = luminance(channels(bg));
  return (Math.max(a,b)+0.05)/(Math.min(a,b)+0.05);
}

const browser = await chromium.launch({headless:true});
try {
  for (const width of [390,768,1440]) {
    const context = await browser.newContext({viewport:{width,height:900},deviceScaleFactor:1,reducedMotion:'reduce'});
    const page = await context.newPage();
    const row = {width,pass:false};
    try {
      let response=await page.goto(url,{waitUntil:'domcontentloaded',timeout:45000});
      if (response?.status() !== 200) response=await page.reload({waitUntil:'domcontentloaded',timeout:45000});
      if (response?.status() !== 200) throw new Error('Public response HTTP '+String(response?.status()||'none'));
      const link = page.locator('.aznet-digital01__process .aznet-digital01__text-link > a').first();
      await link.waitFor({state:'attached',timeout:20000});
      const getData = async () => link.evaluate(el => {
        const process = el.closest('.aznet-digital01__process');
        return {color:getComputedStyle(el).color, background:getComputedStyle(process).backgroundColor, href:el.href, label:el.textContent.trim(), bodyPreset:document.body.classList.contains('aznet-theme-preset--digital-01'), scrollWidth:document.documentElement.scrollWidth, clientWidth:document.documentElement.clientWidth};
      });
      const before=await getData();
      if(!before.bodyPreset || !before.href || !before.label || !before.background) throw new Error('Invalid authoritative public native HTML state');
      await page.addStyleTag({content:scopedCSS});
      const after=await getData();
      const ratio=contrast(after.color,after.background);
      if (ratio < 4.5) throw new Error('Process link contrast '+ratio.toFixed(2)+' is below AA 4.5:1');
      if (before.href !== after.href || before.label !== after.label) throw new Error('Candidate changes native link identity/content');
      if (after.scrollWidth > Math.max(before.scrollWidth,width+2)) throw new Error('Candidate introduces horizontal overflow');
      await link.focus();
      row.pass=true;row.before=before;row.after=after;row.contrast=Math.round(ratio*100)/100;
      await page.locator('.aznet-digital01__process').screenshot({path:path.join(out,'process-'+width+'.png'),animations:'disabled'});
      console.log('PASS Process link L4 '+width+'px: '+ratio.toFixed(2)+':1, native href/label unchanged');
    } catch(err) {
      row.error=String(err);
      result.failures.push(width+': '+String(err));
      console.error('FAIL Process link L4 '+width+'px: '+String(err));
    } finally {
      result.cases.push(row);
      await context.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(out,'summary.json'),JSON.stringify(result,null,2));
}
if(result.failures.length) process.exitCode=1;
else console.log('PASS: public Digital 01 Process link candidate CSS L4 3/3; NOT installed Theme or production certification');
