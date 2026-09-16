const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = (process.env.D027_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const adminUser = process.env.D027_ADMIN_USER || 'admin';
const adminPass = process.env.D027_ADMIN_PASS || '';
const stateDir = process.env.D027_STATE_DIR || '/tmp/d027-l4';
const postId = process.env.D027_POST_ID || '';
const pageId = process.env.D027_PAGE_ID || '';
const catId = process.env.D027_CAT_ID || '';

if (!adminPass) throw new Error('D027_ADMIN_PASS is required');
if (!postId || !pageId || !catId) throw new Error('D027_POST_ID, D027_PAGE_ID and D027_CAT_ID are required');

fs.mkdirSync(stateDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x800': { width: 320, height: 800 },
};

const routes = {
  home: '/',
  post: `/?p=${postId}`,
  page: `/?page_id=${pageId}`,
  category: `/?cat=${catId}`,
  search: '/?s=luat',
  notFound: '/?p=99999999',
};

const failures = [];
const results = [];

function fail(message) {
  throw new Error(message);
}

async function login(page) {
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(adminUser);
  await page.locator('#user_pass').fill(adminPass);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 20000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function assertNoForbiddenDependencyCopy(page, label) {
  const text = await page.locator('body').innerText();
  if (/required plugin|missing dependency|install required plugins/i.test(text)) {
    fail(`${label}: optional provider is presented as a required dependency`);
  }
}

async function assertFocusVisible(locator, label) {
  if (!(await locator.count())) fail(`${label}: no interactive control available for focus check`);
  await locator.first().focus();
  const focus = await locator.first().evaluate((node) => {
    const style = getComputedStyle(node);
    return {
      active: document.activeElement === node,
      outlineStyle: style.outlineStyle,
      outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      boxShadow: style.boxShadow || '',
    };
  });
  const visible = focus.active && (
    (focus.outlineStyle !== 'none' && focus.outlineWidth > 0) ||
    (focus.boxShadow && focus.boxShadow !== 'none')
  );
  if (!visible) fail(`${label}: visible keyboard focus indicator missing ${JSON.stringify(focus)}`);
}

async function assertHeadingFlow(page, rootSelector, label) {
  const levels = await page.locator(`${rootSelector} h1, ${rootSelector} h2, ${rootSelector} h3, ${rootSelector} h4, ${rootSelector} h5, ${rootSelector} h6`).evaluateAll((nodes) =>
    nodes.filter((node) => {
      const style = getComputedStyle(node);
      return style.display !== 'none' && style.visibility !== 'hidden';
    }).map((node) => Number(node.tagName.slice(1)))
  );
  if (!levels.length) fail(`${label}: no visible heading in covered surface`);
  if (levels[0] !== 1) fail(`${label}: covered surface does not start with h1 (${levels.join(',')})`);
  for (let i = 1; i < levels.length; i += 1) {
    if (levels[i] > levels[i - 1] + 1) {
      fail(`${label}: heading level jumps from h${levels[i - 1]} to h${levels[i]}`);
    }
  }
}

async function assertAxe(page, include, label, outputDir) {
  const axe = await new AxeBuilder({ page }).include(include).analyze();
  fs.writeFileSync(path.join(outputDir, `${label}-axe.json`), JSON.stringify(axe, null, 2));
  const serious = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact || ''));
  if (serious.length) fail(`${label}: axe critical/serious violations ${serious.map((item) => item.id).join(',')}`);
}

async function assertNoOverflow(page, selector, label) {
  const overflow = await page.locator(selector).evaluate((node) => node.scrollWidth - node.clientWidth);
  if (overflow > 1) fail(`${label}: horizontal overflow ${overflow}px`);
  return overflow;
}

async function verifyAdmin(page, viewportName, outputDir) {
  for (const section of ['provisioning', 'system-health']) {
    const label = `${viewportName}-admin-${section}`;
    await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=${section}`, { waitUntil: 'domcontentloaded' });
    await page.locator('.aznet-theme-control-center').waitFor({ state: 'visible', timeout: 20000 });
    await assertNoForbiddenDependencyCopy(page, label);
    await assertNoOverflow(page, '.aznet-theme-control-center', label);
    await assertFocusVisible(page.locator('.aznet-theme-control-center a[href], .aznet-theme-control-center button, .aznet-theme-control-center input, .aznet-theme-control-center select'), label);
    await assertAxe(page, '.aznet-theme-control-center', label, outputDir);

    if (section === 'system-health') {
      const text = await page.locator('.aznet-theme-control-center').innerText();
      for (const needle of ['Standalone Core', 'READY', 'Optional Integrations', 'WooCommerce', 'Not present', 'RootProfile', 'ConvertFlow', 'Unknown', 'Support Snapshot']) {
        if (!text.includes(needle)) fail(`${label}: System Health missing visible text ${needle}`);
      }
      const parsed = JSON.parse(await page.locator('textarea[readonly]').inputValue());
      if (parsed.product !== 'aznet-theme') fail(`${label}: Support Snapshot product mismatch`);
      if (parsed.report?.standalone_core?.status !== 'ready') fail(`${label}: Standalone Core snapshot status is not ready`);
      if (parsed.report?.optional_integrations?.woocommerce !== 'not_present') fail(`${label}: WooCommerce absence downgraded or misreported`);
      if (parsed.report?.optional_integrations?.rootprofile_v1 !== 'not_present') fail(`${label}: RootProfile v1 absence misreported`);
      if (parsed.report?.optional_integrations?.rootprofile_v2 !== 'not_present') fail(`${label}: RootProfile v2 absence misreported`);
      if (parsed.report?.optional_integrations?.convertflow !== 'unknown') fail(`${label}: ConvertFlow detector state must remain informational unknown`);
      await page.screenshot({ path: path.join(outputDir, `${label}.png`), fullPage: true });
    }
  }
}

async function verifyFrontend(page, viewportName, outputDir) {
  for (const [routeName, routePath] of Object.entries(routes)) {
    const label = `${viewportName}-${routeName}`;
    const consoleErrors = [];
    const pageErrors = [];
    const onConsole = (message) => { if (message.type() === 'error') consoleErrors.push(message.text()); };
    const onPageError = (error) => pageErrors.push(error.message || String(error));
    page.on('console', onConsole);
    page.on('pageerror', onPageError);

    const response = await page.goto(`${baseUrl}${routePath}`, { waitUntil: 'domcontentloaded' });
    const status = response ? response.status() : 0;
    const expectedStatus = routeName === 'notFound' ? 404 : 200;
    if (status !== expectedStatus) fail(`${label}: expected HTTP ${expectedStatus}, got ${status}`);
    await page.locator('main').waitFor({ state: 'visible', timeout: 20000 });
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (overflow > 1) fail(`${label}: document horizontal overflow ${overflow}px`);
    await assertHeadingFlow(page, 'main', label);
    await assertFocusVisible(page.locator('header a[href]:visible, main a[href]:visible, main button:visible, main input:visible'), label);
    await assertAxe(page, 'main', label, outputDir);
    await assertNoForbiddenDependencyCopy(page, label);

    if (pageErrors.length) fail(`${label}: uncaught browser errors ${pageErrors.join(' | ')}`);
    if (consoleErrors.length) fail(`${label}: browser console errors ${consoleErrors.join(' | ')}`);

    if (routeName === 'home') {
      if (!(await page.locator('.aznet-theme-homepage--law-01').count())) fail(`${label}: Law01 Homepage Composer surface missing`);
      const hero = page.locator('.aznet-theme-law01-hero__image');
      if (!(await hero.count())) fail(`${label}: provisioned Hero image missing`);
      const src = await hero.first().getAttribute('src');
      if (!src || !src.includes('/uploads/')) fail(`${label}: Hero is not served from WordPress uploads (${src || 'empty'})`);
      if (src.includes('/themes/aznet-theme/')) fail(`${label}: Hero still depends on Theme package path`);
      const heroUrl = new URL(src, baseUrl);
      if (heroUrl.origin !== new URL(baseUrl).origin) fail(`${label}: Hero requires external runtime host ${heroUrl.origin}`);
      await page.screenshot({ path: path.join(outputDir, `${label}.png`), fullPage: true });
    }

    results.push({ viewportName, routeName, status, overflow });
    page.off('console', onConsole);
    page.off('pageerror', onPageError);
  }
}

(async () => {
  const browser = await chromium.launch({ headless: true });
  try {
    const loginContext = await browser.newContext({ viewport: viewports['1440x1000'] });
    const loginPage = await loginContext.newPage();
    await login(loginPage);
    const storageState = await loginContext.storageState();
    await loginContext.close();

    for (const [viewportName, viewport] of Object.entries(viewports)) {
      const outputDir = path.join(stateDir, viewportName);
      fs.mkdirSync(outputDir, { recursive: true });
      const context = await browser.newContext({ viewport, storageState });
      const page = await context.newPage();
      try {
        await verifyAdmin(page, viewportName, outputDir);
        await verifyFrontend(page, viewportName, outputDir);
      } catch (error) {
        failures.push(`${viewportName}: ${error instanceof Error ? error.message : String(error)}`);
        await page.screenshot({ path: path.join(outputDir, 'failure.png'), fullPage: true }).catch(() => {});
      } finally {
        await context.close();
      }
    }
  } finally {
    await browser.close();
  }

  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify({ results, failures }, null, 2));
  if (failures.length) {
    console.error(failures.join('\n'));
    process.exit(1);
  }
  console.log(`PASS: D-027 standalone browser/a11y matrix ${results.length}/${results.length}`);
})();
