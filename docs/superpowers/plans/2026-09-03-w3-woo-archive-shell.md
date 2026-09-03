# W3 Woo Archive Shell Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add Theme-owned Shop/Product Archive/Category/Tag presentation using native WooCommerce archive markup and archive-only CSS loading.

**Architecture:** Reuse W1 `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string` as the only request classifier. W3 adds one Theme helper for archive asset eligibility and one CSS file scoped to native Woo archive markup. It does not create a template override, custom query, filter engine or JavaScript.

**Tech Stack:** PHP 8.1+, WordPress 6.9+, WooCommerce public conditionals/native markup, CSS, PHP offline contract tests.

**Spec:** `docs/superpowers/specs/2026-09-03-w3-woo-archive-shell-design.md`

## Global Constraints

- AZnet Theme owns presentation only; WooCommerce owns query/catalogue/taxonomy/ordering/product/price/stock/cart facts.
- ConvertFlow owns Product Journey/Filter/Fit/Fast Conversion; W3 does not integrate it.
- Hooks/CSS/Blocks-first; no `woocommerce/` template override.
- Archive CSS loads only when the normalized Woo surface is `archive`.
- No JavaScript in W3.
- Do not infer L3-L6 PASS from W3 local L0-L2 evidence.

---

### Task 1: Archive-only asset eligibility and enqueue

**Files:**
- Create: `inc/theme/woocommerce-archive.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/w3-archive-asset-scope-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Integrations\\WooCommerce\\current_surface(): ?string`.
- Produces: `AZnet\\Theme\\should_enqueue_woocommerce_archive_assets(): bool`.

- [ ] **Step 1: Write the failing contract**

Create `tests/offline/w3-archive-asset-scope-contract.php`:

```php
<?php
namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', 'test');
    $GLOBALS['aznet_test_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null) { $GLOBALS['aznet_test_styles'][] = $handle; }
    function get_theme_file_uri($path) { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri() { return 'https://example.test/theme/style.css'; }
    function is_page() { return false; }
    function is_singular($type = '') { return false; }
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
    require __DIR__ . '/../../inc/theme/woocommerce-archive.php';
    require __DIR__ . '/../../inc/theme/assets.php';

    $cases = [
        'archive' => true,
        'product' => false,
        'cart' => false,
        'checkout' => false,
        'account' => false,
        '' => false,
    ];

    foreach ($cases as $surface => $expected) {
        $GLOBALS['aznet_test_woo_surface'] = '' === $surface ? null : $surface;
        $GLOBALS['aznet_test_styles'] = [];
        $actual = \AZnet\Theme\should_enqueue_woocommerce_archive_assets();
        if ($actual !== $expected) { fwrite(STDERR, "wrong archive eligibility for {$surface}\n"); exit(1); }
        \AZnet\Theme\enqueue_assets();
        $loaded = in_array('aznet-theme-woocommerce-archive', $GLOBALS['aznet_test_styles'], true);
        if ($loaded !== $expected) { fwrite(STDERR, "wrong archive enqueue for {$surface}\n"); exit(2); }
    }

    echo "PASS: W3 archive-only asset scope\n";
}
```

- [ ] **Step 2: Run RED**

Run:

```bash
php tests/offline/w3-archive-asset-scope-contract.php
```

Expected: FAIL because `inc/theme/woocommerce-archive.php` / `should_enqueue_woocommerce_archive_assets()` does not exist.

- [ ] **Step 3: Implement minimal GREEN**

Create `inc/theme/woocommerce-archive.php`:

```php
<?php
/**
 * WooCommerce archive presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function should_enqueue_woocommerce_archive_assets(): bool {
    return 'archive' === current_surface();
}
```

Add exactly one `require_once __DIR__ . '/woocommerce-archive.php';` in `inc/theme/bootstrap.php` after the Woo integration and product presentation module.

Append to `enqueue_assets()` after the Product block:

```php
if ( should_enqueue_woocommerce_archive_assets() ) {
    wp_enqueue_style(
        'aznet-theme-woocommerce-archive',
        get_theme_file_uri( '/assets/css/components/woocommerce-archive.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}
```

- [ ] **Step 4: Run GREEN and lint**

```bash
php tests/offline/w3-archive-asset-scope-contract.php
php -l inc/theme/woocommerce-archive.php
php -l inc/theme/assets.php
php -l inc/theme/bootstrap.php
```

Expected: contract PASS and no syntax errors.

- [ ] **Step 5: Commit Task 1**

```bash
git add inc/theme/woocommerce-archive.php inc/theme/bootstrap.php inc/theme/assets.php tests/offline/w3-archive-asset-scope-contract.php
git commit -m "feat: add Woo archive-only presentation asset gate"
```

---

### Task 2: Native Woo archive responsive CSS

**Files:**
- Create: `assets/css/components/woocommerce-archive.css`
- Create: `tests/offline/w3-archive-css-contract.php`

**Interfaces:**
- Consumes native Woo archive classes/selectors and Theme CSS tokens.
- Produces presentation-only archive layout CSS.

- [ ] **Step 1: Write failing CSS contract**

Create `tests/offline/w3-archive-css-contract.php` that loads the CSS file and requires these literal capabilities:

```php
<?php
$path = __DIR__ . '/../../assets/css/components/woocommerce-archive.css';
if (!is_file($path)) { fwrite(STDERR, "missing archive CSS\n"); exit(1); }
$css = file_get_contents($path);
$required = [
    '.woocommerce ul.products',
    'grid-template-columns: repeat(4, minmax(0, 1fr))',
    '@media (max-width: 1023px)',
    'repeat(3, minmax(0, 1fr))',
    '@media (max-width: 767px)',
    'repeat(2, minmax(0, 1fr))',
    '@media (max-width: 479px)',
    'grid-template-columns: 1fr',
    '.woocommerce-result-count',
    '.woocommerce-ordering',
    '.woocommerce-loop-product__title',
    '.price',
    ':focus-visible',
    '--aznet-theme-',
];
foreach ($required as $needle) {
    if (false === strpos($css, $needle)) { fwrite(STDERR, "missing: {$needle}\n"); exit(2); }
}
foreach (['display: none !important', 'position: sticky', 'transform: scale'] as $needle) {
    if (false !== strpos($css, $needle)) { fwrite(STDERR, "forbidden: {$needle}\n"); exit(3); }
}
echo "PASS: W3 archive CSS contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/w3-archive-css-contract.php
```

Expected: FAIL because `woocommerce-archive.css` does not exist.

- [ ] **Step 3: Implement minimal CSS**

Create `assets/css/components/woocommerce-archive.css` scoped to `.woocommerce` archive markup. Use CSS Grid for `ul.products`, square image presentation, token-based spacing/border/radius/focus, and no behavior-changing selectors. Required responsive column counts are 4 / 3 / 2 / 1 at the breakpoints specified by the contract.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/w3-archive-css-contract.php
```

Expected: PASS.

- [ ] **Step 5: Commit Task 2**

```bash
git add assets/css/components/woocommerce-archive.css tests/offline/w3-archive-css-contract.php
git commit -m "feat: style native Woo archive shell"
```

---

### Task 3: Ownership gate, retained regressions and closure evidence

**Files:**
- Create: `tests/offline/w3-archive-ownership-static-contract.php`
- Create: `scripts/verify-w3.sh`
- Create: `docs/evidence/W3_WOO_ARCHIVE_SHELL_LOCAL_VERIFICATION.md`

**Interfaces:**
- Consumes W2 verifier chain.
- Produces repeatable W3 local L0-L2 closure evidence.

- [ ] **Step 1: Add static ownership contract**

The test must inspect W3 production paths and fail on direct `get_option(`, `get_post_meta(`, `$wpdb`, `WP_Query`, `pre_get_posts`, `query_posts(`, quoted `_woocommerce_` storage keys, `Automattic\\WooCommerce\\Internal`, `choiceguide_`, `convertflow`, JS files introduced for W3, or any repository-root `woocommerce/` template override directory.

- [ ] **Step 2: Create repeatable verifier**

Create `scripts/verify-w3.sh`:

```bash
#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"
php tests/offline/w3-archive-asset-scope-contract.php
php tests/offline/w3-archive-css-contract.php
php tests/offline/w3-archive-ownership-static-contract.php
echo 'PASS: W3 offline contracts'
bash scripts/verify-w2.sh
echo 'PASS: retained W2 -> W1 -> E5-B chain'
```

- [ ] **Step 3: Run fresh W3 verifier**

```bash
bash scripts/verify-w3.sh
```

Expected: 3 W3 contracts PASS followed by retained W2/W1/E5-B PASS.

- [ ] **Step 4: Write evidence from actual output**

Record branch/head, W2 base SHA, RED causes, exact W3 production delta, GREEN output, ownership/no-query/no-template-override result, lint evidence for changed/new PHP paths, and explicit NOT PROVEN for L3-L6.

- [ ] **Step 5: Commit closure evidence**

```bash
git add tests/offline/w3-archive-ownership-static-contract.php scripts/verify-w3.sh docs/evidence/W3_WOO_ARCHIVE_SHELL_LOCAL_VERIFICATION.md
git commit -m "test: close W3 Woo archive local gate"
```

## Self-Review

- Spec coverage: archive-only loading, native Woo controls/cards, 4/3/2/1 responsive grid, focus visibility, no custom filter/query/template override and retained regressions are all represented.
- Placeholder scan: no implementation placeholders remain.
- Type consistency: Task 1 consumes only W1 `current_surface(): ?string` and produces one Theme boolean helper.
- Scope: Cart/Checkout/Account, ConvertFlow coexistence, runtime/browser/integration/release stay outside W3.
