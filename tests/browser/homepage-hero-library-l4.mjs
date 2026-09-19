import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.LAW01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.LAW01_STATE_DIR || '/tmp/homepage-law01';
const cases = [
  { name: 'desktop', width: 1440, height: 1000 },
  { name: 'mobile', width: 390, height: 844 },
];
const results = [];
const failures = [];

const browser = await chromium.launch({ headless: true });
try {
  for (const item of cases) {
    const context = await browser.newContext({ viewport: { width: item.width, height: item.height } });
    const page = await context.newPage();
    try {
      await page.goto(baseUrl + '/', { waitUntil: 'networkidle' });
      const hero = page.locator('.aznet-theme-law01-hero--library.aznet-theme-law01-hero--media-left');
      if (await hero.count() !== 1) throw new Error('D-030 media-left Hero Library surface missing');
      const title = ((await hero.locator('h1').textContent()) || '').trim();
      if (title !== 'Hero Library WordPress') throw new Error('Synced Hero block content did not win source precedence: ' + title);
      if (await hero.locator('.aznet-theme-law01-hero__grid').count()) throw new Error('Legacy Hero grid must not render when synced Hero content is valid');
      if (await hero.locator('.aznet-theme-law01-hero__trust-item').count() !== 4) throw new Error('Law 01 trust presentation must remain below Hero Library content');
      if (await hero.locator('.aznet-theme-homepage-hero-content__media-help').isVisible()) throw new Error('Hero media authoring hint must not leak into public presentation');

      const metrics = await hero.evaluate((node) => {
        const layout = node.querySelector('.aznet-theme-homepage-hero-content__layout');
        const copy = node.querySelector('.aznet-theme-homepage-hero-content__copy');
        const media = node.querySelector('.aznet-theme-homepage-hero-content__media');
        return {
          overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
          columns: layout ? getComputedStyle(layout).gridTemplateColumns : '',
          copyOrder: copy ? getComputedStyle(copy).order : '',
          mediaOrder: media ? getComputedStyle(media).order : '',
        };
      });
      if (metrics.overflow > 1) throw new Error('Hero Library horizontal overflow: ' + metrics.overflow);
      if (metrics.copyOrder !== '2' || metrics.mediaOrder !== '1') throw new Error('media-left variant order mismatch: ' + JSON.stringify(metrics));
      if (item.width >= 960 && !metrics.columns.includes('px')) throw new Error('desktop Hero Library grid columns unavailable');
      if (item.width < 960 && metrics.columns.split(' ').length > 1) throw new Error('mobile Hero Library must collapse to one column');

      const axe = await new AxeBuilder({ page }).include('.aznet-theme-law01-hero--library').analyze();
      const serious = axe.violations.filter((v) => ['critical', 'serious'].includes(v.impact || ''));
      fs.writeFileSync(path.join(stateDir, 'hero-library-' + item.name + '-axe.json'), JSON.stringify(axe, null, 2));
      if (serious.length) throw new Error('Hero Library axe violations: ' + serious.map((v) => v.id).join(','));

      results.push({ name: item.name, title, metrics });
    } catch (error) {
      failures.push(item.name + ': ' + (error instanceof Error ? error.message : String(error)));
      await page.screenshot({ path: path.join(stateDir, 'hero-library-' + item.name + '-failure.png'), fullPage: true }).catch(() => {});
    } finally {
      await context.close();
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(stateDir, 'hero-library-browser-summary.json'), JSON.stringify({ results, failures }, null, 2));
if (failures.length) {
  console.error(failures.join('\n'));
  process.exit(1);
}
console.log('PASS: D-030 Homepage Hero Library browser matrix ' + results.length + '/' + results.length);
