import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const stateDir = process.env.X3_STATE_DIR || '/tmp/x3';
const outputDir = process.env.X3_L4_DIR || '/tmp/x3-l4';
const fixturePath = path.join(stateDir, 'fixture.json');
const adminUser = process.env.X3_ADMIN_USER || 'admin';
const adminPassword = process.env.X3_ADMIN_PASSWORD || 'x3-media-password';

if (!fs.existsSync(fixturePath)) throw new Error(`X3 fixture is required: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
fs.mkdirSync(outputDir, { recursive: true });

const viewports = {
  '1440x1000': { width: 1440, height: 1000 },
  '1024x900': { width: 1024, height: 900 },
  '390x844': { width: 390, height: 844 },
  '320x760': { width: 320, height: 760 },
};

const routes = {
  post: { url: fixture.post_url, expectH1: true },
  page: { url: fixture.page_url, expectH1: true },
  front: { url: fixture.front_url, expectH1: false },
};

const summary = { fixture, cases: [], editor: { status: 'unknown', details: null } };
const failures = [];

function collectBrowserErrors(page) {
  const consoleErrors = [];
  const pageErrors = [];
  const httpErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') consoleErrors.push(message.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('response', (response) => {
    if (response.status() >= 400) httpErrors.push({ status: response.status(), url: response.url() });
  });
  return { consoleErrors, pageErrors, httpErrors };
}

async function inspectFrontendCase(browser, routeName, route, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const errors = collectBrowserErrors(page);
  const result = {
    route: routeName,
    viewport: viewportName,
    httpStatus: null,
    overflowPx: null,
    blockingAxeViolations: null,
    browserErrorCount: null,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(route.url, { waitUntil: 'networkidle' });
    result.httpStatus = response?.status() ?? null;
    if (result.httpStatus !== 200) throw new Error(`expected HTTP 200, got ${result.httpStatus}`);

    await page.locator('.aznet-theme-entry__content').waitFor({ state: 'visible' });
    if (route.expectH1) {
      const h1Count = await page.locator('h1').count();
      if (h1Count !== 1) throw new Error(`expected exactly one H1, got ${h1Count}`);
    }

    if (await page.locator('#aznet-theme-media-css').count() !== 1) throw new Error('X3 media stylesheet handle missing');
    if (await page.locator('#x3-media-sentinel').count() !== 1) throw new Error('authored media sentinel missing');
    if (await page.locator('.aznet-theme-entry__content .wp-block-gallery').count() < 1) throw new Error('Core gallery missing');
    if (await page.locator('.aznet-theme-entry__content .gallery').count() < 1) throw new Error('legacy gallery missing');
    if (await page.locator('.aznet-theme-entry__content .wp-caption').count() < 1) throw new Error('legacy caption missing');
    if (await page.locator('.aznet-theme-entry__content video[controls]').count() !== 1) throw new Error('native video controls missing');
    if (await page.locator('.aznet-theme-entry__content audio[controls]').count() !== 1) throw new Error('native audio controls missing');
    if (await page.locator('.aznet-theme-entry__content iframe[title="X3 deterministic embed"]').count() !== 1) throw new Error('deterministic embed missing');

    const geometry = await page.evaluate(() => {
      const root = document.querySelector('.aznet-theme-entry__content');
      if (!root) throw new Error('authored root missing');
      const rootRect = root.getBoundingClientRect();
      const media = [...root.querySelectorAll('img, video, audio, iframe')];
      const captions = [...root.querySelectorAll('figcaption, .wp-caption-text, .gallery-caption')];
      const galleryItems = [...root.querySelectorAll('.wp-block-gallery > figure, .gallery-item')];
      const wide = root.querySelector('#x3-alignwide');
      const full = root.querySelector('#x3-alignfull');
      const tolerance = 1;
      const mediaOffenders = media.flatMap((element) => {
        const rect = element.getBoundingClientRect();
        const contained = rect.width >= 0 && rect.right <= window.innerWidth + tolerance && rect.left >= -tolerance;
        if (contained) return [];
        return [{
          tag: element.tagName.toLowerCase(),
          id: element.id || '',
          className: typeof element.className === 'string' ? element.className : '',
          left: rect.left,
          right: rect.right,
          width: rect.width,
          clientWidth: element.clientWidth,
          scrollWidth: element.scrollWidth,
          viewportWidth: window.innerWidth,
        }];
      });
      const mediaContained = mediaOffenders.length === 0;
      const captionsContained = captions.every((element) => {
        const rect = element.getBoundingClientRect();
        return rect.right <= window.innerWidth + tolerance && rect.left >= -tolerance && element.scrollWidth <= element.clientWidth + tolerance;
      });
      const galleryItemsUsable = galleryItems.length >= 4 && galleryItems.every((element) => element.getBoundingClientRect().width > 1);
      const alignmentSafe = [wide, full].every((element) => {
        if (!element) return false;
        const rect = element.getBoundingClientRect();
        return rect.width > rootRect.width && rect.left >= -tolerance && rect.right <= window.innerWidth + tolerance;
      });
      return {
        mediaContained,
        mediaOffenders,
        captionsContained,
        galleryItemsUsable,
        alignmentSafe,
        rootWidth: rootRect.width,
        wideWidth: wide?.getBoundingClientRect().width ?? 0,
        fullWidth: full?.getBoundingClientRect().width ?? 0,
      };
    });

    if (!geometry.mediaContained) throw new Error(`media escaped viewport bounds: ${JSON.stringify(geometry.mediaOffenders)}`);
    if (!geometry.captionsContained) throw new Error('caption overflow detected');
    if (!geometry.galleryItemsUsable) throw new Error('gallery items collapsed or missing');
    if (!geometry.alignmentSafe) throw new Error(`wide/full alignment is not safely expanded: ${JSON.stringify(geometry)}`);

    const focusLink = page.locator('#x3-focus-link');
    await focusLink.focus();
    const focus = await focusLink.evaluate((element) => {
      const style = window.getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return {
        focusVisible: element.matches(':focus-visible'),
        outlineWidth: Number.parseFloat(style.outlineWidth || '0'),
        outlineStyle: style.outlineStyle,
        insideViewport: rect.left >= -1 && rect.right <= window.innerWidth + 1,
      };
    });
    if (!focus.focusVisible || focus.outlineStyle === 'none' || focus.outlineWidth < 1 || !focus.insideViewport) {
      throw new Error(`focus presentation failed: ${JSON.stringify(focus)}`);
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx > 1) throw new Error(`horizontal overflow: ${result.overflowPx}px`);

    const axe = await new AxeBuilder({ page }).analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(outputDir, `axe-${routeName}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`blocking axe violations: ${blocking.map((item) => item.id).join(', ')}`);

    result.browserErrorCount = errors.consoleErrors.length + errors.pageErrors.length + errors.httpErrors.length;
    if (result.browserErrorCount) {
      throw new Error(`unexpected browser errors: ${JSON.stringify(errors)}`);
    }

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

async function inspectClassicEditor(browser) {
  const context = await browser.newContext({ viewport: viewports['1440x1000'] });
  const page = await context.newPage();
  const origin = new URL(fixture.front_url).origin;
  try {
    await page.goto(`${origin}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill(adminUser);
    await page.locator('#user_pass').fill(adminPassword);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
      page.locator('#wp-submit').click(),
    ]);

    await page.goto(fixture.admin_page_edit, { waitUntil: 'networkidle' });
    if (await page.locator('#post').count() !== 1) throw new Error('classic editor form missing');
    if (await page.locator('#title').count() !== 1) throw new Error('classic editor title missing');
    if (await page.locator('#content').count() !== 1) throw new Error('classic editor textarea missing');
    if (await page.locator('.edit-post-layout, .interface-interface-skeleton').count() !== 0) throw new Error('block editor unexpectedly active');

    const iframe = page.locator('#content_ifr');
    if (await iframe.count() !== 1) {
      summary.editor = { status: 'unknown', details: 'Classic Editor retained, but TinyMCE iframe was not available in this runtime.' };
      return;
    }

    const frame = page.frameLocator('#content_ifr');
    await frame.locator('body').waitFor({ state: 'visible' });
    const editorEvidence = await iframe.evaluate((element) => {
      const doc = element.contentDocument;
      if (!doc) return null;
      const hrefs = [...doc.styleSheets].map((sheet) => sheet.href || '');
      const image = doc.querySelector('img');
      const caption = doc.querySelector('figcaption, .wp-caption-text, .gallery-caption');
      return {
        mediaStylesheetLoaded: hrefs.some((href) => href.includes('/assets/css/components/media.css')),
        imageMaxWidth: image ? doc.defaultView.getComputedStyle(image).maxWidth : null,
        captionOverflowWrap: caption ? doc.defaultView.getComputedStyle(caption).overflowWrap : null,
        bodyId: doc.body?.id || '',
      };
    });

    if (!editorEvidence?.mediaStylesheetLoaded) throw new Error('media.css not loaded through add_editor_style');
    if (editorEvidence.imageMaxWidth !== '100%') throw new Error(`editor media max-width mismatch: ${editorEvidence.imageMaxWidth}`);
    if (!['anywhere', 'break-word'].includes(editorEvidence.captionOverflowWrap)) throw new Error(`editor caption wrapping missing: ${editorEvidence.captionOverflowWrap}`);

    summary.editor = { status: 'passed', details: editorEvidence };
    await page.screenshot({ path: path.join(outputDir, 'classic-editor-1440x1000.png'), fullPage: true });
  } catch (error) {
    summary.editor = { status: 'failed', details: error instanceof Error ? error.message : String(error) };
    failures.push(`classic-editor: ${summary.editor.details}`);
    await page.screenshot({ path: path.join(outputDir, 'failure-classic-editor.png'), fullPage: true }).catch(() => {});
  } finally {
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const [viewportName, viewport] of Object.entries(viewports)) {
    for (const [routeName, route] of Object.entries(routes)) {
      await inspectFrontendCase(browser, routeName, route, viewportName, viewport);
    }
  }
  await inspectClassicEditor(browser);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (summary.editor.status === 'unknown') {
  console.warn(`UNKNOWN: ${summary.editor.details}`);
}

if (failures.length) {
  for (const failure of failures) console.error(`FAIL: ${failure}`);
  process.exit(1);
}

console.log(`PASS: X3 browser media matrix ${summary.cases.length}/${summary.cases.length}; editor=${summary.editor.status}`);
