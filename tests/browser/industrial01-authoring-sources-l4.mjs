/**
 * Industrial 01 authoring: operator-only native WordPress source mapping.
 * Disposable GitHub Actions WordPress site only. No production URLs/data.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const base = (process.env.INDUSTRIAL01_BASE_URL || 'http://127.0.0.1:8080').replace(/\/$/, '');
const outDir = process.env.INDUSTRIAL01_OUT_DIR || '/tmp/industrial01-pilot';
const fixture = JSON.parse(fs.readFileSync(path.join(outDir, 'authoring-source-ids.json'), 'utf8'));
const browser = await chromium.launch({ headless: true });
const testStatus = [];
const assert = (ok, msg) => { if (!ok) throw new Error(msg); };
const formSelector = '#aznet-theme-homepage-sources form.aznet-theme-panel';
const sourceKeys = [
  ['about', 'homepage_industrial01_about_page', fixture.about],
  ['solutions', 'homepage_industrial01_solutions_page', fixture.solutions],
  ['process', 'homepage_industrial01_process_page', fixture.process],
  ['contact', 'homepage_industrial01_contact_page', fixture.contact],
  ['knowledge', 'homepage_industrial01_knowledge_terms', fixture.knowledge],
];
try {
  const ctx = await browser.newContext({ viewport: {width:1440, height:950} });
  const page = await ctx.newPage();
  await page.goto(base + '/wp-login.php', {waitUntil:'domcontentloaded'});
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('industrial01-test-password');
  await Promise.all([
    page.waitForURL(u => !u.pathname.endsWith('wp-login.php'), {timeout:25000}),
    page.locator('#wp-submit').click(),
  ]);
  await page.goto(base + '/wp-admin/admin.php?page=aznet-theme&section=homepage', {waitUntil:'domcontentloaded'});
  const center = page.locator('.aznet-theme-control-center');
  await center.waitFor({state:'visible', timeout:20000});
  const details = page.locator('#aznet-theme-homepage-sources');
  assert(await details.count() === 1, 'Industrial 01 native sources panel missing');
  assert(await details.getAttribute('open') !== null, 'Industrial sources must be discoverable without hiding in collapsed diagnostics');

  const sourceForm = details.locator('form.aznet-theme-panel').filter({has:page.getByRole('heading',{name:'Nguồn nội dung'})});
  assert(await sourceForm.count() === 1, 'Industrial 01 source form missing or duplicated');
  for (const [slot,key,id] of sourceKeys) {
    const select = sourceForm.locator('select[name="aznet_theme_settings[' + key + ']'+(slot === 'knowledge' ? '[]' : '')+'"]');
    assert(await select.count()===1,slot+': typed WordPress source field missing');
    await select.selectOption(slot === 'knowledge' ? [String(id)] : String(id));
  }
  await Promise.all([
    page.waitForURL(url => url.pathname.endsWith('/wp-admin/admin.php') && url.searchParams.get('updated') === '1', {timeout:25000}),
    sourceForm.locator('input[type=submit]').click(),
  ]);
  // Theme intentionally redirects Save Settings to the Control Center overview.
  // Re-enter Homepage before checking persisted source references.
  await page.goto(base + '/wp-admin/admin.php?page=aznet-theme&section=homepage', {waitUntil:'domcontentloaded'});
  const reloaded = page.locator(formSelector);
  for (const [slot,key,id] of sourceKeys) {
    const target = reloaded.locator('select[name="aznet_theme_settings['+key+']'+(slot === 'knowledge' ? '[]' : '')+'"]');
    const selected = await target.locator('option:checked').evaluateAll(nodes => nodes.map(n=>n.value));
    assert(selected.includes(String(id)), slot + ' source mapping did not persist: '+JSON.stringify(selected));
  }

  const variantForm = page.locator('form.aznet-theme-panel').filter({
    has: page.locator('select[name="aznet_theme_settings[homepage_industrial01_variant]"]'),
  });
  assert(await variantForm.count() === 1, 'Native Industrial variant selector missing');
  await variantForm.locator('select[name="aznet_theme_settings[homepage_industrial01_variant]"]').selectOption('technical-b2b');
  await Promise.all([
    page.waitForURL(url => url.pathname.endsWith('/wp-admin/admin.php') && url.searchParams.get('updated') === '1', {timeout:25000}),
    variantForm.locator('input[type=submit]').click(),
  ]);
  await page.goto(base + '/wp-admin/admin.php?page=aznet-theme&section=homepage', {waitUntil:'domcontentloaded'});
  for (const [,key,id] of sourceKeys) {
    const sel = page.locator(formSelector).locator('select[name="aznet_theme_settings['+key+']'+(key.endsWith('_terms') ? '[]' : '')+'"]');
    const selected = await sel.locator('option:checked').evaluateAll(nodes=>nodes.map(n=>n.value));
    assert(selected.includes(String(id)), 'Variant save erased WordPress source mapping: '+key);
  }
  await page.screenshot({path:path.join(outDir,'technical-b2b','admin-sources.png'),fullPage:true});
  await ctx.close();

  for(const width of [1440,768,390]) {
    const context = await browser.newContext({viewport:{width,height:930}});
    const front = await context.newPage();
    const res = await front.goto(base+'/', {waitUntil:'networkidle'});
    assert(res?.status()===200, width+': Front Page unavailable');
    const audit = await front.evaluate(() => ({
      h1s: document.querySelectorAll('main#main h1').length,
      about: document.querySelector('.aznet-theme-industrial01-about h2')?.textContent.trim()||'',
      process: document.querySelector('.aznet-theme-industrial01-process h2')?.textContent.trim()||'',
      knowledge: document.querySelector('.aznet-theme-industrial01-knowledge')?.textContent||'',
      contactHref: document.querySelector('.aznet-theme-industrial01-cta__actions .aznet-theme-industrial01-button')?.getAttribute('href')||'',
      productCards: document.querySelectorAll('.aznet-theme-industrial01-product-card').length,
      overflow: document.documentElement.scrollWidth-document.documentElement.clientWidth,
      variant: !!document.querySelector('.aznet-theme-homepage--industrial-01-technical-b2b'),
    }));
    assert(audit.variant,width+': selected technical variant not rendered');
    assert(audit.h1s===1,width+': expected exactly one H1 in Homepage');
    assert(audit.about==='Giới thiệu nguồn B2B',width+': mapped WordPress About absent');
    assert(audit.process==='Quy trình xác nhận yêu cầu',width+': mapped WordPress Process absent');
    assert(audit.knowledge.includes('Kiến thức kiểm tra vật liệu'),width+': mapped WordPress Knowledge post absent');
    assert(audit.contactHref.includes('/contact-source-b2b/'),width+': mapped WordPress Contact link absent');
    assert(audit.productCards>=1,width+': native WooCommerce products unexpectedly missing');
    assert(audit.overflow<=1,width+': horizontal overflow '+audit.overflow+'px');
    const axe = await new AxeBuilder({page:front}).analyze();
    const serious = axe.violations.filter(x => ['critical','serious'].includes(x.impact||''));
    fs.writeFileSync(path.join(outDir,'technical-b2b','mapped-'+width+'-axe.json'), JSON.stringify(axe.violations,null,2));
    assert(!serious.length,width+': severe a11y violations '+serious.map(x=>x.id).join(','));
    await front.screenshot({path:path.join(outDir,'technical-b2b','mapped-'+width+'.png'),fullPage:true});
    testStatus.push({width,...audit,a11ySevere:serious.length});
    await context.close();
  }
  fs.writeFileSync(path.join(outDir,'technical-b2b','source-mapping-result.json'),JSON.stringify(testStatus,null,2));
  console.log('PASS: Industrial 01 editor maps existing WordPress sources; saves through D-050, retains them across variant switch, renders all B2B sections at 390/768/1440');
} finally {
  await browser.close();
}