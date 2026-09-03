# W4 Woo Cart Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Theme-owned WooCommerce Cart presentation for classic Cart and Cart Block with cart-only CSS loading and no commerce/domain behavior.

**Architecture:** Reuse W1 `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string` as the only request classifier. W4 adds one Theme helper returning true only for `cart`, one cart-only enqueue block, and one CSS file targeting native classic/Block markup. No template override, JavaScript, cart mutation or price calculation is introduced.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, WooCommerce public conditionals/native markup/Blocks classes, CSS, PHP offline contract tests.

**Spec:** `docs/superpowers/specs/2026-09-03-w4-woo-cart-shell-design.md`

## Global Constraints

- AZnet Theme owns presentation only; WooCommerce owns line items, quantity, coupon validity, price/subtotal/total, stock/cart mutations and checkout transition.
- ConvertFlow owns Product Journey/Filter/Fit/Fast Conversion; W4 does not integrate it.
- Hooks/CSS/Blocks-first; no `woocommerce/` template override.
- Cart CSS loads only when normalized Woo surface is `cart`.
- No JavaScript or custom AJAX in W4.
- Preserve native table/block semantics and controls.
- Do not infer L3-L6 PASS from local L0-L2 evidence.

---

### Task 1: Cart-only asset eligibility and enqueue

**Files:**
- Create: `inc/theme/woocommerce-cart.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/w4-cart-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string`.
- Produces: `AZnet\\Theme\\should_enqueue_woocommerce_cart_assets(): bool`.

- [ ] **Step 1: Write the failing contract**

Create `tests/offline/w4-cart-asset-scope-contract.php`:

```php
<?php
namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', 'test');
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri($path) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
    function should_enqueue_generic_content_assets(): bool { return false; }
    function should_enqueue_woocommerce_product_assets(): bool { return false; }
    function should_enqueue_woocommerce_archive_assets(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    $root = dirname(__DIR__, 2);
    if (!is_file($root . '/inc/theme/woocommerce-cart.php')) { fwrite(STDERR, "missing cart helper\n"); exit(1); }
    require $root . '/inc/theme/woocommerce-cart.php';
    require $root . '/inc/theme/assets.php';

    $cases = ['cart' => true, 'product' => false, 'archive' => false, 'checkout' => false, 'account' => false, '' => false];
    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        if (\\AZnet\\Theme\\should_enqueue_woocommerce_cart_assets() !== $expected) { exit(2); }
        \\AZnet\\Theme\\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-cart', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { exit(3); }
    }
    echo "PASS: W4 cart-only asset scope\n";
}
```

- [ ] **Step 2: Run RED**

Run:

```bash
php tests/offline/w4-cart-asset-scope-contract.php
```

Expected: FAIL with `missing cart helper`.

- [ ] **Step 3: Implement minimal GREEN**

Create `inc/theme/woocommerce-cart.php`:

```php
<?php
/**
 * WooCommerce Cart presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function should_enqueue_woocommerce_cart_assets(): bool {
    return 'cart' === current_surface();
}
```

Add exactly one `require_once __DIR__ . '/woocommerce-cart.php';` after the archive helper in `inc/theme/bootstrap.php`.

Append one enqueue block after the Archive block in `enqueue_assets()`:

```php
if ( should_enqueue_woocommerce_cart_assets() ) {
    wp_enqueue_style(
        'aznet-theme-woocommerce-cart',
        get_theme_file_uri( '/assets/css/components/woocommerce-cart.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

- [ ] **Step 4: Run GREEN and lint**

```bash
php tests/offline/w4-cart-asset-scope-contract.php
php -l inc/theme/woocommerce-cart.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: contract PASS and no syntax errors.

- [ ] **Step 5: Commit Task 1**

Commit only the four Task 1 paths.

---

### Task 2: Classic + Block Cart responsive CSS

**Files:**
- Create: `assets/css/components/woocommerce-cart.css`
- Create: `tests/offline/w4-cart-css-contract.php`

**Interfaces:**
- Consumes native classic Woo Cart and Woo Cart Block selectors plus Theme tokens.
- Produces presentation-only Cart CSS.

- [ ] **Step 1: Write failing CSS contract**

Require these literals:

```php
$required = [
    '.woocommerce-cart-form',
    '.shop_table',
    '.cart_totals',
    '.coupon',
    '.checkout-button',
    '.wp-block-woocommerce-cart',
    '.wc-block-cart-items',
    '.wc-block-components-quantity-selector',
    '.wc-block-cart__submit-button',
    '@media (max-width: 767px)',
    ':focus-visible',
    '--aznet-theme-',
];
```

Reject `display: none !important`, `position: sticky`, `white-space: nowrap` and `overflow-x: auto`.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/w4-cart-css-contract.php
```

Expected: FAIL because `woocommerce-cart.css` does not exist.

- [ ] **Step 3: Implement minimal CSS**

Use Theme tokens to style classic cart form/table/totals/coupon/CTA and Cart Block containers/quantity/submit button. Preserve native semantics. Mobile rules must let controls wrap/stack and avoid forced horizontal scrolling without converting tables into arbitrary block layout.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/w4-cart-css-contract.php
```

Expected: PASS.

- [ ] **Step 5: Commit Task 2**

Commit CSS + CSS contract only.

---

### Task 3: Ownership / no-commerce-behavior gate and focused verifier

**Files:**
- Create: `tests/offline/w4-cart-ownership-static-contract.php`
- Create: `scripts/verify-w4.sh`
- Create: `docs/evidence/W4_WOO_CART_SHELL_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes W4 production paths and affected W3/W2 bootstrap/assets invariants.
- Produces repeatable local L0-L2 verification.

- [ ] **Step 1: Add ownership gate**

Scan W4 production paths and fail on: `get_option(`, `get_post_meta(`, `$wpdb`, `Automattic\\WooCommerce\\Internal`, `WC()->cart`, `calculate_totals`, `set_quantity`, `apply_coupon`, `remove_coupon`, `wp_ajax_`, `admin-ajax.php`, `fetch(`, `XMLHttpRequest`, `choiceguide_`, `convertflow`, `position: sticky`, quoted `_woocommerce_*` storage keys, any W4 JavaScript asset, or repository `woocommerce/` override directory.

Also assert bootstrap loads Woo integration/product/archive/cart helpers exactly once, retains exactly two `add_action(` registrations, no `add_filter(` and no RootProfile takeover wiring.

- [ ] **Step 2: Create focused verifier**

`scripts/verify-w4.sh` runs:

```bash
php tests/offline/w4-cart-asset-scope-contract.php
php tests/offline/w4-cart-css-contract.php
php tests/offline/w4-cart-ownership-static-contract.php
php tests/offline/w3-archive-asset-scope-contract.php
php tests/offline/w3-archive-ownership-static-contract.php
php -l inc/theme/woocommerce-cart.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

If W3 asset harness requires the new cart helper because it directly loads `assets.php`, add only that dependency include to the retained test harness. Do not add production defensive code for a test-only include-order issue.

- [ ] **Step 3: Run fresh verifier**

```bash
bash scripts/verify-w4.sh
```

Expected: W4 3/3 PASS, invalidated W3 asset/ownership subset PASS, changed/new PHP lint 3/3 PASS.

- [ ] **Step 4: Verify delta**

Compare W3 base to W4 branch. Production delta must be exactly:
- ADD `assets/css/components/woocommerce-cart.css`
- ADD `inc/theme/woocommerce-cart.php`
- MODIFY `inc/theme/assets.php` with one enqueue block only
- MODIFY `inc/theme/bootstrap.php` with one require only

Only directly invalidated retained test harnesses may change outside W4 tests/docs/scripts.

- [ ] **Step 5: Record evidence and open stacked PR**

Record RED/GREEN, verifier output, exact base/head, delta and L0-L2-only claim in `docs/evidence/W4_WOO_CART_SHELL_LOCAL_VERIFICATION.md`. Open W4 PR against `work/w3-woo-archive-shell`. Do not merge automatically.
