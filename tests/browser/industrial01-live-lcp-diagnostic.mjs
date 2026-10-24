/**
 * Read-only mobile LCP diagnostic against a public Industrial 01 pilot.
 * QA-only; no WordPress state, cookies, credentials, or plugin storage.
 * Emulated measurements are diagnostic, NOT PageSpeed/CrUX equivalents.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const url = process.env.AZNET_DIAGNOSTIC_URL || 'https://minhnguyen.vn/';
const output = process.env.AZNET_DIAGNOSTIC_OUTPUT || '/tmp/aznet-industrial01-lcp';
const variants = ['baseline', 'without-thumbnails', 'baseline', 'without-hero', 'baseline', 'without-thumbnails', 'without-hero'];
const scriptRoot = new URL(url);
if (scriptRoot.protocol !== 'https:' || !['minhnguyen.vn', 'www.minhnguyen.vn'].includes(scriptRoot.hostname)) {
  throw new Error('Only the owner-approved public pilot may be probed.');
}

fs.mkdirSync(output, { recursive: true });
const browser = await chromium.launch({ headless: true, args: ['--disable-background-networking'] });
const records = [];
let errors = 0;

for (const [i, variant] of variants.entries()) {
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    deviceScaleFactor: 1,
    isMobile: true,
    hasTouch: true,
    serviceWorkers: 'block',
    locale: 'vi-VN',
    reducedMotion: 'reduce',
  });
  const page = await context.newPage();
  const session = await context.newCDPSession(page);
  await session.send('Network.enable');
  await session.send('Network.setCacheDisabled', { cacheDisabled: true });
  await session.send('Network.emulateNetworkConditions', {
    offline: false,
    latency: 150,
    downloadThroughput: 180 * 1024,
    uploadThroughput: 80 * 1024,
    connectionType: 'cellular4g',
  });
  await session.send('Emulation.setCPUThrottlingRate', { rate: 4 });

  await page.addInitScript(() => {
    window.__aznet_lcp = [];
    try {
      new PerformanceObserver((list) => {
        for (const e of list.getEntries()) {
          const el = e.element;
          window.__aznet_lcp.push({
            renderTime: Math.round(e.renderTime || 0),
            loadTime: Math.round(e.loadTime || 0),
            startTime: Math.round(e.startTime || 0),
            size: e.size || 0,
            url: e.url || '',
            tag: el?.tagName || '',
            id: el?.id || '',
            className: String(el?.className || '').slice(0, 200),
            text: (el?.textContent || '').trim().slice(0, 200),
            outer: (el?.outerHTML || '').slice(0, 600),
          });
        }
      }).observe({ type: 'largest-contentful-paint', buffered: true });
    } catch (error) {
      window.__aznet_lcp_error = String(error);
    }
  });

  const denied = [];
  if (variant !== 'baseline') {
    await page.route('**/*', async (route) => {
      const resource = route.request();
      const src = resource.url();
      const isHero = /Minh-Nguyen-Industrial-Hero\.jpg/i.test(src);
      const isThumbnail = resource.resourceType() === 'image' && /247x296(?:\.|%)/i.test(src);
      if ((variant === 'without-hero' && isHero) || (variant === 'without-thumbnails' && isThumbnail)) {
        denied.push(src);
        await route.abort('blockedbyclient');
      } else {
        await route.continue();
      }
    });
  }

  const requestErrors = [];
  page.on('requestfailed', (req) => {
    const error = req.failure();
    if (!denied.includes(req.url())) requestErrors.push({ url: req.url().slice(0, 170), error: error?.errorText });
  });

  let result;
  try {
    const start = Date.now();
    const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(9200);

    result = await page.evaluate(() => {
      const nav = performance.getEntriesByType('navigation')[0];
      const assets = performance.getEntriesByType('resource').map((r) => ({
        name: r.name,
        initiator: r.initiatorType,
        start: Math.round(r.startTime),
        responseEnd: Math.round(r.responseEnd),
        duration: Math.round(r.duration),
        bytes: r.transferSize || 0,
        decodedBytes: r.decodedBodySize || 0,
      }));
      const hero = document.querySelector('.aznet-theme-industrial01-hero');
      return {
        lcp: window.__aznet_lcp || [],
        observerError: window.__aznet_lcp_error || null,
        pageTitle: document.title,
        nav: nav ? {
          dns: Math.round(nav.domainLookupEnd - nav.domainLookupStart),
          connect: Math.round(nav.connectEnd - nav.connectStart),
          ttfb: Math.round(nav.responseStart - nav.requestStart),
          domContentLoaded: Math.round(nav.domContentLoadedEventEnd),
          load: Math.round(nav.loadEventEnd),
          encodedBytes: nav.transferSize || 0,
        } : null,
        paints: performance.getEntriesByType('paint').map((p) => ({ name: p.name, time: Math.round(p.startTime) })),
        resourceCount: assets.length,
        assets: assets.filter((a) => /\.(css|js|jpe?g|png|webp|woff2?)(?:[?#]|$)/i.test(a.name))
          .sort((a, b) => a.responseEnd - b.responseEnd).slice(0, 120),
        heroBackground: hero ? getComputedStyle(hero).backgroundImage.slice(0, 900) : null,
        categories: document.querySelectorAll('.aznet-theme-industrial01-category-card__media img').length,
      };
    });

    result.variant = variant;
    result.order = i + 1;
    result.status = response?.status() || null;
    result.elapsedMs = Date.now() - start;
    result.denied = denied;
    result.requestErrors = requestErrors.slice(0, 15);
    const lcp = result.lcp.at(-1);
    console.log(JSON.stringify({
      sample: i + 1,
      variant,
      status: result.status,
      lcpMs: lcp?.startTime || null,
      lcpType: lcp?.tag || null,
      lcpClass: lcp?.className || null,
      lcpUrl: lcp?.url || null,
      fcp: result.paints.find((p) => p.name === 'first-contentful-paint')?.time,
      ttfb: result.nav?.ttfb,
      assets: result.resourceCount,
      blocked: denied.length,
      errors: requestErrors.length,
    }));
    if (i === 0) {
      await page.screenshot({ path: path.join(output, 'baseline-mobile.png') });
    }
  } catch (error) {
    errors += 1;
    result = { order: i + 1, variant, error: String(error), denied, requestErrors };
    console.error(JSON.stringify(result));
  } finally {
    records.push(result);
    await context.close();
  }
}

await browser.close();

const median = (numbers) => {
  const s = numbers.filter(Number.isFinite).sort((a, b) => a - b);
  if (!s.length) return null;
  return (s[Math.floor((s.length - 1) / 2)] + s[Math.floor(s.length / 2)]) / 2;
};
const grouped = Object.fromEntries([...new Set(variants)].map((variant) => [
  variant,
  {
    n: records.filter((x) => x.variant === variant && !x.error).length,
    medianLcpMs: median(records.filter((x) => x.variant === variant).map((x) => x.lcp?.at(-1)?.startTime)),
    medianTtfbMs: median(records.filter((x) => x.variant === variant).map((x) => x.nav?.ttfb)),
  },
]));
fs.writeFileSync(path.join(output, 'diagnostic.json'), JSON.stringify({
  url, measuredAt: new Date().toISOString(), profile: '390x844/4xCPU/180KiBps/150ms RTT/cache disabled',
  grouped, errors, records,
}, null, 2));
console.log('AZNET_LCP_DIAGNOSTIC ' + JSON.stringify({ grouped, errors }));
if (errors > 0 || (grouped.baseline?.n || 0) < 2) process.exitCode = 1;
