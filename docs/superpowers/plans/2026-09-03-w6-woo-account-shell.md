# W6 Woo My Account Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Theme-owned WooCommerce My Account presentation using native Woo account markup and account-only CSS loading.

**Architecture:** Reuse W1 `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string` as the only request classifier. W6 adds one Theme helper for account asset eligibility and one CSS file scoped to native Woo My Account markup. It does not create endpoints, auth/account logic, JavaScript or template overrides.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, WooCommerce public conditionals/native markup, CSS, PHP offline contract tests.

**Spec:** `docs/superpowers/specs/2026-09-03-w6-woo-account-shell-design.md`

## Global Constraints

- AZnet Theme owns presentation only; WooCommerce owns account/auth/order/download/address/payment-method data and endpoint routing.
- ConvertFlow is untouched.
- Hooks/CSS/Blocks-first; no `woocommerce/` template override.
- Account CSS loads only when normalized Woo surface is `account`.
- No JavaScript in W6.
- Do not update authoritative AZT source docs for implementation progress.
- Do not infer L3-L6 PASS from local L0-L2 evidence.

---

### Task 1: Account-only asset eligibility and enqueue

**Files:**
- Create: `inc/theme/woocommerce-account.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/w6-account-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string`.
- Produces: `AZnet\\Theme\\should_enqueue_woocommerce_account_assets(): bool`.

- [ ] **Step 1: Write failing contract**

Create `tests/offline/w6-account-asset-scope-contract.php` with stubs for Theme enqueues and Woo surface classification. It must first fail with `missing account helper`, then test `account => true` and `checkout/cart/product/archive/null => false`, and verify the `aznet-theme-woocommerce-account` handle is loaded only for `account`.

- [ ] **Step 2: Run RED**

Run:

```bash
php tests/offline/w6-account-asset-scope-contract.php
```

Expected: FAIL with `missing account helper`.

- [ ] **Step 3: Implement minimal GREEN**

Create `inc/theme/woocommerce-account.php`:

```php
<?php
/**
 * WooCommerce My Account presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned My Account presentation assets should load.
 */
function should_enqueue_woocommerce_account_assets(): bool {
    return 'account' === current_surface();
}
```

Add exactly one `require_once __DIR__ . '/woocommerce-account.php';` in `inc/theme/bootstrap.php` after the Checkout helper and before `content-shell.php`.

Append to `enqueue_assets()` after the Checkout block:

```php
if ( should_enqueue_woocommerce_account_assets() ) {
    wp_enqueue_style(
        'aznet-theme-woocommerce-account',
        get_theme_file_uri( '/assets/css/components/woocommerce-account.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

- [ ] **Step 4: Run GREEN and lint**

```bash
php tests/offline/w6-account-asset-scope-contract.php
php -l inc/theme/woocommerce-account.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: contract PASS and no syntax errors.

- [ ] **Step 5: Commit Task 1**

Commit only the helper, enqueue/bootstrap changes and asset contract.

---

### Task 2: Native Woo My Account responsive CSS

**Files:**
- Create: `assets/css/components/woocommerce-account.css`
- Create: `tests/offline/w6-account-css-contract.php`

**Interfaces:**
- Consumes native Woo My Account classes and Theme CSS tokens.
- Produces presentation-only account layout CSS.

- [ ] **Step 1: Write failing CSS contract**

The contract must require these capabilities:

```php
$required = [
    '.woocommerce-account',
    '.woocommerce-MyAccount-navigation',
    '.woocommerce-MyAccount-content',
    '.woocommerce-orders-table',
    '.woocommerce-Addresses',
    '.woocommerce-EditAccountForm',
    'display: grid',
    'grid-template-columns: minmax(12rem, 0.32fr) minmax(0, 0.68fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    ':focus-visible',
    '--aznet-theme-',
];
```

Reject `display: none !important`, `position: sticky`, `overflow-x: scroll`, `visibility: hidden` and JavaScript-dependent classes invented by the Theme.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/w6-account-css-contract.php
```

Expected: FAIL because `woocommerce-account.css` does not exist.

- [ ] **Step 3: Implement minimal CSS**

Create `assets/css/components/woocommerce-account.css` using native selectors only. Desktop uses a two-column navigation/content grid. Mobile at 767px and below uses one column, navigation before content, token-based spacing/borders/radius/focus, `max-width:100%` for controls/tables, and no forced horizontal scrolling introduced by the Theme.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/w6-account-css-contract.php
```

Expected: PASS.

- [ ] **Step 5: Commit Task 2**

Commit only the stylesheet and CSS contract.

---

### Task 3: Ownership gate, retained regression and closure evidence

**Files:**
- Create: `tests/offline/w6-account-ownership-static-contract.php`
- Create: `scripts/verify-w6.sh`
- Modify only if required by dependency drift: `tests/offline/w5-checkout-asset-scope-contract.php`
- Create after verification: `docs/evidence/W6_WOO_ACCOUNT_SHELL_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes W6 production paths and the affected W5 asset/ownership contracts.
- Produces W6 local L0-L2 evidence only.

- [ ] **Step 1: Add ownership/static contract**

Reject all of the following from W6 production paths:

```php
$forbidden = [
    'get_option(',
    'get_user_meta(',
    'get_post_meta(',
    '$wpdb',
    'WP_Query',
    'wc_get_orders(',
    'WC_Order',
    'wp_signon(',
    'wp_logout(',
    'wp_set_auth_cookie(',
    'add_rewrite_endpoint(',
    'woocommerce_account_menu_items',
    'woocommerce_get_endpoint_url',
    'woocommerce_save_account_details',
    'Automattic\\WooCommerce\\Internal',
    'choiceguide_',
    'convertflow',
];
```

Also fail if a root `woocommerce/` override directory or W6 JavaScript asset exists. Verify bootstrap loads integration/product/archive/cart/checkout/account/rootprofile dispatcher helpers exactly once, keeps only the two existing `add_action()` registrations, adds no filters and does not wire the dormant RootProfile renderer.

- [ ] **Step 2: Reproduce affected retained W5 regression**

Run W5 checkout asset + ownership contracts against W6 code. If the asset harness fails because `assets.php` now calls the new account helper, confirm production bootstrap already loads `woocommerce-account.php` before `assets.php`, then add exactly one account-helper `require` to the retained W5 asset harness. Do not add a production workaround.

- [ ] **Step 3: Create focused verifier**

`scripts/verify-w6.sh` runs, in order:

```bash
php tests/offline/w6-account-asset-scope-contract.php
php tests/offline/w6-account-css-contract.php
php tests/offline/w6-account-ownership-static-contract.php
php tests/offline/w5-checkout-asset-scope-contract.php
php tests/offline/w5-checkout-ownership-static-contract.php
php -l inc/theme/woocommerce-account.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

It prints explicit PASS markers for W6 contracts, affected W5 regression subset and changed/new production PHP lint 3/3.

- [ ] **Step 4: Run fresh verifier and inspect W5→W6 diff**

Expected production delta versus W5 is exactly:

```text
ADD assets/css/components/woocommerce-account.css
ADD inc/theme/woocommerce-account.php
MODIFY inc/theme/assets.php +9/-0
MODIFY inc/theme/bootstrap.php +1/-0
```

The only retained earlier test allowed to change is the W5 asset harness by +1 require if dependency drift is reproduced.

- [ ] **Step 5: Record evidence**

Create `docs/evidence/W6_WOO_ACCOUNT_SHELL_LOCAL_VERIFICATION.md` with RED/GREEN evidence, regression/debugging evidence, fresh verifier output, exact production delta, PASS by L0/L1/L2 and explicit L3-L6 unknowns. State that authoritative AZT source docs were not revised for implementation progress.

- [ ] **Step 6: Open stacked PR**

Open W6 against `work/w5-woo-checkout-shell`. Do not merge automatically.
