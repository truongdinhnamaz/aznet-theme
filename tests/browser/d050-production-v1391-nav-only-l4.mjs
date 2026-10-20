import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

// Live production read-only browser QA. Never login, POST, save, update
// settings, upload, or alter WordPress data. Scroll and DOM test styling
// exist only in the isolated browser page and do not persist to the site.
const base = (process.env.AZNET_PRODUCTION_BASE_URL || '').replace(/\/$/, '');
if (base !== 'https://minhnguyen.vn') throw Error('Only the approved public Minh Nguyên site may be inspected');
const out = process.env.AZNET_PRODUCTION_QA_OUT || '/tmp/d050-production-v1391-l4';
fs.mkdirSync(out, {recursive:true});
const browser = await chromium.launch({headless:true});
const results = [], failures = [], baselines = new Map(), transientDnsRetries = [];
async function gotoProduction(page, url, name) {
  // Only a transient DNS resolution error can be retried. Never silently
  // bypass HTTP, DOM, ownership, visual, or accessibility test failures.
  for (let attempt = 1; attempt <= 3; attempt++) {
    try {
      return await page.goto(url, {waitUntil:'domcontentloaded',timeout:55000});
    } catch (error) {
      if (!String(error.message).includes('net::ERR_NAME_NOT_RESOLVED') || attempt === 3) throw error;
      transientDnsRetries.push({name, attempt, type:'ERR_NAME_NOT_RESOLVED'});
      console.warn('TRANSIENT DNS RETRY '+name+' attempt '+attempt+'/3 (no assertion bypass)');
      await page.waitForTimeout(1000 * attempt);
    }
  }
  throw Error('Unexpected exhaustion of bounded DNS retry');
}
function assert(ok, id, data='') {
  if (!ok) throw Error(id + (data ? ' :: ' + JSON.stringify(data) : ''));
}
async function metrics(page) {
  return page.evaluate(() => {
    const pos = (s) => {
      const el = document.querySelector(s);
      if (!el) return null;
      const b = el.getBoundingClientRect();
      return {left:b.left,right:b.right,top:b.top,bottom:b.bottom,width:b.width,height:b.height};
    };
    return {
      header:pos('[data-aznet-theme-site-header]'),
      brand:pos('.aznet-theme-site-header__brand'),
      actions:pos('.aznet-theme-site-header__actions'),
      nav:pos('.aznet-theme-site-header__nav'),
      band:pos('.aznet-theme-site-header__catalog-rail-label'),
      dropdown:pos('.aznet-theme-site-header__catalog summary'),
      mobile:pos('.aznet-theme-site-header__mobile-trigger'),
      rail:pos('.aznet-theme-industrial01-hero__catalog'),
      hero:pos('.aznet-theme-industrial01-hero'),
      text:pos('.aznet-theme-industrial01-hero__content'),
      overflow:document.documentElement.scrollWidth-document.documentElement.clientWidth,
      scrollY:scrollY,
      body:document.body.className,
    };
  });
}
try {
  for (const width of [1440,1200,1101,1024,390,320]) {
    for (const pageType of ['home','inner']) {
      const name = pageType + '-' + width;
      const desktop = width > 1100;
      const ctx = await browser.newContext({viewport:{width,height:900},locale:'vi-VN'});
      const page = await ctx.newPage(), jsErrors=[];
      page.on('pageerror', e => jsErrors.push(e.message));
      try {
        const route = pageType === 'home' ? '/' : '/giai-phap/';
        const response = await gotoProduction(page,base+route,name);
        await page.locator('header.aznet-theme-site-header').waitFor({state:'attached',timeout:15000});
        await page.waitForTimeout(450);
        assert(response?.status()===200,name+' HTTP '+response?.status());
        const header = page.locator('[data-aznet-theme-site-header]');
        assert(await header.count()===1,name+' Header not unique');
        assert(await page.locator('body.wp-theme-aznet-theme').count()===1 &&
               !/wpvibe-preview|wpvibe-draft/.test(page.url()),name+' not public active AZnet Theme');
        assert(await header.evaluate(el=>el.classList.contains('aznet-theme-site-header--nav-only')),name+' nav-only mode absent');
        assert(await page.locator('link[href*="catalog-navigation.css"]').count()===1,name+' catalog CSS missing');
        const initial=await metrics(page);
        assert(initial.overflow<=1,name+' initial horizontal overflow',initial);
        assert((await page.locator('.aznet-theme-site-header__brand-title').textContent()||'').trim().length>0,name+' brand missing');
        assert(await page.locator('.aznet-theme-site-header__nav .aznet-theme-site-header__menu li').count()>=8,name+' primary menu lost');
        if(pageType==='home') {
          assert(await page.locator('.aznet-theme-industrial01-hero--with-catalog').count()===1,name+' Hero rail missing');
          assert(await page.locator('.aznet-theme-industrial01-hero__content a[href]').count()>0,name+' Hero CTA missing');
        }
        if(desktop) {
          assert(await page.locator('.aznet-theme-site-header__nav').isVisible(),name+' desktop primary nav hidden');
          assert(await page.locator('.aznet-theme-site-header__brand').isVisible(),name+' desktop brand hidden');
          assert(await page.locator('.aznet-theme-site-header__actions').isVisible(),name+' desktop actions hidden');
          if(pageType==='home') {
            assert(await page.locator('.aznet-theme-site-header__catalog-rail-label').isVisible(),name+' category heading missing');
            assert(await page.locator('.aznet-theme-industrial01-hero__catalog').isVisible(),name+' Hero rail not visible');
            assert(initial.band&&initial.rail&&initial.hero&&initial.text,name+' missing geometry');
            assert(Math.abs(initial.band.left-initial.rail.left)<=3 &&
                   Math.abs(initial.band.right-initial.rail.right)<=3,name+' category rail alignment',initial);
            assert(initial.rail.right<=initial.text.left+3,name+' Hero menu overlaps heading',initial);
            assert(initial.rail.height<initial.hero.height-10,name+' short category rail stretches Hero',initial);
            assert(await page.locator('.aznet-theme-industrial01-hero__catalog li').count()>=19,name+' native menu items lost');
            baselines.set(width,initial);
          } else {
            assert(await page.locator('.aznet-theme-site-header__catalog summary').isVisible(),name+' inner category dropdown missing');
            const home=baselines.get(width);
            assert(home&&initial.dropdown,name+' missing cross-page metrics',initial);
            assert(Math.abs(home.band.left-initial.dropdown.left)<=3 &&
                   Math.abs(home.band.width-initial.dropdown.width)<=3,name+' cross-page category mismatch',{home,inner:initial});
            assert(Math.abs(home.brand.left-initial.brand.left)<=3,name+' cross-page brand mismatch',{home,inner:initial});
            const summary=page.locator('.aznet-theme-site-header__catalog summary');
            await summary.focus();
            await page.keyboard.press('Enter');
            assert(await page.locator('.aznet-theme-site-header__catalog').getAttribute('open')!==null,name+' native category keyboard open failed');
            assert(await page.locator('.aznet-theme-site-header__catalog li').count()>=19,name+' nested catalog items lost');
          }
        } else {
          assert(await page.locator('.aznet-theme-site-header__mobile-trigger').isVisible(),name+' mobile main-menu button missing');
          assert(!(await page.locator('.aznet-theme-site-header__nav').isVisible()),name+' desktop nav visible on mobile');
          if(pageType==='home') assert(!(await page.locator('.aznet-theme-industrial01-hero__catalog').isVisible()),name+' desktop Hero rail visible on mobile');
          const trigger=page.locator('[data-aznet-theme-nav-trigger]');
          await trigger.focus();
          await page.keyboard.press('Enter');
          assert(await trigger.getAttribute('aria-expanded')==='true',name+' mobile keyboard main-menu failed');
          const panel=page.locator('[data-aznet-theme-nav-panel]');
          assert(await panel.isVisible(),name+' mobile panel hidden after keyboard open');
          const details=panel.locator('.aznet-theme-catalog-navigation--mobile');
          await details.locator('summary').focus();
          await page.keyboard.press('Enter');
          assert(await details.getAttribute('open')!==null,name+' native mobile catalog disclosure failed');
          assert(await details.locator('li').count()>=19,name+' mobile catalog nested items absent');
          await page.keyboard.press('Escape');
          assert(await trigger.getAttribute('aria-expanded')==='false',name+' mobile Escape close failed');
        }
        if(width===1440||width===390) {
          const axe=await new AxeBuilder({page}).analyze();
          fs.writeFileSync(path.join(out,name+'-axe.json'),JSON.stringify(axe.violations,null,2));
          assert(!axe.violations.some(v=>['critical','serious'].includes(v.impact||'')),name+' serious/critical accessibility violations',axe.violations.map(x=>x.id));
        }
        await page.screenshot({path:path.join(out,name+'-before.png'),animations:'disabled'});
        // Extend only the browser's live DOM when a Page is short. This never
        // modifies the WordPress database, theme files, or server-side state.
        await page.evaluate(() => {
          const main=document.querySelector('main');
          if(main) main.style.minHeight='2100px';
          window.scrollTo(0,650);
        });
        await page.waitForFunction(()=>window.scrollY>500);
        const scrolled=await metrics(page);
        assert(Math.abs(scrolled.header.height-initial.header.height)<=2,name+' Header height jumps during scroll',{initial,scrolled});
        assert(scrolled.overflow<=1,name+' scrolled horizontal overflow',scrolled);
        const pinned=desktop ? scrolled.nav : scrolled.mobile;
        assert(pinned&&Math.abs(pinned.top)<=3,name+' navigation row did not remain sticky',{initial,scrolled});
        assert(scrolled.brand.bottom<=3,name+' brand/top row stayed sticky',{initial,scrolled});
        if(desktop) {
          assert(scrolled.actions?.bottom<=3,name+' search/account top row stayed sticky',{initial,scrolled});
          if(pageType==='home') assert(scrolled.band&&Math.abs(scrolled.band.top)<=3,name+' category band does not track sticky menu',scrolled);
          else assert(scrolled.dropdown&&Math.abs(scrolled.dropdown.top)<=4,name+' dropdown does not track sticky menu',scrolled);
        }
        await page.screenshot({path:path.join(out,name+'-sticky.png'),animations:'disabled'});
        if(width===1440||width===390) {
          // Synthetic admin-bar class checks CSS offset, not authenticated admin UI.
          await page.evaluate(()=>document.body.classList.add('admin-bar'));
          const admin=await metrics(page);
          const target=width===1440?32:46;
          const nav=desktop?admin.nav:admin.mobile;
          assert(nav&&Math.abs(nav.top-target)<=3,name+' WP admin-bar offset broken',{target,admin});
        }
        assert(jsErrors.length===0,name+' JavaScript errors',jsErrors);
        results.push({name,initial,scrolled,errors:jsErrors});
        console.log('PASS '+name+': two-tier, sticky navigation, keyboard, responsive, no overflow');
      } catch(error) {
        failures.push({name,error:error.message,jsErrors});
        console.error('FAIL '+name+': '+error.message);
        try {await page.screenshot({path:path.join(out,name+'-failed.png'),animations:'disabled'});}catch{}
      } finally {await ctx.close();}
    }
  }
  // Native no-JavaScript navigation stays accessible even if enhancement fails.
  const noJs=await browser.newContext({viewport:{width:390,height:844},javaScriptEnabled:false});
  const noJsPage=await noJs.newPage();
  await gotoProduction(noJsPage,base+'/','no-js-home');
  assert(await noJsPage.locator('[data-aznet-theme-nav-panel]').isVisible(),'no-JS menu disclosure not available');
  assert(await noJsPage.locator('.aznet-theme-catalog-navigation--mobile li').count()>=19,'no-JS native catalog lost');
  await noJs.close();
} finally {
  await browser.close();
  fs.writeFileSync(path.join(out,'results.json'),JSON.stringify({base,results,failures,transientDnsRetries},null,2));
}
if(failures.length) throw Error('nav-only Header L4 failed '+failures.length+'/12 cases');
console.log('PASS: production v1.3.91 nav-only Header L4 12/12 + native no-JS fallback (read-only)');
