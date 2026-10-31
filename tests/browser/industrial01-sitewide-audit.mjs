// ANP Industrial 01 read-only public browser L4 evidence, with no site mutations.
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

const widths = [390, 768, 1440];
// Isolate the first failing public surface instead of crawling the full matrix.
// The exact ID must already exist in the reviewed site-local route manifest.
function selectRoutes(routes, onlyId = '') {
  if (!onlyId) return routes;
  if (!/^[a-z0-9][a-z0-9-]{0,55}$/.test(onlyId)) throw Error('Invalid requested route ID');
  const result = routes.filter((r) => r.id === onlyId);
  if (result.length !== 1) throw Error('Requested route ID absent from reviewed manifest');
  return result;
}
function config(baseUrl, data) {
  const base = new URL(baseUrl);
  if (base.username || base.password || base.search || base.hash || base.pathname !== '/' ||
      !(['https:', 'http:'].includes(base.protocol)) ||
      (base.protocol === 'http:' && !['localhost', '127.0.0.1'].includes(base.hostname))) {
    throw Error('Require an HTTPS site origin; localhost HTTP allowed only');
  }
  if (!Array.isArray(data) || data.length < 1 || data.length > 30) throw Error('Require 1-30 explicit routes');
  const ids = new Set();
  const routes = data.map((r) => {
    if (!r || !/^[a-z0-9][a-z0-9-]{0,55}$/.test(r.id) || ids.has(r.id)) throw Error('Invalid or duplicate route ID');
    ids.add(r.id);
    if (typeof r.path !== 'string' || !r.path.startsWith('/') || r.path.startsWith('//') ||
        /[?#\\]/.test(r.path)) throw Error('Only public same-site absolute paths allowed');
    const url = new URL(r.path, base);
    if (url.origin !== base.origin || /^\/(?:wp-admin|wp-login|wp-json)(?:\/|$)/.test(url.pathname) ||
        !['page', 'shop', 'product', 'category', 'article', 'archive', 'other'].includes(r.kind)) {
      throw Error('Unsupported route origin, admin path or kind');
    }
    return { id: r.id, kind: r.kind, path: r.path, url: url.href };
  });
  return { base, routes };
}
if (process.argv.includes('--self-test')) {
  assert.equal(config('https://example.test/', [{ id: 'p', path: '/p/', kind: 'page' }]).routes[0].url, 'https://example.test/p/');
  assert.throws(() => config('http://example.test/', [{ id: 'p', path: '/', kind: 'page' }]));
  assert.throws(() => config('https://example.test/', [{ id: 'p', path: '//evil.test', kind: 'page' }]));
  const fixture = config('https://example.test/', [
    { id: 'home', path: '/', kind: 'page' },
    { id: 'komatsu-product', path: '/product-example', kind: 'product' },
  ]).routes;
  assert.deepEqual(selectRoutes(fixture, 'komatsu-product').map((r) => r.id), ['komatsu-product']);
  assert.equal(selectRoutes(fixture).length, 2);
  assert.throws(() => selectRoutes(fixture, 'not-in-manifest'));
  assert.throws(() => selectRoutes(fixture, '../wp-admin'));
  assert.deepEqual(widths, [390, 768, 1440]);
  console.log('PASS: public route safety, fail-closed single-route selection and viewports');
} else {
  const file = process.env.AZT_AUDIT_ROUTES_FILE;
  if (!file) throw Error('Set AZT_AUDIT_ROUTES_FILE to a reviewed explicit JSON route manifest');
  const { base, routes } = config(process.env.AZT_AUDIT_BASE_URL || '', JSON.parse(fs.readFileSync(file, 'utf8')));
  const selectedRoutes = selectRoutes(routes, process.env.AZT_AUDIT_ONLY_ROUTE || '');
  const dir = path.resolve(process.env.AZT_AUDIT_DIR || '/tmp/azt-industrial01-sitewide');
  fs.mkdirSync(dir, { recursive: true });
  const [{ chromium }, { default: AxeBuilder }] = await Promise.all([import('playwright'), import('@axe-core/playwright')]);
  const result = {
    scope: 'Read-only public DOM; do not infer Theme/domain source from selectors alone',
    base: base.href,
    generatedAt: new Date().toISOString(),
    head: process.env.GITHUB_SHA || null,
    views: [],
  };
  const persist = () => fs.writeFileSync(path.join(dir, 'report.json'), JSON.stringify(result, null, 2) + '\n');
  const browser = await chromium.launch({ headless: true });
  try {
    for (const route of selectedRoutes) for (const width of widths) {
      const record = { route: route.id, kind: route.kind, width, outcome: 'started' };
      result.views.push(record);
      const context = await browser.newContext({ viewport: { width, height: width === 390 ? 844 : 1000 }, reducedMotion: 'reduce' });
      const page = await context.newPage();
      try {
        const response = await page.goto(route.url, { waitUntil: 'domcontentloaded', timeout: 45000 });
        record.httpStatus = response?.status() ?? null;
        record.finalUrl = page.url();
        if (record.httpStatus !== 200 || new URL(page.url()).origin !== base.origin) throw Error('Unexpected public HTTP/final origin');
        record.dom = await page.evaluate(() => {
          const body = document.body, root = document.documentElement;
          return {
            theme: body.classList.contains('wp-theme-aznet-theme'),
            industrial01: body.classList.contains('aznet-theme-preset--industrial-01'),
            mainCount: document.querySelectorAll('main#main').length,
            h1: [...document.querySelectorAll('h1')].map((el) => el.textContent.trim().slice(0, 120)),
            overflowPx: Math.max(0, root.scrollWidth - root.clientWidth),
            visibleBrokenImages: [...document.images].filter((img) => {
              const rect = img.getBoundingClientRect();
              return rect.height > 1 && rect.width > 1 && rect.top < innerHeight && rect.bottom > 0 &&
                img.complete && img.naturalWidth === 0;
            }).slice(0, 8).map((img) => ({ src: img.currentSrc || img.src, alt: img.alt })),
            contactForms: document.querySelectorAll('.wpcf7 form').length,
          };
        });
        if (record.dom.overflowPx > 1) {
          record.overflowElements = await page.evaluate(() => [...document.querySelectorAll('body *')]
            .map((el) => ({ el, rect: el.getBoundingClientRect() }))
            .filter(({ rect }) => rect.width > 1 && rect.right > document.documentElement.clientWidth + 1)
            .slice(0, 10).map(({ el, rect }) => ({
              tag: el.tagName, className: String(el.className).slice(0, 160), right: Math.round(rect.right),
            })));
        }
        const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
        record.axe = [];
        for (const violation of axe.violations) {
          const item = { id: violation.id, impact: violation.impact, help: violation.help, nodeCount: violation.nodes.length, nodes: [] };
          for (const n of violation.nodes.slice(0, 12)) {
            const evidence = {
              target: n.target, html: String(n.html || '').slice(0, 700),
              reason: String(n.failureSummary || '').slice(0, 900),
            };
            if (n.target.length === 1 && typeof n.target[0] === 'string') {
              try {
                evidence.styles = await page.locator(n.target[0]).first().evaluate((el) => {
                  const cs = getComputedStyle(el), ancestors = [];
                  for (let current = el; current && ancestors.length < 4; current = current.parentElement) {
                    const style = getComputedStyle(current);
                    ancestors.push({ tag: current.tagName, className: String(current.className).slice(0, 130), background: style.backgroundColor, color: style.color, opacity: style.opacity });
                  }
                  return {
                    color: cs.color, background: cs.backgroundColor, fontSize: cs.fontSize,
                    text: (el.textContent || '').trim().slice(0, 140), ariaLabel: el.getAttribute('aria-label'),
                    title: el.getAttribute('title'), ancestors,
                  };
                });
              } catch (error) { evidence.inspectError = String(error).slice(0, 250); }
            }
            item.nodes.push(evidence);
          }
          record.axe.push(item);
        }
        record.screenshot = route.id + '-' + width + '.png';
        await page.screenshot({ path: path.join(dir, record.screenshot), fullPage: true, animations: 'disabled' });
        record.outcome = !record.dom.theme || !record.dom.industrial01 ? 'unknown-shell' :
          record.dom.mainCount === 1 && record.dom.overflowPx <= 1 &&
          record.dom.visibleBrokenImages.length === 0 && record.axe.length === 0 ? 'pass' : 'review';
      } catch (error) {
        record.outcome = 'blocked';
        record.error = String(error).slice(0, 1100);
      } finally {
        persist();
        await context.close();
      }
    }
  } finally {
    await browser.close();
  }
  persist();
  const passed = result.views.filter((v) => v.outcome === 'pass').length;
  console.log('Sitewide view checks: ' + passed + '/' + result.views.length + ' PASS; evidence ' + dir);
  if (passed !== result.views.length) process.exitCode = 1;
}
