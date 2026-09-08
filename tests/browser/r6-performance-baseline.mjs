import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.R6_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.R6_STATE_DIR || '/tmp/r6-performance';
const mode = process.env.R6_MODE === 'woo' ? 'woo' : 'clean';
const wooProductId = Number.parseInt(process.env.R6_PRODUCT_ID || '0', 10);
const outputDir = path.join(stateDir, mode);
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '390x844': { width: 390, height: 844 },
};

const routeSets = {
  clean: [
    { name: 'home', path: '/', status: [200] },
    { name: 'page', path: '/sample-page/', status: [200] },
    { name: 'post', path: '/sample-post/', status: [200] },
    { name: 'category', path: '/category/sample/', status: [200] },
    { name: 'search', path: '/?s=sample', status: [200] },
    { name: '404', path: '/404-fixture/', status: [404] },
  ],
  woo: [
    { name: 'shop', path: '/shop/', status: [200] },
    { name: 'product', path: '/product/r4-simple-product/', status: [200] },
    { name: 'cart', path: '/cart/', status: [200] },
    { name: 'checkout', path: '/checkout/', status: [200] },
    { name: 'account', path: '/my-account/', status: [200] },
  ],
};

function normalizeResource(entry) {
  return {
    url: entry.name,
    initiatorType: entry.initiatorType,
    transferSize: Number(entry.transferSize || 0),
    encodedBodySize: Number(entry.encodedBodySize || 0),
    decodedBodySize: Number(entry.decodedBodySize || 0),
    durationMs: Number(Number(entry.duration || 0).toFixed(2)),
  };
}

function isThemeAsset(url) {
  return url.includes('/wp-content/themes/aznet-theme/');
}

if (mode === 'woo' && wooProductId < 1) throw new Error('R6_PRODUCT_ID is required for Woo baseline');

const browser = await chromium.launch({ headless: true });
const cases = [];
const failures = [];

try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const route of routeSets[mode]) {
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();
      const requestFailures = [];
      const failedSubresources = [];
      const consoleErrors = [];
      const pageErrors = [];

      page.on('requestfailed', (request) => {
        requestFailures.push({
          url: request.url(),
          failure: request.failure()?.errorText || 'unknown',
        });
      });
      page.on('console', (message) => {
        if (message.type() !== 'error') return;
        const text = message.text();
        const expectedDocumentStatusNoise = route.status.some((expectedStatus) =>
          expectedStatus >= 400 &&
          new RegExp(`^Failed to load resource: the server responded with a status of ${expectedStatus}\\b`).test(text)
        );
        if (!expectedDocumentStatusNoise) consoleErrors.push(text);
      });
      page.on('pageerror', (error) => pageErrors.push(String(error)));
      page.on('response', (networkResponse) => {
        const responseStatus = networkResponse.status();
        if (responseStatus < 400) return;
        const request = networkResponse.request();
        if (request.resourceType() === 'document') return;
        failedSubresources.push(`${responseStatus} ${request.resourceType()} ${networkResponse.url()}`);
      });

      await page.addInitScript(() => {
        globalThis.__aznetR6Cls = 0;
        try {
          const observer = new PerformanceObserver((list) => {
            for (const entry of list.getEntries()) {
              if (!entry.hadRecentInput) globalThis.__aznetR6Cls += entry.value;
            }
          });
          observer.observe({ type: 'layout-shift', buffered: true });
        } catch {
          // LayoutShift/PerformanceObserver support is measured, not assumed.
        }
      });

      const label = `${viewportName}-${route.name}`;
      try {
        if (mode === 'woo' && ['cart', 'checkout'].includes(route.name)) {
          const seedResponse = await page.goto(`${baseUrl}/?add-to-cart=${wooProductId}`, {
            waitUntil: 'domcontentloaded',
            timeout: 30000,
          });
          if (!seedResponse || seedResponse.status() >= 400) {
            throw new Error(`${label}: unable to seed Woo cart`);
          }
        }

        const response = await page.goto(`${baseUrl}${route.path}`, {
          waitUntil: 'networkidle',
          timeout: 30000,
        });
        const status = response?.status() ?? 0;
        if (!route.status.includes(status)) {
          throw new Error(`${label}: unexpected document status ${status}`);
        }

        const metrics = await page.evaluate(() => {
          const resources = performance.getEntriesByType('resource').map((entry) => ({
            name: entry.name,
            initiatorType: entry.initiatorType,
            transferSize: entry.transferSize || 0,
            encodedBodySize: entry.encodedBodySize || 0,
            decodedBodySize: entry.decodedBodySize || 0,
            duration: entry.duration || 0,
          }));
          const domAssets = [...document.querySelectorAll('link[rel="stylesheet"][href], script[src]')]
            .map((node) => ({
              id: node.id || '',
              tag: node.tagName.toLowerCase(),
              url: node.href || node.src || '',
            }));
          return {
            cls: Number((globalThis.__aznetR6Cls || 0).toFixed(5)),
            overflowPx: document.documentElement.scrollWidth - document.documentElement.clientWidth,
            resources,
            domAssets,
          };
        });

        const resources = metrics.resources.map(normalizeResource).sort((a, b) => a.url.localeCompare(b.url));
        const themeAssets = resources.filter((entry) => isThemeAsset(entry.url));
        const themeCss = themeAssets.filter((entry) => entry.url.includes('.css'));
        const themeJs = themeAssets.filter((entry) => entry.url.includes('.js'));
        const themeTransferBytes = themeAssets.reduce((sum, entry) => sum + entry.transferSize, 0);
        const themeDecodedBytes = themeAssets.reduce((sum, entry) => sum + entry.decodedBodySize, 0);
        const wooThemeAssets = themeAssets.filter((entry) => entry.url.includes('/assets/css/components/woocommerce-'));
        const providerAssets = resources.filter((entry) => /choiceguide|convertflow/i.test(entry.url) && !isThemeAsset(entry.url));

        if (metrics.overflowPx > 1) throw new Error(`${label}: horizontal overflow ${metrics.overflowPx}px`);
        if (requestFailures.length) throw new Error(`${label}: failed requests ${JSON.stringify(requestFailures)}`);
        if (failedSubresources.length) throw new Error(`${label}: failed subresources ${JSON.stringify(failedSubresources)}`);
        if (consoleErrors.length) throw new Error(`${label}: console errors ${JSON.stringify(consoleErrors)}`);
        if (pageErrors.length) throw new Error(`${label}: page errors ${JSON.stringify(pageErrors)}`);
        if (mode === 'clean' && wooThemeAssets.length) {
          throw new Error(`${label}: Woo Theme assets leaked onto clean route ${wooThemeAssets.map((item) => item.url).join(', ')}`);
        }
        if (mode === 'clean' && providerAssets.length) {
          throw new Error(`${label}: provider-owned asset present without provider ${providerAssets.map((item) => item.url).join(', ')}`);
        }

        cases.push({
          mode,
          viewport: viewportName,
          route: route.name,
          path: route.path,
          status,
          cls: metrics.cls,
          overflowPx: metrics.overflowPx,
          themeAssetCount: themeAssets.length,
          themeCssCount: themeCss.length,
          themeJsCount: themeJs.length,
          themeTransferBytes,
          themeDecodedBytes,
          requestFailures,
          failedSubresources,
          consoleErrors,
          pageErrors,
          themeAssets,
          domAssets: metrics.domAssets
            .filter((entry) => isThemeAsset(entry.url))
            .sort((a, b) => a.url.localeCompare(b.url)),
        });
      } catch (error) {
        failures.push(`${label}: ${error instanceof Error ? error.message : String(error)}`);
        await page.screenshot({ path: path.join(outputDir, `${label}-failure.png`), fullPage: true }).catch(() => {});
      } finally {
        await context.close();
      }
    }
  }
} finally {
  await browser.close();
}

const summary = {
  mode,
  routeCount: routeSets[mode].length,
  viewportCount: Object.keys(viewports).length,
  caseCount: cases.length,
  expectedCaseCount: routeSets[mode].length * Object.keys(viewports).length,
  cases,
  failures,
};

fs.writeFileSync(path.join(outputDir, 'summary.json'), `${JSON.stringify(summary, null, 2)}\n`);
if (failures.length || summary.caseCount !== summary.expectedCaseCount) {
  console.error(failures.join('\n') || `Expected ${summary.expectedCaseCount} cases, got ${summary.caseCount}`);
  process.exit(1);
}
console.log(`PASS: R6 ${mode} performance baseline ${summary.caseCount}/${summary.expectedCaseCount}`);
