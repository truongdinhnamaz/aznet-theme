/**
 * Read-only A/B experiment: is preloading the WordPress-owned Hero background
 * helpful under the same emulated 4G/CPU conditions?
 *
 * Both arms intercept and replay the public HTML document identically.
 * Treatment adds one <link rel=preload as=image fetchpriority=high> in <head>.
 * No server or WordPress state is modified. NOT a CrUX/Lighthouse equivalence.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const target = 'https://minhnguyen.vn/';
const heroImage = 'https://minhnguyen.vn/wp-content/uploads/2026/09/Minh-Nguyen-Industrial-Hero.jpg';
const dest = '/tmp/aznet-industrial01-lcp';
const variants = ['baseline', 'preload-hero', 'baseline', 'preload-hero', 'baseline', 'preload-hero'];
fs.mkdirSync(dest, { recursive: true });

const browser = await chromium.launch({ headless: true, args: ['--disable-background-networking'] });
const records = [];

for (const [i, variant] of variants.entries()) {
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    isMobile: true, hasTouch: true, locale: 'vi-VN',
    deviceScaleFactor: 1, reducedMotion: 'reduce',
    serviceWorkers: 'block',
  });
  const page = await context.newPage();
  const cdp = await context.newCDPSession(page);
  await cdp.send('Network.enable');
  await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });
  await cdp.send('Network.emulateNetworkConditions', {
    offline: false, latency: 150, downloadThroughput: 180 * 1024,
    uploadThroughput: 80 * 1024, connectionType: 'cellular4g',
  });
  await cdp.send('Emulation.setCPUThrottlingRate', { rate: 4 });

  await page.addInitScript(() => {
    window.__lcpEntries = [];
    try {
      new PerformanceObserver((list) => {
        for (const e of list.getEntries()) {
          window.__lcpEntries.push({
            startTime: Math.round(e.startTime),
            url: e.url || '',
            tag: e.element?.tagName || '',
            className: String(e.element?.className || '').slice(0, 100),
            size: e.size || 0,
          });
        }
      }).observe({ type: 'largest-contentful-paint', buffered: true });
    } catch (error) { window.__lcpError = String(error); }
  });

  let injected = false;
  await page.route(target, async (route) => {
    const response = await route.fetch();
    const original = await response.text();
    const preload = '<link rel="preload" as="image" fetchpriority="high" href="' + heroImage + '">';
    const html = variant === 'preload-hero'
      ? original.replace(/<head(\s[^>]*)?>/i, (head) => { injected = true; return head + preload; })
      : original;
    await route.fulfill({
      status: response.status(),
      body: html,
      headers: { 'content-type': 'text/html; charset=UTF-8', 'cache-control': 'no-store' },
    });
  });

  let record;
  try {
    const response = await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(8000);
    record = await page.evaluate((heroUrl) => {
      const nav = performance.getEntriesByType('navigation')[0];
      const assets = performance.getEntriesByType('resource');
      const hero = assets.filter((r) => r.name === heroUrl);
      const fonts = assets.filter((r) => /\.woff2?(?:[?#]|$)/i.test(r.name));
      const thumb = assets.filter((r) => /247x296/i.test(r.name));
      return {
        lcp: (window.__lcpEntries || []).at(-1),
        lcpError: window.__lcpError || null,
        ttfb: nav ? Math.round(nav.responseStart - nav.requestStart) : null,
        firstPaint: performance.getEntriesByType('paint')
          .find((p) => p.name === 'first-contentful-paint')?.startTime || null,
        heroRequests: hero.map((r) => ({
          start: Math.round(r.startTime), end: Math.round(r.responseEnd),
          duration: Math.round(r.duration), bytes: r.transferSize || 0,
          initiatedBy: r.initiatorType,
        })),
        thumbnailRequests: thumb.map((r) => ({
          start: Math.round(r.startTime), end: Math.round(r.responseEnd),
          bytes: r.transferSize || 0,
        })),
        fontRequests: fonts.map((r) => ({
          start: Math.round(r.startTime), end: Math.round(r.responseEnd),
          bytes: r.transferSize || 0,
        })),
        renderedHero: !!document.querySelector('.aznet-theme-industrial01-hero--with-background'),
      };
    }, heroImage);
    record.status = response?.status() || 0;
    record.variant = variant;
    record.order = i + 1;
    record.preloadInserted = injected;
  } catch (error) {
    record = { variant, order: i + 1, error: String(error) };
  } finally {
    records.push(record);
    console.log(JSON.stringify({
      sample: i + 1, variant, status: record.status,
      lcp: record.lcp?.startTime, lcpType: record.lcp?.tag,
      ttfb: record.ttfb, hero: record.heroRequests?.at(0),
      preloaded: record.preloadInserted, error: record.error,
    }));
    await context.close();
  }
}
await browser.close();

function median(values) {
  const sorted = values.filter(Number.isFinite).sort((a, b) => a - b);
  if (!sorted.length) return null;
  return (sorted[Math.floor((sorted.length - 1) / 2)] + sorted[Math.floor(sorted.length / 2)]) / 2;
}
const grouped = Object.fromEntries(['baseline','preload-hero'].map((variant) => {
  const items = records.filter((r) => r.variant === variant && !r.error);
  return [variant, {
    count: items.length,
    medianLcpMs: median(items.map((r) => r.lcp?.startTime)),
    medianTtfbMs: median(items.map((r) => r.ttfb)),
    medianHeroStartMs: median(items.map((r) => r.heroRequests?.at(0)?.start)),
    medianHeroEndMs: median(items.map((r) => r.heroRequests?.at(0)?.end)),
  }];
}));
const file = path.join(dest, 'preload-experiment.json');
fs.writeFileSync(file, JSON.stringify({measuredAt:new Date().toISOString(),target,heroImage,grouped,records},null,2));
console.log('AZNET_PRELOAD_AB ' + JSON.stringify(grouped));
if (records.some((r) => r.error) || records.some((r) => r.variant === 'preload-hero' && !r.preloadInserted)) process.exitCode = 1;
