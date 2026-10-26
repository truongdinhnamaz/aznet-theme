/**
 * Issue #522 — anonymous, READ-ONLY browser geometry of the actually deployed
 * Law 01 service Page. No CSS injections, no WordPress credentials or writes.
 * The published Theme version may remain 1.3.92. Do not confuse this evidence
 * with candidate v1.3.98 staging or provider/plugin L5.
 */
import { chromium } from 'playwright';
import { mkdirSync, writeFileSync } from 'node:fs';

const url = 'https://lstamduchn.vn/dich-vu/tu-van-phap-luat/';
const marker = 'AZNET_LAW01_ISSUE522_LIVE_CSS_HOTFIX_20261026';
const widthList = [320, 390, 768, 1024, 1440, 1920];
const out = '/tmp/law01-live-featured-522';
const branch = 'refs/heads/test/law01-live-featured-522-l4-20261026';
if (process.env.GITHUB_REF !== branch) {
  throw Error('QA-only branch guard rejected execution: ' + process.env.GITHUB_REF);
}
mkdirSync(out, { recursive: true });
const report = { url, testedCommit: process.env.GITHUB_SHA || '', testedAt: new Date().toISOString(),
  scope: 'anonymous public production READ ONLY; no injected CSS', checks: [], failures: [] };
const browser = await chromium.launch({ headless: true, channel: 'chrome' });
try {
  for (const width of widthList) {
    const context = await browser.newContext({
      viewport: {width, height: 930}, deviceScaleFactor: 1, reducedMotion: 'reduce'
    });
    const page = await context.newPage();
    page.setDefaultTimeout(15000);
    try {
      const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
      if (!response || response.status() !== 200) {
        throw Error('BLOCKED_NETWORK_OR_HTTP: ' + (response ? response.status() : 'no response'));
      }
      if (new URL(page.url()).hostname !== 'lstamduchn.vn') {
        throw Error('BLOCKED_REDIRECT: unexpected origin ' + page.url());
      }
      const hero = page.locator('article.aznet-theme-page--service-detail header.aznet-theme-page__header');
      try {
        await hero.waitFor({ state: 'visible', timeout: 4500 });
      } catch (error) {
        const dom = await page.evaluate(() => ({
          title: document.title,
          finalUrl: location.href,
          bodyText: (document.body?.innerText || '').slice(0, 700),
          bodyClass: document.body?.className || '',
          hasMain: !!document.querySelector('#main'),
          hasArticle: !!document.querySelector('article'),
          hasWpCustomCss: !!document.querySelector('#wp-custom-css'),
          htmlBytes: document.documentElement?.outerHTML.length || 0
        }));
        await page.screenshot({path: out + '/dom-diagnostic-' + width + '.png'}).catch(() => {});
        throw Error('BLOCKED_BROWSER_DOM: page HTTP 200 but target service header not found: ' + JSON.stringify(dom));
      }
      const img = page.locator('article.aznet-theme-page--service-detail .aznet-theme-page__featured img').first();
      await img.waitFor({ state: 'visible', timeout: 20000 });
      await page.waitForFunction(() => {
        const el = document.querySelector('article.aznet-theme-page--service-detail .aznet-theme-page__featured img');
        return !!el && el.complete && el.naturalWidth > 0 && el.naturalHeight > 0;
      }, null, {timeout: 20000});
      const data = await page.evaluate((marker) => {
        const css = document.querySelector('#wp-custom-css')?.textContent || '';
        const im = document.querySelector('article.aznet-theme-page--service-detail .aznet-theme-page__featured img');
        const fig = im?.closest('.aznet-theme-page__featured');
        const header = im?.closest('.aznet-theme-page__header');
        if (!im || !fig || !header) throw Error('Required WordPress service image DOM missing');
        const r = im.getBoundingClientRect(), f = fig.getBoundingClientRect();
        const style = getComputedStyle(im);
        const styleMarkup = [...document.styleSheets].map(s => s.href).filter(Boolean);
        return {
          requestedViewport: innerWidth,
          url: location.href,
          customCssMarkerCount: css.split(marker).length - 1,
          customCssRulePresent: css.includes('.aznet-theme-page--service-detail .aznet-theme-page__featured img') &&
            css.includes('width: auto;') && css.includes('max-width: 100%;') && css.includes('height: auto;'),
          naturalWidth: im.naturalWidth, naturalHeight: im.naturalHeight,
          htmlWidth: im.getAttribute('width'), htmlHeight: im.getAttribute('height'),
          renderedWidth: r.width, renderedHeight: r.height,
          figureWidth: f.width, headerWidth: header.getBoundingClientRect().width,
          computedWidth: style.width, computedMaxWidth: style.maxWidth, computedHeight: style.height,
          imageComplete: im.complete, imageCurrentSrc: im.currentSrc,
          documentScrollWidth: document.documentElement.scrollWidth,
          viewportWidth: innerWidth, stylesheetCount: styleMarkup.length,
          imageAlt: im.alt
        };
      }, marker);
      const errors = [];
      if (data.customCssMarkerCount !== 1 || !data.customCssRulePresent) errors.push('Published WordPress Additional CSS hotfix absent or duplicate');
      if (!data.imageComplete || data.naturalWidth < 1 || data.naturalHeight < 1) errors.push('Native WordPress featured image failed to load');
      if (data.naturalWidth > 0 && data.renderedWidth > Math.min(data.naturalWidth, data.figureWidth) + 1.2) {
        errors.push('BITMAP_UPSCALED: image ' + data.naturalWidth + 'px rendered ' + data.renderedWidth + 'px in figure ' + data.figureWidth + 'px');
      }
      const expectedHeight = data.renderedWidth * data.naturalHeight / data.naturalWidth;
      if (!Number.isFinite(expectedHeight) || Math.abs(data.renderedHeight - expectedHeight) > 1.2) {
        errors.push('Aspect ratio distorted: height ' + data.renderedHeight + ' vs ' + expectedHeight);
      }
      if (data.renderedWidth < 1 || data.renderedHeight < 1) errors.push('Image rendered with empty geometry');
      if (data.requestedViewport !== width) errors.push('Unexpected emulated viewport width');
      report.checks.push({width, ...data, errors});
      if (errors.length) report.failures.push({width, errors});
      console.log('LAW01_LIVE_IMAGE ' + JSON.stringify({
        width, sourceWidth: data.naturalWidth, renderedWidth: data.renderedWidth,
        renderedHeight: data.renderedHeight, figureWidth: data.figureWidth,
        hotfix: data.customCssMarkerCount, errors
      }));
      if ([320, 390, 1024, 1440].includes(width)) {
        await hero.screenshot({path: out + '/service-header-' + width + '.png', animations: 'disabled'});
      }
    } catch (error) {
      const message = String(error);
      report.failures.push({width, errors: [message]});
      report.checks.push({width, errors: [message]});
      console.error('LAW01_LIVE_IMAGE_ERROR ' + JSON.stringify({width, message}));
      if (message.includes('BLOCKED_BROWSER_DOM')) break;
    } finally {
      await context.close();
    }
  }
} finally {
  await browser.close();
}
writeFileSync(out + '/measurements.json', JSON.stringify(report, null, 2));
console.log('LAW01_LIVE_IMAGE_SUMMARY ' + JSON.stringify({
  viewports: report.checks.length, failures: report.failures.length, failed: report.failures
}));
if (report.failures.length) process.exit(1);
console.log('PASS: production public browser image geometry 6/6 (no upscaling, original ratio)');
