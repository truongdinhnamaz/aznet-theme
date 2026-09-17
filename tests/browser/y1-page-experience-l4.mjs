import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const routes = {
  child: { url: process.env.Y1_CHILD_URL, variant: 'standard', breadcrumbs: true },
  wide: { url: process.env.Y1_WIDE_URL, variant: 'wide', breadcrumbs: false },
  landing: { url: process.env.Y1_LANDING_URL, variant: 'landing', breadcrumbs: false },
};

const outputDir = process.env.Y1_PAGE_STATE_DIR || '/tmp/y1-page-l4';
const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

for (const [name, config] of Object.entries(routes)) {
  if (!config.url) throw new Error(`Missing required Y1 URL: ${name}`);
}

fs.mkdirSync(outputDir, { recursive: true });
const failures = [];
const summary = { routes, cases: [] };

async function focusEvidence(page, selector) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });
  for (let i = 0; i < 220; i += 1) {
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
      };
    }, selector);
    if (evidence) return evidence;
  }
  return null;
}

async function inspectCase(browser, routeName, config, viewportName, viewport) {
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
    url: config.url,
    variant: config.variant,
    overflowPx: null,
    blockingAxeViolations: null,
    breadcrumbFocus: null,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(config.url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) {
      throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    }
    if (await page.locator('main#main').count() !== 1) throw new Error('Expected exactly one Theme main');
    if (await page.locator('h1').count() !== 1) throw new Error('Expected one H1');
    if (await page.locator(`article.aznet-theme-page--${config.variant}`).count() !== 1) {
      throw new Error(`Expected ${config.variant} Page variant`);
    }

    const stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
    if (!stylesheets.some((href) => href.includes('/assets/css/components/page.css'))) {
      throw new Error('Y1 page stylesheet not observed');
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx !== 0) throw new Error(`Horizontal overflow: ${result.overflowPx}px`);

    const breadcrumbCount = await page.locator('.aznet-theme-page__breadcrumbs').count();
    if (config.breadcrumbs && breadcrumbCount !== 1) throw new Error('Expected one breadcrumb navigation');
    if (!config.breadcrumbs && breadcrumbCount !== 0) throw new Error('Unexpected breadcrumb navigation');

    if (config.breadcrumbs) {
      const selector = '.aznet-theme-page__breadcrumbs a:first-of-type';
      if (await page.locator(selector).count() !== 1) throw new Error('Expected one parent breadcrumb link');
      result.breadcrumbFocus = await focusEvidence(page, selector);
      if (!result.breadcrumbFocus?.visible || !result.breadcrumbFocus.focusVisible || result.breadcrumbFocus.outlineStyle === 'none' || result.breadcrumbFocus.outlineWidth < 1) {
        throw new Error(`Breadcrumb lacks visible focus evidence: ${JSON.stringify(result.breadcrumbFocus)}`);
      }
    }

    const axe = await new AxeBuilder({ page }).include('main#main').analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`Blocking axe violations: ${blocking.length}`);

    if (consoleErrors.length) throw new Error(`Console errors: ${consoleErrors.length}`);
    if (pageErrors.length) throw new Error(`Page errors: ${pageErrors.length}`);
    if (failedSubresources.length) throw new Error(`Failed subresources: ${failedSubresources.length}`);

    await page.screenshot({ path: path.join(outputDir, `y1-${routeName}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${routeName}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `y1-${routeName}-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
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

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) {
  throw new Error(`Y1 browser verification failed:\n${failures.join('\n')}`);
}
console.log(`PASS: Y1 browser/a11y matrix ${summary.cases.length}/${summary.cases.length}`);
