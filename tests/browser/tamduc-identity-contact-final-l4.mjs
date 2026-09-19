import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-identity-contact-final';
const logoSelector = 'img.aznet-theme-site-header__logo, img.custom-logo';
const expectedLogoFilename = 'tam-duc-ha-noi-logo-official.jpg';

const viewports = [
  { name: '1440x1000', width: 1440, height: 1000 },
  { name: '390x844', width: 390, height: 844 },
];

fs.mkdirSync(stateDir, { recursive: true });

const summary = {
  scope: 'public-read-only-identity-contact-final-l4',
  base_url: baseUrl,
  viewports: [],
  screenshots: [],
  blocking_failures: [],
};

const browser = await chromium.launch({ headless: true });

for (const viewport of viewports) {
  const context = await browser.newContext({ viewport: { width: viewport.width, height: viewport.height } });
  const page = await context.newPage();
  const console_errors = [];
  const page_errors = [];
  const request_failures = [];

  page.on('console', (message) => {
    if (message.type() === 'error') console_errors.push(message.text());
  });
  page.on('pageerror', (error) => page_errors.push(error.message));
  page.on('requestfailed', (request) => request_failures.push({
    url: request.url(),
    failure: request.failure()?.errorText || 'unknown',
  }));

  const response = await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  const publicState = await page.evaluate((selector) => {
    const logo = document.querySelector(selector);
    return {
      logo_count: document.querySelectorAll(selector).length,
      logo_src: logo?.getAttribute('src') || '',
      main_count: document.querySelectorAll('main').length,
      h1_count: document.querySelectorAll('h1').length,
      horizontal_overflow: Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth),
    };
  }, logoSelector);

  let mobile_navigation = null;
  if (viewport.width <= 390) {
    const trigger = page.locator('[data-aznet-theme-nav-trigger]');
    const panel = page.locator('[data-aznet-theme-nav-panel]');
    const trigger_visible = await trigger.isVisible();
    const initial_expanded = await trigger.getAttribute('aria-expanded');

    if (trigger_visible) {
      await trigger.focus();
      await page.keyboard.press('Enter');
      await panel.waitFor({ state: 'visible', timeout: 5000 });
      const expanded_after_enter = await trigger.getAttribute('aria-expanded');
      const open_horizontal_overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
      await page.keyboard.press('Escape');
      await panel.waitFor({ state: 'hidden', timeout: 5000 });
      const expanded_after_escape = await trigger.getAttribute('aria-expanded');
      const focus_returned = await trigger.evaluate((element) => document.activeElement === element);
      mobile_navigation = {
        trigger_visible,
        initial_expanded,
        expanded_after_enter,
        open_horizontal_overflow,
        expanded_after_escape,
        focus_returned,
      };
    } else {
      mobile_navigation = { trigger_visible };
    }
  }

  const record = {
    viewport: viewport.name,
    http_status: response?.status() || 0,
    logo_count: publicState.logo_count,
    logo_src: publicState.logo_src,
    main_count: publicState.main_count,
    h1_count: publicState.h1_count,
    horizontal_overflow: publicState.horizontal_overflow,
    console_errors,
    page_errors,
    request_failures,
    mobile_navigation,
  };

  const failures = [];
  if (record.http_status !== 200) failures.push(`HTTP_${record.http_status}`);
  if (record.logo_count !== 1) failures.push(`LOGO_COUNT_${record.logo_count}`);
  if (!record.logo_src.includes(expectedLogoFilename)) failures.push('LOGO_SRC_MISMATCH');
  if (record.main_count !== 1) failures.push(`MAIN_COUNT_${record.main_count}`);
  if (record.h1_count !== 1) failures.push(`H1_COUNT_${record.h1_count}`);
  if (record.horizontal_overflow > 1) failures.push(`OVERFLOW_${record.horizontal_overflow}`);
  if (console_errors.length) failures.push(`CONSOLE_ERRORS_${console_errors.length}`);
  if (page_errors.length) failures.push(`PAGE_ERRORS_${page_errors.length}`);
  if (request_failures.length) failures.push(`REQUEST_FAILURES_${request_failures.length}`);

  if (viewport.width <= 390) {
    if (!mobile_navigation?.trigger_visible) failures.push('MOBILE_NAV_TRIGGER_MISSING');
    if (mobile_navigation?.initial_expanded !== 'false') failures.push('MOBILE_NAV_INITIAL_STATE');
    if (mobile_navigation?.expanded_after_enter !== 'true') failures.push('MOBILE_NAV_ENTER_OPEN');
    if ((mobile_navigation?.open_horizontal_overflow ?? 999) > 1) failures.push('MOBILE_NAV_OPEN_OVERFLOW');
    if (mobile_navigation?.expanded_after_escape !== 'false') failures.push('MOBILE_NAV_ESCAPE_CLOSE');
    if (!mobile_navigation?.focus_returned) failures.push('MOBILE_NAV_FOCUS_RETURN');
  }

  if (failures.length) summary.blocking_failures.push({ viewport: viewport.name, failures });

  const screenshotPath = path.join(stateDir, `homepage-${viewport.name}.png`);
  await page.screenshot({ path: screenshotPath, fullPage: true });
  summary.screenshots.push(path.basename(screenshotPath));
  summary.viewports.push(record);
  await context.close();
}

await browser.close();
fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (summary.blocking_failures.length) {
  console.error(JSON.stringify(summary.blocking_failures, null, 2));
  process.exitCode = 1;
} else {
  console.log('PASS: Tâm Đức official logo final public L4 verified at 1440x1000 and 390x844');
}
