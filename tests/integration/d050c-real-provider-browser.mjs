/**
 * D-050C L5: only run with real, separate AZnet Site Guard MU-plugin loaded.
 * Same-site WordPress fixture; never run against production/hosted websites.
 */
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const base = (process.env.D050C_BASE_URL || '').replace(/\/$/, '');
const ownerPass = process.env.D050C_OWNER_PASS || '';
const restrictedPass = process.env.D050C_RESTRICTED_PASS || '';
const themeOnlyPass = process.env.D050C_THEMEONLY_PASS || '';
const guardOnlyPass = process.env.D050C_GUARDONLY_PASS || '';
const evidence = process.env.D050C_EVIDENCE_DIR || '/tmp/d050c-l5-evidence';
if (!/^http:\/\/127\.0\.0\.1:\d+$/.test(base)) throw new Error('L5 requires a localhost-only disposable WordPress origin');
if (![ownerPass, restrictedPass, themeOnlyPass, guardOnlyPass].every(Boolean)) throw new Error('L5 disposable fixture credentials missing');
fs.mkdirSync(evidence, { recursive: true });
const browser = await chromium.launch({ headless: true });
const results = [];
const errors = [];
const themeDanger = `${base}/wp-admin/admin.php?page=aznet-theme&section=danger-zone`;
const nativeFallback = `${base}/wp-admin/admin.php?page=aznet-site-guard`;
const adminPost = `${base}/wp-admin/admin-post.php`;

function check(ok, description) {
  if (!ok) throw new Error('FAIL L5: ' + description);
}
async function login(page, user, password) {
  await page.goto(`${base}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  await page.locator('#user_login').fill(user);
  await page.locator('#user_pass').fill(password);
  try {
    await Promise.all([
      page.waitForURL(/\/wp-admin\//, { waitUntil: 'domcontentloaded', timeout: 30000 }),
      page.locator('#wp-submit').click({ noWaitAfter: true }),
    ]);
  } catch (err) {
    const loginError = (await page.locator('#login_error').textContent().catch(() => '')) || '';
    throw new Error(`FAIL L5: fixture login for ${user}; URL=${page.url()}; error=${loginError.trim().slice(0, 200)}; cause=${String(err)}`);
  }
  check(await page.locator('#wpadminbar').count() > 0, 'WordPress admin shell present for ' + user);
}
async function createActor(user, password, viewport = { width: 1440, height: 920 }) {
  const ctx = await browser.newContext({ viewport });
  const page = await ctx.newPage();
  page.on('pageerror', err => errors.push(`${user}: ${err.message}`));
  await login(page, user, password);
  return { ctx, page };
}
async function openThemeZone(page) {
  await page.goto(themeDanger, { waitUntil: 'domcontentloaded' });
  await page.locator('.aznet-theme-site-guard-panel').waitFor({ state: 'visible', timeout: 18000 });
  return page.locator('.aznet-theme-site-guard-panel');
}
async function isLocked(panel) {
  return (await panel.innerText()).includes('ĐANG KHÓA');
}
async function auditScreen(page, name) {
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  // Capture the specific overflow source for a real RED. Never record text,
  // hidden input values or HTML attributes that might contain auth material.
  if (overflow > 1) {
    const geometry = await page.evaluate(() => {
      const view = document.documentElement.clientWidth;
      const candidates = [...document.querySelectorAll('body *')].map(el => {
        const r = el.getBoundingClientRect();
        return {
          tag: el.tagName.toLowerCase(), id: (el.id || '').slice(0, 90),
          cls: String(el.className?.baseVal ?? el.className ?? '').slice(0, 135),
          x: Math.round(r.left), right: Math.round(r.right),
          width: Math.round(r.width), scroll: el.scrollWidth, client: el.clientWidth,
        };
      }).filter(x => x.right > view + 2 || x.x < -2 || x.scroll > x.client + 2);
      return { viewport: view, scrollWidth: document.documentElement.scrollWidth,
        suspects: candidates.sort((a, b) => (b.right - view) - (a.right - view)).slice(0, 35) };
    });
    fs.writeFileSync(path.join(evidence, `${name}-overflow.json`), JSON.stringify(geometry, null, 2));
    await page.screenshot({ path: path.join(evidence, `${name}-RED-overflow.png`), fullPage: true });
    console.error('D050C mobile overflow diagnostic: ' + JSON.stringify(geometry));
  }
  check(overflow <= 1, `${name}: horizontal overflow ${overflow}`);
  const a11y = await new AxeBuilder({ page }).include('.aznet-theme-control-center').analyze();
  fs.writeFileSync(path.join(evidence, `${name}-axe.json`), JSON.stringify(a11y, null, 2));
  const serious = a11y.violations.filter(v => ['serious', 'critical'].includes(v.impact || ''));
  check(serious.length === 0, `${name}: axe serious/critical ${serious.map(v => v.id).join(',')}`);
  await page.screenshot({ path: path.join(evidence, `${name}.png`), fullPage: true });
}
async function submitScope(page, scope, reason = 'Approved disposable L5 test of provider authority') {
  const zone = await openThemeZone(page);
  const form = zone.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_site_guard_unlock"]') });
  check(await form.count() === 1, 'Theme renders exactly one site-owned unlock form');
  await form.locator('select[name="guard_scope"]').selectOption(scope);
  await form.locator('textarea[name="guard_reason"]').fill(reason);
  await form.locator('input[name="guard_confirm"]').fill('UNLOCK');
  await form.locator('[type="submit"]').click();
  await page.locator('.aznet-theme-site-guard-panel input[name="action"][value="aznet_site_guard_lock"]').waitFor({ state: 'attached', timeout: 18000 });
  check(new URL(page.url()).searchParams.get('section') === 'danger-zone', 'provider safe redirect returns to unified Theme zone');
}
async function closeScope(page) {
  const form = page.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_site_guard_lock"]') });
  check(await form.count() === 1, 'provider lock form visible while unlocked');
  await form.locator('[type="submit"]').click();
  const panel = await openThemeZone(page);
  check(await isLocked(panel), 'provider relocked through its own POST');
}
async function activateTheme(page, stylesheet) {
  await page.goto(`${base}/wp-admin/themes.php`, { waitUntil: 'domcontentloaded' });
  const choice = page.locator(`a[href*="action=activate"][href*="${stylesheet}"]`);
  check(await choice.count() > 0, `native WordPress Theme activation link for ${stylesheet}`);
  const href = await choice.first().getAttribute('href');
  check(href && href.includes('action=activate'), 'native nonce-protected Theme activation link');
  await page.goto(new URL(href, base).href, { waitUntil: 'domcontentloaded' });
  const current = await page.locator('.theme.active').getAttribute('data-slug').catch(() => null);
  if (current) check(current === stylesheet, `active Theme must be ${stylesheet}, found ${current}`);
  results.push('native WordPress Theme activated ' + stylesheet);
}

try {
  // Ordinary Administrator: neither permission implies the other.
  const restricted = await createActor('restricted', restrictedPass);
  const rz = await restricted.page.goto(themeDanger, { waitUntil: 'domcontentloaded' });
  check((rz?.status() || 0) >= 400 || await restricted.page.locator('.aznet-theme-site-guard-panel').count() === 0, 'ordinary Administrator cannot deep-link unified danger screen');
  const denial = await restricted.page.request.post(adminPost, { form: {
    action: 'aznet_site_guard_unlock', guard_scope: 'theme_switch',
    guard_reason: 'This unauthorized request must be denied',
    guard_confirm: 'UNLOCK',
  }, failOnStatusCode: false });
  check(denial.status() === 403, 'provider denies ordinary Administrator direct POST independent of Theme');
  await restricted.ctx.close();
  results.push('ordinary administrator denied on both surfaces');

  const themeOnly = await createActor('themeonly', themeOnlyPass);
  const themePanel = await openThemeZone(themeOnly.page);
  check((await themePanel.innerText()).includes('chưa được cấp quyền vận hành'), 'Theme-only actor receives read-only status');
  check(await themePanel.locator('input[name="action"][value="aznet_site_guard_unlock"]').count() === 0, 'Theme-only actor never receives Guard unlock');
  await themeOnly.ctx.close();
  results.push('Theme capability cannot escalate Site Guard');

  const guardOnly = await createActor('guardonly', guardOnlyPass);
  await guardOnly.page.goto(nativeFallback, { waitUntil: 'domcontentloaded' });
  check((await guardOnly.page.locator('body').innerText()).includes('Status: LOCKED by default.'), 'Site Guard fallback screen remains available without Theme privilege');
  await guardOnly.page.goto(themeDanger, { waitUntil: 'domcontentloaded' });
  check(await guardOnly.page.locator('.aznet-theme-site-guard-panel').count() === 0, 'Site Guard operator has no implicit Theme Danger permission');
  await guardOnly.ctx.close();
  results.push('Site Guard independent fallback + separate privilege');

  for (const viewport of [
    { width: 1440, height: 920 },
    { width: 390, height: 844 },
  ]) {
    const label = String(viewport.width);
    const { page, ctx } = await createActor('owner', ownerPass, viewport);
    let panel = await openThemeZone(page);
    check(await isLocked(panel), label + ': real installed Site Guard starts locked');
    check(!(await panel.innerText()).includes('CHƯA XÁC MINH'), label + ': actual provider is verified in Theme');
    check(await page.locator('input[name="action"][value="aznet_theme_reset_settings"]').count() === 0, 'Theme local reset remains separately locked');
    await auditScreen(page, `locked-${label}`);

    const scopeForm = panel.locator('form').filter({ has: page.locator('input[name="action"][value="aznet_site_guard_unlock"]') });
    const nonce = await scopeForm.locator('input[name="_wpnonce"]').inputValue();
    const bad = await page.request.post(adminPost, { form: {
      action: 'aznet_site_guard_unlock', _wpnonce: nonce, guard_scope: 'theme_switch',
      guard_reason: 'Invalid confirmation must not unlock Site Guard',
      guard_confirm: 'WRONG', guard_return_to: themeDanger,
    }, failOnStatusCode: false });
    check(bad.status() === 400, label + ': wrong confirmation rejected by provider');
    panel = await openThemeZone(page);
    check(await isLocked(panel), label + ': invalid confirmation did not unlock');

    await submitScope(page, 'theme_switch');
    panel = await openThemeZone(page);
    check((await panel.innerText()).includes('ĐÃ MỞ KHÓA TẠM THỜI'), label + ': real provider scope is shown as open');
    check((await panel.innerText()).includes('Activate another WordPress theme'), label + ': only selected Site Guard scope is surfaced');
    await auditScreen(page, `unlocked-${label}`);

    const anotherSession = await createActor('owner', ownerPass, viewport);
    const second = await openThemeZone(anotherSession.page);
    check(await isLocked(second), label + ': independent login session remains locked');
    await anotherSession.ctx.close();
    await closeScope(page);
    await ctx.close();
    results.push(label + ' responsive + a11y + nonce/actor/session isolation');
  }

  // WordPress-native Theme switch with an authorized provider scope (disposable fixture only).
  const { page: switching, ctx: switchContext } = await createActor('owner', ownerPass);
  await submitScope(switching, 'theme_switch', 'Approved disposable L5 WordPress Theme switch and rollback');
  await activateTheme(switching, 'twentytwentyfive');
  await switching.goto(nativeFallback, { waitUntil: 'domcontentloaded' });
  check((await switching.locator('body').innerText()).includes('Status: LOCKED by default.'), 'MU-plugin remains active and relocked after AZnet Theme deactivation');
  await switching.locator('#aznet-sg-scope').selectOption('theme_switch');
  await switching.locator('#aznet-sg-reason').fill('Return disposable L5 WordPress fixture back to AZnet Theme');
  await switching.locator('#aznet-sg-confirm').fill('UNLOCK');
  await switching.locator('form input[type="submit"]').first().click();
  await switching.getByText('TEMPORARILY UNLOCKED:', { exact: false }).waitFor();
  await activateTheme(switching, 'aznet-theme');
  check(await (await openThemeZone(switching)).count() === 1, 'unified Theme page works after real Theme switch/rollback');
  await switchContext.close();
  results.push('native Theme switch and restore without losing site-owned Guard');

  check(errors.length === 0, 'unexpected browser JavaScript errors: ' + errors.join(' | '));
  fs.writeFileSync(path.join(evidence, 'RESULTS.txt'), results.join('\n') + '\n');
  console.log('PASS L5: ' + results.join('; '));
} finally {
  await browser.close();
}
