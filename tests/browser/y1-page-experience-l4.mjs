import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const routes = {
  services: { url: process.env.Y1_SERVICES_URL, variant: 'standard', breadcrumbs: false, servicesRoot: true },
  child: { url: process.env.Y1_CHILD_URL, variant: 'standard', breadcrumbs: true, service: true },
  wide: { url: process.env.Y1_WIDE_URL, variant: 'wide', breadcrumbs: false },
  landing: { url: process.env.Y1_LANDING_URL, variant: 'landing', breadcrumbs: false },
  contact: { url: process.env.Y1_CONTACT_URL || 'http://127.0.0.1:8080/y1-contact/', variant: 'standard', breadcrumbs: false, contact: true },
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
    servicesPrimaryFocus: null,
    servicePrimaryFocus: null,
    contactPrimaryFocus: null,
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
    if (await page.locator('article.aznet-theme-page--full-bleed').count() !== 1) {
      throw new Error('Expected native Page full-bleed presentation');
    }

    const fullBleed = await page.locator('article.aznet-theme-page--full-bleed').evaluate((element) => {
      const rect = element.getBoundingClientRect();
      const first = element.firstElementChild?.getBoundingClientRect() ?? null;
      const last = element.lastElementChild?.getBoundingClientRect() ?? null;
      return {
        left: rect.left,
        right: rect.right,
        viewportWidth: document.documentElement.clientWidth,
        top: rect.top,
        bottom: rect.bottom,
        firstTop: first?.top ?? null,
        lastBottom: last?.bottom ?? null,
      };
    });
    if (Math.abs(fullBleed.left) > 1 || Math.abs(fullBleed.right - fullBleed.viewportWidth) > 1) {
      throw new Error(`Page does not span viewport width: ${JSON.stringify(fullBleed)}`);
    }
    if (fullBleed.firstTop !== null && Math.abs(fullBleed.firstTop - fullBleed.top) > 1) {
      throw new Error(`First Page section exposes an outer top gutter: ${JSON.stringify(fullBleed)}`);
    }
    if (fullBleed.lastBottom !== null && Math.abs(fullBleed.lastBottom - fullBleed.bottom) > 1) {
      throw new Error(`Last Page section exposes an outer bottom gutter: ${JSON.stringify(fullBleed)}`);
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

    if (config.servicesRoot) {
      if (await page.locator('.aznet-theme-page--services-root').count() !== 1) throw new Error('Expected mapped premium Services Page presentation');
      if (await page.locator('.aznet-theme-services-page__hero').count() !== 1) throw new Error('Expected Services Page hero');
      const servicesHeroRadius = await page.locator('.aznet-theme-services-page__hero').evaluate((element) => {
        const style = getComputedStyle(element);
        return [style.borderTopLeftRadius, style.borderTopRightRadius];
      });
      if (servicesHeroRadius.some((value) => Number.parseFloat(value) > 0)) {
        throw new Error(`Services first section exposes rounded top edges: ${servicesHeroRadius.join(', ')}`);
      }
      if (await page.locator('.aznet-theme-services-page__intro').count() !== 0) throw new Error('Empty mapped Services Page must not render an orphan intro card');
      if (await page.locator('.aznet-theme-services-page__card').count() !== 3) throw new Error('Expected three direct published service child cards');
      if (await page.getByText('Y1 Business Service', { exact: true }).count() !== 1) throw new Error('Expected Business service child card');
      if (await page.getByText('Y1 Civil Service', { exact: true }).count() !== 1) throw new Error('Expected Civil service child card');
      const servicesStyles = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
      if (!servicesStyles.some((href) => href.includes('/assets/css/components/services-page.css'))) {
        throw new Error('Services Page stylesheet not observed');
      }
      result.servicesPrimaryFocus = await focusEvidence(page, '.aznet-theme-services-page__primary');
      if (!result.servicesPrimaryFocus?.visible || !result.servicesPrimaryFocus.focusVisible || result.servicesPrimaryFocus.outlineStyle === 'none' || result.servicesPrimaryFocus.outlineWidth < 1) {
        throw new Error(`Services primary CTA lacks visible focus evidence: ${JSON.stringify(result.servicesPrimaryFocus)}`);
      }
    }

    if (config.service) {
      if (await page.locator('.aznet-theme-page--service-detail').count() !== 1) throw new Error('Expected mapped service detail presentation');
      const serviceHeaderRadius = await page.locator('.aznet-theme-page--service-detail .aznet-theme-page__header').evaluate((element) => {
        const style = getComputedStyle(element);
        return [style.borderTopLeftRadius, style.borderTopRightRadius];
      });
      if (serviceHeaderRadius.some((value) => Number.parseFloat(value) > 0)) {
        throw new Error(`Service detail first section exposes rounded top edges: ${serviceHeaderRadius.join(', ')}`);
      }
      if (await page.locator('.aznet-theme-page__service-actions').count() !== 1) throw new Error('Expected service CTA group');
      if (await page.locator('.aznet-theme-page__service-primary').count() !== 1) throw new Error('Expected mapped Contact CTA');
      if (await page.locator('.aznet-theme-page__service-siblings').count() !== 1) throw new Error('Expected sibling services section');
      if (await page.getByText('Y1 Civil Service', { exact: true }).count() !== 1) throw new Error('Expected sibling service card');
      const stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
      if (!stylesheets.some((href) => href.includes('/assets/css/components/service-page.css'))) {
        throw new Error('Service page stylesheet not observed');
      }
      result.servicePrimaryFocus = await focusEvidence(page, '.aznet-theme-page__service-primary');
      if (!result.servicePrimaryFocus?.visible || !result.servicePrimaryFocus.focusVisible || result.servicePrimaryFocus.outlineStyle === 'none' || result.servicePrimaryFocus.outlineWidth < 1) {
        throw new Error(`Service primary CTA lacks visible focus evidence: ${JSON.stringify(result.servicePrimaryFocus)}`);
      }
    }

    if (config.contact) {
      if (await page.locator('.aznet-theme-page--contact').count() !== 1) throw new Error('Expected mapped premium Contact Page presentation');
      if (await page.locator('.aznet-theme-contact-page__hero').count() !== 1) throw new Error('Expected Contact Page hero');
      const contactHeroRadius = await page.locator('.aznet-theme-contact-page__hero').evaluate((element) => {
        const style = getComputedStyle(element);
        return [style.borderTopLeftRadius, style.borderTopRightRadius];
      });
      if (contactHeroRadius.some((value) => Number.parseFloat(value) > 0)) {
        throw new Error(`Contact first section exposes rounded top edges: ${contactHeroRadius.join(', ')}`);
      }
      if (await page.locator('.aznet-theme-contact-page__content-card').count() !== 1) throw new Error('Expected Contact Page authored-content card');
      if (await page.locator('#y1-contact-content').count() !== 1) throw new Error('Expected WordPress-owned Contact Page content');
      const contactStyles = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
      if (!contactStyles.some((href) => href.includes('/assets/css/components/contact-page.css'))) {
        throw new Error('Contact Page stylesheet not observed');
      }
      result.contactPrimaryFocus = await focusEvidence(page, '.aznet-theme-contact-page__primary');
      if (!result.contactPrimaryFocus?.visible || !result.contactPrimaryFocus.focusVisible || result.contactPrimaryFocus.outlineStyle === 'none' || result.contactPrimaryFocus.outlineWidth < 1) {
        throw new Error(`Contact primary CTA lacks visible focus evidence: ${JSON.stringify(result.contactPrimaryFocus)}`);
      }
    }

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
