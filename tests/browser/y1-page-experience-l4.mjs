import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.Y1_STATE_DIR || '/tmp/y1';
const outputDir = process.env.Y1_L4_DIR || '/tmp/y1-l4';
const fixture = JSON.parse(fs.readFileSync(path.join(stateDir, 'fixture.json'), 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const routes = {
  parent: { url: fixture.parent_url, variant: 'standard', breadcrumb: false, lead: true, featured: true },
  child: { url: fixture.child_url, variant: 'standard', breadcrumb: true, lead: true, featured: false },
  wide: { url: fixture.wide_url, variant: 'wide', breadcrumb: false, lead: false, featured: false },
  landing: { url: fixture.landing_url, variant: 'landing', breadcrumb: false, lead: true, featured: false },
  plainSlug: { url: fixture.plain_slug_url, variant: 'standard', breadcrumb: false, lead: false, featured: false },
};

const browser = await chromium.launch();
const failures = [];
const summary = { fixture, cases: [] };

try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const [routeName, route] of Object.entries(routes)) {
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();
      const consoleErrors = [];
      const pageErrors = [];
      page.on('console', (message) => {
        if (message.type() === 'error') consoleErrors.push(message.text());
      });
      page.on('pageerror', (error) => pageErrors.push(error.message));
      const result = { route: routeName, viewport: viewportName, status: 'failed', error: null };

      try {
        const response = await page.goto(route.url, { waitUntil: 'networkidle' });
        if ((response?.status() ?? null) !== 200) throw new Error(`HTTP ${response?.status()}`);
        if (await page.locator('main#main').count() !== 1) throw new Error('expected one Theme main');
        if (await page.locator('h1').count() !== 1) throw new Error('expected one H1');
        if (await page.locator('#aznet-theme-page-css').count() !== 1) throw new Error('page.css missing');
        if (await page.locator(`.aznet-theme-page--${route.variant}`).count() !== 1) throw new Error(`variant ${route.variant} missing`);
        if ((await page.locator('.aznet-theme-page__breadcrumbs').count() === 1) !== route.breadcrumb) throw new Error('breadcrumb presence mismatch');
        if ((await page.locator('.aznet-theme-page__lead').count() === 1) !== route.lead) throw new Error('authored lead presence mismatch');
        if ((await page.locator('.aznet-theme-page__featured img').count() === 1) !== route.featured) throw new Error('featured image presence mismatch');
        if (await page.locator('.aznet-theme-entry__content.aznet-theme-page__content').count() !== 1) throw new Error('authored-content media compatibility wrapper missing');

        if (routeName === 'child') {
          const parentLink = page.locator('.aznet-theme-page__breadcrumbs a', { hasText: 'Y1 Parent Page' });
          if (await parentLink.count() !== 1) throw new Error('real parent breadcrumb missing');
          if ((await parentLink.getAttribute('href')) !== fixture.parent_url) throw new Error('parent breadcrumb URL mismatch');
          await parentLink.focus();
          const focus = await parentLink.evaluate((el) => {
            const style = getComputedStyle(el);
            return { visible: el.matches(':focus-visible'), outline: style.outlineStyle, width: parseFloat(style.outlineWidth || '0') };
          });
          if (!focus.visible || focus.outline === 'none' || focus.width < 1) throw new Error(`breadcrumb focus not visible: ${JSON.stringify(focus)}`);
        }

        if (routeName === 'wide') {
          if (await page.getByText('This body text must not become a generated lead.').count() !== 1) throw new Error('wide body sentinel missing');
          if (await page.locator('.aznet-theme-page__lead').count() !== 0) throw new Error('generated excerpt leaked into lead');
        }

        const overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
        if (overflow > 1) throw new Error(`horizontal overflow ${overflow}px`);

        const axe = await new AxeBuilder({ page }).analyze();
        const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
        fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
        if (blocking.length) throw new Error(`blocking axe: ${blocking.map((violation) => violation.id).join(',')}`);
        if (consoleErrors.length || pageErrors.length) throw new Error(`browser errors: ${JSON.stringify({ consoleErrors, pageErrors })}`);

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
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) {
  failures.forEach((failure) => console.error(`FAIL: ${failure}`));
  process.exit(1);
}
console.log(`PASS: Y1 browser matrix ${summary.cases.length}/${summary.cases.length}`);
