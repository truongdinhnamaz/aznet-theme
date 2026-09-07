# AZnet Theme v1.1 R2 Native Pattern Library Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship a curated WordPress-native pattern library that lets users build polished responsive pages quickly without a proprietary builder or non-portable content schema.

**Architecture:** Use theme pattern files under `patterns/` with core blocks and Woo Blocks where available. Patterns carry only composition/example content; all design behavior comes from R1 semantic tokens and block/style classes. Woo patterns fail soft by remaining insertable only when their referenced Woo blocks are available.

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

Assert `patterns.php` registers only Theme-owned categories and every future `patterns/*.php` file must contain `Title`, `Slug`, `Categories`, and `Description` headers. Also reject forbidden strings:
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
Wire it to `init` after confirming WordPress 6.9 supports the API.

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

### Task 5: Ship three Commerce and two Utility launch patterns with fail-soft registration

**Files:**
- Create: `patterns/commerce-category-grid.php`
- Create: `patterns/commerce-featured-products.php`
- Create: `patterns/commerce-promotion.php`
- Create: `patterns/utility-contact.php`
- Create: `patterns/utility-footer-cta.php`
- Modify: `inc/theme/patterns.php`
- Create: `tests/offline/r2-commerce-utility-patterns-contract.php`

**Interfaces:**
- Woo-specific patterns register only when required public block types/capabilities are present; utility patterns are always available.
- Defers `commerce-benefits` and `utility-newsletter` from the approved candidate pool.

- [ ] **Step 1: Write RED capability contract**

Stub block availability and assert:
```php
assert(\AZnet\Theme\should_register_woocommerce_patterns() === false);
$GLOBALS['r2_woo_blocks_available'] = true;
assert(\AZnet\Theme\should_register_woocommerce_patterns() === true);
```
No Woo private classes/storage are allowed.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r2-commerce-utility-patterns-contract.php
```

- [ ] **Step 3: Implement public block detection**

Use WordPress block registry only:
```php
function should_register_woocommerce_patterns(): bool {
    $registry = \WP_Block_Type_Registry::get_instance();
    return $registry->is_registered('woocommerce/product-collection')
        || $registry->is_registered('woocommerce/product-template');
}
```
If WordPress/Woo version exposes different public block names on the support matrix, use the exact names proven in runtime evidence; do not inspect Woo internals.

- [ ] **Step 4: Implement commerce/utility files**

Commerce patterns use public Woo blocks where available; promotion may remain core-block-only. Contact is a presentation shell only and must not implement form submission. Footer CTA is core-block-only.

- [ ] **Step 5: Run GREEN and exact-count contract**

```bash
php tests/offline/r2-commerce-utility-patterns-contract.php
bash scripts/verify-g3-core.sh
find patterns -maxdepth 1 -name '*.php' | wc -l
```
Expected launch candidate count after Tasks 2-5: `18` before any quality-gate exclusion.

- [ ] **Step 6: Commit**

```bash
git add patterns/commerce-*.php patterns/utility-*.php inc/theme/patterns.php tests/offline/r2-commerce-utility-patterns-contract.php
git commit -m "feat: add commerce and utility launch patterns"
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

- [ ] **Step 5: Record final shipped count**

Evidence must state exact shipped count, the original 18-candidate launch set, any candidate excluded for failing the gate, and the five deliberately deferred approved-spec candidates. Do not replace a failed candidate merely to preserve a quota.

- [ ] **Step 6: Commit**

```bash
git add tests/browser/r2-pattern-library-l4.mjs .github/workflows/r2-pattern-library-browser.yml docs/evidence/R2_PATTERN_LIBRARY_L4.md
git commit -m "test: verify native pattern library quality"
```

**Exit:** R2 L1-L4 PASS; patterns are portable WordPress content and no builder runtime is introduced.
