import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const routes = {
  comments: process.env.X5_COMMENT_URL,
  blocks: process.env.X5_BLOCKS_URL,
  password: process.env.X5_PASSWORD_URL,
  search: process.env.X5_SEARCH_URL,
};

const outputDir = process.env.X5_FORM_STATE_DIR || '/tmp/x5-form-l4';
const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

for (const [name, url] of Object.entries(routes)) {
  if (!url) throw new Error(`Missing required X5 URL: ${name}`);
}

fs.mkdirSync(outputDir, { recursive: true });

const routeConfig = {
  comments: {
    controls: [
      '#respond input[name="author"]',
      '#respond input[name="email"]',
      '#respond textarea#comment',
      '#respond input[type="submit"]',
    ],
    textFocus: '#respond input[name="author"]',
    actionFocus: '#respond input[type="submit"]',
    axeRoot: '#comments',
  },
  blocks: {
    controls: [
      '.aznet-theme-entry__content .wp-block-search input[type="search"]',
      '.aznet-theme-entry__content .wp-block-search button',
      '.aznet-theme-entry__content .wp-block-categories-dropdown select',
    ],
    textFocus: '.aznet-theme-entry__content .wp-block-categories-dropdown select',
    actionFocus: '.aznet-theme-entry__content .wp-block-search button',
    axeRoot: '.aznet-theme-entry__content',
  },
  password: {
    controls: [
      '.aznet-theme-entry__content .post-password-form input[type="password"]',
      '.aznet-theme-entry__content .post-password-form input[type="submit"]',
    ],
    textFocus: '.aznet-theme-entry__content .post-password-form input[type="password"]',
    actionFocus: '.aznet-theme-entry__content .post-password-form input[type="submit"]',
    axeRoot: '.aznet-theme-entry__content',
  },
  search: {
    controls: [
      '.aznet-theme-search-header__form .search-field',
      '.aznet-theme-search-header__form .search-submit',
    ],
    textFocus: '.aznet-theme-search-header__form .search-field',
    actionFocus: '.aznet-theme-search-header__form .search-submit',
    axeRoot: '.aznet-theme-listing',
  },
};

const summary = { routes, cases: [] };
const failures = [];

async function controlStyle(page, selector) {
  return page.locator(selector).evaluate((element) => {
    const style = getComputedStyle(element);
    const rect = element.getBoundingClientRect();
    return {
      width: rect.width,
      height: rect.height,
      left: rect.left,
      right: rect.right,
      borderStyle: style.borderStyle,
      borderWidth: Number.parseFloat(style.borderWidth || '0'),
      borderRadius: style.borderRadius,
      borderColor: style.borderColor,
      opacity: Number.parseFloat(style.opacity || '1'),
      cursor: style.cursor,
      outlineStyle: style.outlineStyle,
      outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      outlineColor: style.outlineColor,
    };
  });
}

async function keyboardFocusEvidence(page, selector) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  for (let index = 0; index < 220; index += 1) {
    await page.keyboard.press('Tab');
    const evidence = await page.evaluate((target) => {
      const element = document.activeElement;
      if (!(element instanceof HTMLElement) || !element.matches(target)) return null;
      const style = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return {
        focusVisible: element.matches(':focus-visible'),
        visible: rect.width > 1 && rect.height > 1,
        outlineStyle: style.outlineStyle,
        outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
        outlineColor: style.outlineColor,
      };
    }, selector);
    if (evidence) return evidence;
  }

  return null;
}

function requireFocus(label, evidence) {
  if (!evidence?.visible || !evidence.focusVisible || evidence.outlineStyle === 'none' || evidence.outlineWidth < 1) {
    throw new Error(`${label} lacks visible :focus-visible evidence: ${JSON.stringify(evidence)}`);
  }
}

async function assertControl(page, selector, viewportWidth) {
  const locator = page.locator(selector);
  if (await locator.count() !== 1) throw new Error(`expected exactly one control for ${selector}`);
  const state = await controlStyle(page, selector);
  if (state.width <= 1 || state.height <= 1) throw new Error(`control not visible: ${selector}`);
  if (state.left < -0.5 || state.right > viewportWidth + 0.5) {
    throw new Error(`control overflows viewport: ${selector} ${JSON.stringify(state)}`);
  }
  if (state.borderStyle === 'none' || state.borderWidth < 1) {
    throw new Error(`control lacks visible border: ${selector} ${JSON.stringify(state)}`);
  }
  if (state.height < 44) {
    throw new Error(`control target height below 44px: ${selector} ${state.height}`);
  }
  return state;
}

async function inspectCase(browser, routeName, url, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const failedSubresources = [];

  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() < 400 || response.request().resourceType() === 'document') return;
    failedSubresources.push(`${response.status()} ${response.request().resourceType()} ${response.url()}`);
  });

  const result = {
    route: routeName,
    viewport: viewportName,
    url,
    overflowPx: null,
    blockingAxeViolations: null,
    textFocus: null,
    actionFocus: null,
    disabledState: null,
    invalidState: null,
    consoleErrors,
    pageErrors,
    failedSubresources,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) {
      throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    }

    if (await page.locator('main').count() !== 1) throw new Error('expected exactly one main landmark');

    const stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
    if (!stylesheets.some((href) => href.includes('/assets/css/components/forms.css'))) {
      throw new Error('X5 forms stylesheet not observed');
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx !== 0) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    for (const selector of routeConfig[routeName].controls) {
      await assertControl(page, selector, viewport.width);
    }

    result.textFocus = await keyboardFocusEvidence(page, routeConfig[routeName].textFocus);
    requireFocus(`${routeName} text/select control`, result.textFocus);
    result.actionFocus = await keyboardFocusEvidence(page, routeConfig[routeName].actionFocus);
    requireFocus(`${routeName} action control`, result.actionFocus);

    if (routeName === 'blocks') {
      const selectSelector = '.aznet-theme-entry__content .wp-block-categories-dropdown select';
      await page.locator(selectSelector).evaluate((element) => { element.disabled = true; });
      result.disabledState = await controlStyle(page, selectSelector);
      if (!(result.disabledState.opacity < 1) || result.disabledState.cursor !== 'not-allowed') {
        throw new Error(`disabled select presentation missing: ${JSON.stringify(result.disabledState)}`);
      }

      const searchSelector = '.aznet-theme-entry__content .wp-block-search input[type="search"]';
      await page.locator(searchSelector).evaluate((element) => element.setAttribute('aria-invalid', 'true'));
      result.invalidState = await page.locator(searchSelector).evaluate((element) => {
        const probe = document.createElement('span');
        probe.style.borderColor = 'var(--aznet-theme-color-danger)';
        document.body.appendChild(probe);
        const expected = getComputedStyle(probe).borderColor;
        probe.remove();
        return {
          actual: getComputedStyle(element).borderColor,
          expected,
        };
      });
      if (!result.invalidState.expected || result.invalidState.actual !== result.invalidState.expected) {
        throw new Error(`invalid-state danger border missing: ${JSON.stringify(result.invalidState)}`);
      }
    }

    if (routeName === 'comments' && await page.locator('#wp-comment-cookies-consent').count()) {
      const checkbox = await page.locator('#wp-comment-cookies-consent').evaluate((element) => {
        const rect = element.getBoundingClientRect();
        return { width: rect.width, height: rect.height };
      });
      if (checkbox.width > 32 || checkbox.height > 32) {
        throw new Error(`comment consent checkbox stretched: ${JSON.stringify(checkbox)}`);
      }
    }

    const axe = await new AxeBuilder({ page }).include(routeConfig[routeName].axeRoot).analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.length}`);

    if (consoleErrors.length) throw new Error(`console errors: ${consoleErrors.length}`);
    if (pageErrors.length) throw new Error(`page errors: ${pageErrors.length}`);
    if (failedSubresources.length) throw new Error(`failed subresources: ${failedSubresources.length}`);

    await page.screenshot({ path: path.join(outputDir, `x5-${routeName}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${routeName}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `x5-${routeName}-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [routeName, url] of Object.entries(routes)) {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspectCase(browser, routeName, url, viewportName, viewport);
    }
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (failures.length) {
  failures.forEach((failure) => console.error(`FAIL: ${failure}`));
  process.exit(1);
}

if (summary.cases.length !== 16) throw new Error(`expected 16 cases, got ${summary.cases.length}`);
console.log('PASS: X5 native form controls browser matrix 16/16');
