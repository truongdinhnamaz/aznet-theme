# W1 Woo Capability and Surface-Aware Asset Boundary Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a fail-soft WooCommerce capability/surface adapter and stop generic-content CSS from loading on recognized Woo surfaces.

**Architecture:** Keep Woo detection in `inc/integrations/woocommerce.php`, using only public Woo runtime capability and conditional tags. Theme presentation code consumes the normalized surface classifier; W1 changes only asset eligibility and does not introduce Woo templates or commerce logic.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, WooCommerce public PHP APIs/conditional tags, offline PHP contract harnesses.

**Spec:** `docs/superpowers/specs/2026-09-03-woo-override-policy-design.md`

## Global Constraints

- AZnet Theme owns presentation only; WooCommerce owns commerce truth/state.
- ConvertFlow Product Journey/Filter/Fit/Fast Conversion remains external and unchanged.
- Public APIs only; no Woo option/meta/table/private internals.
- Fail soft when WooCommerce or conditional functions are absent.
- No `woocommerce/` template override in W1.
- No new global frontend bundle or Woo asset in W1; W1 narrows existing generic-content asset eligibility.
- Internal Theme version remains `0.1.0-alpha.7`; version promotion belongs to release closure.

---

### Task 1: Woo capability and normalized surface classifier

**Files:**
- Create: `inc/integrations/woocommerce.php`
- Modify: `inc/theme/bootstrap.php`
- Test: `tests/offline/w1-woocommerce-absent-contract.php`
- Test: `tests/offline/w1-woocommerce-surface-contract.php`

**Interfaces:**
- Produces: `AZnet\Theme\Integrations\WooCommerce\available(): bool`
- Produces: `AZnet\Theme\Integrations\WooCommerce\current_surface(): ?string`
- Surface values: `product|archive|cart|checkout|account|null`

- [ ] **Step 1: Write the absent-provider RED contract**

Create a harness that defines only `ABSPATH`, requires `inc/integrations/woocommerce.php`, then asserts `available() === false` and `current_surface() === null`. Before the production file exists, expected RED is `integration module does not exist`.

- [ ] **Step 2: Write the present-provider RED contract**

Define a public `WC()` test stub and Woo conditional-tag stubs backed by a mutable global state. Assert each allowed state resolves to its normalized surface and an all-false state resolves to `null`. Before implementation, expected RED is missing integration module/functions.

- [ ] **Step 3: Run both contracts and confirm RED**

Run:
```bash
php tests/offline/w1-woocommerce-absent-contract.php
php tests/offline/w1-woocommerce-surface-contract.php
```
Expected: both fail because the Woo integration module/API does not exist.

- [ ] **Step 4: Implement minimal public-capability adapter**

Create `inc/integrations/woocommerce.php` with `available()` based on global `WC()` existence and `current_surface()` that checks only public conditional functions. Every conditional function must be guarded with `function_exists()`; absent Woo or unavailable tags return `null`.

Modify `inc/theme/bootstrap.php` to require the new integration module exactly once; do not add a new WordPress action/filter.

- [ ] **Step 5: Re-run Task 1 contracts**

Expected: both PASS.

- [ ] **Step 6: Run PHP lint for changed PHP files**

Run:
```bash
php -l inc/integrations/woocommerce.php
php -l inc/theme/bootstrap.php
```
Expected: no syntax errors.

### Task 2: Surface-aware generic-content asset eligibility

**Files:**
- Modify: `inc/theme/content-shell.php`
- Test: `tests/offline/w1-woocommerce-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\Theme\Integrations\WooCommerce\current_surface(): ?string`
- Preserves: `AZnet\Theme\should_enqueue_generic_content_assets(): bool`

- [ ] **Step 1: Write RED asset-scope contract**

Create a harness with Woo and WordPress conditional-tag stubs. Assert a normal WordPress Page remains eligible for generic-content CSS, while Product, Shop/Product taxonomy, Cart, Checkout and Account surfaces are ineligible even if their underlying WordPress condition would otherwise make the generic asset eligible.

- [ ] **Step 2: Run asset-scope contract and confirm RED**

Run:
```bash
php tests/offline/w1-woocommerce-asset-scope-contract.php
```
Expected: at least the cart/checkout/account cases fail because current generic asset eligibility sees them as WordPress Pages.

- [ ] **Step 3: Implement minimal GREEN**

At the beginning of `should_enqueue_generic_content_assets()`, return `false` when `AZnet\Theme\Integrations\WooCommerce\current_surface()` is non-null. Preserve the pre-existing generic Page/Post/Archive/Search/404 expression unchanged for non-Woo requests.

- [ ] **Step 4: Re-run the asset-scope contract**

Expected: PASS.

### Task 3: W1 ownership/regression closure

**Files:**
- Create: `tests/offline/w1-woocommerce-ownership-static-contract.php`
- Create: `scripts/verify-w1.sh`
- Create: `docs/evidence/W1_WOOCOMMERCE_CAPABILITY_ASSET_SCOPE.md`

**Interfaces:**
- Verifies Tasks 1-2 without adding production behavior.

- [ ] **Step 1: Add static ownership gate**

Scan W1 production paths for forbidden direct storage/private patterns (`get_option(`, `get_post_meta(`, `$wpdb`, `_woocommerce_`, `Automattic\WooCommerce\Internal\`) and assert no `woocommerce/` template directory exists. Assert `bootstrap.php` still has exactly its two pre-existing `add_action()` registrations.

- [ ] **Step 2: Add repeatable verifier**

`scripts/verify-w1.sh` runs all W1 contracts, retained `scripts/verify-e5b.sh`, and lints every production PHP file outside `tests/` and `docs/`.

- [ ] **Step 3: Run full W1 verifier**

Run:
```bash
bash scripts/verify-w1.sh
```
Expected: W1 contracts PASS; retained E5-B contracts PASS; production PHP lint PASS.

- [ ] **Step 4: Record evidence**

Evidence must state W1 PASS only at local L0-L2, list changed production paths, preserve Woo/ConvertFlow ownership, and explicitly mark Woo runtime/browser/integration/release as not yet proven.

- [ ] **Step 5: Commit W1 branch checkpoint**

Commit only the bounded W1 code/tests/verifier/spec/plan/evidence to `work/w1-woo-capability-assets`. Do not merge `main` without owner approval.
