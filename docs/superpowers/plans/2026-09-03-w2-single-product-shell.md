# W2 Single Product Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a Theme-owned, product-only WooCommerce presentation stylesheet and layout shell without changing WooCommerce commerce behavior or templates.

**Architecture:** Reuse W1 `AZnet\Theme\Integrations\WooCommerce\current_surface()` as the only request classifier. W2 adds a Theme presentation helper and conditionally enqueues one CSS file on `product` only; the CSS styles native WooCommerce markup. No `woocommerce/` template override and no JS are added.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, WooCommerce public conditionals/native markup, CSS, shell/PHP offline contract tests.

**Spec:** `docs/superpowers/specs/2026-09-03-w2-single-product-shell-design.md`

## Global Constraints

- AZnet Theme is presentation owner only; WooCommerce remains owner of product/price/stock/variation/cart/checkout/order/account state.
- ConvertFlow remains owner of Product Journey/Filter/Fit/Fast Conversion; W2 does not integrate ConvertFlow.
- Hooks/CSS/Blocks-first; no `woocommerce/` template override in W2.
- Surface-aware asset loading; product CSS must load only when W1 normalized surface is `product`.
- PHP namespace/prefix family remains `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-`, `--aznet-theme-*`.
- Do not infer L3-L6 PASS from W2 local L0-L2 tests.

---

### Task 1: Product-only asset eligibility and enqueue

**Files:**
- Create: `inc/theme/woocommerce-product.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/w2-product-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\Theme\Integrations\WooCommerce\current_surface(): ?string` from W1.
- Produces: `AZnet\Theme\should_enqueue_woocommerce_product_assets(): bool`.

- [ ] **Step 1: Write the failing contract**

Create a PHP harness that stubs WordPress enqueue functions and controls the W1 surface. The assertions must prove `product => true + enqueue`, while `archive`, `cart`, `checkout`, `account`, and `null` do not enqueue `aznet-theme-woocommerce-product`.

```php
<?php
namespace {
    define( 'ABSPATH', __DIR__ );
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = null ) {
        $GLOBALS['aznet_test_styles'][] = $handle;
    }
    function get_theme_file_uri( $path ) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
    function is_page() { return false; }
    function is_singular() { return false; }
    function is_archive() { return false; }
    function is_search() { return false; }
    function is_404() { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_test_woo_surface'] ?? null; }
}
namespace {
    require __DIR__ . '/../../inc/theme/content-shell.php';
    require __DIR__ . '/../../inc/theme/woocommerce-product.php';
    require __DIR__ . '/../../inc/theme/assets.php';

    $cases = [
        'product' => true,
        'archive' => false,
        'cart' => false,
        'checkout' => false,
        'account' => false,
        null => false,
    ];

    foreach ( $cases as $surface => $expected ) {
        $GLOBALS['aznet_test_woo_surface'] = $surface === '' ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        $actual = \AZnet\Theme\should_enqueue_woocommerce_product_assets();
        if ( $actual !== $expected ) { exit( 1 ); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array( 'aznet-theme-woocommerce-product', $GLOBALS['aznet_test_styles'], true );
        if ( $loaded !== $expected ) { exit( 2 ); }
    }
    echo "PASS: W2 product-only asset scope\n";
}
```

- [ ] **Step 2: Run RED**

Run:

```bash
php tests/offline/w2-product-asset-scope-contract.php
```

Expected: FAIL because `inc/theme/woocommerce-product.php` / `should_enqueue_woocommerce_product_assets()` does not exist.

- [ ] **Step 3: Implement minimal GREEN**

`inc/theme/woocommerce-product.php`:

```php
<?php
namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function should_enqueue_woocommerce_product_assets(): bool {
    return 'product' === current_surface();
}
```

Add one `require_once` for this module in `inc/theme/bootstrap.php` after the Woo integration adapter is available.

In `inc/theme/assets.php`, add after the generic-content block:

```php
if ( should_enqueue_woocommerce_product_assets() ) {
    wp_enqueue_style(
        'aznet-theme-woocommerce-product',
        get_theme_file_uri( '/assets/css/components/woocommerce-product.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

- [ ] **Step 4: Run GREEN and PHP lint**

```bash
php tests/offline/w2-product-asset-scope-contract.php
php -l inc/theme/woocommerce-product.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: contract PASS; all lint commands report no syntax errors.

- [ ] **Step 5: Commit Task 1**

```bash
git add inc/theme/woocommerce-product.php inc/theme/bootstrap.php inc/theme/assets.php tests/offline/w2-product-asset-scope-contract.php
git commit -m "feat: add product-only Woo presentation asset gate"
```

---

### Task 2: Native Woo Single Product responsive CSS

**Files:**
- Create: `assets/css/components/woocommerce-product.css`
- Create: `tests/offline/w2-product-css-contract.php`

**Interfaces:**
- Consumes: native Woo Single Product classes (`.single-product`, `.product`, `.woocommerce-product-gallery`, `.summary`, `.woocommerce-tabs`) and Theme CSS tokens.
- Produces: presentation-only desktop/mobile CSS; no PHP/domain interface.

- [ ] **Step 1: Write the failing CSS contract**

```php
<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-product.css';
if ( ! is_file( $path ) ) { fwrite( STDERR, "missing product CSS\n" ); exit( 1 ); }
$css = file_get_contents( $path );
$required = [
    '.single-product',
    '.woocommerce-product-gallery',
    '.summary',
    '.woocommerce-tabs',
    'display: grid',
    'minmax(0, 1.25fr)',
    'minmax(0, 1fr)',
    '@media (max-width: 767px)',
    'grid-template-columns: 1fr',
    '--aznet-theme-',
    ':focus-visible',
];
foreach ( $required as $needle ) {
    if ( false === strpos( $css, $needle ) ) { fwrite( STDERR, "missing: {$needle}\n" ); exit( 2 ); }
}
$forbidden = [ 'position: sticky', 'display: none !important' ];
foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $css, $needle ) ) { fwrite( STDERR, "forbidden: {$needle}\n" ); exit( 3 ); }
}
echo "PASS: W2 product CSS contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/w2-product-css-contract.php
```

Expected: FAIL because product CSS does not exist.

- [ ] **Step 3: Implement minimal CSS**

Create `assets/css/components/woocommerce-product.css` with scoped native Woo rules. The primary product region must use a two-column grid on desktop and one column at `max-width: 767px`; gallery/summary children must use `min-width: 0`; tabs must span the full grid width; controls and links get visible `:focus-visible` treatment from Theme tokens. Do not hide Woo commerce information, add sticky CTA, or add animation-dependent behavior.

Core layout must include:

```css
.single-product .site-main .product {
    display: grid;
    grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
    gap: var(--aznet-theme-space-8, 2rem);
    align-items: start;
}

.single-product .site-main .woocommerce-product-gallery,
.single-product .site-main .summary {
    min-width: 0;
    width: 100%;
}

.single-product .site-main .woocommerce-tabs,
.single-product .site-main .related,
.single-product .site-main .upsells {
    grid-column: 1 / -1;
}

@media (max-width: 767px) {
    .single-product .site-main .product {
        grid-template-columns: 1fr;
        gap: var(--aznet-theme-space-6, 1.5rem);
    }
}
```

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/w2-product-css-contract.php
```

Expected: PASS.

- [ ] **Step 5: Commit Task 2**

```bash
git add assets/css/components/woocommerce-product.css tests/offline/w2-product-css-contract.php
git commit -m "feat: style native Woo single product shell"
```

---

### Task 3: Ownership gate, retained regressions, repeatable verifier and evidence

**Files:**
- Create: `tests/offline/w2-product-ownership-static-contract.php`
- Create: `scripts/verify-w2.sh`
- Create: `docs/evidence/W2_SINGLE_PRODUCT_SHELL_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes: W1 verifier and E5-B verifier.
- Produces: repeatable W2 local L0-L2 closure evidence.

- [ ] **Step 1: Add ownership/static contract**

The contract must fail if W2 introduces any `woocommerce/` directory/file override, direct `get_option(` / `get_post_meta(` / `$wpdb` access in W2 production paths, `_woocommerce_` storage-key literals, `Automattic\\WooCommerce\\Internal` private namespace references, sticky Add to Cart behavior, or ConvertFlow-specific code.

- [ ] **Step 2: Create repeatable verifier**

`scripts/verify-w2.sh` must run, in order:

```bash
php tests/offline/w2-product-asset-scope-contract.php
php tests/offline/w2-product-css-contract.php
php tests/offline/w2-product-ownership-static-contract.php
bash scripts/verify-w1.sh
bash scripts/verify-e5b.sh
find . -path './tests' -prune -o -path './docs' -prune -o -name '*.php' -print0 | xargs -0 -n1 php -l
```

The script exits nonzero on any failure.

- [ ] **Step 3: Run fresh full W2 verifier**

```bash
bash scripts/verify-w2.sh
```

Expected: all 3 W2 contracts PASS, retained W1 PASS, retained E5-B PASS, production PHP lint PASS.

- [ ] **Step 4: Write evidence from actual output**

Record branch/head, base W1 head, RED causes, GREEN scope, exact production delta, verifier output counts, ownership/no-template-override result, and explicit `NOT PROVEN` for L3 runtime, L4 browser/a11y, L5 integration and L6 release.

- [ ] **Step 5: Commit closure evidence**

```bash
git add tests/offline/w2-product-ownership-static-contract.php scripts/verify-w2.sh docs/evidence/W2_SINGLE_PRODUCT_SHELL_LOCAL_VERIFICATION.md
git commit -m "test: close W2 single product local gate"
```

## Self-Review

- Spec coverage: product-only asset gate, native Woo markup styling, desktop/mobile layout, accessibility focus state, ownership boundary, no override and retained regressions are all represented.
- Placeholder scan: no deferred implementation placeholders are present.
- Type/interface consistency: W2 uses only W1 `current_surface(): ?string` and produces one Theme bool helper.
- Scope: W2 does not include runtime/browser, ConvertFlow coexistence, Cart/Checkout/Account/Archive or release closure.
