import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.X2_STATE_DIR || '/tmp/x2';
const outputDir = process.env.X2_L4_DIR || '/tmp/x2-l4';
const fixturePath = path.join(stateDir, 'fixture.json');

if (!fs.existsSync(fixturePath)) {
  throw new Error(`X2 fixture is required: ${fixturePath}`);
}

const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const routes = {
  'search-result': { url: fixture.result_url, expectedStatus: 200, selector: '.aznet-theme-listing' },
  'search-empty': { url: fixture.empty_search_url, expectedStatus: 200, selector: '.aznet-theme-recovery--search' },
  'archive-empty': { url: fixture.empty_archive_url, expectedStatus: 200, selector: '.aznet-theme-recovery--empty' },
  'not-found': { url: fixture.not_found_url, expectedStatus: 404, selector: '.aznet-theme-recovery--404' },
};

const summary = { fixture, cases: [] };
const failures = [];

async function focusEvidence(page, selector) {
  await page.locator(selector).focus();
  return page.locator(selector).evaluate((element) => {
    const rect = element.getBoundingClientRect();
    const style = window.getComputedStyle(element);
    const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
    return {
      visible: rect.width > 1 && rect.height > 1,
      insideViewport: rect.left >= -1 && rect.top >= -1 && rect.right <= window.innerWidth + 1 && rect.bottom <= window.innerHeight + 1,
      focusVisible: element.matches(':focus-visible'),
      hasIndicator: (style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent') || (style.boxShadow && style.boxShadow !== 'none'),
    };
  });
}

function browserErrorsForRoute(route, consoleErrors, pageErrors, httpErrors) {
  const expectedNavigation404 = route.expectedStatus === 404 && httpErrors.some(
    (entry) => entry.status === 404 && entry.url === route.url
  );
  const unexpectedHttpErrors = httpErrors.filter(
    (entry) => !(route.expectedStatus === 404 && entry.status === 404 && entry.url === route.url)
  );
  const unexpectedConsoleErrors = consoleErrors.filter((message) => {
    return !(
      expectedNavigation404
      && message.includes('Failed to load resource')
      && message.includes('404')
    );
  });

  return {
    expectedNavigation404,
    unexpectedHttpErrors,
    unexpectedConsoleErrors,
    pageErrors,
    count: unexpectedHttpErrors.length + unexpectedConsoleErrors.length + pageErrors.length,
  };
}

async function inspectCase(browser, routeName, route, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const httpErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() >= 400) {
      httpErrors.push({ status: response.status(), url: response.url() });
    }
  });

  const result = {
    route: routeName,
    viewport: viewportName,
    httpStatus: null,
    h1Count: null,
    overflowPx: null,
    blockingAxeViolations: null,
    browserErrorCount: null,
    unexpectedHttpErrorCount: null,
    unexpectedConsoleErrorCount: null,
    pageErrorCount: null,
    expectedNavigation404: false,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(route.url, { waitUntil: 'networkidle' });
    result.httpStatus = response?.status() ?? null;
    if (result.httpStatus !== route.expectedStatus) {
      throw new Error(`expected HTTP ${route.expectedStatus}, got ${result.httpStatus}`);
    }

    await page.locator(route.selector).waitFor({ state: 'visible' });
    result.h1Count = await page.locator('h1').count();
    if (result.h1Count !== 1) throw new Error(`expected exactly one H1, got ${result.h1Count}`);

    if (routeName === 'search-result') {
      if (await page.getByText('X2 Recovery Search Target', { exact: true }).count() < 1) throw new Error('native search result title missing');
      if (await page.locator('.aznet-theme-recovery--search').count() !== 0) throw new Error('empty recovery rendered on populated search');
    }
    if (routeName === 'search-empty') {
      if (await page.locator('.aznet-theme-recovery--search .search-form').count() !== 1) throw new Error('native search form missing from empty Search');
    }
    if (routeName === 'archive-empty') {
      if (await page.locator('.aznet-theme-recovery--empty').count() !== 1) throw new Error('empty Archive recovery missing');
    }
    if (routeName === 'not-found') {
      if (await page.getByRole('heading', { level: 1, name: 'Page not found' }).count() !== 1) throw new Error('404 H1 missing');
      if (await page.locator('.aznet-theme-recovery__code').filter({ hasText: '404' }).count() !== 1) throw new Error('visible 404 code missing');
      if (await page.locator('.aznet-theme-recovery--404 .search-form').count() !== 1) throw new Error('404 native search form missing');
      if (await page.getByRole('link', { name: 'Back to home' }).count() !== 1) throw new Error('404 Home action missing');
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx > 1) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    const axe = await new AxeBuilder({ page }).analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.length}`);

    if (routeName === 'search-empty' || routeName === 'not-found') {
      const inputSelector = `${route.selector} .search-field`;
      const submitSelector = `${route.selector} .search-submit`;
      for (const selector of [inputSelector, submitSelector]) {
        const focus = await focusEvidence(page, selector);
        if (!focus.visible || !focus.insideViewport || !focus.focusVisible || !focus.hasIndicator) {
          throw new Error(`focus evidence failed for ${selector}`);
        }
      }
      if (routeName === 'not-found') {
        const focus = await focusEvidence(page, `${route.selector} .aznet-theme-recovery__action`);
        if (!focus.visible || !focus.insideViewport || !focus.focusVisible || !focus.hasIndicator) {
          throw new Error('focus evidence failed for 404 Home action');
        }
      }
      if (viewportName === '1440x1000' || viewportName === '390x844') {
        await page.screenshot({ path: path.join(outputDir, `focus-${routeName}-${viewportName}.png`), fullPage: true });
      }
    }

    const browserErrors = browserErrorsForRoute(route, consoleErrors, pageErrors, httpErrors);
    result.expectedNavigation404 = browserErrors.expectedNavigation404;
    result.unexpectedHttpErrorCount = browserErrors.unexpectedHttpErrors.length;
    result.unexpectedConsoleErrorCount = browserErrors.unexpectedConsoleErrors.length;
    result.pageErrorCount = browserErrors.pageErrors.length;
    result.browserErrorCount = browserErrors.count;
    if (result.browserErrorCount) {
      throw new Error(`unexpected browser errors: ${result.browserErrorCount}`);
    }

    await page.screenshot({ path: path.join(outputDir, `${routeName}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    const browserErrors = browserErrorsForRoute(route, consoleErrors, pageErrors, httpErrors);
    result.expectedNavigation404 = browserErrors.expectedNavigation404;
    result.unexpectedHttpErrorCount = browserErrors.unexpectedHttpErrors.length;
    result.unexpectedConsoleErrorCount = browserErrors.unexpectedConsoleErrors.length;
    result.pageErrorCount = browserErrors.pageErrors.length;
    result.browserErrorCount = browserErrors.count;
    failures.push(`${routeName}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `failure-${routeName}-${viewportName}.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const [routeName, route] of Object.entries(routes)) {
      await inspectCase(browser, routeName, route, viewportName, viewport);
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (failures.length) {
  for (const failure of failures) console.error(`FAIL: ${failure}`);
  process.exit(1);
}

console.log(`PASS: X2 browser recovery matrix ${summary.cases.length}/${summary.cases.length}`);
