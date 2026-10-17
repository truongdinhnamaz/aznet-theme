/**
 * Throwaway QA experiment: compare native gallery initialization with a browser-only
 * mobile image width cap. Never installed on WordPress or bundled with Theme.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import { parsePilotTargets } from './r6-public-pilot-vitals.mjs';
import { installR6VitalsObserver } from './r6-vitals-diagnostics.mjs';
import { installR6LayoutTimeline } from './r6-layout-timeline.mjs';

const allowedHost = process.env.R6_PILOT_ALLOWED_HOST;
const urls = parsePilotTargets(process.env.R6_PILOT_URLS, allowedHost);
const outputDir = process.env.R6_AB_OUTPUT_DIR || '/tmp/r6-performance/gallery-width-ab';
const browser = await chromium.launch({ headless: true });
const cases = [];

try {
  for (const target of urls) {
    for (const mode of ['control', 'capped']) {
      const context = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 1 });
      const page = await context.newPage();
      const report = { route: new URL(target).pathname, mode, status: 0, error: null };
      try {
        await page.addInitScript({ content: '(' + installR6VitalsObserver.toString() + ')();' });
        await page.addInitScript({ content: '(' + installR6LayoutTimeline.toString() + ')();' });
        if (mode === 'capped') {
          await page.addInitScript(() => {
            const apply = () => {
              if (!document.head || document.querySelector('style[data-r6-width-cap]')) return false;
              const style = document.createElement('style');
              style.setAttribute('data-r6-width-cap', 'true');
              style.textContent = '@media (max-width:767px) { .single-product #main .woocommerce-product-gallery__image img { max-width:100vw !important; } }';
              document.head.appendChild(style);
              return true;
            };
            if (!apply()) {
              const listener = new MutationObserver(() => { if (apply()) listener.disconnect(); });
              listener.observe(document.documentElement || document, { childList: true, subtree: true });
            }
          });
        }
        const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
        report.status = response?.status() || 0;
        await page.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
        await page.waitForTimeout(8500);
        const b = await page.evaluate(() => {
          const geom = globalThis.__aznetR6LayoutTimeline || {};
          const rows = geom.samples || [];
          const peak = (key, field) => Math.max(0, ...rows.map(s => s.nodes?.[key]?.rect?.[field] || 0));
          const image = document.querySelector('.woocommerce-product-gallery__image img');
          const viewport = document.querySelector('.flex-viewport');
          const active = document.querySelector('.woocommerce-product-gallery__image.flex-active-slide img') || image;
          const rect = (el) => el ? { width: Number(el.getBoundingClientRect().width.toFixed(2)), height: Number(el.getBoundingClientRect().height.toFixed(2)) } : null;
          return {
            clsFromTimeline: Number((geom.shifts || []).filter(x=>!x.hadRecentInput).reduce((n,x)=>n+x.value,0).toFixed(5)),
            shiftCount: (geom.shifts || []).length,
            peakImageWidth: peak('mainImage','width'),
            peakGalleryHeight: peak('gallery','height'),
            peakViewportHeight: peak('viewport','height'),
            peakSummaryY: peak('summary','y'),
            samples: rows.length,
            mutations: (geom.mutations || []).length,
            timelineAvailable: Boolean(geom.availability?.layoutShift),
            widthCapInserted: Boolean(document.querySelector('style[data-r6-width-cap]')),
            computedImageMaxWidth: image ? getComputedStyle(image).maxWidth : null,
            finalViewport: rect(viewport),
            finalActiveImage: rect(active),
            finalImageDecoded: Boolean(active && active.complete && active.naturalWidth > 0),
            overflowPx: document.documentElement.scrollWidth - document.documentElement.clientWidth,
          };
        });
        Object.assign(report, b);
        const slug = report.route.replace(/[^a-z0-9]+/gi,'-').slice(0,55);
        fs.mkdirSync(outputDir, { recursive: true });
        await page.screenshot({ path: path.join(outputDir, slug + '-' + mode + '.png'), animations:'disabled' });
        if (report.status !== 200 || !report.timelineAvailable || !report.finalImageDecoded || !report.finalViewport?.height) {
          report.error = 'Public browser QA precondition failed';
        }
      } catch (error) {
        report.error = String(error?.message || error).slice(0,250);
      } finally {
        cases.push(report);
        console.log(JSON.stringify(report));
        await context.close();
      }
    }
  }
} finally {
  await browser.close();
}
fs.mkdirSync(outputDir, { recursive: true });
fs.writeFileSync(path.join(outputDir, 'result.json'), JSON.stringify({ measuredUtc: new Date().toISOString(), cases }, null, 2) + '\n');
if (cases.some(c => c.error)) process.exitCode = 1;
