/**
 * RQA public-page diagnostic only. Does not authenticate or mutate the site.
 * Observation is not authority to change WordPress editorial content.
 */
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');

(async () => {
  const output = 'rqa-page-diagnostics';
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ headless: true });
  const records = [];
  const targets = [
    ['gioi-thieu', '/gioi-thieu/'],
    ['cong-trinh', '/cong-trinh/'],
    ['lien-he', '/lien-he/'],
  ];

  for (const [name, route] of targets) {
    for (const width of [390, 1440]) {
      const context = await browser.newContext({
        viewport: { width, height: 900 },
        reducedMotion: 'reduce',
        deviceScaleFactor: 1,
      });
      const page = await context.newPage();
      const pageErrors = [];
      const httpFailures = [];
      page.on('pageerror', (error) => pageErrors.push(String(error.message).slice(0, 200)));
      page.on('response', (response) => {
        if (response.status() >= 400) {
          httpFailures.push({ status: response.status(), url: response.url().slice(0, 200) });
        }
      });
      const record = { page: name, viewport: width, url: 'https://remquocanh.vn' + route };
      try {
        const response = await page.goto(record.url, { waitUntil: 'domcontentloaded', timeout: 45000 });
        record.httpStatus = response ? response.status() : null;
        await page.waitForTimeout(1800);
        record.dom = await page.evaluate(() => {
          const rect = (selector) => {
            const element = document.querySelector(selector);
            if (!element) return null;
            const r = element.getBoundingClientRect();
            const s = getComputedStyle(element);
            return {
              tag: element.tagName.toLowerCase(),
              left: Math.round(r.left),
              right: Math.round(r.right),
              width: Math.round(r.width),
              height: Math.round(r.height),
              display: s.display,
              padding: s.padding,
              background: s.backgroundColor,
              position: s.position,
              fontSize: s.fontSize
            };
          };
          const css = document.getElementById('wp-custom-css');
          const customText = css ? css.textContent : '';
          const overflowing = [...document.querySelectorAll('main *')]
            .filter((e) => {
              const r = e.getBoundingClientRect();
              const s = getComputedStyle(e);
              return s.display !== 'none' && (r.left < -2 || r.right > window.innerWidth + 2) && r.width > 0;
            })
            .slice(0, 15)
            .map((e) => {
              const r = e.getBoundingClientRect();
              return { tag: e.tagName.toLowerCase(), cls: String(e.className).slice(0, 120),
                left: Math.round(r.left), right: Math.round(r.right) };
            });
          return {
            title: document.title,
            bodyClass: document.body.className,
            screenWidth: innerWidth,
            documentScrollWidth: document.documentElement.scrollWidth,
            main: rect('main'),
            content: rect('.aznet-theme-page__content-inner'),
            aboutHero: rect('.rqa-about-hero'),
            projectsHero: rect('.rqa-projects-hero'),
            contactHero: rect('.rqa-contact-hero'),
            firstSection: rect('.aznet-theme-page__content-inner > *'),
            customCssChars: customText.length,
            customCssAboutMatches: (customText.match(/rqa-about/g) || []).length,
            customCssProjectsMatches: (customText.match(/rqa-projects/g) || []).length,
            customCssContactMatches: (customText.match(/rqa-contact/g) || []).length,
            stylesheetUrls: [...document.querySelectorAll('link[rel="stylesheet"]')]
              .map((node) => node.href.replace(location.origin, '')),
            linksContainingBr: document.querySelectorAll('main a br').length,
            navContainingBr: document.querySelectorAll('main nav br').length,
            h1Count: document.querySelectorAll('main h1').length,
            overflowing
          };
        });
        await page.screenshot({
          path: path.join(output, name + '-' + width + '.png'),
          fullPage: true, animations: 'disabled', timeout: 45000
        });
      } catch (error) {
        record.error = String(error.message).slice(0, 350);
      }
      record.pageErrors = pageErrors.slice(0, 10);
      record.httpFailures = httpFailures.slice(0, 15);
      records.push(record);
      console.log('RQA_PAGE_DIAGNOSTIC ' + JSON.stringify(record));
      await context.close();
    }
  }
  await browser.close();
  fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify({
    observedAt: new Date().toISOString(),
    source: 'public Chromium, no authenticated session, no site mutation',
    records
  }, null, 2) + '\n');
  if (records.every((record) => record.error)) process.exitCode = 1;
})().catch((error) => { console.error(error); process.exitCode = 1; });
