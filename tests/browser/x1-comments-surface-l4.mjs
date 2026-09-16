import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const url = process.env.X1_COMMENTS_URL;
const outputDir = process.env.X1_COMMENTS_STATE_DIR || '/tmp/x1-comments-l4';

if (!url) {
  throw new Error('X1_COMMENTS_URL is required');
}

fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
};

const summary = { url, cases: [] };
const failures = [];

async function keyboardFocusEvidence(page, selector) {
  await page.evaluate(() => {
    if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
    window.scrollTo(0, 0);
  });

  for (let index = 0; index < 160; index += 1) {
    await page.keyboard.press('Tab');
    const evidence = await page.evaluate((targetSelector) => {
      const element = document.activeElement;
      if (!(element instanceof HTMLElement) || !element.matches(targetSelector)) return null;
      const rect = element.getBoundingClientRect();
      const style = window.getComputedStyle(element);
      const outlineWidth = Number.parseFloat(style.outlineWidth || '0');
      const hasOutline = style.outlineStyle !== 'none' && outlineWidth > 0 && style.outlineColor !== 'transparent';
      const hasShadow = Boolean(style.boxShadow && style.boxShadow !== 'none');
      return {
        visible: rect.width > 1 && rect.height > 1,
        focusVisible: element.matches(':focus-visible'),
        hasIndicator: hasOutline || hasShadow,
        tag: element.tagName.toLowerCase(),
        id: element.id || '',
        className: String(element.className || ''),
      };
    }, selector);

    if (evidence) return evidence;
  }

  return null;
}

async function inspectViewport(browser, name, viewport) {
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
    viewport: name,
    overflowPx: null,
    mainCount: null,
    replyFocus: null,
    textareaFocus: null,
    blockingAxeViolations: null,
    consoleErrors,
    pageErrors,
    failedSubresources,
    stylesheets: [],
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) {
      throw new Error(`expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    }

    const comments = page.locator('#comments.aznet-theme-comments');
    await comments.waitFor({ state: 'visible' });

    result.mainCount = await page.locator('main').count();
    if (result.mainCount !== 1) throw new Error(`expected exactly one main landmark, got ${result.mainCount}`);
    if (await page.locator('.comment-list').count() !== 1) throw new Error('expected one native comment list');
    if (await page.getByText('X1 parent comment', { exact: true }).count() !== 1) throw new Error('missing parent comment');
    if (await page.getByText('X1 nested reply', { exact: true }).count() !== 1) throw new Error('missing nested reply');
    if (await page.locator('#respond textarea#comment').count() !== 1) throw new Error('missing native comment textarea');
    if (await page.locator('nav.comments-pagination').count() !== 1) throw new Error('missing native comments pagination');

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx !== 0) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    result.replyFocus = await keyboardFocusEvidence(page, '.comment-reply-link');
    if (!result.replyFocus?.visible || !result.replyFocus.focusVisible || !result.replyFocus.hasIndicator) {
      throw new Error('reply link lacks keyboard-visible focus evidence');
    }

    result.textareaFocus = await keyboardFocusEvidence(page, '#respond textarea#comment');
    if (!result.textareaFocus?.visible || !result.textareaFocus.focusVisible || !result.textareaFocus.hasIndicator) {
      throw new Error('comment textarea lacks keyboard-visible focus evidence');
    }

    const axe = await new AxeBuilder({ page }).include('#comments').analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${name}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.length}`);

    result.stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
    if (!result.stylesheets.some((href) => href.includes('/assets/css/components/comments.css'))) {
      throw new Error('comments stylesheet not observed');
    }
    if (consoleErrors.length) throw new Error(`console errors: ${consoleErrors.length}`);
    if (pageErrors.length) throw new Error(`page errors: ${pageErrors.length}`);
    if (failedSubresources.length) throw new Error(`failed subresources: ${failedSubresources.length}`);

    await page.screenshot({ path: path.join(outputDir, `x1-comments-${name}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${name}: ${result.error}`);
    await page.screenshot({ path: path.join(outputDir, `x1-comments-${name}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.cases.push(result);
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [name, viewport] of Object.entries(viewports)) {
    await inspectViewport(browser, name, viewport);
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (failures.length) {
  for (const failure of failures) console.error(`FAIL: ${failure}`);
  process.exit(1);
}

console.log(`PASS: X1 comments browser quality ${summary.cases.length}/${summary.cases.length}`);
