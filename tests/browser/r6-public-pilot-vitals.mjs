/**
 * Explicitly opt-in, read-only public pilot diagnostic. Never enabled by default.
 * This script is a QA runner, not a Theme or plugin runtime feature.
 */
import fs from 'node:fs';
import path from 'node:path';
import { pathToFileURL } from 'node:url';
import { installR6VitalsObserver, normalizeR6Resource, publicR6ResourceTiming } from './r6-vitals-diagnostics.mjs';
import { installR6LayoutTimeline } from './r6-layout-timeline.mjs';

export function parsePilotTargets(raw, approvedHost) {
  if (!approvedHost || !/^[a-z0-9.-]+$/i.test(approvedHost)) throw new Error('R6_PILOT_ALLOWED_HOST required');
  const candidates = String(raw || '').split(',').map((entry) => entry.trim()).filter(Boolean);
  if (candidates.length < 1 || candidates.length > 3) throw new Error('Expected 1-3 approved pilot pages');
  const unique = new Set();
  for (const input of candidates) {
    const parsed = new URL(input);
    if (parsed.protocol !== 'https:' || parsed.hostname !== approvedHost || parsed.port ||
        parsed.username || parsed.password || parsed.search || parsed.hash) {
      throw new Error('Unapproved or sensitive pilot URL');
    }
    unique.add(parsed.href);
  }
  return [...unique];
}

async function capturePilotPages() {
  const targets = parsePilotTargets(process.env.R6_PILOT_URLS, process.env.R6_PILOT_ALLOWED_HOST);
  const captureLayoutTimeline = process.env.R6_PILOT_LAYOUT_TIMELINE === '1';
  const cssTrial = process.env.R6_PILOT_CSS_TRIAL || 'none';
  if (!['none', 'cap-viewport', 'intrinsic'].includes(cssTrial)) throw new Error('Unapproved CSS-only trial');
  const outputPath = process.env.R6_PILOT_OUTPUT || '/tmp/r6-performance/public-pilot-summary.json';
  const { chromium } = await import('playwright');
  const browser = await chromium.launch({ headless: true });
  const cases = [];
  try {
    for (const target of targets) {
      const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
      const page = await context.newPage();
      const record = { url: target, viewport: '390x844', status: 0, error: null, cssTrial };
      try {
        if (cssTrial !== 'none') {
          await page.addInitScript((mode) => {
            // QA-only ephemeral CSS override in the isolated browser; no server write.
            const text = mode === 'cap-viewport'
              ? '.single-product #main .woocommerce-product-gallery__image img { max-width: 100vw !important; }'
              : '.single-product #main .woocommerce-product-gallery__image img { width: auto !important; max-width: 100% !important; }';
            const style = document.createElement('style');
            style.textContent = text;
            const attach = () => (document.head || document.documentElement).appendChild(style);
            if (document.documentElement) attach();
            else document.addEventListener('DOMContentLoaded', attach, { once: true });
          }, cssTrial);
        }
        await page.addInitScript({ content: '(' + installR6VitalsObserver.toString() + ')();' });
        if (captureLayoutTimeline) await page.addInitScript({ content: '(' + installR6LayoutTimeline.toString() + ')();' });
        await page.addInitScript(() => {
          globalThis.__r6PilotCls = 0;
          try {
            new PerformanceObserver((list) => {
              for (const entry of list.getEntries()) {
                if (!entry.hadRecentInput) globalThis.__r6PilotCls += entry.value;
              }
            }).observe({ type: 'layout-shift', buffered: true });
          } catch {
            // Missing observer support is reported in vitalDiagnostics.
          }
        });
        const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
        record.status = response?.status() || 0;
        await page.waitForLoadState('load', { timeout: 30000 }).catch(() => {});
        await page.waitForTimeout(captureLayoutTimeline ? 9000 : 3500);
        const browserRecord = await page.evaluate(() => ({
          cls: Number((globalThis.__r6PilotCls || 0).toFixed(5)),
          vitalDiagnostics: globalThis.__aznetR6Vitals || null,
          layoutTimeline: globalThis.__aznetR6LayoutTimeline || null,
          resources: performance.getEntriesByType('resource').map((entry) => ({
            name: entry.name,
            initiatorType: entry.initiatorType,
            transferSize: entry.transferSize || 0,
            encodedBodySize: entry.encodedBodySize || 0,
            decodedBodySize: entry.decodedBodySize || 0,
            duration: entry.duration || 0,
            startTime: entry.startTime || 0,
            fetchStart: entry.fetchStart || 0,
            responseStart: entry.responseStart || 0,
            responseEnd: entry.responseEnd || 0,
          })),
        }));
        record.cls = browserRecord.cls;
        record.vitalDiagnostics = browserRecord.vitalDiagnostics;
        if (captureLayoutTimeline) record.layoutTimeline = browserRecord.layoutTimeline;
        record.resourceTimings = browserRecord.resources.map(normalizeR6Resource)
          .sort((a, b) => a.startTimeMs - b.startTimeMs)
          .slice(0, 300).map(publicR6ResourceTiming);
        record.resourceTimingsTruncated = browserRecord.resources.length > 300;
        if (record.status !== 200) record.error = 'Unexpected HTTP ' + record.status;
        if (!record.vitalDiagnostics?.availability?.layoutShift) record.error = 'LayoutShift observer unavailable';
      } catch (error) {
        record.error = String(error?.message || error).slice(0, 300);
      } finally {
        cases.push(record);
        await context.close();
      }
      const shiftCount = record.vitalDiagnostics?.layoutShifts?.length ?? 0;
      console.log(target + ': HTTP ' + record.status + ', CLS ' + (record.cls ?? 'unknown') + ', shift entries ' + shiftCount + ', status ' + (record.error || 'observed'));
    }
  } finally {
    await browser.close();
  }
  fs.mkdirSync(path.dirname(outputPath), { recursive: true });
  fs.writeFileSync(outputPath, JSON.stringify({ measuredAtUtc: new Date().toISOString(), cases }, null, 2) + '\n');
  if (cases.some((entry) => entry.error)) process.exitCode = 1;
}

if (process.argv[1] && import.meta.url === pathToFileURL(path.resolve(process.argv[1])).href) {
  await capturePilotPages();
}
