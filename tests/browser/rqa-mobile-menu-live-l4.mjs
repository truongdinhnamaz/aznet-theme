/**
 * RQA customer-acceptance L4: check the native Theme submenu on the real public site.
 * All interactions are browser-local. No WordPress login, form submission, REST
 * mutation, product navigation, provider data edit, or Theme production update.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const home = 'https://remquocanh.vn/';
const out = process.env.RQA_MENU_EVIDENCE_DIR || '/tmp/rqa-menu-l4';
const cases = [
  { name: 'mobile-390', width: 390, height: 844, mode: 'mobile', js: true },
  { name: 'mobile-768', width: 768, height: 900, mode: 'mobile', js: true },
  { name: 'desktop-1440', width: 1440, height: 900, mode: 'desktop', js: true },
  { name: 'nojs-mobile-390', width: 390, height: 844, mode: 'mobile', js: false },
];
const categoryPaths = ['/rem-cuon/', '/rem-cau-vong/', '/rem-vai/'];
const summary = {
  purpose: 'RQA native WordPress menu + AZnet Theme public presentation L4 (read-only)',
  source: home,
  ref: process.env.GITHUB_SHA || '',
  results: [],
};
fs.mkdirSync(out, { recursive: true });

const browser = await chromium.launch({headless:true});
try {
  for (const testCase of cases) {
    const context = await browser.newContext({
      viewport: { width: testCase.width, height: testCase.height },
      reducedMotion: 'reduce',
      javaScriptEnabled: testCase.js,
      hasTouch: testCase.mode === 'mobile',
    });
    const page = await context.newPage();
    const record = { ...testCase, passed: false, links: [], error: null };
    try {
      const response = await page.goto(home, { waitUntil: 'domcontentloaded', timeout: 45000 });
      if (!response || response.status() !== 200) {
        throw new Error('Public homepage HTTP status: ' + String(response?.status()));
      }
      const banner = page.locator('[data-aznet-theme-site-header]').first();
      await banner.waitFor({state: 'visible', timeout: 15000});

      const navRoot = testCase.mode === 'mobile'
        ? page.locator('[data-aznet-theme-nav-panel]')
        : page.locator('.aznet-theme-site-header__nav');
      const trigger = page.locator('[data-aznet-theme-nav-trigger]');
      if (testCase.mode === 'mobile') {
        await trigger.waitFor({state:'visible'});
        if (testCase.js) {
          await page.waitForFunction(() =>
            document.querySelector('[data-aznet-theme-nav-panel]')?.hidden === true,
            { timeout: 10000 }
          );
          if (await trigger.getAttribute('aria-expanded') !== 'false')
            throw new Error('Initial mobile Menu aria-expanded is not false');
          await trigger.click();
          if (await trigger.getAttribute('aria-expanded') !== 'true')
            throw new Error('Clicking Menu did not set aria-expanded=true');
        } else {
          if (await navRoot.evaluate(e => e.hidden))
            throw new Error('No-JavaScript fallback menu is unexpectedly hidden');
        }
        if (!(await navRoot.isVisible()))
          throw new Error('Mobile panel is not visible after opening / no-JS fallback');
      } else {
        if (!(await navRoot.isVisible()))
          throw new Error('Native desktop primary navigation is hidden');
      }

      // Avoid depending on a WordPress Page ID or heuristic: the asserted category
      // destinations are observed public menu links, not inferred product identities.
      const direct = navRoot.locator('.aznet-theme-site-header__menu > .menu-item-has-children > a').first();
      if (!(await direct.count()) || (await direct.innerText()).trim() !== 'Sản phẩm')
        throw new Error('Visible native primary menu lacks the Sản phẩm parent link');
      const submenu = navRoot.locator('.aznet-theme-site-header__menu > .menu-item-has-children > .sub-menu').first();
      if (!(await submenu.count()))
        throw new Error('Sản phẩm has no WordPress-native submenu');
      if (testCase.mode === 'desktop') await direct.hover();
      if (!(await submenu.isVisible()))
        throw new Error('Sản phẩm submenu remains visually hidden after hover/mobile open');
      const allLinks = submenu.locator('a[href]');
      const hrefs = await allLinks.evaluateAll(els => els.map(el => ({
        text: (el.textContent || '').trim(),
        href: new URL(el.href).pathname,
        visible: Boolean(el.getBoundingClientRect().width && el.getBoundingClientRect().height)
          && getComputedStyle(el).visibility !== 'hidden',
      })));
      record.links = hrefs;
      for (const dest of categoryPaths) {
        if (!hrefs.some(link => link.href === dest && link.visible && link.text.length > 0))
          throw new Error('Category link missing/not visible: ' + dest);
      }

      if (testCase.mode === 'mobile' && testCase.js) {
        let reachedChildByTab = false;
        for (let tabIndex = 0; tabIndex < 22; tabIndex++) {
          if (await page.evaluate(() => {
            const a = document.activeElement;
            return a instanceof HTMLAnchorElement &&
              a.closest('[data-aznet-theme-nav-panel] .sub-menu') &&
              new URL(a.href).pathname === '/rem-cuon/';
          })) { reachedChildByTab = true; break; }
          await page.keyboard.press('Tab');
        }
        if (!reachedChildByTab)
          throw new Error('Keyboard Tab could not reach Rèm cuốn in the open mobile submenu');
        await page.screenshot({path:path.join(out,testCase.name+'.png')});
        await page.keyboard.press('Escape');
        if (await trigger.getAttribute('aria-expanded') !== 'false' || await navRoot.isVisible())
          throw new Error('Escape did not close the mobile panel');
        if (!(await trigger.evaluate(el => document.activeElement === el)))
          throw new Error('Escape did not return keyboard focus to Menu');
      } else if (testCase.mode === 'desktop') {
        await direct.focus();
        if (!(await submenu.isVisible()))
          throw new Error('Keyboard focus on Sản phẩm fails to expose submenu');
        await page.keyboard.press('Tab');
        const childByTab = await page.evaluate(() => {
          const el = document.activeElement;
          return el instanceof HTMLAnchorElement
            && new URL(el.href).pathname === '/rem-cuon/';
        });
        if (!childByTab)
          throw new Error('Desktop keyboard Tab cannot reach the first Sản phẩm submenu link');
        await page.screenshot({path:path.join(out,testCase.name+'.png')});
      } else {
        await page.screenshot({path:path.join(out,testCase.name+'.png')});
      }

      record.passed = true;
      console.log('PASS RQA native menu '+testCase.name+': categories visible, keyboard/no-JS state verified');
    } catch(err) {
      record.error = String(err);
      console.error('FAIL RQA native menu '+testCase.name+': '+String(err));
    } finally {
      summary.results.push(record);
      await context.close();
    }
  }
} finally {
  await browser.close();
  fs.writeFileSync(path.join(out,'summary.json'),JSON.stringify(summary,null,2));
}
const failures = summary.results.filter(x => !x.passed);
if (failures.length) process.exitCode = 1;
else console.log('PASS: RQA public Theme-owned Sản phẩm submenu 4/4; production content not modified');
