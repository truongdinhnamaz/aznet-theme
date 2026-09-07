import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = (process.env.R3_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outputRoot = process.env.R3_STATE_DIR || '/tmp/r3-header';
const scenario = process.env.R3_SCENARIO || 'standard-clean';
const requestedPreset = process.env.R3_PRESET || 'standard';
const stickyMode = process.env.R3_STICKY || 'sticky';
const wooEnabled = process.env.R3_WOO_ENABLED === '1';
const outputDir = path.join(outputRoot, scenario);
const screenshotDir = path.join(outputDir, 'screenshots');
const axeDir = path.join(outputDir, 'axe');
fs.mkdirSync(screenshotDir, { recursive: true });
fs.mkdirSync(axeDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x800': { width: 320, height: 800 },
};

const routes = requestedPreset === 'overlay'
  ? {
      front: { path: '/', expectedPreset: 'overlay' },
      inner: { path: '/r3-inner/', expectedPreset: 'standard' },
    }
  : {
      inner: { path: '/r3-inner/', expectedPreset: requestedPreset },
    };

const summary = {
  scenario,
  requestedPreset,
  stickyMode,
  wooEnabled,
  cases: [],
  noJs: null,
};
const failures = [];

function hasVisibleFocusIndicator(payload) {
  return Boolean(payload?.visible && (payload?.hasOutline || payload?.hasShadow));
}

async function activeElementSnapshot(page) {
  return page.evaluate(() => {
    const element = document.activeElement;
    if (!(element instanceof HTMLElement)) return null;
    const style = getComputedStyle(element);
    const rect = element.getBoundingClientRect();
    const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
    return {
      tagName: element.tagName,
      className: String(element.className || ''),
      id: element.id || '',
      href: element.getAttribute('href'),
      insideMobilePanel: Boolean(element.closest('[data-aznet-theme-nav-panel]')),
      visible: rect.width > 1 && rect.height > 1,
      hasOutline: style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent',
      hasShadow: Boolean(style.boxShadow && style.boxShadow !== 'none'),
    };
  });
}

async function tabUntil(page, predicate, maxTabs = 20) {
  for (let index = 0; index < maxTabs; index += 1) {
    await page.keyboard.press('Tab');
    const snapshot = await activeElementSnapshot(page);
    if (predicate(snapshot)) return snapshot;
  }
  return null;
}

async function verifyFirstFocus(page) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  await page.keyboard.press('Tab');
  const snapshot = await activeElementSnapshot(page);
  if (!snapshot?.className.includes('aznet-theme-skip-link') || snapshot.href !== '#main' || !hasVisibleFocusIndicator(snapshot)) {
    throw new Error(`first keyboard focus is not the visible Theme skip link: ${JSON.stringify(snapshot)}`);
  }
  return snapshot;
}

async function verifyDesktopKeyboard(page) {
  const parentLink = await tabUntil(
    page,
    (snapshot) => snapshot?.visible && snapshot.className.includes('r3-parent-link'),
    15,
  );
  if (!parentLink || !hasVisibleFocusIndicator(parentLink)) {
    throw new Error(`depth-1 primary navigation was not keyboard reachable with visible focus: ${JSON.stringify(parentLink)}`);
  }

  await page.keyboard.press('Tab');
  const child = await activeElementSnapshot(page);
  if (!child?.visible || !child.className.includes('r3-child-link') || !hasVisibleFocusIndicator(child)) {
    throw new Error(`depth-2 submenu link was not keyboard reachable: ${JSON.stringify(child)}`);
  }
}

async function verifyMobileKeyboard(page) {
  const trigger = await tabUntil(
    page,
    (snapshot) => snapshot?.visible && snapshot.className.includes('aznet-theme-site-header__mobile-trigger'),
    12,
  );
  if (!trigger || !hasVisibleFocusIndicator(trigger)) {
    throw new Error(`mobile trigger was not keyboard reachable with visible focus: ${JSON.stringify(trigger)}`);
  }

  await page.keyboard.press('Enter');
  const panel = page.locator('[data-aznet-theme-nav-panel]');
  await panel.waitFor({ state: 'visible' });
  const expanded = await page.locator('[data-aznet-theme-nav-trigger]').getAttribute('aria-expanded');
  if (expanded !== 'true') throw new Error(`mobile trigger aria-expanded expected true, got ${expanded}`);

  const bodyOverflow = await page.evaluate(() => document.body.style.overflow);
  if (bodyOverflow !== 'hidden') throw new Error(`mobile open state did not lock body scroll: ${bodyOverflow}`);

  const firstInside = await activeElementSnapshot(page);
  if (!firstInside?.insideMobilePanel || !firstInside.visible) {
    throw new Error(`mobile open state did not move focus into panel: ${JSON.stringify(firstInside)}`);
  }

  await page.keyboard.press('Shift+Tab');
  const wrapped = await activeElementSnapshot(page);
  if (!wrapped?.insideMobilePanel || !wrapped.visible) {
    throw new Error(`mobile focus containment failed: ${JSON.stringify(wrapped)}`);
  }

  await page.keyboard.press('Escape');
  await panel.waitFor({ state: 'hidden' });
  const returned = await activeElementSnapshot(page);
  if (!returned?.className.includes('aznet-theme-site-header__mobile-trigger')) {
    throw new Error(`Escape did not return focus to mobile trigger: ${JSON.stringify(returned)}`);
  }
  const collapsed = await page.locator('[data-aznet-theme-nav-trigger]').getAttribute('aria-expanded');
  if (collapsed !== 'false') throw new Error(`mobile trigger aria-expanded expected false, got ${collapsed}`);
  const restoredOverflow = await page.evaluate(() => document.body.style.overflow);
  if (restoredOverflow === 'hidden') throw new Error('mobile close state did not restore body scroll');
}

async function verifyStickyCompact(page) {
  if (stickyMode !== 'sticky-compact') return null;

  await page.evaluate(() => {
    window.__r3Cls = 0;
    window.__r3Observer = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (!entry.hadRecentInput) window.__r3Cls += entry.value;
      }
    });
    window.__r3Observer.observe({ type: 'layout-shift', buffered: true });
  });

  await page.evaluate(() => window.scrollTo(0, 700));
  await page.waitForTimeout(350);
  const compact = await page.locator('[data-aznet-theme-site-header]').evaluate((header) => header.classList.contains('is-aznet-theme-header-compact'));
  const cls = await page.evaluate(() => {
    window.__r3Observer?.disconnect();
    return Number(window.__r3Cls || 0);
  });
  if (!compact) throw new Error('sticky-compact enhancement did not enter compact state after scroll');
  if (cls > 0.05) throw new Error(`sticky-compact caused meaningful CLS ${cls}`);
  return cls;
}

async function inspectCase(browser, routeName, route, viewportName, viewport) {
  const context = await browser.newContext({
    viewport,
    reducedMotion: viewport.width <= 390 ? 'reduce' : 'no-preference',
  });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const failedSubresources = [];

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() >= 400 && response.request().resourceType() !== 'document') {
      failedSubresources.push(`${response.status()} ${response.request().resourceType()} ${response.url()}`);
    }
  });

  const key = `${routeName}-${viewportName}`;
  const result = {
    route: routeName,
    viewport: viewportName,
    expectedPreset: route.expectedPreset,
    overflowPx: null,
    duplicateIds: [],
    bannerCount: null,
    scripts: [],
    cls: null,
    axeBlocking: null,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(`${baseUrl}${route.path}`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    const header = page.locator('[data-aznet-theme-site-header]');
    await header.waitFor({ state: 'visible' });
    await page.locator('main#main').waitFor({ state: 'visible' });

    const headerClass = String((await header.getAttribute('class')) || '');
    if (!headerClass.split(/\s+/).includes(`aznet-theme-site-header--${route.expectedPreset}`)) {
      throw new Error(`effective preset mismatch: expected ${route.expectedPreset}, class=${headerClass}`);
    }
    if (requestedPreset === 'overlay' && routeName === 'inner' && headerClass.includes('aznet-theme-site-header--overlay')) {
      throw new Error('overlay leaked onto a non-front-page route');
    }

    result.bannerCount = await page.locator('[role="banner"]').count();
    if (result.bannerCount !== 1) throw new Error(`expected one banner landmark, got ${result.bannerCount}`);

    result.duplicateIds = await page.evaluate(() => {
      const seen = new Set();
      const duplicate = new Set();
      for (const node of document.querySelectorAll('[id]')) {
        if (!node.id) continue;
        if (seen.has(node.id)) duplicate.add(node.id);
        seen.add(node.id);
      }
      return [...duplicate].sort();
    });
    if (result.duplicateIds.length > 0) throw new Error(`duplicate IDs: ${result.duplicateIds.join(', ')}`);

    result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflowPx > 1) throw new Error(`horizontal overflow ${result.overflowPx}px`);

    await page.getByText('AZnet Header Fixture — Một tiêu đề website rất dài để kiểm tra khả năng co giãn', { exact: false }).first().waitFor({ state: 'visible' });

    result.scripts = await page.locator('script[src]').evaluateAll((scripts) => scripts.map((script) => script.src));
    if (!result.scripts.some((src) => src.includes('/assets/js/header-navigation.js'))) {
      throw new Error('header-navigation.js was not loaded while the mobile panel capability is active');
    }
    const stickyScriptLoaded = result.scripts.some((src) => src.includes('/assets/js/sticky-header.js'));
    if (stickyScriptLoaded !== (stickyMode === 'sticky-compact')) {
      throw new Error(`sticky script scope mismatch: loaded=${stickyScriptLoaded}, mode=${stickyMode}`);
    }

    const accountCount = await page.locator('.aznet-theme-site-header__utility--account').count();
    const cartCount = await page.locator('.aznet-theme-site-header__utility--cart').count();
    if (requestedPreset === 'commerce') {
      if (wooEnabled && (accountCount < 1 || cartCount < 1)) {
        throw new Error(`Woo-present Commerce Header missing public account/cart actions: ${accountCount}/${cartCount}`);
      }
      if (!wooEnabled && (accountCount !== 0 || cartCount !== 0)) {
        throw new Error('Woo-absent Commerce Header leaked account/cart actions');
      }
    }

    if (route.expectedPreset === 'overlay') {
      const surface = await header.evaluate((node) => {
        const style = getComputedStyle(node);
        return { backgroundColor: style.backgroundColor, color: style.color };
      });
      if (!surface.backgroundColor || surface.backgroundColor === 'transparent' || surface.backgroundColor === 'rgba(0, 0, 0, 0)') {
        throw new Error(`overlay computed background is transparent: ${JSON.stringify(surface)}`);
      }
    }

    await verifyFirstFocus(page);
    if (viewport.width > 980) {
      await verifyDesktopKeyboard(page);
    } else {
      await verifyMobileKeyboard(page);
    }

    if (stickyMode === 'sticky-compact' && viewport.width <= 390) {
      const transitionDuration = await page.locator('.aznet-theme-site-header__inner').evaluate((node) => getComputedStyle(node).transitionDuration);
      if (transitionDuration !== '0s') throw new Error(`reduced motion did not disable sticky transition: ${transitionDuration}`);
    }

    result.cls = await verifyStickyCompact(page);

    const axeResults = await new AxeBuilder({ page }).include('.aznet-theme-site-header').analyze();
    fs.writeFileSync(path.join(axeDir, `${key}.json`), JSON.stringify(axeResults, null, 2));
    const blocking = axeResults.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.axeBlocking = blocking.length;
    if (blocking.length > 0) throw new Error(`blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);

    if (failedSubresources.length > 0) throw new Error(`failed subresources: ${failedSubresources.join(' | ')}`);
    if (consoleErrors.length > 0) throw new Error(`console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length > 0) throw new Error(`page errors: ${pageErrors.join(' | ')}`);

    await page.screenshot({ path: path.join(screenshotDir, `${key}.png`), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R3 ${scenario} ${key}`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${key}: ${result.error}`);
    console.error(`FAIL: R3 ${scenario} ${key}: ${result.error}`);
    await page.screenshot({ path: path.join(screenshotDir, `${key}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

async function inspectNoJs(browser) {
  const route = requestedPreset === 'overlay' ? routes.front : routes.inner;
  const context = await browser.newContext({
    viewport: { width: 390, height: 844 },
    javaScriptEnabled: false,
    reducedMotion: 'reduce',
  });
  const page = await context.newPage();
  const result = { status: 'failed', overflowPx: null, keyboardReachable: false, error: null };

  try {
    const response = await page.goto(`${baseUrl}${route.path}`, { waitUntil: 'domcontentloaded' });
    if (!response || response.status() !== 200) throw new Error(`no-JS expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    const trigger = page.locator('[data-aznet-theme-nav-trigger]');
    const panel = page.locator('[data-aznet-theme-nav-panel]');
    await trigger.waitFor({ state: 'visible' });
    await panel.waitFor({ state: 'visible' });
    await panel.locator('nav a').first().waitFor({ state: 'visible' });

    result.overflowPx = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    if (result.overflowPx > 1) throw new Error(`no-JS mobile fallback overflow ${result.overflowPx}px`);

    await verifyFirstFocus(page);
    const navLink = await tabUntil(page, (snapshot) => snapshot?.visible && snapshot.insideMobilePanel && Boolean(snapshot.href), 16);
    if (!navLink) throw new Error('no-JS mobile primary navigation was not keyboard reachable');
    result.keyboardReachable = true;

    await page.screenshot({ path: path.join(screenshotDir, 'no-js-390x844.png'), fullPage: true });
    result.status = 'passed';
    console.log(`PASS: R3 ${scenario} no-JS mobile fallback`);
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`no-js: ${result.error}`);
    console.error(`FAIL: R3 ${scenario} no-JS: ${result.error}`);
  } finally {
    summary.noJs = result;
    await context.close();
  }
}

const browser = await chromium.launch({ headless: true });
try {
  for (const [routeName, route] of Object.entries(routes)) {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspectCase(browser, routeName, route, viewportName, viewport);
    }
  }
  await inspectNoJs(browser);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length > 0) {
  throw new Error(`R3 ${scenario} L4 failed:\n${failures.join('\n')}`);
}
console.log(`PASS: R3 ${scenario} Header System L4 ${summary.cases.length} browser cases + no-JS`);
