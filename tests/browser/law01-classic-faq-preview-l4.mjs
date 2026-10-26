import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

/** Browser regression for WordPress-owned Classic Editor FAQ Page #527. */
const baseUrl = (process.env.LAW01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
if (!/^http:\/\/127\.0\.0\.1:8080$/.test(baseUrl)) {
  throw new Error('Classic FAQ browser test is restricted to disposable local WordPress.');
}
const stateDir = process.env.LAW01_STATE_DIR || '/tmp/homepage-law01';
const out = path.join(stateDir, 'classic-faq');
fs.mkdirSync(out, { recursive: true });
const viewports = { '1440x1000': { width: 1440, height: 1000 }, '390x844': { width: 390, height: 844 }, '320x760': { width: 320, height: 760 } };
const browser = await chromium.launch({ headless: true });
const summary = [];
try {
  for (const [name, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    try {
      const response = await page.goto(baseUrl + '/', { waitUntil: 'networkidle' });
      if (response?.status() !== 200) throw new Error(name + ': Homepage HTTP status ' + response?.status());
      const faq = page.locator('.aznet-theme-law01-faq');
      await faq.waitFor();
      const entries = faq.locator('.aznet-theme-law01-faq__items details.wp-block-details');
      if (await entries.count() !== 5) throw new Error(name + ': expected five authored Classic HTML details');
      const titles = (await entries.locator('summary').allTextContents()).map((value) => value.trim());
      for (let i = 1; i <= 5; i++) {
        if (titles[i - 1] !== 'Classic question ' + i) throw new Error(name + ': missing or reordered WordPress FAQ ' + i);
      }
      if (await faq.getByText('Classic question 6').count()) throw new Error(name + ': unbounded sixth FAQ entry');
      if (await faq.locator('#faq-classic-author-hero, h1, script, [onerror]').count()) throw new Error(name + ': duplicated Page hero or unsafe HTML');
      if (await page.locator('main#main h1').count() !== 1) throw new Error(name + ': Homepage H1 regression');
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      if (overflow > 1) throw new Error(name + ': horizontal overflow ' + overflow + 'px');
      const control = entries.first().locator('summary');
      await control.focus();
      if (!(await control.evaluate((node) => document.activeElement === node))) throw new Error(name + ': native summary is not keyboard focusable');
      await page.keyboard.press('Enter');
      if (!(await entries.first().evaluate((node) => node.hasAttribute('open')))) throw new Error(name + ': keyboard cannot expand native FAQ');
      if (!(await entries.first().innerText()).includes('Classic answer 1')) throw new Error(name + ': expanded answer not preserved');
      const axe = await new AxeBuilder({ page }).include('.aznet-theme-law01-faq').analyze();
      const violations = axe.violations.filter((x) => x.impact === 'critical' || x.impact === 'serious');
      if (violations.length) throw new Error(name + ': accessibility violations ' + violations.map((x) => x.id).join(','));
      await page.screenshot({ path: path.join(out, 'faq-classic-' + name + '.png'), fullPage: true });
      summary.push({ viewport: name, count: 5, openByKeyboard: true, overflow, a11yBlocking: 0, status: 'PASS' });
    } finally {
      await context.close();
    }
  }
} finally {
  await browser.close();
}
fs.writeFileSync(path.join(out, 'summary.json'), JSON.stringify(summary, null, 2));
console.log('PASS: Law 01 native Classic FAQ browser responsive/a11y ' + summary.length + '/' + Object.keys(viewports).length);
