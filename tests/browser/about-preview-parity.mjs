import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs/promises';
import crypto from 'node:crypto';
const dir='/tmp/about-parity'; await fs.mkdir(dir,{recursive:true});
const browser=await chromium.launch({headless:true});
const context=await browser.newContext();
const origin='https://lstamduchn.vn';
const response=await context.request.get(origin+'/wp-content/themes/aznet-theme-wpvibe-draft/assets/css/components/about-fullbleed-preview.css');
if(!response.ok())throw new Error('Cannot read actual draft CSS: '+response.status());
const baseline=await response.text();
await fs.writeFile(dir+'/about-before.css',baseline);
const marker='/* Body + section system */';
if(!baseline.includes(marker))throw new Error('Baseline marker changed; revalidate before editing.');
let css=baseline,mode='RED-current-preview-css';
try{const body=await fs.readFile('ops/about-preview/about-body-parity.css','utf8');css=baseline+'\n'+body;mode='candidate-body-correction';}catch(e){if(e.code!=='ENOENT')throw e;}
await fs.writeFile(dir+'/about-tested.css',css);
const results={mode,scope:'Read-only live WordPress content; draft CSS applied in browser memory, NOT a deployment or a provider integration test.',cssSha:crypto.createHash('sha256').update(css).digest('hex'),viewports:[],failures:[]};
for(const width of [390,768,1024,1440,1920]){
 const page=await context.newPage();await page.setViewportSize({width,height:900});
 await page.goto(origin+'/',{waitUntil:'networkidle'});await page.evaluate(()=>document.fonts.ready);
 const ref=await page.locator('.aznet-theme-law01-services').evaluate(el=>{
  const c=el.querySelector('.aznet-theme-law01-container'),h=el.querySelector('h2'),s=getComputedStyle(h),r=c.getBoundingClientRect();
  return{x:r.x,w:r.width,font:s.fontFamily,size:parseFloat(s.fontSize)};
 });
 await page.goto(origin+'/gioi-thieu/',{waitUntil:'networkidle'});
 await fs.writeFile(dir+'/about-public-'+width+'.html',await page.content());
 await page.evaluate(()=>{
  const main=document.querySelector('main'); main.className='aznet-theme-main aznet-theme-main--about-preview';
  const article=main.querySelector('article');article.className='aznet-theme-about';
  article.querySelector(':scope > header')?.remove();
  const body=article.querySelector('.aznet-theme-page__content');body.id='aznet-theme-about-content';
 });
 await page.evaluate(text=>{const style=document.createElement('style');style.textContent=text;document.head.prepend(style);},css);
 await page.evaluate(()=>document.fonts.ready);
 const metrics=await page.evaluate(()=>{
  const root=document.querySelector('#aznet-theme-about-content');
  const box=el=>{const r=el.getBoundingClientRect();return{x:r.x,y:r.y,w:r.width,h:r.height};};
  const sections=[...root.querySelector('.aznet-theme-page-kit--about').children].filter(e=>e.classList.contains('aznet-theme-page-kit__section'));
  return{viewport:document.documentElement.clientWidth,scroll:document.documentElement.scrollWidth,sections:sections.map(el=>({
   cls:el.className,box:box(el),pad:parseFloat(getComputedStyle(el).paddingLeft),bg:getComputedStyle(el).backgroundColor,image:getComputedStyle(el).backgroundImage,radius:getComputedStyle(el).borderRadius,
   h2:el.querySelector('h2')?{...box(el.querySelector('h2')),font:getComputedStyle(el.querySelector('h2')).fontFamily,size:parseFloat(getComputedStyle(el.querySelector('h2')).fontSize)}:null,
   children:[...el.children].map(e=>({tag:e.tagName,cls:e.className,box:box(e)})),
   rows:[...el.querySelectorAll(':scope > .wp-block-columns')].map(r=>({box:box(r),cols:[...r.children].map(box)}))
  }))};
 });
 const failures=[];
 if(metrics.scroll>metrics.viewport+1)failures.push('horizontal overflow');
 if(metrics.sections.length!==8)failures.push('must preserve eight authored sections');
 for(const s of metrics.sections){
  const name=s.cls.match(/aznet-theme-page-kit__(\S+)/)?.[1]||s.cls;
  if(Math.abs(s.box.x)>1||Math.abs(s.box.w-metrics.viewport)>1)failures.push(name+': background not edge-to-edge');
  if(Math.abs(s.pad-ref.x)>2)failures.push(name+': content axis differs from Homepage');
  if(s.h2&&s.h2.size>ref.size*1.15+1)failures.push(name+': heading scale exceeds Homepage');
  if(s.h2&&s.h2.font!==ref.font)failures.push(name+': font differs from Homepage');
  for(const row of s.rows){
   if(width>=1024&&row.box.w<s.box.w-2*s.pad-3)failures.push(name+': WordPress constrains row width');
   if(width<640&&row.cols.some(c=>Math.abs(c.w-row.box.w)>2))failures.push(name+': mobile cards not one column');
  }
 }
 const intro=metrics.sections[0];if(width>=1024&&intro.box.h>330)failures.push('intro is oversized rather than a compact statement');
 const team=metrics.sections.find(s=>s.cls.includes('__team-teaser'));if(team&&team.image!=='none')failures.push('team has illustrative background without verified team evidence');
 const axe=await new AxeBuilder({page}).include('#aznet-theme-about-content').withTags(['wcag2a','wcag2aa','wcag21aa']).analyze();
 for(const v of axe.violations)failures.push('axe:'+v.id);
 await fs.writeFile(dir+'/axe-'+width+'.json',JSON.stringify(axe.violations,null,2));
 await page.locator('#aznet-theme-about-content').screenshot({path:dir+'/below-hero-'+width+'.png'});
 results.viewports.push({width,reference:ref,metrics,failures});results.failures.push(...failures.map(f=>width+': '+f));
 console.log(JSON.stringify({width,mode,failures}));await page.close();
}
await fs.writeFile(dir+'/result.json',JSON.stringify(results,null,2));await browser.close();
if(results.failures.length)process.exitCode=1;
