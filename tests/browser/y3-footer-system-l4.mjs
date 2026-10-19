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

function setHomepagePreset(preset) {
  wpEval(`$s=get_theme_mod('aznet_theme_settings',[]); if(!is_array($s)){$s=[];} $s['homepage_preset']='${preset}'; set_theme_mod('aznet_theme_settings',$s);`);
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

    for (const [location, region] of Object.entries(emptyRegions)) {
      if (preset === 'law-01' && location === 'footer-social') {
        if (await footer.getByText(region.sentinel, { exact: true }).count() !== 0) {
          throw new Error('Law 01 must not render unmanaged social-menu sentinel content');
        }
        const contactBlock = footer.locator('.aznet-theme-site-footer__contact-social');
        const contactControls = contactBlock.locator('.aznet-theme-site-footer__contact-link');
        if (await contactControls.count() !== 5) {
          throw new Error(`Law 01 contact column must render five populated rows, got ${await contactControls.count()}`);
        }
        const rowLabels = (await contactBlock.locator('.aznet-theme-site-footer__contact-label').allTextContents())
          .map((value) => value.trim().replace(/:$/, ''));
        const expectedRowLabels = ['Địa chỉ', 'SĐT', 'Email', 'Website', 'Fanpage'];
        if (JSON.stringify(rowLabels) !== JSON.stringify(expectedRowLabels)) {
          throw new Error(`Law 01 contact row order mismatch: ${JSON.stringify(rowLabels)}`);
        }
        for (const value of ['Y3 Contact Sentinel', '02437164123', 'contact-overflow-regression@example-legal-services-domain.test', 'example.test', 'Facebook']) {
          const text = contactBlock.locator('.aznet-theme-site-footer__contact-text').filter({ hasText: value });
          if (await text.count() !== 1 || !await text.isVisible()) {
            throw new Error(`Law 01 populated contact value must be visibly rendered: ${value}`);
          }
        }
        if (await contactBlock.locator('.aznet-theme-site-footer__channel-link, .aznet-theme-site-footer__channel-icon, .aznet-theme-site-footer__contact-icon').count() !== 0) {
          throw new Error('Law 01 contact information must be a labeled text list without icon controls.');
        }
        const website = contactBlock.locator('.aznet-theme-site-footer__contact-link--website');
        if (await website.count() !== 1 || !await website.isVisible()) {
          throw new Error('Law 01 website contact must render only when website profile data exists');
        }
        const fanpage = contactBlock.locator('.aznet-theme-site-footer__contact-link--fanpage');
        if (await fanpage.count() !== 1 || await fanpage.getAttribute('target') !== '_blank') {
          throw new Error('Law 01 Fanpage row must render the managed Facebook URL as a safe external link');
        }
        continue;
      }
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

async function inspectLawSkinWithProfessional(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  const result = { preset: 'professional', skin: 'law-01', status: 'failed', error: null };

  try {
    setPreset('professional');
    setHomepagePreset('law-01');
    const response = await page.goto(`${fixture.front_url}?y3-skin=law-01`, { waitUntil: 'networkidle' });
    if (!response || response.status() !== 200) throw new Error(`Expected HTTP 200, got ${response?.status() ?? 'missing'}`);

    const footer = page.locator('footer[data-aznet-theme-site-footer]');
    if (await footer.count() !== 1) throw new Error('Expected exactly one Theme Footer');
    const classes = await footer.getAttribute('class') || '';
    if (!classes.includes('aznet-theme-site-footer--professional')) throw new Error('Professional content preset class missing');
    if (!classes.includes('aznet-theme-site-footer--skin-law-01')) throw new Error('Law 01 visual skin class missing');

    const shell = await footer.evaluate((element) => {
      const style = getComputedStyle(element);
      return {
        backgroundImage: style.backgroundImage,
        borderTopWidth: style.borderTopWidth,
        borderTopStyle: style.borderTopStyle,
      };
    });
    if (!shell.backgroundImage.includes('gradient')) throw new Error('Law 01 visual skin gradient background missing');
    if (shell.borderTopWidth === '0px' || shell.borderTopStyle === 'none') throw new Error('Law 01 visual skin top border missing');

    const overflowPx = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
    if (overflowPx !== 0) throw new Error(`Professional + Law 01 skin horizontal overflow: ${overflowPx}px`);

    await page.screenshot({ path: path.join(stateDir, 'y3-professional-law01-skin.png'), fullPage: true });
    result.status = 'passed';
  } catch (error) {
    result.error = error instanceof Error ? error.message : String(error);
    failures.push(`professional/law-01-skin: ${result.error}`);
  } finally {
    setHomepagePreset('');
    summary.presets.push(result);
    await context.close();
  }
}


/**
 * Industrial 01 visual skin on Professional WordPress-owned rich Footer columns.
 * The source menu/content remains native; this is a visual-only, four-viewport
 * browser/a11y assertion with a preserved plain-text vs authored-link boundary.
 */
async function inspectIndustrialProfessionalFooter(browser) {
  const existing = wpEval("echo base64_encode(wp_json_encode(get_theme_mod('aznet_theme_settings', [])));");
  const columns = [
    '<p><strong>Địa chỉ:</strong> 62 địa chỉ nguồn WordPress, Hà Nội<br><strong>SĐT:</strong> <a href="tel:02437164123">02437164123</a><br><strong>Email:</strong> <a href="mailto:footer-source@example.test">footer-source@example.test</a><br><strong>Website:</strong> <a href="https://example.test/">example.test</a></p>',
    '<p><strong>SẢN PHẨM</strong></p><ul><li>Máy chế biến gỗ nguyên văn</li><li><a href="/wp-source-category/">Danh mục có liên kết nguồn</a></li></ul>',
    '<p><strong>GIẢI PHÁP &amp; DỊCH VỤ</strong></p><ul><li>Giải pháp từ WordPress</li><li>Lắp đặt và chạy thử</li></ul>',
    '<p><strong>THÔNG TIN</strong></p><ul><li>Về doanh nghiệp</li><li>Liên hệ</li></ul>',
  ];
  const encoded = Buffer.from(JSON.stringify(columns), 'utf8').toString('base64');
  const seed = "$blocks=json_decode(base64_decode('" + encoded + "'),true);"
    + "$s=get_theme_mod('aznet_theme_settings',[]);if(!is_array($s)){$s=[];}"
    + "foreach($blocks as $i=>$html){"
    + "$id=wp_insert_post(['post_type'=>'wp_block','post_status'=>'publish','post_title'=>'Y3 Industrial Footer '.($i+1),'post_content'=>$html],true);"
    + "if(is_wp_error($id)||!$id){throw new RuntimeException('Native WordPress Footer column missing');}"
    + "$s['footer_professional_column_'.($i+1).'_block']=(int)$id;"
    + "}$s['footer_preset']='professional';$s['visual_preset']='industrial-01';"
    + "set_theme_mod('aznet_theme_settings',$s);";
  wpEval(seed);

  try {
    for (const [viewportName, viewport] of Object.entries(viewports)) {
      const context = await browser.newContext({ viewport });
      const page = await context.newPage();
      const result = { preset: 'professional', skin: 'industrial-01', viewport: viewportName, status: 'failed', error: null };
      try {
        const response = await page.goto(fixture.front_url + '?y3-industrial-premium=' + viewportName, { waitUntil: 'networkidle' });
        if (!response || response.status() !== 200) throw new Error('Industrial Footer returned non-200');
        if (await page.locator('body.aznet-theme-preset--industrial-01').count() !== 1) {
          throw new Error('Industrial visual preset not applied to native body');
        }
        const footer = page.locator('footer.aznet-theme-site-footer--professional');
        if (await footer.count() !== 1 || await footer.locator('.aznet-theme-site-footer__column-content').count() !== 4) {
          throw new Error('Native WordPress-owned four Footer columns were not rendered');
        }
        const cols = await footer.evaluate((element) => {
          const computed = (selector) => element.querySelector(selector);
          const get = (selector) => {
            const target = computed(selector);
            return target ? getComputedStyle(target) : null;
          };
          const grid = get('.aznet-theme-site-footer__main');
          const heading = get('.aznet-theme-site-footer__column-content--2 .aznet-theme-site-footer__rich-content > p:first-child > strong');
          const nav = get('.aznet-theme-site-footer__column-content--2 .aznet-theme-site-footer__rich-content ul');
          const brand = get('.aznet-theme-site-footer__brand');
          const logo = get('.aznet-theme-site-footer__logo');
          const bottom = get('.aznet-theme-site-footer__bottom');
          const footerStyle = getComputedStyle(element);
          return {
            columns: grid ? grid.gridTemplateColumns.trim().split(/\s+/).length : 0,
            background: footerStyle.backgroundImage,
            borderColor: footerStyle.borderTopColor,
            brandLayout: brand?.display || '',
            logoMaxWidth: logo?.maxWidth || '',
            headingColor: heading?.color || '',
            listStyle: nav?.listStyleType || '',
            bottomBorder: bottom?.borderTopStyle || '',
          };
        });
        // The Footer content and copyright must sit on the same actual
        // Industrial shell as Homepage sections; on wide screens that cap
        // also sets the Header logo's left axis.
        const alignment = await page.evaluate(() => {
          const inner = document.querySelector('.aznet-theme-site-footer__inner');
          const header = document.querySelector('.aznet-theme-site-header__inner');
          const brand = inner?.querySelector('.aznet-theme-site-footer__brand');
          const firstColumn = inner?.querySelector('.aznet-theme-site-footer__column-content--1');
          const copyright = inner?.querySelector('.aznet-theme-site-footer__copyright');
          const probe = document.createElement('div');
          probe.style.cssText = 'position:fixed;top:0;left:0;visibility:hidden;pointer-events:none;width:var(--aznet-theme-container-shell);height:var(--aznet-theme-gutter);box-sizing:content-box';
          document.body.appendChild(probe);
          const capPx = probe.getBoundingClientRect().width;
          const gutterPx = probe.getBoundingClientRect().height;
          probe.remove();
          const viewportPx = document.documentElement.clientWidth;
          const width = Math.min(capPx, viewportPx - 2 * gutterPx);
          const expectedLeft = (viewportPx - width) / 2;
          const innerRect = inner?.getBoundingClientRect();
          return {
            expectedLeft,
            expectedWidth: width,
            innerLeft: innerRect?.left ?? null,
            innerWidth: innerRect?.width ?? null,
            headerLeft: header?.getBoundingClientRect().left ?? null,
            brandLeft: brand?.getBoundingClientRect().left ?? null,
            columnLeft: firstColumn?.getBoundingClientRect().left ?? null,
            copyrightLeft: copyright?.getBoundingClientRect().left ?? null,
            viewportPx,
          };
        });
        const align = (value, expected) => Number.isFinite(value) && Math.abs(value - expected) <= 2;
        if (!align(alignment.innerLeft, alignment.expectedLeft) ||
            !align(alignment.innerWidth, alignment.expectedWidth) ||
            !align(alignment.columnLeft, alignment.expectedLeft) ||
            !align(alignment.brandLeft, alignment.expectedLeft) ||
            !align(alignment.copyrightLeft, alignment.expectedLeft) ||
            (viewport.width >= 1440 && !align(alignment.headerLeft, alignment.expectedLeft))) {
          throw new Error('Industrial Footer is off the shared Header/Homepage content axis: ' + JSON.stringify(alignment));
        }
        const expectedColumns = viewport.width <= 768 ? 1 : (viewport.width <= 1120 ? 2 : 4);
        if (cols.columns !== expectedColumns || !cols.background.includes('gradient') ||
            cols.borderColor !== 'rgb(245, 202, 114)' || cols.brandLayout !== 'flex' ||
            cols.headingColor !== 'rgb(255, 255, 255)' || cols.listStyle !== 'none' ||
            cols.bottomBorder === 'none') {
          throw new Error('Industrial Footer premium computed styles missing: ' + JSON.stringify({ expectedColumns, ...cols }));
        }
        if (await footer.getByText('Máy chế biến gỗ nguyên văn', { exact: true }).count() !== 1 ||
            await footer.locator('li:has-text("Máy chế biến gỗ nguyên văn") a').count() !== 0 ||
            await footer.locator('a[href="/wp-source-category/"]').count() !== 1) {
          throw new Error('Native Footer content/links were mutated or invented');
        }
        const contact = footer.locator('.aznet-theme-site-footer__column-content--1 .aznet-theme-site-footer__rich-content');
        if (await contact.locator('a[href="tel:02437164123"]').count() !== 1 ||
            await contact.locator('a[href="mailto:footer-source@example.test"]').count() !== 1) {
          throw new Error('WordPress-authored contact URLs were not retained');
        }
        const overflow = await page.evaluate(() => Math.max(0, document.documentElement.scrollWidth - document.documentElement.clientWidth));
        if (overflow !== 0) throw new Error('Industrial Footer horizontal overflow: ' + overflow + 'px');
        const axe = await new AxeBuilder({ page }).include('footer[data-aznet-theme-site-footer]').analyze();
        fs.writeFileSync(path.join(stateDir, 'axe-industrial-professional-' + viewportName + '.json'), JSON.stringify(axe, null, 2));
        const blocking = axe.violations.filter((violation) => ['critical', 'serious'].includes(violation.impact));
        if (blocking.length) throw new Error('Industrial Footer blocking accessibility findings: ' + JSON.stringify(blocking.map((v) => v.id)));
        await footer.screenshot({ path: path.join(stateDir, 'y3-industrial-professional-' + viewportName + '.png') });
        result.status = 'passed';
      } catch (error) {
        result.error = error instanceof Error ? error.message : String(error);
        failures.push('industrial-professional/' + viewportName + ': ' + result.error);
        await page.screenshot({ path: path.join(stateDir, 'y3-industrial-professional-' + viewportName + '-failure.png'), fullPage: true }).catch(() => {});
      } finally {
        summary.presets.push(result);
        await context.close();
      }
    }
  } finally {
    wpEval("set_theme_mod('aznet_theme_settings',(array)json_decode(base64_decode('" + existing + "'),true));");
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

  await inspectLawSkinWithProfessional(browser);

  setPreset('professional');
  for (const [location, region] of Object.entries(emptyRegions)) {
    await inspectEmptyMenu(browser, location, region);
  }

  await inspectIndustrialProfessionalFooter(browser);
} finally {
  restoreAllMenus();
  setPreset('standard');
  await browser.close();
}

fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));
if (failures.length) throw new Error(`Y3 Footer browser verification failed:\n${failures.join('\n')}`);
console.log(`PASS: Y3 Footer presets ${summary.presets.length}/${summary.presets.length}; empty-menu states ${summary.emptyMenus.length}/${summary.emptyMenus.length}`);
