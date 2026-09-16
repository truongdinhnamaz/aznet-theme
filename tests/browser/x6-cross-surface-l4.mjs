import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.X6_STATE_DIR || '/tmp/x6';
const outputDir = process.env.X6_L4_DIR || '/tmp/x6-l4';
const fixturePath = path.join(stateDir, 'fixture.json');
if (!fs.existsSync(fixturePath)) throw new Error(`X6 fixture is required: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const routes = {
  comments: { url: fixture.comment_url, status: 200, assets: ['aznet-theme-comments-css', 'aznet-theme-forms-css'], focus: '#respond textarea#comment' },
  'search-result': { url: fixture.search_result_url, status: 200, assets: ['aznet-theme-navigation-css', 'aznet-theme-forms-css'], focus: '.aznet-theme-search-header__form .search-field' },
  'search-empty': { url: fixture.search_empty_url, status: 200, assets: ['aznet-theme-recovery-css', 'aznet-theme-forms-css'], focus: '.aznet-theme-recovery .search-field' },
  'not-found': { url: fixture.missing_url, status: 404, assets: ['aznet-theme-recovery-css', 'aznet-theme-forms-css'], focus: '.aznet-theme-recovery .search-field' },
  'archive-page-2': { url: fixture.archive_page_2_url, status: 200, assets: ['aznet-theme-navigation-css'], focus: '.aznet-theme-listing__pagination a.page-numbers' },
  multipage: { url: fixture.multipage_url, status: 200, assets: ['aznet-theme-navigation-css', 'aznet-theme-media-css'], focus: '.aznet-theme-article__page-links a.post-page-numbers' },
  media: { url: fixture.media_url, status: 200, assets: ['aznet-theme-media-css'], focus: null },
  controls: { url: fixture.controls_url, status: 200, assets: ['aznet-theme-forms-css'], focus: '.aznet-theme-entry__content .wp-block-search input[type="search"]' },
};

for (const [name, config] of Object.entries(routes)) {
  if (!config.url) throw new Error(`Missing X6 fixture URL for ${name}`);
}

const summary = { total_cases: 0, blocking_failures: 0, cases: [] };
const failures = [];

async function keyboardFocusEvidence(page, selector) {
  if (!selector) return null;
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  for (let index = 0; index < 240; index += 1) {
    await page.keyboard.press('Tab');
    const state = await page.evaluate((target) => {
      const element = document.activeElement;
      if (!(element instanceof HTMLElement) || !element.matches(target)) return null;
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return {
        focusVisible: element.matches(':focus-visible'),
        visible: rect.width > 1 && rect.height > 1,
        outlineStyle: style.outlineStyle,
        outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
        boxShadow: style.boxShadow,
      };
    }, selector);
    if (state) return state;
  }
  return null;
}

function requireVisibleFocus(name, state) {
  const hasOutline = state && state.outlineStyle !== 'none' && state.outlineWidth >= 1;
  const hasShadow = state && state.boxShadow && state.boxShadow !== 'none';
  if (!state?.visible || !state.focusVisible || (!hasOutline && !hasShadow)) {
    throw new Error(`${name} lacks visible keyboard focus: ${JSON.stringify(state)}`);
  }
}

async function inspectCase(browser, routeName, config, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const result = {
    route: routeName,
    viewport: viewportName,
    url: config.url,
    expectedStatus: config.status,
    overflowPx: null,
    blockingAxeViolations: null,
    focus: null,
    consoleErrors,
    pageErrors,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(config.url, { waitUntil: 'networkidle' });
    const status = response?.status() ?? null;
    if (status !== config.status) throw new Error(`expected HTTP ${config.status}, got ${status}`);
    if (await page.locator('main').count() !== 1) throw new Error('expected exactly one main landmark');
    if (await page.locator('h1').count() !== 1) throw new Error('expected exactly one H1');

    for (const assetId of config.assets) {
      if (await page.locator(`#${assetId}`).count() !== 1) throw new Error(`required Theme asset handle missing: ${assetId}`);
    }

    if (routeName === 'comments') {
      if (await page.locator('#comments').count() !== 1) throw new Error('comments surface missing');
      if (await page.locator('.comment-list .comment').count() < 1) throw new Error('approved comments missing');
      if (await page.locator('#respond .comment-form').count() !== 1) throw new Error('native comment form missing');
    }
    if (routeName === 'search-result' && await page.getByText('X6 Cross Surface Target', { exact: false }).count() < 1) {
      throw new Error('search result sentinel missing');
    }
    if (routeName === 'search-empty' && await page.locator('.aznet-theme-recovery').count() !== 1) {
      throw new Error('empty-search recovery surface missing');
    }
    if (routeName === 'not-found' && await page.locator('.aznet-theme-recovery--404').count() !== 1) {
      throw new Error('404 recovery surface missing');
    }
    if (routeName === 'archive-page-2') {
      if (await page.locator('.aznet-theme-listing__pagination .page-numbers.current').count() !== 1) throw new Error('archive current page state missing');
    }
    if (routeName === 'multipage') {
      if (await page.locator('#x6-page-one').count() !== 1) throw new Error('multipage page-one sentinel missing');
      if (await page.locator('.aznet-theme-article__page-links a.post-page-numbers').count() < 1) throw new Error('native multipage navigation missing');
    }
    if (routeName === 'media') {
      for (const selector of ['#x6-media-sentinel', '.wp-block-gallery', '#x6-video', '#x6-audio', '#x6-embed iframe']) {
        if (await page.locator(selector).count() < 1) throw new Error(`media marker missing: ${selector}`);
      }
      const mediaOverflow = await page.locator('#x6-embed iframe, .wp-block-gallery, #x6-video, #x6-audio').evaluateAll((elements) =>
        elements.map((element) => {
          const rect = element.getBoundingClientRect();
          return { left: rect.left, right: rect.right, width: rect.width };
        })
      );
      if (mediaOverflow.some((item) => item.width <= 1 || item.left < -1 || item.right > viewport.width + 1)) {
        throw new Error(`media containment failed: ${JSON.stringify(mediaOverflow)}`);
      }
    }
    if (routeName === 'controls') {
      if (await page.locator('.aznet-theme-entry__content .wp-block-search input[type="search"]').count() !== 1) throw new Error('Core Search input missing');
      if (await page.locator('.aznet-theme-entry__content .wp-block-categories-dropdown select').count() !== 1) throw new Error('Core Categories dropdown missing');
    }

    result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflowPx > 1) throw new Error(`horizontal overflow ${result.overflowPx}px`);

    if (config.focus) {
      result.focus = await keyboardFocusEvidence(page, config.focus);
      requireVisibleFocus(routeName, result.focus);
    }

    const axe = await new AxeBuilder({ page }).analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);
    if (consoleErrors.length) throw new Error(`console errors: ${JSON.stringify(consoleErrors)}`);
    if (pageErrors.length) throw new Error(`page errors: ${JSON.stringify(pageErrors)}`);

    await page.screenshot({ path: path.join(outputDir, `${routeName}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${routeName}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `failure-${routeName}-${viewportName}.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [routeName, config] of Object.entries(routes)) {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspectCase(browser, routeName, config, viewportName, viewport);
    }
  }
} finally {
  await browser.close();
}

summary.total_cases = summary.cases.length;
summary.blocking_failures = failures.length;
fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (summary.total_cases !== 32) failures.push(`expected 32 cases, got ${summary.total_cases}`);
if (failures.length) {
  summary.blocking_failures = failures.length;
  fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
  failures.forEach((failure) => console.error(`FAIL: ${failure}`));
  process.exit(1);
}

console.log('PASS: X6 cross-surface browser matrix 32/32');
