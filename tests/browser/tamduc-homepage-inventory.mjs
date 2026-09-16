import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-homepage-inventory';

fs.mkdirSync(stateDir, { recursive: true });

const snapshot = {
  base_url: baseUrl,
  scope: 'authenticated-read-only-homepage-inventory',
  observed: {},
  not_observed: [],
  unknown: [],
  diagnostics: {
    console_errors: [],
    page_errors: [],
    request_failures: [],
  },
};

function valueState(value) {
  return value === null || value === undefined || value === '' ? 'not_observed' : 'observed';
}

function recordValue(key, value) {
  if (valueState(value) === 'observed') snapshot.observed[key] = value;
  else snapshot.not_observed.push(key);
}

async function login(page) {
  if (!adminUser || !adminPass) throw new Error('Required pilot credentials are unavailable');
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('#user_login').fill(adminUser);
  await page.locator('#user_pass').fill(adminPass);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 30000 }),
    page.locator('#wp-submit').click(),
  ]);
}

async function readSelected(page, name) {
  const locator = page.locator(`select[name="${name}"]`);
  if ((await locator.count()) !== 1) return null;
  return locator.evaluate((node) => ({
    value: node.value,
    label: node.selectedOptions?.[0]?.textContent?.trim() || '',
  }));
}

async function readMultiSelected(page, name) {
  const locator = page.locator(`select[name="${name}"]`);
  if ((await locator.count()) !== 1) return null;
  return locator.evaluate((node) => Array.from(node.selectedOptions || []).map((option) => ({
    value: option.value,
    label: option.textContent?.trim() || '',
  })));
}

async function readPublicRest(page, route, key) {
  const url = `${baseUrl}${route}`;
  const response = await page.request.get(url, { failOnStatusCode: false, timeout: 30000 });
  if (!response.ok()) {
    snapshot.unknown.push({ key, reason: `Public REST ${response.status()} at ${route}` });
    return [];
  }
  const data = await response.json();
  if (!Array.isArray(data)) {
    snapshot.unknown.push({ key, reason: `Public REST payload is not an array at ${route}` });
    return [];
  }
  return data;
}

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();

  page.on('console', (message) => {
    if (message.type() === 'error') snapshot.diagnostics.console_errors.push(message.text());
  });
  page.on('pageerror', (error) => snapshot.diagnostics.page_errors.push(error.stack || error.message));
  page.on('requestfailed', (request) => snapshot.diagnostics.request_failures.push({
    url: request.url(),
    error: request.failure()?.errorText || 'unknown',
  }));

  await login(page);

  await page.goto(`${baseUrl}/wp-admin/options-general.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  recordValue('site_title', (await page.locator('#blogname').count()) === 1 ? await page.locator('#blogname').inputValue() : null);
  recordValue('tagline', (await page.locator('#blogdescription').count()) === 1 ? await page.locator('#blogdescription').inputValue() : null);

  await page.goto(`${baseUrl}/wp-admin/options-reading.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const frontMode = await page.locator('input[name="show_on_front"]:checked').count() === 1
    ? await page.locator('input[name="show_on_front"]:checked').inputValue()
    : null;
  const frontPage = await readSelected(page, 'page_on_front');
  recordValue('front_page', frontMode ? { mode: frontMode, selection: frontPage } : null);

  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=system-health`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const supportSnapshot = page.locator('textarea[readonly]');
  if ((await supportSnapshot.count()) === 1) {
    try {
      const parsed = JSON.parse(await supportSnapshot.inputValue());
      const report = parsed?.report || {};
      const core = report?.standalone_core || {};
      recordValue('theme_version', report?.theme?.version || null);
      recordValue('custom_logo', typeof core?.logo === 'boolean' ? core.logo : null);
      recordValue('primary_menu', typeof core?.primary_menu === 'boolean' ? core.primary_menu : null);
      recordValue('system_health', {
        product: parsed?.product || null,
        wordpress: report?.environment?.wordpress || null,
        php: report?.environment?.php || null,
        theme_name: report?.theme?.name || null,
        standalone_core_status: core?.status || null,
        visual_preset: core?.visual_preset || null,
        header_preset: core?.header_preset || null,
        optional_integrations: report?.optional_integrations || null,
      });
    } catch (error) {
      snapshot.unknown.push({ key: 'system_health', reason: `Support Snapshot JSON unreadable: ${error.message}` });
    }
  } else {
    snapshot.not_observed.push('theme_version', 'custom_logo', 'primary_menu', 'system_health');
  }

  await page.goto(`${baseUrl}/wp-admin/admin.php?page=aznet-theme&section=homepage`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const homepage_preset = await readSelected(page, 'aznet_theme_settings[homepage_preset]');
  const homepage_variant = await readSelected(page, 'aznet_theme_settings[homepage_law01_variant]');
  recordValue('homepage_preset', homepage_preset);
  recordValue('homepage_variant', homepage_variant);

  const pageKeys = [
    'homepage_services_page',
    'homepage_about_page',
    'homepage_team_page',
    'homepage_process_page',
    'homepage_faq_page',
    'homepage_contact_page',
    'homepage_case_analysis_term',
    'homepage_legal_news_term',
  ];
  const mappings = {};
  for (const key of pageKeys) {
    const selected = await readSelected(page, `aznet_theme_settings[${key}]`);
    mappings[key] = selected;
  }
  mappings.homepage_knowledge_terms = await readMultiSelected(page, 'aznet_theme_settings[homepage_knowledge_terms][]');
  recordValue('homepage_mappings', mappings);

  const homepageStatusRows = page.locator('.aznet-theme-panel table tbody tr');
  const homepageStatuses = {};
  for (let index = 0; index < await homepageStatusRows.count(); index += 1) {
    const row = homepageStatusRows.nth(index);
    const cells = row.locator('th, td');
    if ((await cells.count()) >= 2) {
      const key = (await cells.nth(0).innerText()).trim();
      const value = (await cells.nth(1).innerText()).trim();
      if (key) homepageStatuses[key] = value;
    }
  }
  recordValue('homepage_slot_statuses', Object.keys(homepageStatuses).length ? homepageStatuses : null);
  await page.screenshot({ path: path.join(stateDir, 'admin-homepage-1440.png'), fullPage: true });

  const published_pages = await readPublicRest(
    page,
    '/wp-json/wp/v2/pages?status=publish&per_page=100&_fields=id,parent,slug,link,title,excerpt,featured_media',
    'published_pages',
  );
  const published_posts = await readPublicRest(
    page,
    '/wp-json/wp/v2/posts?status=publish&per_page=100&_fields=id,link,title,excerpt,featured_media,categories,date',
    'published_posts',
  );
  const categories = await readPublicRest(
    page,
    '/wp-json/wp/v2/categories?per_page=100&_fields=id,name,count,link',
    'categories',
  );
  const media = await readPublicRest(
    page,
    '/wp-json/wp/v2/media?per_page=100&_fields=id,slug,link,title,alt_text,caption,media_type,mime_type,source_url',
    'media_candidates',
  );
  const media_candidates = media.filter((item) => item?.media_type === 'image' || String(item?.mime_type || '').startsWith('image/'));
  const serviceParentId = Number.parseInt(String(mappings?.homepage_services_page?.value || '0'), 10) || 0;
  const service_child_pages = serviceParentId > 0
    ? published_pages.filter((item) => Number(item?.parent || 0) === serviceParentId)
    : [];

  const wp_public_inventory = {
    published_pages,
    published_posts,
    categories,
    media_candidates,
    service_child_pages,
  };
  recordValue('wp_public_inventory', wp_public_inventory);

  await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  const publicInventory = await page.evaluate(() => {
    const links = Array.from(document.querySelectorAll('a[href]'));
    const contactLinks = links
      .map((node) => ({ href: node.getAttribute('href') || '', text: (node.textContent || '').trim() }))
      .filter((item) => /^(tel:|mailto:)/i.test(item.href));
    const sectionNodes = Array.from(document.querySelectorAll('main section, main [class*="homepage"], main [id]'));
    const sections = sectionNodes.map((node) => ({
      tag: node.tagName.toLowerCase(),
      id: node.id || '',
      className: typeof node.className === 'string' ? node.className : '',
      heading: node.querySelector('h1,h2,h3')?.textContent?.trim() || '',
    }));
    return {
      title: document.title,
      body_class: document.body.className,
      h1: Array.from(document.querySelectorAll('h1')).map((node) => (node.textContent || '').trim()),
      public_contact_links: contactLinks,
      homepage_sections: sections,
      main_count: document.querySelectorAll('main').length,
      nav_count: document.querySelectorAll('nav').length,
      image_count: document.querySelectorAll('main img').length,
    };
  });
  recordValue('public_contact_links', publicInventory.public_contact_links);
  recordValue('homepage_sections', publicInventory.homepage_sections);
  recordValue('public_homepage', publicInventory);
  await page.screenshot({ path: path.join(stateDir, 'public-homepage-1440.png'), fullPage: true });

  await context.close();
} catch (error) {
  snapshot.unknown.push({ key: 'inventory_run', reason: error instanceof Error ? error.message : String(error) });
  process.exitCode = 1;
} finally {
  await browser.close();
  fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(snapshot, null, 2));
}

if (process.exitCode !== 1) {
  console.log('PASS: Tâm Đức authenticated read-only homepage inventory');
}
