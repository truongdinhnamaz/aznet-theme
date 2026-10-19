import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

/** Feature-ON real WordPress/browser gate, independent of the retained OFF baseline. */
const base = (process.env.INDUSTRIAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const out = path.join(process.env.INDUSTRIAL01_OUT_DIR || '/tmp/industrial01-pilot', 'catalog-on');
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ headless: true });
const cases = [];

async function assert(condition, message) {
  if (!condition) throw new Error(message);
}

async function noHorizontalOverflow(page, label) {
  const px = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  await assert(px <= 1, label + ': horizontal overflow ' + px + 'px');
}

async function a11y(page, label) {
  const result = await new AxeBuilder({ page }).analyze();
  fs.writeFileSync(path.join(out, label + '-axe.json'), JSON.stringify(result, null, 2));
  const failures = result.violations.filter((v) => ['serious', 'critical'].includes(v.impact || ''));
  await assert(failures.length === 0, label + ': serious/critical axe violations: ' + failures.map(v => v.id).join(', '));
}

try {
  for (const [name, viewport] of Object.entries({
    desktop: { width: 1440, height: 1000 },
    tablet: { width: 1024, height: 900 },
    mobile: { width: 390, height: 844 },
    narrow: { width: 320, height: 800 },
  })) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    await page.goto(base + '/', { waitUntil: 'networkidle' });
    await assert(await page.locator('.aznet-theme-industrial01-hero--with-catalog').count() === 1, name + ': catalog Hero layout missing');
    await assert(await page.locator('link[href*="catalog-navigation.css"]').count() === 1, name + ': catalog asset not scoped exactly once');
    await assert(await page.locator('.aznet-theme-industrial01-hero h1').count() === 1, name + ': existing Hero h1 lost');
    await assert(await page.locator('.aznet-theme-industrial01-hero__content a').count() >= 1, name + ': native Hero CTA missing');
    const aside = page.locator('.aznet-theme-industrial01-hero__catalog');
    await assert(await aside.locator('li').count() >= 19, name + ': WordPress menu item or children lost');
    const group = aside.locator('li.menu-item-has-children').first();
    const nested = group.locator(':scope > ul.sub-menu');
    const submenuToggle = group.locator(':scope > button.aznet-theme-catalog-navigation__submenu-toggle');
    await assert(await submenuToggle.count() === 1, name + ': hierarchical category needs an explicit accessible toggle');
    await assert(await submenuToggle.getAttribute('aria-expanded') === 'false', name + ': category starts collapsed');
    await assert(!(await nested.isVisible()), name + ': child categories must not be permanently expanded');
    await submenuToggle.focus();
    await page.keyboard.press('Enter');
    await assert(await nested.isVisible(), name + ': category must expand by keyboard');
    await assert(await submenuToggle.getAttribute('aria-expanded') === 'true', name + ': expanded state not announced');
    await page.keyboard.press('Escape');
    await assert(!(await nested.isVisible()), name + ': Escape collapses group without losing WordPress menu');
    const desktop = viewport.width > 1100;
    if (desktop) {
      await assert(await aside.isVisible(), name + ': side rail must be visible on desktop');
      await assert(await page.locator('.aznet-theme-site-header__catalog').count() === 0, name + ': duplicate desktop header catalog');
      const rects = await page.evaluate(() => {
        const a = document.querySelector('.aznet-theme-industrial01-hero__catalog')?.getBoundingClientRect();
        const b = document.querySelector('.aznet-theme-industrial01-hero__content')?.getBoundingClientRect();
        if (!a || !b) return null;
        return { asideRight: a.right, contentLeft: b.left, asideHeight: a.height, maxHeight: window.innerHeight * 0.72 };
      });
      await assert(rects && rects.asideRight < rects.contentLeft + 2, name + ': Hero menu overlaps content');
      await assert(rects.asideHeight <= rects.maxHeight + 2, name + ': sidebar exceeds height limit');
      await aside.evaluate((el) => { el.scrollTop = el.scrollHeight; });
      await assert(await aside.locator('a[href]').count() >= 19, name + ': long scrolling catalog links missing');
    } else {
      await assert(!(await aside.isVisible()), name + ': desktop sidebar must hide at tablet/mobile width');
      const trigger = page.locator('[data-aznet-theme-nav-trigger]');
      await trigger.click();
      const panel = page.locator('[data-aznet-theme-nav-panel]');
      await panel.waitFor({ state: 'visible' });
      await assert(await trigger.getAttribute('aria-expanded') === 'true', name + ': panel aria-expanded');
      const summary = panel.locator('.aznet-theme-catalog-navigation--mobile summary');
      await assert(await summary.count() === 1, name + ': mobile catalog disclosure missing');
      await summary.focus();
      await page.keyboard.press('Enter');
      await assert(await summary.evaluate((el) => el.parentElement.open), name + ': mobile disclosure not keyboard operable');
      await assert(await panel.locator('.aznet-theme-catalog-navigation--mobile li').count() >= 19, name + ': mobile WordPress menu lost children');
      const mobileGroup = panel.locator('.aznet-theme-catalog-navigation--mobile li.menu-item-has-children').first();
      const mobileToggle = mobileGroup.locator(':scope > button.aznet-theme-catalog-navigation__submenu-toggle');
      const mobileChildren = mobileGroup.locator(':scope > ul.sub-menu');
      await assert(await mobileToggle.count() === 1 && !(await mobileChildren.isVisible()), name + ': mobile nested WordPress terms must start collapsed');
      await mobileToggle.click();
      await assert(await mobileChildren.isVisible(), name + ': tap expands native child links');
      await mobileToggle.click();
      await assert(!(await mobileChildren.isVisible()), name + ': second tap collapses child links');
      await a11y(page, name + '-open');
      await page.keyboard.press('Escape');
      await assert(await trigger.getAttribute('aria-expanded') === 'false', name + ': Esc should close mobile panel');
      await assert(await trigger.evaluate((el) => el === document.activeElement), name + ': Esc should return focus');
    }
    await noHorizontalOverflow(page, name);
    await a11y(page, name);
    await page.screenshot({ path: path.join(out, name + '-home.png'), fullPage: false });
    await assert(errors.length === 0, name + ': JS page errors: ' + errors.join('; '));
    cases.push({ viewport: name, heroCatalog: true, panel: desktop ? 'desktop-rail' : 'mobile-disclosure', overflow: 0, errors });
    await context.close();
  }

  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await page.goto(base + '/shop/', { waitUntil: 'networkidle' });
  const dropdown = page.locator('.aznet-theme-site-header__catalog');
  await assert(await dropdown.count() === 1 && await dropdown.isVisible(), 'inner-page dropdown missing when mode=homepage-rail');
  await assert(await page.locator('.aznet-theme-industrial01-hero__catalog').count() === 0, 'inner-page must not render Homepage catalog rail');
  await dropdown.locator('summary').focus();
  await page.keyboard.press('Enter');
  await assert(await dropdown.evaluate(el => el.open), 'desktop dropdown keyboard toggle failed');
  await assert(await dropdown.locator('a').count() >= 19, 'desktop dropdown did not consume native WordPress menu');
  const innerToggle = dropdown.locator('li.menu-item-has-children > button.aznet-theme-catalog-navigation__submenu-toggle').first();
  await assert(await innerToggle.count() === 1, 'inner-page hierarchical menu must expose a toggle');
  await innerToggle.click();
  await assert(await innerToggle.getAttribute('aria-expanded') === 'true', 'inner-page nested catalog did not expand');
  await noHorizontalOverflow(page, 'inner-dropdown');
  await a11y(page, 'inner-dropdown');
  await page.screenshot({ path: path.join(out, 'inner-dropdown.png'), fullPage: false });
  cases.push({ route: '/shop/', panel: 'header-dropdown', a11y: 'passed' });
  await context.close();

  const noJs = await browser.newContext({ viewport: { width: 390, height: 844 }, javaScriptEnabled: false });
  const nativePage = await noJs.newPage();
  await nativePage.goto(base + '/', { waitUntil: 'domcontentloaded' });
  const nativeSummary = nativePage.locator('.aznet-theme-catalog-navigation--mobile summary');
  await assert(await nativeSummary.isVisible(), 'no-JS native catalog menu must remain discoverable in mobile panel');
  await nativeSummary.click();
  await assert(await nativeSummary.evaluate(el => el.parentElement.open), 'native details must work without JS');
  await assert(await nativePage.locator('.aznet-theme-catalog-navigation--mobile .sub-menu').first().isVisible(), 'without JS, native child menus must remain visible');
  cases.push({ scenario: 'mobile-no-js', fallback: 'passed' });
  await noJs.close();

  fs.writeFileSync(path.join(out, 'summary.json'), JSON.stringify({ status: 'PASS', cases }, null, 2));
  console.log('PASS: Core vertical catalog + Industrial 01 Hero live-WordPress feature-ON browser QA', JSON.stringify(cases));
} finally {
  await browser.close();
}
