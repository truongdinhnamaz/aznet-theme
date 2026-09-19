import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const P1_BASE_URL = (process.env.P1_BASE_URL || 'https://lstamduchn.vn').replace(/\/$/, '');
const P1_ADMIN_USER = process.env.P1_ADMIN_USER || '';
const P1_ADMIN_PASS = process.env.P1_ADMIN_PASS || '';
const stateDir = process.env.P1_STATE_DIR || '/tmp/p1-pilot-identity';
const canonicalThemeVersion = '1.1.0';

fs.mkdirSync(stateDir, { recursive: true });

const blocking_failures = [];
const observations = {
  themes: [],
  aznet_themes: [],
  system_health: {},
  classification: [],
};

function recordFailure(code, message) {
  blocking_failures.push({ code, message });
}

async function login(page) {
  if (!P1_ADMIN_USER || !P1_ADMIN_PASS) {
    throw new Error('Required GitHub Actions credentials are unavailable');
  }

  await page.goto(`${P1_BASE_URL}/wp-login.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  await page.locator('#user_login').fill(P1_ADMIN_USER);
  await page.locator('#user_pass').fill(P1_ADMIN_PASS);
  await Promise.all([
    page.waitForURL(/\/wp-admin\//, { timeout: 30000 }),
    page.locator('#wp-submit').click(),
  ]);
}

function normalizeTheme(theme, cards) {
  const id = String(theme?.id || theme?.stylesheet || '').trim();
  const card = cards.find((item) => item.slug === id) || null;
  return {
    id: id || card?.slug || null,
    name: String(theme?.name || card?.name || '').trim() || null,
    version: String(theme?.version || '').trim() || null,
    active: Boolean(theme?.active) || Boolean(card?.active),
    parent: String(theme?.parent || '').trim() || null,
  };
}

const browser = await chromium.launch({ headless: true });
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();

  try {
    await login(page);
  } catch (error) {
    recordFailure('AUTH_LOGIN_FAILED', error instanceof Error ? error.message : String(error));
  }

  if (!blocking_failures.length) {
    try {
      await page.goto(`${P1_BASE_URL}/wp-admin/themes.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
      await page.locator('body.wp-admin').waitFor({ state: 'visible', timeout: 20000 });
      await page.screenshot({ path: path.join(stateDir, 'themes-1440.png'), fullPage: true });

      const inventory = await page.evaluate(() => {
        const cards = Array.from(document.querySelectorAll('.theme')).map((card) => ({
          slug: card.getAttribute('data-slug') || '',
          name: (card.getAttribute('data-name') || card.querySelector('.theme-name')?.textContent || '').trim(),
          active: card.classList.contains('active'),
        }));
        const settings = globalThis._wpThemeSettings || {};
        const themes = Array.isArray(settings.themes) ? settings.themes : [];
        return {
          cards,
          themes: themes.map((theme) => ({
            id: theme?.id || theme?.stylesheet || '',
            name: theme?.name || '',
            version: theme?.version || '',
            active: Boolean(theme?.active),
            parent: theme?.parent || '',
          })),
        };
      });

      if (!inventory.themes.length && !inventory.cards.length) {
        recordFailure('THEME_INVENTORY_UNAVAILABLE', 'WordPress Themes inventory could not be observed');
      } else {
        const source = inventory.themes.length ? inventory.themes : inventory.cards;
        observations.themes = source.map((theme) => normalizeTheme(theme, inventory.cards));
        observations.aznet_themes = observations.themes.filter((theme) => theme.name === 'AZnet Theme');
      }
    } catch (error) {
      recordFailure('THEME_INVENTORY_UNAVAILABLE', error instanceof Error ? error.message : String(error));
    }
  }

  if (!blocking_failures.length) {
    try {
      await page.goto(`${P1_BASE_URL}/wp-admin/admin.php?page=aznet-theme&section=system-health`, {
        waitUntil: 'domcontentloaded',
        timeout: 30000,
      });
      const snapshotField = page.locator('textarea[readonly]');
      if ((await snapshotField.count()) !== 1) {
        recordFailure('SYSTEM_HEALTH_UNAVAILABLE', 'Expected exactly one read-only Support Snapshot field');
      } else {
        const snapshot = JSON.parse(await snapshotField.inputValue());
        const report = snapshot?.report || {};
        observations.system_health = {
          product: snapshot?.product || null,
          theme: report?.theme || null,
          standalone_core_status: report?.standalone_core?.status || null,
        };

        if (snapshot?.product !== 'aznet-theme') {
          recordFailure('SYSTEM_HEALTH_IDENTITY', 'Support Snapshot product marker mismatch');
        }
        if (report?.theme?.name !== 'AZnet Theme') {
          recordFailure('SYSTEM_HEALTH_IDENTITY', `Unexpected active Theme name: ${report?.theme?.name || 'missing'}`);
        }
        if (report?.theme?.version !== canonicalThemeVersion) {
          recordFailure('SYSTEM_HEALTH_VERSION', `Expected active AZnet Theme ${canonicalThemeVersion}, observed ${report?.theme?.version || 'missing'}`);
        }
      }
    } catch (error) {
      recordFailure('SYSTEM_HEALTH_UNAVAILABLE', error instanceof Error ? error.message : String(error));
    }
  }

  if (!blocking_failures.length) {
    const activeAznet = observations.aznet_themes.filter((theme) => theme.active);
    const inactiveAznet = observations.aznet_themes.filter((theme) => !theme.active);

    if (activeAznet.length !== 1) {
      recordFailure('ACTIVE_IDENTITY_AMBIGUOUS', `Expected exactly one active AZnet Theme card, observed ${activeAznet.length}`);
    } else {
      const active = activeAznet[0];
      if (!active.id) {
        recordFailure('ACTIVE_IDENTITY_AMBIGUOUS', 'Active AZnet Theme folder identity is unavailable');
      }
      if (active.version && active.version !== canonicalThemeVersion) {
        recordFailure('ACTIVE_IDENTITY_VERSION', `Active Themes inventory reports ${active.version}; expected ${canonicalThemeVersion}`);
      }

      observations.classification = inactiveAznet.map((theme) => {
        const sameVersion = theme.version === canonicalThemeVersion;
        const hasDistinctIdentity = Boolean(theme.id && active.id && theme.id !== active.id);
        return {
          id: theme.id,
          name: theme.name,
          version: theme.version,
          active: false,
          classification: !hasDistinctIdentity || sameVersion ? 'AMBIGUOUS' : 'INACTIVE_CANDIDATE',
          reason: !hasDistinctIdentity
            ? 'Folder identity is missing or not distinct from active Theme'
            : sameVersion
              ? 'Inactive Theme reports the same 1.1.0 version as active Theme; inspect before any destructive action'
              : 'Inactive AZnet Theme has a distinct folder identity and a version different from the active canonical 1.1.0 Theme',
        };
      });
    }
  }

  await context.close();
} finally {
  await browser.close();
}

const ambiguous = observations.classification.filter((item) => item.classification === 'AMBIGUOUS');
const candidates = observations.classification.filter((item) => item.classification === 'INACTIVE_CANDIDATE');
const inspectionStatus = ambiguous.length
  ? 'AMBIGUOUS'
  : candidates.length
    ? 'CANDIDATES_FOUND'
    : 'NO_DUPLICATES';

const summary = {
  base_url: P1_BASE_URL,
  scope: 'authenticated-read-only-theme-identity-inspection',
  canonical_theme_version: canonicalThemeVersion,
  mutation_performed: false,
  deletion_authorized: false,
  inspection_status: inspectionStatus,
  blocking_failures,
  observations,
};

fs.writeFileSync(path.join(stateDir, 'summary.json'), JSON.stringify(summary, null, 2));

if (blocking_failures.length) {
  console.error(`P1 pilot identity inspection blocking failures: ${blocking_failures.map((item) => item.code).join(', ')}`);
  process.exit(1);
}

console.log(`PASS: P1 read-only identity inspection ${inspectionStatus}; inactive candidates=${candidates.length}; ambiguous=${ambiguous.length}`);
