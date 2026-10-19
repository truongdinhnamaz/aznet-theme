import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

// Production read-only acceptance. No logins, POSTs, options, menus or content mutation.
const base = (process.env.AZNET_PRODUCTION_BASE_URL || '').replace(/\/$/, '');
if (!/^https:\/\/[a-z0-9.-]+$/i.test(base)) throw new Error('HTTPS site required');
const output = process.env.AZNET_PRODUCTION_QA_OUT || '/tmp/d049-live';
fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ headless: true });
const results = [];
const errors = [];
const homeWidths = new Map();
const assert = (yes, label, evidence = '') => {
  if (!yes) throw new Error(label + (evidence ? ' :: ' + JSON.stringify(evidence) : ''));
};
async function rects(page) {
  return page.evaluate(() => {
    const rect = (css) => {
      const el = document.querySelector(css);
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return {x:r.x, y:r.y, width:r.width, height:r.height, right:r.right, bottom:r.bottom};
    };
    return {
      body:document.body.className,
      overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth,
      brand:rect('.aznet-theme-site-header__brand-title'),
      label:rect('.aznet-theme-site-header__catalog-rail-label'),
      summary:rect('.aznet-theme-site-header__catalog summary'),
      nav:rect('.aznet-theme-site-header__nav'),
      rail:rect('.aznet-theme-industrial01-hero__catalog'),
      hero:rect('.aznet-theme-industrial01-hero'),
      copy:rect('.aznet-theme-industrial01-hero__content'),
      labelTransform:getComputedStyle(document.querySelector('.aznet-theme-site-header--catalog')).getPropertyValue('--aznet-theme-text-navigation') || '',
    };
  });
}
try {
  for (const width of [1440,1200,1101,1024,390,320]) {
    for (const kind of ['home','solutions']) {
      const id = kind + '-' + width;
      const url = base + (kind === 'home' ? '/' : '/giai-phap/');
      const context = await browser.newContext({viewport:{width,height:width>1100?1000:900},locale:'vi-VN'});
      const page = await context.newPage();
      const pageErrors = [];
      page.on('pageerror', e => pageErrors.push(e.message));
      try {
        const response = await page.goto(url, {waitUntil:'domcontentloaded',timeout:55000});
        assert(response && response.status()===200,id+' HTTP',response?.status());
        await page.locator('header.aznet-theme-site-header').waitFor({state:'attached',timeout:15000});
        await page.waitForTimeout(500);
        const g = await rects(page);
        assert(/\bwp-theme-aznet-theme\b/.test(g.body) && !/wpvibe-draft/.test(g.body),id+' not active live theme',g.body);
        assert(!page.url().includes('wpvibe_preview'),id+' preview route');
        assert(await page.locator('header.aznet-theme-site-header--catalog').count()===1,id+' catalog header missing');
        assert(await page.locator('link[href*="catalog-navigation.css"]').count()===1,id+' catalog CSS missing');
        assert(g.overflow<=1,id+' horizontal overflow',g.overflow);
        assert((await page.locator('.aznet-theme-site-header__brand-title').first().textContent()||'').trim()==='Minh Nguyên',id+' brand title');
        const desktop=width>=1101;
        if (kind==='home') {
          assert(await page.locator('.aznet-theme-industrial01-hero--with-catalog').count()===1,id+' Hero rail missing');
          assert((await page.locator('.aznet-theme-industrial01-hero h1').innerText()).includes('Giải pháp máy chế biến gỗ'),id+' Hero title');
          assert(await page.locator('.aznet-theme-industrial01-hero__content a[href]').count()>0,id+' Hero CTA');
        } else {
          assert(await page.locator('main.aznet-theme-main--page h1').count()>0,id+' WordPress Page missing');
          assert(await page.locator('.aznet-theme-industrial01-hero__catalog').count()===0,id+' rail leaked to inner Page');
        }
        if (desktop) {
          assert(await page.locator('.aznet-theme-site-header__brand-title').isVisible(),id+' brand hidden');
          assert(await page.locator('.aznet-theme-site-header__nav').isVisible(),id+' nav hidden');
          if (kind==='home') {
            assert(await page.locator('.aznet-theme-site-header__catalog-rail-label').isVisible(),id+' category band hidden');
            assert(await page.locator('.aznet-theme-industrial01-hero__catalog').isVisible(),id+' Hero rail hidden');
            assert(g.label&&g.rail&&g.hero&&g.copy,id+' geometry missing');
            assert(Math.abs(g.label.x-g.rail.x)<=3&&Math.abs(g.label.right-g.rail.right)<=3,id+' Header rail alignment',g);
            assert(g.rail.right<=g.copy.x+3,id+' Hero rail overlaps copy',g);
            assert(g.rail.height<g.hero.height-10,id+' short menu stretched to Hero height',g);
            assert(await page.locator('.aznet-theme-industrial01-hero__catalog li').count()>=19,id+' WordPress menu data lost');
            homeWidths.set(width,g);
          } else {
            const home=homeWidths.get(width);
            assert(home&&home.label&&g.summary&&g.brand,id+' baseline geometry missing',g);
            assert(Math.abs(g.summary.x-home.label.x)<=3&&Math.abs(g.summary.width-home.label.width)<=3,id+' Header category width mismatch',{home,inner:g});
            assert(Math.abs(g.brand.x-home.brand.x)<=3,id+' brand alignment mismatch',{home,inner:g});
            const details=page.locator('.aznet-theme-site-header__catalog');
            await details.locator('summary').focus();
            await page.keyboard.press('Enter');
            assert(await details.getAttribute('open')!==null,id+' keyboard disclosure failed');
            assert(await details.locator('li').count()>=19,id+' catalog menu children absent');
          }
        } else {
          if(kind==='home') assert(!(await page.locator('.aznet-theme-industrial01-hero__catalog').isVisible()),id+' mobile rail still visible');
          const trigger=page.locator('[data-aznet-theme-nav-trigger]');
          assert(await trigger.isVisible(),id+' mobile trigger hidden');
          await trigger.click();
          assert(await page.locator('[data-aznet-theme-nav-panel]').isVisible(),id+' mobile panel hidden');
          const details=page.locator('[data-aznet-theme-nav-panel] .aznet-theme-catalog-navigation--mobile');
          assert(await details.locator('summary').isVisible(),id+' mobile catalog summary hidden');
          await details.locator('summary').focus();
          await page.keyboard.press('Enter');
          assert(await details.getAttribute('open')!==null,id+' keyboard mobile catalog failed');
          assert(await details.locator('li').count()>=19,id+' mobile menu children lost');
        }
        if(width===1440||width===390) {
          const axe=await new AxeBuilder({page}).analyze();
          const severe=axe.violations.filter(v=>['serious','critical'].includes(v.impact||''));
          fs.writeFileSync(path.join(output,id+'-axe.json'),JSON.stringify({violations:axe.violations},null,2));
          assert(severe.length===0,id+' serious/critical a11y issues',severe.map(v=>v.id));
        }
        await page.screenshot({path:path.join(output,id+'.png'),fullPage:false,animations:'disabled'});
        results.push({id,status:'PASS',metrics:g,pageErrors});
        console.log('PASS '+id+': public live page, geometry, menu, responsive');
      } catch(err) {
        errors.push({id,message:err.message,pageErrors});
        console.error('FAIL '+id+': '+err.message);
        try {await page.screenshot({path:path.join(output,id+'-failed.png'),fullPage:false});} catch {}
      } finally {await context.close();}
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(output,'results.json'),JSON.stringify({base,results,errors},null,2));
}
if(errors.length) throw new Error('D049 live QA failed '+errors.length+'/12 cases');
console.log('PASS: 12/12 D049 production read-only browser cases; screenshots and metrics retained');
