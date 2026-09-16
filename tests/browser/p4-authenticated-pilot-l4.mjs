import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const P4_AUTH_BASE_URL = (process.env.P4_AUTH_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const P4_AUTH_ADMIN_USER = process.env.P4_AUTH_ADMIN_USER || '';
const P4_AUTH_ADMIN_PASS = process.env.P4_AUTH_ADMIN_PASS || '';
const stateDir = process.env.P4_AUTH_STATE_DIR || '/tmp/p4-authenticated-pilot';

fs.mkdirSync(stateDir, { recursive: true });

const blocking_failures = [];
const observations = {
  admin_overview: {},
  system_health: {},
  viewport_checks: [],
  console_errors: [],
  page_errors: [],
  request_failures: [],
};

function recordFailure(code, message) {
  blocking_failures.push({ code, message });
}

function versionAtLeast(value, minimumMajor, minimumMinor) {
  const match = String(value || '').match(/^(\d+)\.(\d+)/);
  if (!match) return false;
  const major = Number.parseInt(match[1], 10);
  const minor = Number.parseInt(match[2], 10);
  return major > minimumMajor || (major === minimumMajor && minor >= minimumMinor);
}

function isThemeOwnedDiagnostic(text) {
  return /aznet-theme|wp-content\/themes\/aznet/i.test(String(text || ''));
}

async function login(page) {
  if (!P4_AUTH_ADMIN_USER || !P4_AUTH_ADMIN_PASS) {
    throw new Error('Required GitHub Actions credentials are unavailable');
  }

  await page.goto(`${P4_AUTH_BASE_URL}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('#user_login').fill(P4_AUTH_ADMIN_USER);
  await page.locator('#user_pass').fill(P4_AUTH_ADMIN_PASS);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 30000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function gotoControlCenter(page, section = 'overview') {
  const suffix = section === 'overview' ? '' : `&section=${section}`;
  await page.goto(`${P4_AUTH_BASE_URL}/wp-admin/admin.php?page=aznet-theme${suffix}`, {
    waitUntil: 'domcontentloaded',
    timeout: 30000,
  });
  await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 20000 });
}

async function verifyViewport(page, viewportName, viewport) {
  await page.setViewportSize(viewport);
  await gotoControlCenter(page, 'system-health');

  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  if (overflow > 1) {
    recordFailure('THEME_ADMIN_OVERFLOW', `${viewportName}: horizontal overflow ${overflow}px`);
  }

  const firstTab = page.locator('.aznet-theme-control-center .nav-tab').first();
  await firstTab.focus();
  const focus = await firstTab.evaluate((node) => {
    const style = getComputedStyle(node);
    return {
      active: document.activeElement === node,
      outline: style.outlineStyle !== 'none' && Number.parseFloat(style.outlineWidth || '0') > 0,
      shadow: Boolean(style.boxShadow && style.boxShadow !== 'none'),
    };
  });
  if (!focus.active || (!focus.outline && !focus.shadow)) {
    recordFailure('THEME_ADMIN_FOCUS', `${viewportName}: visible focus indicator missing`);
  }

  const axe = await new AxeBuilder({ page }).include('.aznet-theme-control-center').analyze();
  const serious = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact || ''));
  if (serious.length) {
    recordFailure('THEME_ADMIN_A11Y', `${viewportName}: axe critical/serious ${serious.map((item) => item.id).join(', ')}`);
  }

  fs.writeFileSync(path.join(stateDir, `${viewportName}-axe.json`), JSON.stringify(axe, null, 2));
  await page.screenshot({ path: path.join(stateDir, `${viewportName}-system-health.png`), fullPage: true });
  observations.viewport_checks.push({ viewportName, overflow, focus, serious_axe: serious.map((item) => item.id) });
}

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();

  page.on('console', (message) => {
    if (message.type() === 'error') observations.console_errors.push(message.text());
  });
  page.on('pageerror', (error) => observations.page_errors.push(error.stack || error.message));
  page.on('requestfailed', (request) => {
    observations.request_failures.push({ url: request.url(), error: request.failure()?.errorText || 'unknown' });
  });

  try {
    await login(page);
  } catch (error) {
    recordFailure('AUTH_LOGIN_FAILED', error instanceof Error ? error.message : String(error));
  }

  if (!blocking_failures.length) {
    try {
      await gotoControlCenter(page, 'overview');
      const heading = (await page.locator('h1').first().textContent())?.trim() || '';
      const adminMenuCount = await page.locator('#toplevel_page_aznet-theme').count();
      observations.admin_overview = { heading, admin_menu_count: adminMenuCount };
      if (heading !== 'AZnet Theme') recordFailure('THEME_ADMIN_OVERVIEW', `Unexpected Control Center heading: ${heading}`);
      if (adminMenuCount !== 1) recordFailure('THEME_ADMIN_MENU', `Expected one AZnet Theme admin menu, found ${adminMenuCount}`);
      await page.screenshot({ path: path.join(stateDir, 'overview-1440.png'), fullPage: true });
    } catch (error) {
      recordFailure('THEME_ADMIN_OVERVIEW', error instanceof Error ? error.message : String(error));
    }
  }

  if (!blocking_failures.length) {
    try {
      await gotoControlCenter(page, 'system-health');
      const center = page.locator('.aznet-theme-control-center');
      const text = await center.innerText();
      for (const needle of [
        'Standalone Core',
        'READY',
        'Optional Integrations',
        'WooCommerce',
        'RootProfile v1',
        'RootProfile v2',
        'RootProfile Current Surface',
        'ConvertFlow',
        'Support Snapshot',
      ]) {
        if (!text.includes(needle)) recordFailure('THEME_SYSTEM_HEALTH_UI', `System Health missing ${needle}`);
      }

      const snapshotField = page.locator('textarea[readonly]');
      if ((await snapshotField.count()) !== 1) {
        recordFailure('THEME_SUPPORT_SNAPSHOT', 'Expected exactly one read-only Support Snapshot field');
      } else {
        const snapshot = JSON.parse(await snapshotField.inputValue());
        const report = snapshot?.report || {};
        const core = report?.standalone_core || {};
        const optional = report?.optional_integrations || {};

        observations.system_health = {
          product: snapshot?.product || null,
          environment: report?.environment || null,
          theme: report?.theme || null,
          standalone_core: core,
          optional_integrations: optional,
        };

        if (snapshot?.product !== 'aznet-theme') recordFailure('THEME_SUPPORT_SNAPSHOT', 'Support Snapshot product marker mismatch');
        if (report?.theme?.name !== 'AZnet Theme') recordFailure('THEME_ACTIVE_IDENTITY', `Unexpected active Theme name: ${report?.theme?.name || 'missing'}`);
        if (report?.theme?.version !== '1.1.0') recordFailure('THEME_ACTIVE_VERSION', `Unexpected active Theme version: ${report?.theme?.version || 'missing'}`);
        if (!versionAtLeast(report?.environment?.wordpress, 6, 9)) recordFailure('THEME_ENVIRONMENT', `WordPress below supported floor or unreadable: ${report?.environment?.wordpress || 'missing'}`);
        if (!versionAtLeast(report?.environment?.php, 8, 1)) recordFailure('THEME_ENVIRONMENT', `PHP below supported floor or unreadable: ${report?.environment?.php || 'missing'}`);
        if (core?.status !== 'ready') recordFailure('THEME_STANDALONE_CORE', `Standalone Core status is ${core?.status || 'missing'}`);
        if (!['default', 'editorial', 'commerce'].includes(core?.visual_preset)) recordFailure('THEME_CURRENT_CONFIG', `Invalid visual preset: ${core?.visual_preset || 'missing'}`);
        if (!['standard', 'compact', 'commerce', 'overlay'].includes(core?.header_preset)) recordFailure('THEME_CURRENT_CONFIG', `Invalid header preset: ${core?.header_preset || 'missing'}`);
        if (typeof core?.logo !== 'boolean') recordFailure('THEME_CURRENT_CONFIG', 'Logo continuity marker is not boolean');
        if (typeof core?.primary_menu !== 'boolean') recordFailure('THEME_CURRENT_CONFIG', 'Primary menu continuity marker is not boolean');

        const allowedOptionalStates = new Set(['available', 'not_present', 'unknown']);
        for (const [key, value] of Object.entries(optional)) {
          if (!allowedOptionalStates.has(value)) recordFailure('THEME_OPTIONAL_DIAGNOSTIC', `Invalid optional integration state ${key}=${value}`);
        }
        if (optional?.convertflow !== 'unknown') recordFailure('THEME_CONVERTFLOW_BOUNDARY', `ConvertFlow must remain unknown without a public capability contract; got ${optional?.convertflow || 'missing'}`);
      }
    } catch (error) {
      recordFailure('THEME_SYSTEM_HEALTH', error instanceof Error ? error.message : String(error));
    }
  }

  if (!blocking_failures.length) {
    await verifyViewport(page, '1440x1000', { width: 1440, height: 1000 });
    await verifyViewport(page, '390x844', { width: 390, height: 844 });
  }

  for (const item of observations.console_errors) {
    if (isThemeOwnedDiagnostic(item)) recordFailure('THEME_CONSOLE_ERROR', item);
  }
  for (const item of observations.page_errors) {
    if (isThemeOwnedDiagnostic(item)) recordFailure('THEME_PAGE_ERROR', item);
  }
  for (const item of observations.request_failures) {
    if (isThemeOwnedDiagnostic(item.url)) recordFailure('THEME_REQUEST_FAILURE', `${item.url}: ${item.error}`);
  }

  await context.close();
} finally {
  await browser.close();
}

const summary = {
  base_url: P4_AUTH_BASE_URL,
  scope: 'authenticated-read-only-admin',
  blocking_failures,
  observations,
};
fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (blocking_failures.length) {
  console.error(`P4 authenticated pilot blocking failures: ${blocking_failures.map((item) => item.code).join(', ')}`);
  process.exit(1);
}

console.log('PASS: P4 authenticated pilot read-only Admin/System Health QA');
