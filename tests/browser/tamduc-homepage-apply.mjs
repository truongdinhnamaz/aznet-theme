// Bounded one-shot site mutation harness. The workflow executes only on an explicit [apply-tamduc] commit.
// Retry checkpoint: native Quick Edit activation passed the static regression gate.
import fs from 'node:fs';
import path from 'node:path';
import { chromium } from 'playwright';

const baseUrl = (process.env.TAMDUC_BASE_URL || 'https://tamduchanoi.aznet.vn').replace(/\/$/, '');
const adminUser = process.env.PILOT_WP_USER || '';
const adminPass = process.env.PILOT_WP_PASSWORD || '';
const stateDir = process.env.TAMDUC_STATE_DIR || '/tmp/tamduc-homepage-apply';
const planPath = process.env.TAMDUC_MUTATION_PLAN || 'ops/tamduc-homepage/mutation-plan.json';

fs.mkdirSync(stateDir, { recursive: true });
const plan = JSON.parse(fs.readFileSync(planPath, 'utf8'));
fs.copyFileSync(planPath, path.join(stateDir, 'mutation-plan.json'));

const runState = {
  site: baseUrl,
  status: 'started',
  applied: [],
  rollback: [],
  error: null,
};

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

async function waitUntil(check, label, timeoutMs = 15000) {
  const started = Date.now();
  while (Date.now() - started < timeoutMs) {
    if (await check()) return;
    await new Promise((resolve) => setTimeout(resolve, 250));
  }
  throw new Error(`Timed out waiting for ${label}`);
}

async function readBlogdescription(page) {
  await page.goto(`${baseUrl}/wp-admin/options-general.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const field = page.locator('#blogdescription');
  if ((await field.count()) !== 1) throw new Error('blogdescription field unavailable');
  return field.inputValue();
}

async function setBlogdescription(page, value) {
  await page.goto(`${baseUrl}/wp-admin/options-general.php`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const field = page.locator('#blogdescription');
  if ((await field.count()) !== 1) throw new Error('blogdescription field unavailable');
  await field.fill(value);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }),
    page.locator('#submit').click(),
  ]);
}

async function readPageTitle142(page) {
  await page.goto(`${baseUrl}/wp-admin/edit.php?post_type=page`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const row = page.locator('#post-142');
  if ((await row.count()) !== 1) throw new Error('Page #142 row unavailable');
  return (await row.locator('.row-title').innerText()).trim();
}

async function clickQuickEdit(row) {
  const button = row.locator('.editinline');
  if ((await button.count()) !== 1) throw new Error('Quick Edit button unavailable for Page #142');
  await button.evaluate((button) => button.click());
}

async function setPageTitle142(page, value) {
  await page.goto(`${baseUrl}/wp-admin/edit.php?post_type=page`, { waitUntil: 'domcontentloaded', timeout: 30000 });
  const row = page.locator('#post-142');
  if ((await row.count()) !== 1) throw new Error('Page #142 row unavailable');
  await clickQuickEdit(row);
  const editor = page.locator('#edit-142');
  await editor.waitFor({ state: 'visible', timeout: 10000 });
  const title = editor.locator('input[name="post_title"]');
  if ((await title.count()) !== 1) throw new Error('Quick Edit title input unavailable for Page #142');
  await title.fill(value);
  await editor.locator('.save').click();
  await waitUntil(async () => {
    const currentRow = page.locator('#post-142');
    return (await currentRow.count()) === 1
      && (await currentRow.locator('.row-title').innerText()).trim() === value;
  }, `Page #142 title to become ${value}`);
}

async function readMutationField(page, mutation) {
  if (mutation.field === 'blogdescription') return readBlogdescription(page);
  if (mutation.field === 'page_title_142') return readPageTitle142(page);
  throw new Error(`Unsupported mutation field: ${mutation.field}`);
}

async function writeMutationField(page, mutation, value) {
  if (mutation.field === 'blogdescription') return setBlogdescription(page, value);
  if (mutation.field === 'page_title_142') return setPageTitle142(page, value);
  throw new Error(`Unsupported mutation field: ${mutation.field}`);
}

async function verifyPreconditions(page) {
  const beforeState = {};
  for (const mutation of plan.mutations) {
    const before = await readMutationField(page, mutation);
    beforeState[mutation.id] = { field: mutation.field, value: before };
    if (before !== mutation.before) {
      throw new Error(`Baseline drift for ${mutation.id}: expected ${JSON.stringify(mutation.before)}, observed ${JSON.stringify(before)}`);
    }
  }
  fs.writeFileSync(path.join(stateDir, 'before.json'), JSON.stringify(beforeState, null, 2));
  return beforeState;
}

async function rollbackApplied(page, appliedMutations) {
  for (const mutation of [...appliedMutations].reverse()) {
    try {
      await writeMutationField(page, mutation, mutation.rollback);
      const observed = await readMutationField(page, mutation);
      if (observed !== mutation.rollback) throw new Error(`Rollback verification mismatch: ${observed}`);
      runState.rollback.push({ id: mutation.id, status: 'restored', value: observed });
    } catch (error) {
      runState.rollback.push({ id: mutation.id, status: 'rollback_failed', error: error.message });
    }
  }
}

const browser = await chromium.launch({ headless: true });
const appliedMutations = [];
try {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  await login(page);
  await verifyPreconditions(page);

  for (const mutation of plan.mutations) {
    await writeMutationField(page, mutation, mutation.after);
    const observed = await readMutationField(page, mutation);
    if (observed !== mutation.after) {
      throw new Error(`Post-save verification failed for ${mutation.id}: ${JSON.stringify(observed)}`);
    }
    appliedMutations.push(mutation);
    runState.applied.push({ id: mutation.id, field: mutation.field, before: mutation.before, after: observed });
  }

  const afterState = {};
  for (const mutation of plan.mutations) {
    afterState[mutation.id] = { field: mutation.field, value: await readMutationField(page, mutation) };
  }
  fs.writeFileSync(path.join(stateDir, 'after.json'), JSON.stringify(afterState, null, 2));

  await page.goto(baseUrl, { waitUntil: 'networkidle', timeout: 30000 });
  await page.screenshot({ path: path.join(stateDir, 'public-homepage-after-1440.png'), fullPage: true });
  runState.status = 'applied';
  await context.close();
} catch (error) {
  runState.error = error instanceof Error ? error.message : String(error);
  runState.status = 'failed';
  try {
    const rollbackContext = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const rollbackPage = await rollbackContext.newPage();
    await login(rollbackPage);
    await rollbackApplied(rollbackPage, appliedMutations);
    await rollbackContext.close();
  } catch (rollbackError) {
    runState.rollback.push({ status: 'rollback_context_failed', error: rollbackError.message });
  }
  process.exitCode = 1;
} finally {
  await browser.close();
  fs.writeFileSync(path.join(stateDir, 'run-state.json'), JSON.stringify(runState, null, 2));
}

if (process.exitCode !== 1) console.log('PASS: Tâm Đức bounded homepage mutations applied and verified');
