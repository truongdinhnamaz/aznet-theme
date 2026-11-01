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
// Normalization permits WordPress trailing-slash canonicalization only.
// An unrelated 200 redirect, especially to Homepage, must never count as Page PASS.
function samePublicRoute(requestedUrl, finalUrl) {
  const requested = new URL(requestedUrl);
  const actual = new URL(finalUrl);
  const pathname = (url) => url.pathname.replace(/\/+$/, '') || '/';
  // A public route must never silently become a preview, cache-buster, or
  // parameterized variant. Keep redirects limited to trailing-slash shape.
  return !actual.search && !actual.hash &&
    requested.origin === actual.origin && pathname(requested) === pathname(actual);
}
// A Page cannot pass sitewide semantic QA with no, empty, or duplicate H1.
function hasOneNamedH1(headings) {
  return Array.isArray(headings) && headings.length === 1 &&
    typeof headings[0] === 'string' && headings[0].trim().length > 0;
}
// Contact is an explicitly reviewed route: a form in the page's main content
// is required for visual PASS, but this does NOT verify sending/delivery.
function routeHasRequiredForm(route, dom) {
  return route.id !== 'contact' ||
    (Number.isInteger(dom?.contactForms) && dom.contactForms > 0);
}
// axe-core "incomplete" means manual review is required, not WCAG PASS.
function axeIsConclusive(violations, incomplete) {
  return Array.isArray(violations) && Array.isArray(incomplete) &&
    violations.length === 0 && incomplete.length === 0;
}
// Keep confirmed axe defects and manual-review findings equally actionable:
// capture the same real DOM selector, source snippet and computed color context.
async function inspectAxeNode(page, node) {
  const evidence = {
    target: node.target,
    html: String(node.html || '').slice(0, 700),
    reason: String(node.failureSummary || '').slice(0, 900),
  };
  if (Array.isArray(node.target) && node.target.length === 1 && typeof node.target[0] === 'string') {
    try {
      evidence.styles = await page.locator(node.target[0]).first().evaluate((el) => {
        const cs = getComputedStyle(el), ancestors = [];
        for (let current = el; current && ancestors.length < 4; current = current.parentElement) {
          const style = getComputedStyle(current);
          ancestors.push({
            tag: current.tagName, className: String(current.className).slice(0, 130),
            background: style.backgroundColor, color: style.color, opacity: style.opacity,
          });
        }
        return {
          color: cs.color, background: cs.backgroundColor, fontSize: cs.fontSize,
          text: (el.textContent || '').trim().slice(0, 140),
          ariaLabel: el.getAttribute('aria-label'), title: el.getAttribute('title'), ancestors,
        };
      });
    } catch (error) { evidence.inspectError = String(error).slice(0, 250); }
  }
  return evidence;
}
// Full-page capture can trigger offscreen/lazy-loaded content. Axe must
// inspect the post-capture DOM, never the earlier partial state.
async function captureThenAudit(page, screenshotPath, AxeBuilder, inspectAfterCapture) {
  await page.screenshot({ path: screenshotPath, fullPage: true, animations: 'disabled' });
  // The DOM, headings and overflow must be measured after lazy-load-triggering capture.
  await inspectAfterCapture();
  return new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']).analyze();
}
// Anonymous browser errors are evidence for triage, not automatically Theme bugs.
// Store bounded error samples, strip query/fragment values, and preserve counts.
function sanitizedSource(value) {
  try {
    const url = new URL(String(value || ''));
    return ['https:', 'http:'].includes(url.protocol) ? url.origin + url.pathname : null;
  } catch { return null; }
}
function sanitizedMessage(value) {
  return String(value || '').replace(/https?:\/\/[^\s'"<>]+/g, (raw) => sanitizedSource(raw) || '[url]').slice(0, 500);
}
function attachBrowserDiagnostics(page, record) {
  record.browserErrors = [];
  record.browserErrorCount = 0;
  const add = (kind, message, source) => {
    record.browserErrorCount += 1;
    if (record.browserErrors.length >= 12) return;
    record.browserErrors.push({ kind, message: sanitizedMessage(message), source: sanitizedSource(source) });
  };
  page.on('console', (message) => {
    if (message.type() !== 'error') return;
    add('console', message.text(), message.location()?.url);
  });
  page.on('pageerror', (error) => add('pageerror', error?.message || String(error), ''));
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
  assert.equal(samePublicRoute('https://example.test/p/', 'https://example.test/p'), true);
  assert.equal(samePublicRoute('https://example.test/p/', 'https://example.test/p/?preview=true'), false);
  assert.equal(samePublicRoute('https://example.test/p/', 'https://example.test/p/?wpvibe_cb=123'), false);
  assert.equal(samePublicRoute('https://example.test/p/', 'https://example.test/p/#draft'), false);
  assert.equal(samePublicRoute('https://example.test/p/', 'https://example.test/'), false);
  assert.equal(samePublicRoute('https://example.test/p/', 'https://evil.test/p/'), false);
  assert.equal(hasOneNamedH1(['Valid product title']), true);
  assert.equal(hasOneNamedH1([]), false);
  assert.equal(hasOneNamedH1(['']), false);
  assert.equal(hasOneNamedH1(['First', 'Duplicate']), false);
  assert.equal(routeHasRequiredForm({ id: 'contact' }, { contactForms: 0 }), false);
  assert.equal(routeHasRequiredForm({ id: 'contact' }, { contactForms: 1 }), true);
  assert.equal(routeHasRequiredForm({ id: 'contact' }, {}), false);
  assert.equal(routeHasRequiredForm({ id: 'home' }, { contactForms: 0 }), true);
  assert.equal(axeIsConclusive([], []), true);
  assert.equal(axeIsConclusive([{ id: 'color-contrast' }], []), false);
  assert.equal(axeIsConclusive([], [{ id: 'color-contrast' }]), false);
  assert.equal(axeIsConclusive([], undefined), false);
  const stubPage = { locator: (selector) => ({
    first: () => ({ evaluate: async () => ({ color: selector === '#test' ? 'rgb(1, 2, 3)' : '' }) }),
  }) };
  const sampleAxeNode = { target: ['#test'], html: '<a id="test">Text</a>', failureSummary: 'review color' };
  const enriched = await inspectAxeNode(stubPage, sampleAxeNode);
  assert.deepEqual(enriched.target, ['#test']);
  assert.equal(enriched.styles.color, 'rgb(1, 2, 3)');
  const failedInspection = await inspectAxeNode({ locator: () => { throw Error('bad selector'); } }, sampleAxeNode);
  assert.match(failedInspection.inspectError, /bad selector/);
  const auditOrder = [];
  const capturePage = { screenshot: async (options) => {
    assert.equal(options.fullPage, true);
    assert.equal(options.animations, 'disabled');
    auditOrder.push('capture');
  } };
  class StubAxeBuilder {
    withTags(tags) {
      assert.deepEqual(tags, ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa']);
      return this;
    }
    async analyze() {
      auditOrder.push('axe');
      return { violations: [], incomplete: [] };
    }
  }
  const capturedAudit = await captureThenAudit(capturePage, '/tmp/self-test-only.png', StubAxeBuilder, async () => {
    assert.deepEqual(auditOrder, ['capture']);
    auditOrder.push('dom');
  });
  assert.deepEqual(auditOrder, ['capture', 'dom', 'axe']);
  assert.deepEqual(capturedAudit.incomplete, []);
  assert.deepEqual(widths, [390, 768, 1440]);
  const browserCallbacks = new Map(), browserRecord = {};
  attachBrowserDiagnostics({ on: (name, callback) => browserCallbacks.set(name, callback) }, browserRecord);
  browserCallbacks.get('console')({ type: () => 'warning', text: () => 'ignore' });
  assert.equal(browserRecord.browserErrorCount, 0);
  browserCallbacks.get('console')({
    type: () => 'error',
    text: () => 'failed https://example.test/api?secret=123',
    location: () => ({ url: 'https://example.test/script.js?key=private' }),
  });
  browserCallbacks.get('pageerror')(new Error('Uncaught exception'));
  assert.equal(browserRecord.browserErrorCount, 2);
  assert.equal(browserRecord.browserErrors[0].source, 'https://example.test/script.js');
  assert.equal(browserRecord.browserErrors[1].kind, 'pageerror');
  assert.equal(JSON.stringify(browserRecord).includes('secret=123'), false);
  assert.equal(JSON.stringify(browserRecord).includes('key=private'), false);
  for (let i = 0; i < 15; i++) browserCallbacks.get('pageerror')(new Error('additional'));
  assert.equal(browserRecord.browserErrorCount, 17);
  assert.equal(browserRecord.browserErrors.length, 12);

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
      attachBrowserDiagnostics(page, record);
      try {
        const response = await page.goto(route.url, { waitUntil: 'domcontentloaded', timeout: 45000 });
        record.httpStatus = response?.status() ?? null;
        record.finalUrl = page.url();
        if (record.httpStatus !== 200 || !samePublicRoute(route.url, record.finalUrl)) throw Error('HTTP status or final path mismatch for reviewed route');
        record.screenshot = route.id + '-' + width + '.png';
        const axe = await captureThenAudit(page, path.join(dir, record.screenshot), AxeBuilder, async () => {
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
              contactForms: document.querySelectorAll('main#main .wpcf7 form').length,
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
        });
        record.axe = [];
        for (const violation of axe.violations) {
          const item = { id: violation.id, impact: violation.impact, help: violation.help, nodeCount: violation.nodes.length, nodes: [] };
          for (const n of violation.nodes.slice(0, 12)) {
            const evidence = await inspectAxeNode(page, n);
            item.nodes.push(evidence);
          }
          record.axe.push(item);
        }
        // Manual-review results are not proven defects, but must never be
        // silently counted as axe-clean. Preserve exact targets for triage.
        record.axeIncomplete = [];
        for (const rule of axe.incomplete) {
          const item = { id: rule.id, impact: rule.impact, help: rule.help, nodeCount: rule.nodes.length, nodes: [] };
          for (const n of rule.nodes.slice(0, 12)) item.nodes.push(await inspectAxeNode(page, n));
          record.axeIncomplete.push(item);
        }
        // The preceding screenshot can trigger lazy image loads. Inspect all image
        // elements afterwards, not only initially visible images above the fold.
        // Unresolved lazy images are UNKNOWN, never treated as verified PASS.
        record.media = await page.evaluate(() => {
          const images = [...document.images].filter((img) => img.currentSrc || img.getAttribute('src') || img.getAttribute('srcset'));
          const broken = images.filter((img) => img.complete && img.naturalWidth === 0);
          const pending = images.filter((img) => !img.complete);
          return {
            total: images.length,
            brokenCount: broken.length,
            pendingCount: pending.length,
            broken: broken.slice(0, 12).map((img) => ({ src: img.currentSrc || img.src, alt: img.alt })),
            pending: pending.slice(0, 12).map((img) => ({ src: img.currentSrc || img.src, loading: img.loading })),
          };
        });
        record.outcome = !record.dom.theme || !record.dom.industrial01 ? 'unknown-shell' :
          record.dom.mainCount === 1 && hasOneNamedH1(record.dom.h1) && routeHasRequiredForm(route, record.dom) && record.dom.overflowPx <= 1 &&
          record.media.brokenCount === 0 && record.media.pendingCount === 0 &&
          record.browserErrorCount === 0 && axeIsConclusive(record.axe, record.axeIncomplete) ? 'pass' : 'review';
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
