import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = process.env.Y2_BASE_URL || 'http://127.0.0.1:8080';
const fixturePath = process.env.Y2_FIXTURE_PATH || '/tmp/y2/fixture.json';
const stateDir = process.env.Y2_STATE_DIR || '/tmp/y2-page-l4';
const adminUser = process.env.Y2_ADMIN_USER || 'admin';
const adminPassword = process.env.Y2_ADMIN_PASSWORD || 'y2-browser-password';

if (!fs.existsSync(fixturePath)) throw new Error(`Missing Y2 fixture: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
if (!Array.isArray(fixture.pages) || fixture.pages.length !== 5) throw new Error('Expected five Y2 Page Kit fixtures');

const expectedRoles = ['about', 'services', 'team', 'contact', 'content_landing'];
const actualRoles = fixture.pages.map((item) => item.role);
if (JSON.stringify(actualRoles) !== JSON.stringify(expectedRoles)) {
  throw new Error(`Unexpected Y2 role order: ${JSON.stringify(actualRoles)}`);
}

const roleClasses = {
  about: 'about',
  services: 'services',
  team: 'team',
  contact: 'contact',
  content_landing: 'content-landing',
};
const viewports = {
  desktop: { width: 1440, height: 1000 },
  mobile: { width: 390, height: 844 },
};

fs.mkdirSync(stateDir, { recursive: true });
const summary = { frontend: [], classicEditor: [] };
const failures = [];

async function inspectFrontend(browser, item, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const result = {
    role: item.role,
    viewport: viewportName,
    url: item.url,
    overflowPx: null,
    blockingAxeViolations: null,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(item.url, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`Expected HTTP 200, got ${response?.status() ?? 'missing'}`);
    if (await page.locator('main#main').count() !== 1) throw new Error('Expected exactly one Theme main');
    if (await page.locator('h1').count() !== 1) throw new Error('Expected exactly one H1');
    if (await page.locator('.aznet-theme-page-kit').count() !== 1) throw new Error('Expected one Page Kit root');
    if (await page.locator(`.aznet-theme-page-kit--${roleClasses[item.role]}`).count() !== 1) {
      throw new Error(`Expected role class for ${item.role}`);
    }

    const stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
    if (!stylesheets.some((href) => href.includes('/assets/css/components/page.css'))) {
      throw new Error('Y2 Page stylesheet not observed');
    }

    if (item.role === 'about') {
      const iconGeometry = await page.evaluate(() => {
        const root = document.querySelector('.aznet-theme-page-kit--about');
        if (!root) throw new Error('About Page Kit root missing for icon geometry audit');

        const probe = document.createElement('div');
        probe.setAttribute('data-aznet-theme-about-icon-probe', '');
        probe.innerHTML = `
          <div class="aznet-theme-page-kit__principles"><div class="wp-block-columns"><div class="aznet-theme-page-kit__card"><h3>Principle probe</h3><p>Probe copy</p></div></div></div>
          <div class="aznet-theme-page-kit__capabilities"><div class="aznet-theme-page-kit__card"><h3>Capability probe heading that reserves icon space</h3><p>Probe copy</p></div></div>
          <div class="aznet-theme-page-kit__trust"><div class="wp-block-columns"><div class="wp-block-column"><h3>Trust probe</h3><p>Probe copy</p></div></div></div>
        `;
        root.appendChild(probe);

        const px = (value) => Number.parseFloat(value) || 0;
        const audit = (selector, pseudo, axis) => {
          const node = probe.querySelector(selector);
          if (!node) throw new Error(`Icon geometry probe missing: ${selector}`);
          const box = getComputedStyle(node);
          const decoration = getComputedStyle(node, pseudo);
          const size = px(decoration.width);
          const inset = axis === 'block' ? px(decoration.top) : px(decoration.right);
          const padding = axis === 'block' ? px(box.paddingTop) : px(box.paddingRight);
          return { size, inset, padding, clearance: padding - inset - size };
        };

        const result = {
          principle: audit('.aznet-theme-page-kit__principles .aznet-theme-page-kit__card', '::before', 'block'),
          capability: audit('.aznet-theme-page-kit__capabilities .aznet-theme-page-kit__card', '::after', 'inline'),
          trust: audit('.aznet-theme-page-kit__trust .wp-block-column', '::before', 'block'),
        };
        probe.remove();
        return result;
      });
      result.aboutIconGeometry = iconGeometry;
      for (const [name, geometry] of Object.entries(iconGeometry)) {
        if (geometry.size <= 0 || geometry.clearance < 8) {
          throw new Error(`About ${name} icon does not retain a safe text clearance: ${JSON.stringify(geometry)}`);
        }
      }
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx !== 0) throw new Error(`Horizontal overflow: ${result.overflowPx}px`);

    const axe = await new AxeBuilder({ page }).include('main#main').analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(stateDir, `axe-${item.role}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`Blocking axe violations: ${blocking.length}`);

    await page.screenshot({ path: path.join(stateDir, `y2-${item.role}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${item.role}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(stateDir, `y2-${item.role}-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.frontend.push(result);
    await context.close();
  }
}

async function verifyClassicEditor(browser) {
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await context.newPage();

  try {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    await page.locator('#user_login').fill(adminUser);
    await page.locator('#user_pass').fill(adminPassword);
    await page.locator('#wp-submit').click();
    await page.waitForURL(/\/wp-admin\//, { timeout: 30000 });

    for (const item of fixture.pages) {
      const result = { role: item.role, tinyMceObserved: false, pageCssObservedInTinyMce: false, status: 'failed', error: null };
      try {
        await page.goto(`${baseUrl}/wp-admin/post.php?post=${item.id}&action=edit`, { waitUntil: 'domcontentloaded' });
        await page.locator('form#post').waitFor({ state: 'attached', timeout: 30000 });

        if (await page.locator('.block-editor').count()) throw new Error('Block Editor unexpectedly active for Page');
        if (await page.locator('textarea#content').count() !== 1) throw new Error('Classic Editor content textarea missing');
        const stored = await page.locator('textarea#content').inputValue();
        if (!stored.includes('<!-- wp:')) throw new Error('Portable block markup missing from Classic Editor source');
        if (!stored.includes('aznet-theme-page-kit')) throw new Error('Page Kit marker missing from Classic Editor source');

        const iframe = page.locator('iframe#content_ifr');
        if (await iframe.count()) {
          result.tinyMceObserved = true;
          const frame = page.frameLocator('iframe#content_ifr');
          await frame.locator('body').waitFor({ state: 'attached', timeout: 15000 });
          const hrefs = await frame.locator('html').evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
          result.pageCssObservedInTinyMce = hrefs.some((href) => href.includes('/assets/css/components/page.css'));
          if (!result.pageCssObservedInTinyMce) throw new Error('TinyMCE observed without Page Kit editor stylesheet');
        }

        if (await page.locator('#content-html').count()) await page.locator('#content-html').click();
        await page.locator('#publish').click();
        await page.waitForURL(new RegExp(`post=${item.id}.*action=edit`), { timeout: 30000 });
        await page.locator('#message.updated, .notice-success').first().waitFor({ state: 'visible', timeout: 15000 });

        result.status = 'passed';
      } catch (error) {
        result.error = error instanceof Error ? error.message : String(error);
        failures.push(`classic-editor/${item.role}: ${result.error}`);
        await page.screenshot({ path: path.join(stateDir, `y2-classic-${item.role}-failure.png`), fullPage: true }).catch(() => {});
      }
      summary.classicEditor.push(result);
    }
  } finally {
    await context.close();
  }
}

const browser = await chromium.launch();
try {
  for (const item of fixture.pages) {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspectFrontend(browser, item, viewportName, viewport);
    }
  }
  await verifyClassicEditor(browser);
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error(`Y2 browser/editor verification failed:\n${failures.join('\n')}`);
console.log(`PASS: Y2 frontend ${summary.frontend.length}/${summary.frontend.length}; Classic Editor ${summary.classicEditor.length}/${summary.classicEditor.length}`);
