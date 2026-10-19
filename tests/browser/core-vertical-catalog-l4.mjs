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
let homepageHeaderBaseline = null;

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
    await assert((await page.locator('.aznet-theme-industrial01-hero h1').innerText()).includes('Giải pháp máy chế biến gỗ'),
      name + ': WordPress-authored long Hero title must render without hardcoded Theme data');
    await assert(await page.locator('.aznet-theme-industrial01-hero__content a').count() >= 1, name + ': native Hero CTA missing');
    const aside = page.locator('.aznet-theme-industrial01-hero__catalog');
    await assert(await aside.locator('li').count() >= 19, name + ': WordPress menu item or children lost');
    const desktop = viewport.width > 1100;
    const railHeaderLabel = page.locator('.aznet-theme-site-header__catalog-rail-label');
    await assert(await railHeaderLabel.count() === 1, name + ': Header must compose exactly one opt-in category label');
    if (desktop) {
      await assert(await railHeaderLabel.isVisible(), name + ': desktop Header category label must be visible');
      await assert(!(await aside.locator('.aznet-theme-industrial01-hero__catalog-heading').isVisible()), name + ': Hero card must not repeat the visible Header category title');
      const railGeometry = await page.evaluate(() => {
        const label = document.querySelector('.aznet-theme-site-header__catalog-rail-label')?.getBoundingClientRect();
        const aside = document.querySelector('.aznet-theme-industrial01-hero__catalog')?.getBoundingClientRect();
        const navigation = document.querySelector('.aznet-theme-site-header__nav')?.getBoundingClientRect();
        const content = document.querySelector('.aznet-theme-industrial01-hero__content')?.getBoundingClientRect();
        if (!label || !aside || !navigation || !content) return null;
        return {
          offsetLeft: Math.abs(label.left - aside.left),
          offsetRight: Math.abs(label.right - aside.right),
          categoryGap: aside.top - label.bottom,
          navOffset: navigation.left - label.right,
          contentOffset: content.left - aside.right,
        };
      });
      await assert(railGeometry && railGeometry.offsetLeft <= 3 && railGeometry.offsetRight <= 3,
        name + ': category Header band and Hero rail must share column edges ' + JSON.stringify(railGeometry));
      await assert(railGeometry && railGeometry.categoryGap >= -2 && railGeometry.categoryGap <= 8,
        name + ': category header must sit directly above Hero rail ' + JSON.stringify(railGeometry));
      await assert(railGeometry && railGeometry.navOffset >= -2 && railGeometry.contentOffset > 0,
        name + ': primary navigation and Hero text must stay alongside category column ' + JSON.stringify(railGeometry));
      await assert(await page.locator('.aznet-theme-site-header__brand-title').isVisible(),
        name + ': WordPress logo and brand name must appear on catalog-enabled desktops');
      const homepageMetrics = await page.evaluate(() => {
        const band = document.querySelector('.aznet-theme-site-header__catalog-rail-label')?.getBoundingClientRect();
        const nav = document.querySelector('.aznet-theme-site-header__nav')?.getBoundingClientRect();
        const aside = document.querySelector('.aznet-theme-industrial01-hero__catalog');
        const asideRect = aside?.getBoundingClientRect();
        const asideNav = aside?.querySelector('nav')?.getBoundingClientRect();
        if (!band || !nav || !asideRect || !asideNav) return null;
        const labelText = document.querySelector('.aznet-theme-site-header__catalog-rail-label span')?.getBoundingClientRect();
        return {
          categoryLeft: band.left, categoryWidth: band.width, categoryHeight: band.height, navLeft: nav.left,
          labelTextOffset: labelText ? labelText.left - band.left : null,
          catalogTextTransform: getComputedStyle(document.querySelector('.aznet-theme-site-header__catalog-rail-label')).textTransform,
          vacantRailHeight: asideRect.bottom - asideNav.bottom,
        };
      });
      await assert(homepageMetrics !== null && homepageMetrics.vacantRailHeight < 24,
        name + ': seven collapsed WordPress categories may not stretch through the Hero ' + JSON.stringify(homepageMetrics));
      if (name === 'desktop') homepageHeaderBaseline = homepageMetrics;
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
      await assert(!(await railHeaderLabel.isVisible()), name + ': desktop category label must hide on mobile');
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
  await assert(await page.locator('.aznet-theme-site-header__brand-title').isVisible(),
    'inner page must display WordPress brand text alongside its native logo');
  const innerMetrics = await page.evaluate(() => {
    const summary = document.querySelector('.aznet-theme-site-header__catalog summary');
    const band = summary?.getBoundingClientRect();
    const nav = document.querySelector('.aznet-theme-site-header__nav')?.getBoundingClientRect();
    const textNode = summary && Array.from(summary.childNodes).find(node =>
      node.nodeType === Node.TEXT_NODE && node.textContent.trim());
    let textOffset = null;
    if (textNode && band) {
      const range = document.createRange();
      range.selectNodeContents(textNode);
      textOffset = range.getBoundingClientRect().left - band.left;
    }
    return band && nav ? {
      categoryLeft: band.left, categoryWidth: band.width, categoryHeight: band.height,
      navLeft: nav.left, labelTextOffset: textOffset,
      catalogTextTransform: getComputedStyle(summary).textTransform,
    } : null;
  });
  await assert(homepageHeaderBaseline && innerMetrics &&
    Math.abs(innerMetrics.categoryLeft - homepageHeaderBaseline.categoryLeft) <= 3 &&
    Math.abs(innerMetrics.categoryWidth - homepageHeaderBaseline.categoryWidth) <= 3 &&
    Math.abs(innerMetrics.categoryHeight - homepageHeaderBaseline.categoryHeight) <= 3 &&
    Math.abs(innerMetrics.navLeft - homepageHeaderBaseline.navLeft) <= 3 &&
    innerMetrics.labelTextOffset !== null &&
    homepageHeaderBaseline.labelTextOffset !== null &&
    Math.abs(innerMetrics.labelTextOffset - homepageHeaderBaseline.labelTextOffset) <= 8 &&
    innerMetrics.catalogTextTransform === homepageHeaderBaseline.catalogTextTransform &&
    innerMetrics.catalogTextTransform === 'uppercase',
    'Homepage and inner-page category/nav heading presentation differs ' +
    JSON.stringify({ homepageHeaderBaseline, innerMetrics }));
  await page.screenshot({ path: path.join(out, 'inner-header-closed.png'), fullPage: false });
  await dropdown.locator('summary').focus();
  await page.keyboard.press('Enter');
  await assert(await dropdown.evaluate(el => el.open), 'desktop dropdown keyboard toggle failed');
  const popoverWidthDelta = await page.evaluate(() => {
    const button = document.querySelector('.aznet-theme-site-header__catalog summary')?.getBoundingClientRect();
    const popover = document.querySelector('.aznet-theme-site-header__catalog > nav')?.getBoundingClientRect();
    return button && popover ? Math.abs(button.width - popover.width) : null;
  });
  await assert(popoverWidthDelta !== null && popoverWidthDelta <= 3,
    'dropdown panel must match native category-band width ' + popoverWidthDelta);
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

  // The owner's actual inner-page screenshot is a WordPress Page, not the
  // Woo Shop archive. Exercise the same native catalog shell on /giai-phap/.
  const solutionsContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const solutionsPage = await solutionsContext.newPage();
  await solutionsPage.goto(base + '/giai-phap/', { waitUntil: 'networkidle' });
  await assert((await solutionsPage.locator('body').innerText()).includes('Bắt đầu từ nhu cầu sản xuất'),
    'native WordPress Giải pháp Page fixture must render its owner-authored content');
  await assert(await solutionsPage.locator('.aznet-theme-site-header__brand-title').isVisible(),
    'Giải pháp Page must retain the same WordPress brand as the Homepage');
  const solutionsMetrics = await solutionsPage.evaluate(() => {
    const catalog = document.querySelector('.aznet-theme-site-header__catalog summary')?.getBoundingClientRect();
    const nav = document.querySelector('.aznet-theme-site-header__nav')?.getBoundingClientRect();
    return catalog && nav
      ? { categoryLeft: catalog.left, categoryWidth: catalog.width, navLeft: nav.left,
          textTransform: getComputedStyle(document.querySelector('.aznet-theme-site-header__catalog summary')).textTransform }
      : null;
  });
  await assert(solutionsMetrics && homepageHeaderBaseline &&
    Math.abs(solutionsMetrics.categoryLeft - homepageHeaderBaseline.categoryLeft) <= 3 &&
    Math.abs(solutionsMetrics.categoryWidth - homepageHeaderBaseline.categoryWidth) <= 3 &&
    Math.abs(solutionsMetrics.navLeft - homepageHeaderBaseline.navLeft) <= 3 &&
    solutionsMetrics.textTransform === homepageHeaderBaseline.catalogTextTransform,
    'WordPress Giải pháp Page Header breaks homepage catalog alignment ' +
    JSON.stringify({ homepageHeaderBaseline, solutionsMetrics }));
  await noHorizontalOverflow(solutionsPage, 'page-giai-phap');
  await a11y(solutionsPage, 'page-giai-phap');
  await solutionsPage.screenshot({ path: path.join(out, 'page-giai-phap.png'), fullPage: false });
  cases.push({ route: '/giai-phap/', panel: 'header-dropdown', a11y: 'passed' });
  await solutionsContext.close();

  // The desktop-to-mobile boundary must keep catalog and primary nav usable
  // without horizontal overflow even when the site has eight primary links.
  for (const width of [1200, 1101]) {
    const narrowContext = await browser.newContext({ viewport: { width, height: 900 } });
    const narrowPage = await narrowContext.newPage();
    await narrowPage.goto(base + '/shop/', { waitUntil: 'networkidle' });
    await assert(await narrowPage.locator('.aznet-theme-site-header__catalog summary').isVisible(),
      width + ': native catalog dropdown missing on narrow desktop');
    await assert(await narrowPage.locator('.aznet-theme-site-header__brand-title').isVisible(),
      width + ': WordPress logo title disappears on narrow desktop');
    await noHorizontalOverflow(narrowPage, 'inner-' + width);
    await a11y(narrowPage, 'inner-' + width);
    await narrowPage.screenshot({ path: path.join(out, 'inner-' + width + '.png'), fullPage: false });
    cases.push({ route: '/shop/', width, panel: 'header-dropdown', a11y: 'passed' });
    await narrowContext.close();
  }

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
