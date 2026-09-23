# AZnet Theme v1.1 R2 Native Pattern Library Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a curated WordPress-native pattern library that lets users build polished responsive pages quickly without a proprietary builder or non-portable content schema.

**Architecture:** Core-only patterns use native Theme pattern discovery under `patterns/`. Woo-block-dependent patterns are kept out of the auto-discovered directory and are registered programmatically from `inc/patterns/woocommerce/` only when the exact public Woo block types they require are registered. All design behavior comes from R1 semantic tokens and block/style classes; no pattern creates domain storage or runtime dependence on AZnet JavaScript.

**Tech Stack:** WordPress 6.9+, Gutenberg/core blocks, Woo Blocks, PHP pattern files, `theme.json`, CSS tokens, WP-CLI, Playwright/axe.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- R1 must be merged and its semantic token contract stable before R2.
- Patterns do not create CPTs, options, provider state or proprietary serialized structures.
- Pattern content must remain WordPress block content after theme switch.
- No site-specific authoritative URLs/data; only clearly replaceable example text/media placeholders.
- V1.1 launch set is exactly **18 candidate patterns**. A candidate that fails the quality gate is removed rather than replaced with filler; final shipped count may therefore be below 18.
- The five approved-spec candidates deferred from the v1.1 launch set are: `trust-stats`, `cta-split`, `content-featured-articles`, `commerce-benefits`, `utility-newsletter`.
- Every shipped pattern needs deliberate desktop/tablet/mobile intent and editor/frontend parity.

---

### Task 1: Add pattern category registration and static quality contract

**Files:**
- Create: `inc/theme/patterns.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/r2-pattern-static-contract.php`

**Interfaces:**
- Produces categories:
  - `aznet-theme-hero`
  - `aznet-theme-trust`
  - `aznet-theme-content`
  - `aznet-theme-commerce`
  - `aznet-theme-utility`

- [ ] **Step 1: Write RED static contract**

Assert `patterns.php` registers only Theme-owned categories and every auto-discovered `patterns/*.php` file contains `Title`, `Slug`, `Categories`, and `Description` headers. Also reject forbidden strings:
```php
$forbidden = [
    'get_option(', 'get_post_meta(', '$wpdb',
    'choiceguide_', '_choiceguide_', 'rootprofile_',
    'wp_insert_post(', 'register_post_type(',
];
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-pattern-static-contract.php
```
Expected: FAIL because `inc/theme/patterns.php` does not exist.

- [ ] **Step 3: Implement category registration**

Use:
```php
function register_pattern_categories(): void {
    $categories = [
        'aznet-theme-hero'     => __('AZnet — Hero', 'aznet-theme'),
        'aznet-theme-trust'    => __('AZnet — Trust & CTA', 'aznet-theme'),
        'aznet-theme-content'  => __('AZnet — Content', 'aznet-theme'),
        'aznet-theme-commerce' => __('AZnet — Commerce', 'aznet-theme'),
        'aznet-theme-utility'  => __('AZnet — Utility', 'aznet-theme'),
    ];
    foreach ($categories as $slug => $label) {
        register_block_pattern_category($slug, ['label' => $label]);
    }
}
```
Wire category registration to `init`. Programmatic Woo pattern registration is added in Task 5 on the same hook after block types are available.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r2-pattern-static-contract.php
bash scripts/verify-g3-core.sh
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/patterns.php inc/theme/bootstrap.php tests/offline/r2-pattern-static-contract.php
git commit -m "feat: register native pattern categories"
```

---

### Task 2: Ship the four Hero patterns

**Files:**
- Create: `patterns/hero-centered.php`
- Create: `patterns/hero-split.php`
- Create: `patterns/hero-inverse.php`
- Create: `patterns/hero-commerce.php`
- Create: `tests/offline/r2-hero-patterns-contract.php`

**Interfaces:**
- Consumes: R1 semantic tokens/classes.
- Produces four portable core-block compositions.

- [ ] **Step 1: Write RED contract for the four expected slugs**

Assert exact slugs:
```text
aznet-theme/hero-centered
aznet-theme/hero-split
aznet-theme/hero-inverse
aznet-theme/hero-commerce
```
Assert each uses core blocks only and no hard-coded external URL.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-hero-patterns-contract.php
```

- [ ] **Step 3: Implement `hero-centered.php`**

Use native blocks and Theme classes, for example:
```php
<?php
/**
 * Title: Hero — Centered
 * Slug: aznet-theme/hero-centered
 * Categories: aznet-theme-hero
 * Description: Centered hero with eyebrow, heading, copy and action.
 */
?>
<!-- wp:group {"align":"full","className":"aznet-theme-pattern aznet-theme-pattern--hero-centered","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull aznet-theme-pattern aznet-theme-pattern--hero-centered">
<!-- wp:paragraph {"className":"aznet-theme-pattern__eyebrow"} --><p class="aznet-theme-pattern__eyebrow"><?php esc_html_e('Giải pháp WordPress từ nền tảng sạch', 'aznet-theme'); ?></p><!-- /wp:paragraph -->
<!-- wp:heading {"level":1,"textAlign":"center"} --><h1 class="wp-block-heading has-text-align-center"><?php esc_html_e('Xây website nhanh mà không khóa nội dung vào builder riêng', 'aznet-theme'); ?></h1><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><?php esc_html_e('Thay nội dung mẫu này bằng thông điệp của bạn.', 'aznet-theme'); ?></p><!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button"><?php esc_html_e('Hành động chính', 'aznet-theme'); ?></a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:group -->
```
No fixed destination URL is required; the editor can set it.

- [ ] **Step 4: Implement split/inverse/commerce variants**

Use the same semantic hierarchy, varying only composition/class names. `hero-commerce.php` remains core-block-only so the Hero category is usable with Woo absent.

- [ ] **Step 5: Run GREEN**

```bash
php tests/offline/r2-hero-patterns-contract.php
```

- [ ] **Step 6: Commit**

```bash
git add patterns/hero-*.php tests/offline/r2-hero-patterns-contract.php
git commit -m "feat: add native hero patterns"
```

---

### Task 3: Ship four Trust/CTA launch patterns

**Files:**
- Create: `patterns/trust-logo-strip.php`
- Create: `patterns/trust-feature-grid.php`
- Create: `patterns/trust-testimonials.php`
- Create: `patterns/cta-full-width.php`
- Create: `tests/offline/r2-trust-patterns-contract.php`

**Interfaces:**
- Produces four core-block patterns with no authoritative trust data.
- Defers `trust-stats` and `cta-split` from the approved candidate pool.

- [ ] **Step 1: Write RED**

Assert the four launch files exist, use `aznet-theme-trust`, contain only example copy and do not claim real customer counts/certifications.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-trust-patterns-contract.php
```

- [ ] **Step 3: Implement replaceable example semantics**

Testimonials must be visibly sample content, for example:
```html
<!-- wp:quote --><blockquote class="wp-block-quote"><p>“Nội dung đánh giá mẫu — hãy thay bằng phản hồi đã được xác thực của bạn.”</p></blockquote><!-- /wp:quote -->
```
Do not imply AZnet Theme owns or verifies the business claim.

- [ ] **Step 4: Run GREEN + forbidden-data scan**

```bash
php tests/offline/r2-trust-patterns-contract.php
! grep -R -E 'get_option\(|get_post_meta\(|\$wpdb|Entity UUID|Journey' patterns/trust-*.php patterns/cta-full-width.php
```

- [ ] **Step 5: Commit**

```bash
git add patterns/trust-*.php patterns/cta-full-width.php tests/offline/r2-trust-patterns-contract.php
git commit -m "feat: add trust and CTA launch patterns"
```

---

### Task 4: Ship five Content launch patterns

**Files:**
- Create: `patterns/content-intro.php`
- Create: `patterns/content-alternating.php`
- Create: `patterns/content-article-grid.php`
- Create: `patterns/content-faq.php`
- Create: `patterns/content-authority-quote.php`
- Create: `tests/offline/r2-content-patterns-contract.php`

**Interfaces:**
- Produces five portable content compositions.
- Defers `content-featured-articles` from the approved candidate pool.

- [ ] **Step 1: Write RED**

Assert headings are not hard-coded to multiple H1s and FAQ uses ordinary heading/paragraph/group blocks rather than schema/domain logic.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-content-patterns-contract.php
```

- [ ] **Step 3: Implement content patterns**

Use heading levels 2/3 by default so inserted patterns do not create a second document H1. For `content-article-grid`, use Query Loop and let WordPress own query semantics; do not add custom query PHP.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r2-content-patterns-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add patterns/content-*.php tests/offline/r2-content-patterns-contract.php
git commit -m "feat: add native content launch patterns"
```

---

### Task 5: Ship three Commerce and two Utility launch patterns with true fail-soft Woo registration

**Files:**
- Create: `patterns/commerce-promotion.php`
- Create: `patterns/utility-contact.php`
- Create: `patterns/utility-footer-cta.php`
- Create: `inc/patterns/woocommerce/category-grid.php`
- Create: `inc/patterns/woocommerce/featured-products.php`
- Modify: `inc/theme/patterns.php`
- Create: `tests/offline/r2-commerce-utility-patterns-contract.php`

**Interfaces:**
- Auto-discovered core-only patterns are always available.
- `aznet-theme/commerce-category-grid` registers only if public block `woocommerce/product-categories` is registered.
- `aznet-theme/commerce-featured-products` registers only if public block `woocommerce/product-collection` is registered.
- Defers `commerce-benefits` and `utility-newsletter` from the approved candidate pool.

- [ ] **Step 1: Write RED capability contract**

Stub `WP_Block_Type_Registry` and `register_block_pattern()` and assert:
```text
Woo absent -> neither Woo-dependent pattern registers
only product-categories registered -> category-grid only
only product-collection registered -> featured-products only
both registered -> both patterns
```
Also assert no Woo private classes/storage/constants are referenced.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-commerce-utility-patterns-contract.php
```

- [ ] **Step 3: Implement each Woo pattern as a registration array**

`inc/patterns/woocommerce/category-grid.php` returns:
```php
return [
    'slug' => 'aznet-theme/commerce-category-grid',
    'requires_block' => 'woocommerce/product-categories',
    'properties' => [
        'title' => __('Commerce — Product Categories', 'aznet-theme'),
        'categories' => ['aznet-theme-commerce'],
        'description' => __('Responsive WooCommerce category grid.', 'aznet-theme'),
        'content' => '<!-- wp:woocommerce/product-categories /-->',
    ],
];
```
`featured-products.php` follows the same shape with slug `aznet-theme/commerce-featured-products`, required block `woocommerce/product-collection`, and a bounded Product Collection block composition. These files are not under `/patterns`, so WordPress cannot auto-register them when Woo is absent.

- [ ] **Step 4: Implement public block-gated registration**

In `inc/theme/patterns.php`:
```php
function register_woocommerce_patterns(): void {
    $registry = \WP_Block_Type_Registry::get_instance();
    $files = [
        dirname(__DIR__, 2) . '/inc/patterns/woocommerce/category-grid.php',
        dirname(__DIR__, 2) . '/inc/patterns/woocommerce/featured-products.php',
    ];

    foreach ($files as $file) {
        $definition = require $file;
        if (!is_array($definition) || !$registry->is_registered($definition['requires_block'])) {
            continue;
        }
        register_block_pattern($definition['slug'], $definition['properties']);
    }
}
```
Before GREEN, the test must verify the final filesystem path expression resolves both definition files. If `dirname(__DIR__, 2)` does not resolve the repository root from `inc/theme/patterns.php`, replace it with the correct deterministic path expression; do not guess at runtime.

- [ ] **Step 5: Implement core-only commerce/utility files**

`commerce-promotion`, `utility-contact`, and `utility-footer-cta` live under `/patterns` and use core blocks only. Contact is a presentation shell only and does not implement form submission.

- [ ] **Step 6: Run GREEN and exact-candidate count contract**

```bash
php tests/offline/r2-commerce-utility-patterns-contract.php
bash scripts/verify-g3-core.sh
expr "$(find patterns -maxdepth 1 -name '*.php' | wc -l)" + "$(find inc/patterns/woocommerce -maxdepth 1 -name '*.php' | wc -l)"
```
Expected launch candidate definitions after Tasks 2-5: `18` before any quality-gate exclusion. On Woo-absent runtime only the 16 core-only patterns are registered.

- [ ] **Step 7: Commit**

```bash
git add patterns/commerce-promotion.php patterns/utility-*.php inc/patterns/woocommerce inc/theme/patterns.php tests/offline/r2-commerce-utility-patterns-contract.php
git commit -m "feat: add fail soft commerce and utility patterns"
```

---

### Task 6: Browser/editor portability and responsive quality gate

**Files:**
- Create: `tests/browser/r2-pattern-library-l4.mjs`
- Create: `.github/workflows/r2-pattern-library-browser.yml`
- Create: `docs/evidence/R2_PATTERN_LIBRARY_L4.md`

**Interfaces:**
- Consumes: all shipped R2 patterns.
- Produces: representative editor/frontend/a11y/portability evidence.

- [ ] **Step 1: Insert representative patterns through WordPress**

Create pages using at least:
```text
hero-split
trust-feature-grid
content-article-grid
content-faq
commerce-featured-products (Woo matrix only)
utility-contact
```

- [ ] **Step 2: Test 1440 / 1024 / 390 viewports**

Assert no overflow >1px, no duplicate IDs introduced by patterns, buttons/links receive visible focus, axe critical/serious = 0 on Theme-controlled markup and media dimensions do not cause obvious layout shift.

- [ ] **Step 3: Verify editor/frontend parity**

Capture screenshots or computed-style assertions showing the R1 visual preset vocabulary is represented consistently in editor and frontend for at least Default and one alternate preset.

- [ ] **Step 4: Verify theme-switch portability**

Switch temporarily to a stock WordPress theme and assert the saved post content still renders as block content rather than raw proprietary shortcodes/opaque data. Switch back without data loss.

- [ ] **Step 5: Verify Woo absence/presence registration**

With Woo absent, assert 16 core-only AZnet patterns and no broken Woo-dependent pattern entries. With Woo present and the two required public blocks registered, assert the two commerce patterns appear, bringing the candidate registration set to 18.

- [ ] **Step 6: Record final shipped count**

Evidence must state exact shipped count, the original 18-candidate launch set, any candidate excluded for failing the gate, and the five deliberately deferred approved-spec candidates. Do not replace a failed candidate merely to preserve a quota.

- [ ] **Step 7: Commit**

```bash
git add tests/browser/r2-pattern-library-l4.mjs .github/workflows/r2-pattern-library-browser.yml docs/evidence/R2_PATTERN_LIBRARY_L4.md
git commit -m "test: verify native pattern library quality"
```

**Exit:** R2 L1-L4 PASS; patterns are portable WordPress content, Woo-dependent patterns fail soft, and no builder runtime is introduced.
