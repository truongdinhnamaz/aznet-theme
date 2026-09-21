import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const baseUrl = process.env.Y3_BASE_URL || 'http://127.0.0.1:8080';
const fixturePath = process.env.Y3_FIXTURE_PATH || '/tmp/y3/fixture.json';
const stateDir = process.env.Y3_STATE_DIR || '/tmp/y3-footer-l4';
const wpPath = process.env.Y3_WP_PATH || '/tmp/wp';

if (!fs.existsSync(fixturePath)) throw new Error(`Missing Y3 fixture: ${fixturePath}`);
const fixture = JSON.parse(fs.readFileSync(fixturePath, 'utf8'));
if (!fixture.front_url || !fixture.menu_ids) throw new Error('Incomplete Y3 runtime fixture');

const presets = ['standard', 'professional', 'compact', 'law-01'];
const viewports = {
  desktop: { width: 1440, height: 1000 },
  tablet: { width: 1024, height: 900 },
  mobile: { width: 390, height: 844 },
  narrow: { width: 320, height: 760 },
};
const emptyRegions = {
  footer: { selector: '.aznet-theme-site-footer__navigation', sentinel: 'Y3 Primary Sentinel', heading: 'Khám phá' },
  'footer-contact': { selector: '.aznet-theme-site-footer__contact', sentinel: 'Y3 Contact Sentinel', heading: 'Liên hệ' },
  'footer-social': { selector: '.aznet-theme-site-footer__social', sentinel: 'Y3 Social Sentinel', heading: null },
  'footer-policy': { selector: '.aznet-theme-site-footer__policies', sentinel: 'Y3 Policy Sentinel', heading: null },
};

fs.mkdirSync(stateDir, { recursive: true });
const summary = { presets: [], emptyMenus: [] };
const failures = [];

function wpEval(code) {
  return execFileSync('wp', ['eval', code, `--path=${wpPath}`, '--allow-root'], { encoding: 'utf8' }).trim();
}

function setPreset(preset) {
  if (!presets.includes(preset)) throw new Error(`Unsupported test preset ${preset}`);
  wpEval(`$s=get_theme_mod('aznet_theme_settings',[]); if(!is_array($s)){$s=[];} $s['footer_preset']='${preset}'; set_theme_mod('aznet_theme_settings',$s);`);
}

function setMenuLocation(location, menuId) {
  if (!Object.hasOwn(emptyRegions, location)) throw new Error(`Unsupported Footer location ${location}`);
  const numericId = Number(menuId);
  if (!Number.isInteger(numericId) || numericId <= 0) throw new Error(`Invalid menu id for ${location}`);
  wpEval(`$l=get_theme_mod('nav_menu_locations',[]); if(!is_array($l)){$l=[];} $l['${location}']=${numericId}; set_theme_mod('nav_menu_locations',$l);`);
}

function clearMenuLocation(location) {
  if (!Object.hasOwn(emptyRegions, location)) throw new Error(`Unsupported Footer location ${location}`);
  wpEval(`$l=get_theme_mod('nav_menu_locations',[]); if(!is_array($l)){$l=[];} unset($l['${location}']); set_theme_mod('nav_menu_locations',$l);`);
}

function restoreAllMenus() {
  for (const [location, menuId] of Object.entries(fixture.menu_ids)) setMenuLocation(location, menuId);
}

async function inspectPreset(browser, preset, viewportName, viewport) {
  const context = await browser.newContext({ viewport });
  const page = await context.newPage();
  const browserErrors = [];
  page.on('console', (message) => {
    if (message.type() === 'error') browserErrors.push(`console: ${message.text()}`);
  });
  page.on('pageerror', (error) => browserErrors.push(`pageerror: ${error.message}`));
  page.on('requestfailed', (request) => {
    if (request.url().startsWith(baseUrl)) browserErrors.push(`requestfailed: ${request.url()} ${request.failure()?.errorText || ''}`);
  });

  const result = {
    preset,
    viewport: viewportName,
    overflowPx: null,
    blockingAxeViolations: null,
    status: 'failed',
    error: null,
  };

  try {
    const response = await page.goto(`${fixture.front_url}?y3-preset=${preset}&viewport=${viewportName}`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`Expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    const footer = page.locator('footer[data-aznet-theme-site-footer]');
    if (await footer.count() !== 1) throw new Error('Expected exactly one Theme Footer');
    if (await page.locator('footer[role="contentinfo"]').count() !== 1) throw new Error('Expected exactly one contentinfo Footer');
    if (!await footer.evaluate((element, expected) => element.classList.contains(`aznet-theme-site-footer--${expected}`), preset)) {
      throw new Error(`Expected Footer preset class ${preset}`);
    }

    const stylesheets = await page.evaluate(() => Array.from(document.styleSheets).map((sheet) => sheet.href).filter(Boolean));
    if (!stylesheets.some((href) => href.includes('/assets/css/components/site-footer.css'))) {
      throw new Error('Footer stylesheet not observed');
    }

    for (const region of Object.values(emptyRegions)) {
      if (await footer.locator(region.selector).count() !== 1) throw new Error(`Expected populated Footer region ${region.selector}`);
      if (await footer.getByText(region.sentinel, { exact: true }).count() !== 1) throw new Error(`Missing sentinel ${region.sentinel}`);
    }

    result.overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (result.overflowPx !== 0) throw new Error(`Horizontal overflow: ${result.overflowPx}px`);

    const links = footer.locator('a');
    if (await links.count() < 2) throw new Error('Expected keyboard-reachable Footer links');
    await links.first().focus();
    await page.keyboard.press('Tab');
    const focus = await page.evaluate(() => {
      const active = document.activeElement;
      if (!(active instanceof HTMLElement)) return { inFooter: false, outlineStyle: '', outlineWidth: '0px' };
      const style = getComputedStyle(active);
      return {
        inFooter: Boolean(active.closest('footer[data-aznet-theme-site-footer]')),
        outlineStyle: style.outlineStyle,
        outlineWidth: style.outlineWidth,
      };
    });
    if (!focus.inFooter) throw new Error('Keyboard focus left Footer link sequence unexpectedly');
    if (focus.outlineStyle === 'none' || focus.outlineWidth === '0px') throw new Error('Footer keyboard focus is not visibly outlined');

    const axe = await new AxeBuilder({ page }).include('footer[data-aznet-theme-site-footer]').analyze();
    const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
    result.blockingAxeViolations = blocking.length;
    fs.writeFileSync(path.join(stateDir, `axe-${preset}-${viewportName}.json`), JSON.stringify(axe, null, 2));
    if (blocking.length) throw new Error(`Blocking Footer axe violations: ${blocking.length}`);
    if (browserErrors.length) throw new Error(browserErrors.join(' | '));

    await page.screenshot({ path: path.join(stateDir, `y3-${preset}-${viewportName}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`${preset}/${viewportName}: ${result.error}`);
    await page.screenshot({ path: path.join(stateDir, `y3-${preset}-${viewportName}-failure.png`), fullPage: true }).catch(() => {});
  } finally {
    summary.presets.push(result);
    await context.close();
  }
}

async function inspectEmptyMenu(browser, location, region) {
  const context = await browser.newContext({ viewport: { width: 390, height: 844 } });
  const page = await context.newPage();
  const result = { location, status: 'failed', error: null };

  try {
    clearMenuLocation(location);
    const response = await page.goto(`${fixture.front_url}?y3-empty=${encodeURIComponent(location)}`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`Expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    const footer = page.locator('footer[data-aznet-theme-site-footer]');
    if (await footer.count() !== 1) throw new Error('Expected Theme Footer in empty-menu state');
    if (await footer.locator(region.selector).count() !== 0) throw new Error(`Empty ${location} left orphan region ${region.selector}`);
    if (await footer.getByText(region.sentinel, { exact: true }).count() !== 0) throw new Error(`Empty ${location} left sentinel content`);
    if (region.heading && await footer.getByRole('heading', { name: region.heading, exact: true }).count() !== 0) {
      throw new Error(`Empty ${location} left orphan heading ${region.heading}`);
    }

    const overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (overflowPx !== 0) throw new Error(`Empty-menu horizontal overflow: ${overflowPx}px`);

    await page.screenshot({ path: path.join(stateDir, `y3-empty-${location}.png`), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`empty/${location}: ${result.error}`);
  } finally {
    setMenuLocation(location, fixture.menu_ids[location]);
    summary.emptyMenus.push(result);
    await context.close();
  }
}

restoreAllMenus();
const browser = await chromium.launch();
try {
  for (const preset of presets) {
    setPreset(preset);
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      await inspectPreset(browser, preset, viewportName, viewport);
    }
  }

  setPreset('professional');
  for (const [location, region] of Object.entries(emptyRegions)) {
    await inspectEmptyMenu(browser, location, region);
  }
} finally {
  restoreAllMenus();
  setPreset('standard');
  await browser.close();
}

fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error(`Y3 Footer browser verification failed:\n${failures.join('\n')}`);
console.log(`PASS: Y3 Footer presets ${summary.presets.length}/${summary.presets.length}; empty-menu states ${summary.emptyMenus.length}/${summary.emptyMenus.length}`);
