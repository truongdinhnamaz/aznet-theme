# Woo L4 Browser Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a test-only GitHub Actions browser verification slice that proves AZnet Theme Woo surfaces render without overflow, preserve visible keyboard focus, have no critical/serious axe findings, and produce reviewable screenshots at desktop/mobile widths.

**Architecture:** Stack a new test branch on the already-PASS L3 runtime branch. Reuse the L3 WordPress/Woo fixture, build a fresh isolated WordPress+Woo runtime in GitHub Actions, then run a Playwright + axe browser harness. Keep all changes in test/workflow/evidence/docs paths and never modify production Theme files.

**Tech Stack:** GitHub Actions, PHP 8.1, MySQL 8.0, WP-CLI, WordPress >= 6.9, WooCommerce, Node.js, Playwright Chromium, @axe-core/playwright.

**Spec:** `docs/superpowers/specs/2026-09-03-woo-l4-browser-design.md`

## Global Constraints

- Production Theme files MUST remain byte-identical to L3/main.
- Test exactly five Woo surfaces at 1440x1000 and 390x844.
- Checkout MUST use a real Woo cart session; do not disable Woo redirect/validation behavior.
- Axe `critical` and `serious` violations MUST fail the run.
- Every surface/viewport MUST produce a full-page screenshot and axe JSON artifact.
- Do not deploy to remquocanh.vn.
- Do not code RootProfile or ConvertFlow.
- Do not update authoritative AZT source documents.

---

### Task 1: L4 workflow contract

**Files:**
- Create: `tests/offline/woo-l4-browser-workflow-contract.php`
- Later consumes: `.github/workflows/woo-l4-browser.yml`, `tests/browser/woo-l4-browser.mjs`

**Interfaces:**
- Consumes: approved L4 spec invariants.
- Produces: one static contract that prevents weakening browser/a11y/evidence requirements.

- [ ] **Step 1: Write the failing contract**

The contract must fail while the L4 workflow/browser script are absent and then require these exact invariants:

```php
$requiredWorkflow = [
    'mysql:8.0',
    "php-version: '8.1'",
    'wp plugin install woocommerce --activate',
    'wp theme activate aznet-theme',
    'npx playwright install chromium --with-deps',
    'node tests/browser/woo-l4-browser.mjs',
    'actions/upload-artifact@v4',
];

$requiredBrowser = [
    'chromium.launch',
    '1440',
    '390',
    'AxeBuilder',
    "['critical', 'serious']",
    'scrollWidth',
    'screenshot',
    'page.keyboard.press(\'Tab\')',
    'add-to-cart=',
];
```

Also reject references to `remquocanh.vn`, Woo private storage, or production source paths.

- [ ] **Step 2: Run contract and verify RED**

Run:

```bash
php tests/offline/woo-l4-browser-workflow-contract.php
```

Expected: FAIL because `.github/workflows/woo-l4-browser.yml` and/or `tests/browser/woo-l4-browser.mjs` do not exist.

- [ ] **Step 3: Commit only the RED contract**

```bash
git add tests/offline/woo-l4-browser-workflow-contract.php
git commit -m "test: define Woo L4 browser contract"
```

---

### Task 2: Browser verification harness

**Files:**
- Create: `tests/browser/woo-l4-browser.mjs`

**Interfaces:**
- Consumes: `WOO_L3_BASE_URL`, `WOO_L3_STATE_DIR/runtime-ids.env` from the inherited L3 fixture.
- Produces: `/tmp/woo-l4/screenshots/*.png`, `/tmp/woo-l4/axe/*.json`, `/tmp/woo-l4/browser-summary.json`.

- [ ] **Step 1: Implement the minimal browser harness required by the RED contract**

Core shape:

```js
import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';

const viewports = {
  desktop: { width: 1440, height: 1000 },
  mobile: { width: 390, height: 844 },
};

const surfaces = [
  ['product', `/?post_type=product&p=${ids.PRODUCT_ID}`, '.woocommerce div.product', 'button.single_add_to_cart_button'],
  ['archive', `/?page_id=${ids.SHOP_PAGE_ID}`, '.woocommerce ul.products', 'ul.products a'],
  ['cart', `/?page_id=${ids.CART_PAGE_ID}`, '.woocommerce-cart-form', '.checkout-button'],
  ['checkout', `/?page_id=${ids.CHECKOUT_PAGE_ID}`, 'form.checkout', '#billing_first_name'],
  ['account', `/?page_id=${ids.ACCOUNT_PAGE_ID}`, '.woocommerce', '#username'],
];
```

For each viewport create one browser context, establish the Woo cart session through `/?add-to-cart=<PRODUCT_ID>`, then test all surfaces using the same context so Checkout remains valid.

Overflow gate:

```js
const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
if (overflow > 1) throw new Error(`horizontal overflow: ${overflow}px`);
```

Focus gate: press Tab repeatedly until focus enters `main`; require a nonzero outline or non-`none` box-shadow on the focused element.

Axe gate:

```js
const axe = await new AxeBuilder({ page }).analyze();
const blocking = axe.violations.filter(v => ['critical', 'serious'].includes(v.impact));
if (blocking.length) throw new Error(JSON.stringify(blocking, null, 2));
```

Screenshot:

```js
await page.screenshot({ path: screenshotPath, fullPage: true });
```

- [ ] **Step 2: Run syntax/import-independent static checks**

Run:

```bash
node --check tests/browser/woo-l4-browser.mjs
php tests/offline/woo-l4-browser-workflow-contract.php
```

Expected contract remains RED only because workflow is still absent.

- [ ] **Step 3: Commit browser harness**

```bash
git add tests/browser/woo-l4-browser.mjs
git commit -m "test: add Woo L4 browser harness"
```

---

### Task 3: GitHub Actions L4 runtime

**Files:**
- Create: `.github/workflows/woo-l4-browser.yml`

**Interfaces:**
- Consumes: inherited `tests/runtime/woo-l3-fixtures.php` and L4 browser harness.
- Produces: real Chromium execution and `woo-l4-browser-evidence` artifact.

- [ ] **Step 1: Add isolated WordPress/Woo runtime workflow**

Workflow must:
1. start MySQL 8.0;
2. set up PHP 8.1;
3. run the L4 static contract;
4. install WP-CLI;
5. download WordPress and assert version >= 6.9;
6. install/activate WooCommerce;
7. copy and activate AZnet Theme;
8. execute inherited L3 fixture;
9. save runtime versions;
10. start `wp server` on `127.0.0.1:8080`;
11. install `playwright` and `@axe-core/playwright` ephemerally with `npm install --no-save --package-lock=false`;
12. install Chromium using `npx playwright install chromium --with-deps`;
13. run `node tests/browser/woo-l4-browser.mjs`;
14. upload `/tmp/woo-l4` plus runtime/server evidence using `actions/upload-artifact@v4` even on failure.

- [ ] **Step 2: Run local static verification and verify GREEN**

Run:

```bash
php tests/offline/woo-l4-browser-workflow-contract.php
node --check tests/browser/woo-l4-browser.mjs
bash -n <(sed -n '/run: |/,$p' .github/workflows/woo-l4-browser.yml)
```

Expected: contract PASS and JS syntax PASS. YAML is additionally parsed in the sandbox before push.

- [ ] **Step 3: Commit workflow**

```bash
git add .github/workflows/woo-l4-browser.yml
git commit -m "ci: add Woo L4 browser verification"
```

---

### Task 4: Fresh CI closure and evidence

**Files:**
- Create after successful run: `docs/evidence/WOO_L4_BROWSER_VISUAL_A11Y_VERIFICATION.md`

**Interfaces:**
- Consumes: fresh GitHub Actions run/log/artifact from the exact branch head.
- Produces: L4 PASS evidence only if automated gates and manual screenshot review both pass.

- [ ] **Step 1: Open a draft stacked PR**

Base: `test/woo-l3-runtime`
Head: `test/woo-l4-browser`

The PR must state that it is test-only and not intended for automatic merge.

- [ ] **Step 2: Inspect failed run scientifically if any gate fails**

Read job steps, full logs and artifacts. Fix only the root cause and rerun. Do not weaken assertions to obtain green.

- [ ] **Step 3: On a successful fresh run, verify the full evidence set**

Confirm:
- runtime versions populated;
- 10 screenshots exist;
- 10 axe JSON reports exist;
- browser summary reports 10 successful surface/viewport cases;
- zero critical/serious axe violations;
- screenshots visually show no clipping/overlap/overflow and expected Woo controls remain usable.

- [ ] **Step 4: Save immutable evidence checkpoint**

Record PR, branch/head, workflow run/job, exact runtime versions, artifact ID/SHA if available, automated assertions, screenshot review result, scope and remaining L5/L6 unknowns.

- [ ] **Step 5: Re-run/confirm no production Theme delta**

Compare `test/woo-l3-runtime` -> `test/woo-l4-browser`; only L4 test/docs/evidence paths may differ.

- [ ] **Step 6: Do not merge**

Leave the stacked PR draft unless the owner separately approves retaining L4 infrastructure in another branch/main.
