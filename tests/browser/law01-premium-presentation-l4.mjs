import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl=(process.env.PROVISION_BASE_URL||'http://127.0.0.1:8080').replace(/\/$/,'');
const out=process.env.PROVISION_STATE_DIR||'/tmp/law-provisioning';
fs.mkdirSync(path.join(out,'premium'),{recursive:true});

function isTransparent(value){return value==='transparent'||value==='rgba(0, 0, 0, 0)';}

const browser=await chromium.launch({headless:true});
try {
  for (const [name,viewport] of Object.entries({'1440x1000':{width:1440,height:1000},'390x844':{width:390,height:844}})) {
    const context=await browser.newContext({viewport});
    const page=await context.newPage();
    const response=await page.goto(`${baseUrl}/`,{waitUntil:'networkidle'});
    if(!response||response.status()!==200)throw new Error(`${name}: homepage HTTP ${response?.status()}`);
    await page.locator('.aznet-theme-homepage--law-01').waitFor();

    const overflow=await page.evaluate(()=>document.documentElement.scrollWidth-document.documentElement.clientWidth);
    if(overflow>1)throw new Error(`${name}: horizontal overflow ${overflow}`);

    const serviceCards=await page.locator('.aznet-theme-law01-services .aznet-theme-law01-card').count();
    if(serviceCards!==6)throw new Error(`${name}: expected 6 service cards, got ${serviceCards}`);

    const teamBackground=await page.locator('.aznet-theme-law01-team').evaluate(el=>getComputedStyle(el).backgroundColor);
    if(isTransparent(teamBackground))throw new Error(`${name}: Team must render as a distinct premium band`);

    const panelBackground=await page.locator('.aznet-theme-law01-team .aznet-theme-law01-panel').evaluate(el=>getComputedStyle(el).backgroundColor);
    if(!isTransparent(panelBackground))throw new Error(`${name}: Team inner panel must not regress to a floating white placeholder`);

    if(viewport.width>=1024){
      const fallback=await page.locator('.aznet-theme-law01-hero__grid--text').evaluate(el=>{
        const pseudo=getComputedStyle(el,'::after');
        return {content:pseudo.content,minHeight:parseFloat(pseudo.minHeight||'0')};
      });
      if(!fallback.content||fallback.content==='none'||fallback.content==='normal'||fallback.minHeight<250)throw new Error(`${name}: Hero text-only premium visual fallback missing`);

      const editorial=await page.evaluate(()=>{
        const container=document.querySelector('.aznet-theme-law01-editorial__grid');
        const child=container?.firstElementChild;
        if(!(container instanceof HTMLElement)||!(child instanceof HTMLElement))return null;
        const style=getComputedStyle(child);
        return {
          childCount:container.children.length,
          gridColumnStart:style.gridColumnStart,
          gridColumnEnd:style.gridColumnEnd,
          childWidth:child.getBoundingClientRect().width
        };
      });
      if(!editorial||editorial.childCount!==1||editorial.gridColumnStart!=='1'||editorial.gridColumnEnd!=='-1'||editorial.childWidth<650)throw new Error(`${name}: About no-media fallback must span the grid while keeping a readable editorial measure`);
    }

    const axe=await new AxeBuilder({page}).analyze();
    const blocking=axe.violations.filter(v=>['critical','serious'].includes(v.impact));
    if(blocking.length)throw new Error(`${name}: axe ${blocking.map(v=>`${v.id}:${v.impact}`).join(',')}`);

    await page.screenshot({path:path.join(out,'premium',`law01-premium-${name}.png`),fullPage:true});
    await context.close();
  }
} finally {
  await browser.close();
}
console.log('PASS: Law 01 premium presentation browser/a11y');
