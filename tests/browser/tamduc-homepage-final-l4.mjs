import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-homepage-final';
const screenshots = path.join(stateDir, 'screenshots');

fs.mkdirSync(screenshots, { recursive: true });

const viewports = [
  ['1440x1000', { width: 1440, height: 1000 }],
  ['1024x900', { width: 1024, height: 900 }],
  ['390x844', { width: 390, height: 844 }],
  ['320x800', { width: 320, height: 800 }],
];

const summary = {
  base_url: baseUrl,
  scope: 'public-homepage-final-l4',
  viewports: {},
  blocking_failures: [],
};

function recordFailure(code, viewport, detail) {
  summary.blocking_failures.push({ code, viewport, detail });
}

function themeOwnedUrl(url) {
  return url.startsWith(baseUrl) && url.includes('/wp-content/themes/aznet-theme/');
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [viewportName, viewport] of viewports) {
    const context = await browser.newContext({ viewport });
    const page = await context.newPage();
    const console_errors = [];
    const page_errors = [];
    const request_failures = [];

    page.on('console', (message) => {
      if (message.type() === 'error') console_errors.push(message.text());
    });
    page.on('pageerror', (error) => page_errors.push(error.stack || error.message));
    page.on('requestfailed', (request) => {
      request_failures.push({
        url: request.url(),
        error: request.failure()?.errorText || 'unknown',
        theme_owned: themeOwnedUrl(request.url()),
      });
    });

    const response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
    const status = response?.status() ?? null;

    const geometry = await page.evaluate(() => {
      const html = document.documentElement;
      const body = document.body;
      const width = Math.max(html.scrollWidth, body.scrollWidth);
      const horizontal_overflow = Math.max(0, width - window.innerWidth);
      const main_count = document.querySelectorAll('main').length;
      const h1_count = document.querySelectorAll('h1').length;
      const navCandidates = Array.from(document.querySelectorAll('nav, [role="navigation"]'));
      const primary_navigation = navCandidates.some((node) => {
        const style = window.getComputedStyle(node);
        const rect = node.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
      });
      const internal_cta_links = Array.from(document.querySelectorAll('main a[href]'))
        .map((node) => ({
          href: node.href,
          text: (node.textContent || '').trim(),
          className: typeof node.className === 'string' ? node.className : '',
        }))
        .filter((item) => item.href.startsWith(window.location.origin) && item.href !== window.location.href && item.text.length > 0);
      const sections = Array.from(document.querySelectorAll('main section')).map((node) => ({
        id: node.id || '',
        className: typeof node.className === 'string' ? node.className : '',
        heading: node.querySelector('h1,h2,h3')?.textContent?.trim() || '',
        width: Math.round(node.getBoundingClientRect().width),
      }));
      return {
        main_count,
        h1_count,
        horizontal_overflow,
        primary_navigation,
        internal_cta_links,
        sections,
        body_class: body.className,
        title: document.title,
        footer_text: document.querySelector('footer')?.innerText || '',
        team_text: Array.from(document.querySelectorAll('main h2, main h3')).map((node) => node.textContent?.trim() || '').filter(Boolean),
      };
    });

    let mobile_navigation = null;
    if (viewport.width <= 390) {
      const trigger = page.locator('[data-aznet-theme-nav-trigger]');
      const panel = page.locator('[data-aznet-theme-nav-panel]');

      await trigger.waitFor({ state: 'visible', timeout: 10000 });
      const initialExpanded = await trigger.getAttribute('aria-expanded');
      if (initialExpanded !== 'false') {
        recordFailure('MOBILE_NAV_INITIAL_STATE', viewportName, `Expected aria-expanded=false, received ${initialExpanded}`);
      }

      await trigger.focus();
      await page.keyboard.press('Enter');
      await panel.waitFor({ state: 'visible', timeout: 10000 });
      const expandedAfterEnter = await trigger.getAttribute('aria-expanded');
      if (expandedAfterEnter !== 'true') {
        recordFailure('MOBILE_NAV_OPEN', viewportName, `Expected aria-expanded=true after Enter, received ${expandedAfterEnter}`);
      }

      const openHorizontalOverflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
      if (openHorizontalOverflow > 1) {
        recordFailure('MOBILE_NAV_OVERFLOW', viewportName, `${openHorizontalOverflow}px while open`);
      }

      await page.keyboard.press('Escape');
      await panel.waitFor({ state: 'hidden', timeout: 10000 });
      const collapsedAfterEscape = await trigger.getAttribute('aria-expanded');
      const focusReturnedAfterEscape = await page.evaluate(() => document.activeElement === document.querySelector('[data-aznet-theme-nav-trigger]'));

      if (collapsedAfterEscape !== 'false') {
        recordFailure('MOBILE_NAV_CLOSE', viewportName, `Expected aria-expanded=false after Escape, received ${collapsedAfterEscape}`);
      }
      if (!focusReturnedAfterEscape) {
        recordFailure('MOBILE_NAV_FOCUS_RETURN', viewportName, 'Escape did not return focus to mobile navigation trigger');
      }

      mobile_navigation = {
        trigger_visible: true,
        initial_expanded: initialExpanded,
        expanded_after_enter: expandedAfterEnter,
        open_horizontal_overflow: openHorizontalOverflow,
        collapsed_after_escape: collapsedAfterEscape,
        focus_returned_after_escape: focusReturnedAfterEscape,
      };
    }

    const axe = await new AxeBuilder({ page }).analyze();
    const seriousA11y = axe.violations
      .filter((violation) => ['critical', 'serious'].includes(violation.impact || ''))
      .map((violation) => ({
        id: violation.id,
        impact: violation.impact,
        help: violation.help,
        targets: violation.nodes.slice(0, 10).map((node) => node.target),
      }));

    const result = {
      viewport: viewportName,
      status,
      ...geometry,
      mobile_navigation,
      console_errors,
      page_errors,
      request_failures,
      axe_critical_serious: seriousA11y,
    };
    summary.viewports[viewportName] = result;

    if (status !== 200) recordFailure('HTTP_STATUS', viewportName, `Expected 200, received ${status}`);
    if (geometry.main_count !== 1) recordFailure('MAIN_COUNT', viewportName, `Expected one main, received ${geometry.main_count}`);
    if (geometry.h1_count < 1) recordFailure('H1_COUNT', viewportName, 'Expected at least one H1');
    if (geometry.horizontal_overflow > 1) recordFailure('HORIZONTAL_OVERFLOW', viewportName, `${geometry.horizontal_overflow}px`);
    if (viewport.width > 390 && !geometry.primary_navigation) recordFailure('PRIMARY_NAVIGATION', viewportName, 'No visible desktop navigation landmark');
    if (geometry.internal_cta_links.length < 1) recordFailure('INTERNAL_CTA_LINKS', viewportName, 'No internal Homepage CTA/link observed');
    if (page_errors.length > 0) recordFailure('PAGE_ERRORS', viewportName, page_errors.join(' | '));
    const themeRequestFailures = request_failures.filter((item) => item.theme_owned && !/ERR_ABORTED/i.test(item.error));
    if (themeRequestFailures.length > 0) recordFailure('THEME_REQUEST_FAILURES', viewportName, JSON.stringify(themeRequestFailures));
    if (seriousA11y.length > 0) recordFailure('AXE_CRITICAL_SERIOUS', viewportName, JSON.stringify(seriousA11y));
    if (!geometry.footer_text.includes('Trọn Tâm với khách') || !geometry.footer_text.includes('Vẹn Đức với nghề')) {
      recordFailure('TAGLINE_PUBLIC', viewportName, 'Approved Site Tagline not observed in footer');
    }
    if (!geometry.team_text.includes('Đội ngũ')) recordFailure('TEAM_TITLE_PUBLIC', viewportName, 'Corrected Đội ngũ heading not observed');

    await page.screenshot({ path: path.join(screenshots, `homepage-${viewportName}.png`), fullPage: true });
    await context.close();
  }
} catch (error) {
  recordFailure('HARNESS_ERROR', 'global', error instanceof Error ? error.stack || error.message : String(error));
} finally {
  await browser.close();
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));
}

if (summary.blocking_failures.length > 0) {
  console.error(JSON.stringify(summary.blocking_failures, null, 2));
  process.exit(1);
}

console.log('PASS: Tâm Đức final public Homepage L4 matrix');
