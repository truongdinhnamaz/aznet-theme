import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.LAW01_ARCHIVE_STATE_DIR || '/tmp/law01-archive';
const outputDir = process.env.LAW01_ARCHIVE_L4_DIR || '/tmp/law01-archive-l4';
const fixturePath = path.join(stateDir, 'fixture.json');

if (!fs.existsSync(fixturePath)) throw new Error(`Fixture missing: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

const failures = [];
const summary = { fixture, cases: [] };

async function inspect(browser, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));

  const result = { viewport: viewportName, status: 'failed', error: null };

  try {
    const response = await page.goto(fixture.archive_url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`archive HTTP ${response?.status() ?? 'missing'}`);

    if (await page.locator('main#main').count() !== 1) throw new Error('Expected one main landmark');
    if (await page.locator('h1').count() !== 1) throw new Error('Expected exactly one H1');
    if (await page.locator('.aznet-theme-site-header--law01-burgundy-gold').count() !== 1) throw new Error('Law 01 site Header missing');
    if (await page.locator('.aznet-theme-law01-archive').count() !== 1) throw new Error('Choice 1 archive shell missing');
    if (await page.locator('#aznet-theme-archive-law-01-css').count() !== 1) throw new Error('Choice 1 archive stylesheet missing');

    if (await page.locator('.aznet-theme-law01-archive-item--featured').count() !== 1) throw new Error('Expected exactly one featured article');
    if (await page.locator('.aznet-theme-law01-archive-item--latest').count() !== 3) throw new Error('Expected exactly three latest cards');
    if (await page.locator('.aznet-theme-law01-archive-item--standard').count() !== 2) throw new Error('Expected two editorial-list rows on page one');
    if (await page.locator('.aznet-theme-law01-archive__sidebar').count() !== 1) throw new Error('Legal-site archive sidebar missing');
    if (await page.locator('.aznet-theme-law01-archive__category-list .current-cat').count() !== 1) throw new Error('Current category state missing');

    const consultation = page.locator('.aznet-theme-law01-archive__consultation > a');
    if (await consultation.count() !== 1) throw new Error('Mapped consultation CTA missing');
    const contactHref = await consultation.getAttribute('href');
    if (contactHref !== fixture.contact_url) throw new Error(`Contact CTA mismatch: ${contactHref}`);

    const overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (overflow > 1) throw new Error(`Horizontal overflow ${overflow}px`);

    await consultation.focus();
    const focusState = await consultation.evaluate((element) => {
      const style = getComputedStyle(element);
      return {
        focusVisible: element.matches(':focus-visible'),
        outlineStyle: style.outlineStyle,
        outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
      };
    });
    if (!focusState.focusVisible || focusState.outlineStyle === 'none' || focusState.outlineWidth < 1) {
      throw new Error(`Consultation CTA focus is not visible: ${JSON.stringify(focusState)}`);
    }

    const axe = await new AxeBuilder({ page }).include('main#main').analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    fs.writeFileSync(path.join(outputDir, `axe-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`Blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);

    if (consoleErrors.length) throw new Error(`Console errors: ${consoleErrors.join(' | ')}`);
    if (pageErrors.length) throw new Error(`Page errors: ${pageErrors.join(' | ')}`);

    const pagination = page.locator('.aznet-theme-law01-archive__pagination .navigation.pagination');
    if (await pagination.count() !== 1) throw new Error('Native category pagination missing');
    const next = pagination.locator('a.next.page-numbers');
    if (await next.count() !== 1) throw new Error('Native next-page link missing');

    const initialUrl = page.url();
    await next.click();
    await page.waitForLoadState('networkidle');
    if (page.url() === initialUrl) throw new Error('Native category pagination URL did not change');
    if (await page.locator('.aznet-theme-law01-archive').count() !== 1) throw new Error('Choice 1 presentation missing on paginated category page');
    if (await page.locator('.aznet-theme-law01-archive__pagination .page-numbers.current').count() !== 1) throw new Error('Current pagination state missing');

    await page.screenshot({ path: path.join(outputDir, `law01-category-${viewportName}.png`), fullPage: true });

    result.overflowPx = overflow;
    result.blockingAxeViolations = blocking.length;
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `failure-${viewportName}.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    await inspect(browser, viewportName, viewport);
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error(`Law 01 archive Choice 1 browser verification failed:\n${failures.join('\n')}`);
console.log(`PASS: Law 01 archive Choice 1 browser matrix ${summary.cases.length}/${summary.cases.length}`);
