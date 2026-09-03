# W5 Woo Checkout Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Theme-owned WooCommerce Checkout presentation for classic Checkout and Woo Checkout Block with checkout-only asset loading and no commerce/domain behavior.

**Architecture:** Reuse the existing normalized `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string` classifier. W5 adds one Theme helper for checkout asset eligibility and one CSS file targeting native classic/Block Checkout markup. It does not add template overrides, checkout hooks, field/gateway mutation, JavaScript or order/payment logic.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, WooCommerce public conditionals/native markup/Checkout Block markup, CSS, PHP offline contract tests.

**Spec:** `docs/superpowers/specs/2026-09-03-w5-woo-checkout-shell-design.md`

## Global Constraints

- AZnet Theme owns presentation only.
- WooCommerce owns checkout fields/data, validation, shipping, tax, totals, gateways, payment processing, order creation and endpoint state.
- ConvertFlow is untouched in W5.
- Hooks/CSS/Blocks-first; no `woocommerce/` template override.
- Checkout CSS loads only for normalized Woo surface `checkout`.
- No JavaScript/custom AJAX in W5.
- Do not hide or reorder checkout fields/payment methods.
- Do not infer L3-L6 PASS from local L0-L2 evidence.
- Authoritative AZT source documents remain unchanged for implementation progress.

---

### Task 1: Checkout-only asset eligibility and enqueue

**Files:**
- Create: `inc/theme/woocommerce-checkout.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/w5-checkout-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string`.
- Produces: `AZnet\\Theme\\should_enqueue_woocommerce_checkout_assets(): bool`.

- [ ] **Step 1: Write the failing contract**

Create `tests/offline/w5-checkout-asset-scope-contract.php`:

```php
<?php
namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', 'test');
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri($path) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
}
namespace AZnet\Theme {
    function should_enqueue_generic_content_assets(): bool { return false; }
    function should_enqueue_woocommerce_product_assets(): bool { return false; }
    function should_enqueue_woocommerce_archive_assets(): bool { return false; }
    function should_enqueue_woocommerce_cart_assets(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    $root = dirname(__DIR__, 2);
    if (!is_file($root . '/inc/theme/woocommerce-checkout.php')) { fwrite(STDERR, "missing checkout helper\n"); exit(1); }
    require $root . '/inc/theme/woocommerce-checkout.php';
    require $root . '/inc/theme/assets.php';

    $cases = ['checkout' => true, 'cart' => false, 'product' => false, 'archive' => false, 'account' => false, '' => false];
    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        if (\AZnet\Theme\should_enqueue_woocommerce_checkout_assets() !== $expected) { fwrite(STDERR, "eligibility mismatch: {$surface}\n"); exit(2); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-checkout', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { fwrite(STDERR, "enqueue mismatch: {$surface}\n"); exit(3); }
    }
    echo "PASS: W5 checkout-only asset scope\n";
}
```

- [ ] **Step 2: Run RED**

Run:

```bash
php tests/offline/w5-checkout-asset-scope-contract.php
```

Expected: FAIL with `missing checkout helper` because production helper does not exist.

- [ ] **Step 3: Implement minimal GREEN**

Create `inc/theme/woocommerce-checkout.php`:

```php
<?php
/**
 * WooCommerce Checkout presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned Checkout presentation assets should load.
 */
function should_enqueue_woocommerce_checkout_assets(): bool {
    return 'checkout' === current_surface();
}
```

Add exactly one bootstrap require after `woocommerce-cart.php`:

```php
require_once __DIR__ . '/woocommerce-checkout.php';
```

Append to `enqueue_assets()` after the Cart block:

```php
if ( should_enqueue_woocommerce_checkout_assets() ) {
    wp_enqueue_style(
        'aznet-theme-woocommerce-checkout',
        get_theme_file_uri( '/assets/css/components/woocommerce-checkout.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

- [ ] **Step 4: Run GREEN and lint**

```bash
php tests/offline/w5-checkout-asset-scope-contract.php
php -l inc/theme/woocommerce-checkout.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: contract PASS and all three PHP files report no syntax errors.

---

### Task 2: Classic + Block Checkout responsive CSS

**Files:**
- Create: `assets/css/components/woocommerce-checkout.css`
- Create: `tests/offline/w5-checkout-css-contract.php`

**Interfaces:**
- Consumes: native classic Woo Checkout markup and Woo Checkout Block component classes.
- Produces: presentation-only Checkout CSS.

- [ ] **Step 1: Write failing CSS contract**

Create `tests/offline/w5-checkout-css-contract.php`:

```php
<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-checkout.css';
if (!is_file($path)) { fwrite(STDERR, "missing checkout CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce form.checkout',
    '#customer_details',
    '#order_review_heading',
    '#order_review',
    '#payment',
    '.wc-block-checkout',
    '.wc-block-components-text-input',
    '.wc-block-components-checkout-place-order-button',
    '.wc-block-components-notice-banner',
    'display: grid',
    'grid-template-columns: minmax(0, 1.25fr) minmax(20rem, 0.75fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'overflow-x: scroll', 'visibility: hidden', 'order:'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
echo "PASS: W5 checkout CSS contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/w5-checkout-css-contract.php
```

Expected: FAIL with `missing checkout CSS`.

- [ ] **Step 3: Implement minimal CSS**

Create `assets/css/components/woocommerce-checkout.css` with these exact responsibilities:

- `.woocommerce form.checkout` uses a two-column grid on desktop.
- `#customer_details` occupies the left column; `#order_review_heading` and `#order_review` occupy the right column by grid-column placement only.
- Native fields, review table, `#payment`, notices and Place Order receive token-based border/radius/spacing/control/focus presentation.
- `.wc-block-checkout` and the required Block component selectors receive token-based presentation without replacing Block business behavior.
- At `max-width: 767px`, classic checkout becomes one column and the native regions use `grid-column: 1`.
- Controls use `max-width: 100%`; no forced horizontal scroll, sticky CTA, hiding or `order:` reordering.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/w5-checkout-css-contract.php
```

Expected: PASS.

---

### Task 3: Ownership gate, affected regression and closure verifier

**Files:**
- Create: `tests/offline/w5-checkout-ownership-static-contract.php`
- Modify only if reproduced dependency drift requires it: `tests/offline/w4-cart-asset-scope-contract.php`
- Create: `scripts/verify-w5.sh`
- Create after fresh verification: `docs/evidence/W5_WOO_CHECKOUT_SHELL_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes W5 production paths plus affected W4 asset/ownership contracts.
- Produces a local L0-L2 verification checkpoint only.

- [ ] **Step 1: Add W5 ownership/static gate**

The gate must fail if a repository-level `woocommerce/` override directory exists or if W5 production paths contain any of:

```text
get_option(
get_post_meta(
$wpdb
Automattic\WooCommerce\Internal
WC()->cart
WC_Order
wc_create_order
calculate_totals
set_total
set_payment_method
woocommerce_checkout_fields
woocommerce_default_address_fields
woocommerce_available_payment_gateways
woocommerce_checkout_process
woocommerce_checkout_create_order
wp_ajax_
admin-ajax.php
fetch(
XMLHttpRequest
choiceguide_
convertflow
position: sticky
```

It must also reject quoted `_woocommerce_*` storage-key literals, W5 JavaScript files, more than the two pre-existing bootstrap `add_action(` registrations, any bootstrap `add_filter(`, any wiring of `render_current_rootprofile_surface`, and a missing/duplicate checkout helper require.

- [ ] **Step 2: Reproduce affected retained W4 asset harness**

Run the W4 asset contract against the W5 production `assets.php` before changing the W4 test harness.

Expected if dependency drift occurs: undefined `should_enqueue_woocommerce_checkout_assets()` because the retained harness directly includes `assets.php` without loading the new helper, while production bootstrap does load it first.

Only after observing that exact failure, add exactly one dependency include for `woocommerce-checkout.php` in `tests/offline/w4-cart-asset-scope-contract.php` before `assets.php`. Do not add production `function_exists()` workarounds.

- [ ] **Step 3: Add focused verifier**

Create `scripts/verify-w5.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

php tests/offline/w5-checkout-asset-scope-contract.php
php tests/offline/w5-checkout-css-contract.php
php tests/offline/w5-checkout-ownership-static-contract.php

echo 'PASS: W5 offline contracts'

php tests/offline/w4-cart-asset-scope-contract.php
php tests/offline/w4-cart-ownership-static-contract.php

echo 'PASS: retained W4 invalidated regression subset'

php -l inc/theme/woocommerce-checkout.php >/dev/null
php -l inc/theme/assets.php >/dev/null
php -l inc/theme/bootstrap.php >/dev/null

echo 'PASS: W5 changed/new production PHP lint 3/3'
```

- [ ] **Step 4: Run fresh verifier**

```bash
bash scripts/verify-w5.sh
```

Expected output contains all three W5 PASS lines, both affected W4 PASS lines and `PASS: W5 changed/new production PHP lint 3/3` with exit code 0.

- [ ] **Step 5: Verify exact delta against W4**

Expected production delta is exactly:

```text
ADD assets/css/components/woocommerce-checkout.css
ADD inc/theme/woocommerce-checkout.php
MODIFY inc/theme/assets.php +9/-0
MODIFY inc/theme/bootstrap.php +1/-0
```

The only retained earlier test allowed to change is `tests/offline/w4-cart-asset-scope-contract.php` by one dependency include if the drift was reproduced.

- [ ] **Step 6: Record evidence and open stacked PR**

Write `docs/evidence/W5_WOO_CHECKOUT_SHELL_LOCAL_VERIFICATION.md` with RED/GREEN evidence, any harness-drift root cause, fresh verifier output, exact production delta and explicit L3-L6 NOT PROVEN statements.

Open a stacked PR from `work/w5-woo-checkout-shell` to `work/w4-woo-cart-shell`. Do not merge automatically.

## Self-review

- Spec coverage: asset scope, classic Checkout, Checkout Block, responsive, accessibility intent, ownership and rollback are each mapped to a task.
- No placeholder/TBD steps remain.
- Function names are consistent: `should_enqueue_woocommerce_checkout_assets()` and handle `aznet-theme-woocommerce-checkout`.
- No authoritative AZT source-document update is part of this plan.
