/** Real WordPress 6.9 responsive QA for the forward-ported Wellness inner Page. */
import fs from 'node:fs';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import { probeWellnessInnerLayout } from './wellness01-inner-layout-probe.mjs';

const base = process.env.WELLNESS_BASE_URL || 'http://127.0.0.1:8080';
const id = process.env.WELLNESS_INNER_PAGE_ID;
const out = process.env.WELLNESS_EVIDENCE_DIR || '/tmp/wellness01-native';
if (!/^\d+$/.test(id || '')) throw Error('WELLNESS_INNER_PAGE_ID must be a real disposable WordPress Page ID');
fs.mkdirSync(out, { recursive: true });
const browser = await chromium.launch({ headless: true });
const widths = [390, 768, 1440];
const failures = [];
const results = [];
try {
  for (const width of widths) {
    const context = await browser.newContext({ viewport: { width, height: width === 390 ? 844 : 1000 }, reducedMotion: 'reduce' });
    const page = await context.newPage();
    const errors = [];
    page.on('pageerror', e => errors.push(e.message));
    const result = { width };
    const response = await page.goto(base + '/?page_id=' + id, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) failures.push(width + ' WordPress Page HTTP status unexpected');
    result.layout = await page.evaluate(probeWellnessInnerLayout);
    failures.push(...result.layout.failures.map(x => width + ' ' + x));
    const state = await page.evaluate(() => {
      const foot = document.querySelectorAll('footer.aznet-theme-site-footer');
      const header = document.querySelectorAll('header.aznet-theme-site-header');
      const css = [...document.querySelectorAll('link[rel=stylesheet]')].map(e => e.href);
      const f = foot[0], styles = f ? getComputedStyle(f) : null;
      return {
        footerCount: foot.length, headerCount: header.length,
        footerBackground: styles?.backgroundColor || '',
        footerColor: styles?.color || '',
        contentinfoLandmarks: document.querySelectorAll('footer[role=contentinfo],footer.aznet-theme-site-footer').length,
        pageCssLoaded: css.some(x => x.includes('/page-wellness-01.css?ver=')),
        homepageCssLoaded: css.some(x => x.includes('/homepage-wellness-01.css?ver=')),
        bodyWidth: document.documentElement.scrollWidth, clientWidth: document.documentElement.clientWidth,
        h1: document.querySelectorAll('main h1').length
      };
    });
    result.state = state;
    if (state.footerCount !== 1 || state.headerCount !== 1 || state.h1 !== 1) failures.push(width + ' missing shared shell or single H1');
    if (!state.pageCssLoaded || state.homepageCssLoaded) failures.push(width + ' scoped Wellness Page CSS boundary missing');
    if (state.bodyWidth > state.clientWidth + 1) failures.push(width + ' document overflow');
    if (errors.length) failures.push(width + ' JS pageerrors: ' + errors.join('; '));
    const home = await page.goto(base + '/', { waitUntil: 'networkidle' });
    if (!home || home.status() !== 200) failures.push(width + ' WordPress Homepage HTTP status unexpected');
    const footerOnHome = await page.locator('footer.aznet-theme-site-footer').evaluate(el => {
      const s = getComputedStyle(el); return { background: s.backgroundColor, color: s.color };
    });
    if (state.footerBackground !== footerOnHome.background || state.footerColor !== footerOnHome.color) {
      failures.push(width + ' Footer visual changes between Homepage and inner Page');
    }
    // Run axe on the actual inner Page, not on a synthetic HTML preview.
    await page.goto(base + '/?page_id=' + id, { waitUntil: 'networkidle' });
    const audit = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa']).analyze();
    result.axe = audit.violations.map(v => ({ id: v.id, impact: v.impact, nodes: v.nodes.length }));
    for (const violation of audit.violations) {
      if (violation.impact === 'critical' || violation.impact === 'serious') {
        failures.push(width + ' axe ' + violation.id + ': ' + violation.impact);
      }
    }
    await page.screenshot({ path: out + '/wellness-inner-' + width + '.png', fullPage: true });
    results.push(result);
    await context.close();
  }
} finally {
  await browser.close();
}
fs.writeFileSync(out + '/wellness01-forwardport-inner-l4.json', JSON.stringify({ results, failures }, null, 2));
if (failures.length) throw Error('Wellness 01 inner Page L4 failed: ' + failures.join(' | '));
console.log('PASS: Wellness inner WordPress Page 390/768/1440; shared Footer styling + source-aware CSS + axe serious/critical=0');
