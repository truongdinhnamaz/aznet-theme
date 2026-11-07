/**
 * Browser-only RQA-PAGE-01 CSS overlay. Do not edit WordPress or submit forms.
 */
const fs = require('node:fs');
const { chromium } = require('playwright');

(async () => {
  const directory = 'rqa-page-compat-evidence';
  fs.mkdirSync(directory, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const css = fs.readFileSync('assets/css/components/page-curtain-01-authored.css', 'utf8');
  const records = [];
  let failures = 0;
  for (const item of [
    { key: 'about', path: '/gioi-thieu/', hero: '.rqa-about-hero', layout: '.rqa-about-story__grid' },
    { key: 'projects', path: '/cong-trinh/', hero: '.rqa-projects-hero', layout: '.rqa-projects-process__list' },
  ]) {
    for (const width of [320, 390, 768, 1440]) {
      const context = await browser.newContext({
        viewport: { width, height: 900 }, deviceScaleFactor: 1, reducedMotion: 'reduce'
      });
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      const url = 'https://remquocanh.vn' + item.path;
      const record = { key: item.key, width, url };
      const snap = async () => page.evaluate(({hero, layout}) => {
        const measure = (selector) => {
          const e = document.querySelector(selector);
          if (!e) return null;
          const r = e.getBoundingClientRect(), s = getComputedStyle(e);
          return {
            height: Math.round(r.height),
            left: Math.round(r.left), right: Math.round(r.right),
            width: Math.round(r.width), position: s.position,
            display: s.display, objectFit: s.objectFit, color: s.color
          };
        };
        return {
          vw: innerWidth,
          scrollWidth: document.documentElement.scrollWidth,
          hero: measure(hero),
          media: measure(hero + ' [class$="__media"]'),
          image: measure(hero + ' [class$="__media"] img'),
          heading: measure(hero + ' h1'),
          layout: measure(layout),
          h1: document.querySelectorAll('main h1').length
        };
      }, item);
      try {
        const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
        record.httpStatus = response ? response.status() : null;
        await page.waitForTimeout(900);
        record.before = await snap();
        await page.screenshot({ path: directory + '/' + item.key + '-' + width + '-before.png',
          fullPage: true, animations: 'disabled', timeout: 45000 });
        await page.addStyleTag({ content: css });
        record.after = await snap();
        await page.screenshot({ path: directory + '/' + item.key + '-' + width + '-overlay.png',
          fullPage: true, animations: 'disabled', timeout: 45000 });
        const before = record.before, after = record.after;
        const assertions = {
          http200: record.httpStatus === 200,
          baselineUnstyled: before && before.media && before.media.position !== 'absolute',
          absoluteCoverMedia: after && after.media && after.image && after.media.position === 'absolute' && after.image.objectFit === 'cover',
          heroBounded: after && after.hero && after.hero.height >= 400 && after.hero.height <= 900,
          responsiveGrid: after && after.layout && after.layout.display === 'grid',
          singleH1: after && after.h1 === 1,
          noHorizontalOverflow: after && after.scrollWidth <= width + 1,
          noPageError: errors.length === 0
        };
        if (width === 1440) {
          assertions.desktopHeightReduction = before && before.hero && after && after.hero &&
            before.hero.height - after.hero.height >= 180;
        }
        record.assertions = assertions;
        record.errors = errors.slice(0, 5);
        if (Object.values(assertions).some(value => !value)) failures++;
      } catch (e) {
        record.error = String(e.message).slice(0, 350);
        failures++;
      } finally {
        console.log('RQA_PAGE_CSS ' + JSON.stringify(record));
        records.push(record);
        await context.close();
      }
    }
  }
  await browser.close();
  fs.writeFileSync(directory + '/report.json', JSON.stringify({
    source: 'Public Chromium; exact PR CSS injected via addStyleTag; no site mutation',
    checkedAt: new Date().toISOString(), records, failures
  }, null, 2) + '\n');
  if (failures) {
    console.error('FAIL: RQA Page CSS overlay checks ' + failures + ' / ' + records.length);
    process.exitCode = 1;
  } else {
    console.log('PASS: RQA Page CSS overlay 8/8 and bounded responsive presentation');
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
