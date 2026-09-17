import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.Y5_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const stateDir = process.env.Y5_STATE_DIR || '/tmp/y5-client-delivery';
const expectedFooter = process.env.Y5_EXPECT_FOOTER || 'standard';
const matrixMode = process.env.Y5_MATRIX_MODE || 'full';
const state = JSON.parse(fs.readFileSync(path.join(stateDir, 'site-state.json'), 'utf8'));
const screenshotsDir = path.join(stateDir, 'screenshots');
const axeDir = path.join(stateDir, 'axe');
fs.mkdirSync(screenshotsDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const blockingImpacts = new Set(['critical', 'serious']);
const summary = {
  mode: matrixMode,
  expectedFooter,
  routes: [],
  focus: [],
  themeSubresourceFailures: [],
};

function normalizedUrl(url) {
  return new URL(url, baseUrl).toString();
}

async function assertAxe(page, label) {
  const result = await new AxeBuilder({ page }).analyze();
  fs.writeFileSync(path.join(axeDir, `${label}.json`), JSON.stringify(result, null, 2));
  const blocking = result.violations.filter((violation) => blockingImpacts.has(violation.impact));
  if (blocking.length > 0) {
    throw new Error(`${label}: blocking axe violations ${blocking.map((v) => `${v.id}:${v.impact}`).join(', ')}`);
  }
}

async function assertVisibleFocus(locator, label) {
  if (await locator.count() < 1) throw new Error(`${label}: focus target missing`);
  await locator.first().focus();
  const focus = await locator.first().evaluate((element) => {
    const style = getComputedStyle(element);
    return {
      active: document.activeElement === element,
      outlineStyle: style.outlineStyle,
      outlineWidth: style.outlineWidth,
      boxShadow: style.boxShadow,
    };
  });
  const outlineWidth = Number.parseFloat(focus.outlineWidth || '0');
  const visible = focus.active && ((focus.outlineStyle !== 'none' && outlineWidth > 0) || (focus.boxShadow && focus.boxShadow !== 'none'));
  if (!visible) throw new Error(`${label}: keyboard focus is not visibly styled (${JSON.stringify(focus)})`);
  summary.focus.push({ label, ...focus });
}

async function assertDocument(page, label, expectedStatus) {
  const response = await page.goto(label.url, { waitUntil: 'networkidle' });
  if (!response || response.status() !== expectedStatus) {
    throw new Error(`${label.name}: expected HTTP ${expectedStatus}, got ${response?.status()}`);
  }
  if (await page.locator('main#main').count() !== 1) throw new Error(`${label.name}: expected exactly one main#main`);
  const h1Count = await page.locator('h1').count();
  if (h1Count > 1) throw new Error(`${label.name}: expected at most one H1, got ${h1Count}`);
  if (await page.locator('[data-aznet-theme-site-header]').count() !== 1) throw new Error(`${label.name}: expected one Theme Header`);
  if (await page.locator('[data-aznet-theme-site-footer]').count() !== 1) throw new Error(`${label.name}: expected one Theme Footer`);
  const footerClass = await page.locator('[data-aznet-theme-site-footer]').getAttribute('class');
  if (!footerClass || !footerClass.includes(`aznet-theme-site-footer--${expectedFooter}`)) {
    throw new Error(`${label.name}: Footer preset mismatch ${footerClass}`);
  }
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) throw new Error(`${label.name}: horizontal overflow ${overflow}px`);
  return { status: response.status(), h1Count, overflow, footerClass };
}

const allRoutes = {
  home: { url: normalizedUrl(state.routes.home), status: 200 },
  about: { url: normalizedUrl(state.routes.about), status: 200 },
  about_child: { url: normalizedUrl(state.routes.about_child), status: 200 },
  services: { url: normalizedUrl(state.routes.services), status: 200 },
  team: { url: normalizedUrl(state.routes.team), status: 200 },
  contact: { url: normalizedUrl(state.routes.contact), status: 200 },
  landing: { url: normalizedUrl(state.routes.landing), status: 200 },
  blog: { url: normalizedUrl(state.routes.blog), status: 200 },
  post: { url: normalizedUrl(state.routes.post), status: 200 },
  search: { url: normalizedUrl(state.routes.search), status: 200 },
  '404': { url: normalizedUrl('/?p=999999999'), status: 404 },
};

const routeNames = matrixMode === 'footer'
  ? ['home', 'contact']
  : matrixMode === 'smoke'
    ? ['home', 'services', 'post', '404']
    : ['home', 'about', 'about_child', 'services', 'team', 'contact', 'landing', 'blog', 'post', 'search', '404'];

const viewports = matrixMode === 'smoke'
  ? {
      '1440x1000': { width: 1440, height: 1000 },
      '390x844': { width: 390, height: 844 },
    }
  : {
      '1440x1000': { width: 1440, height: 1000 },
      '1024x900': { width: 1024, height: 900 },
      '390x844': { width: 390, height: 844 },
      '320x760': { width: 320, height: 760 },
    };

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    const consoleErrors = [];
    const pageErrors = [];
    const themeFailures = [];
    page.on('console', (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); });
    page.on('pageerror', (error) => pageErrors.push(error.message));
    page.on('requestfailed', (request) => {
      if (request.url().includes('/wp-content/themes/aznet-theme/')) themeFailures.push(`requestfailed ${request.url()} ${request.failure()?.errorText || ''}`);
    });
    page.on('response', (response) => {
      if (response.url().includes('/wp-content/themes/aznet-theme/') && response.status() >= 400) themeFailures.push(`HTTP ${response.status()} ${response.url()}`);
    });

    for (const routeName of routeNames) {
      const route = allRoutes[routeName];
      const label = `${matrixMode}-${expectedFooter}-${viewportName}-${routeName}`;
      const result = await assertDocument(page, { name: label, url: route.url }, route.status);

      if (routeName === 'about' && await page.locator('.aznet-theme-page--standard').count() !== 1) throw new Error(`${label}: About Standard presentation missing`);
      if (routeName === 'services' && await page.locator('.aznet-theme-page--wide').count() !== 1) throw new Error(`${label}: Services Wide presentation missing`);
      if (routeName === 'landing' && await page.locator('.aznet-theme-page--landing').count() !== 1) throw new Error(`${label}: Landing presentation missing`);
      if (routeName === 'about_child') {
        const breadcrumb = page.locator('.aznet-theme-page__breadcrumbs');
        if (await breadcrumb.count() !== 1) throw new Error(`${label}: child breadcrumb missing`);
        await assertVisibleFocus(breadcrumb.locator('a:visible').first(), `${label}:breadcrumb`);
      }

      if (routeName === 'home') {
        await assertVisibleFocus(page.locator('[data-aznet-theme-site-header] a:visible, [data-aznet-theme-site-header] button:visible').first(), `${label}:header`);
        await assertVisibleFocus(page.locator('main#main a:visible, main#main button:visible').first(), `${label}:main-link`);
        await assertVisibleFocus(page.locator('[data-aznet-theme-site-footer] a:visible').first(), `${label}:footer`);
      }

      await assertAxe(page, label);
      if (['home', 'services', 'about_child', 'post', '404'].includes(routeName)) {
        await page.screenshot({ path: path.join(screenshotsDir, `${label}.png`), fullPage: true });
      }
      summary.routes.push({ route: routeName, viewport: viewportName, ...result, axeBlocking: 0 });
    }

    if (consoleErrors.length > 0) throw new Error(`${matrixMode}/${viewportName}: console errors ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`${matrixMode}/${viewportName}: page errors ${pageErrors.join(' | ')}`);
    if (themeFailures.length > 0) throw new Error(`${matrixMode}/${viewportName}: failed Theme subresources ${themeFailures.join(' | ')}`);
    summary.themeSubresourceFailures.push(...themeFailures);
    await context.close();
  }
} finally {
  await browser.close();
}

const output = path.join(stateDir, `browser-summary-${matrixMode}-${expectedFooter}.json`);
fs.writeFileSync(output, JSON.stringify(summary, null, 2));
console.log(`PASS: Y5 ${matrixMode} browser matrix Footer=${expectedFooter} routes=${summary.routes.length}`);
