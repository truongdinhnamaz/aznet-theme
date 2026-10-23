# AZnet Theme v1.1 R4 WooCommerce Presentation 2.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Raise WooCommerce presentation quality to a polished commercial-theme level without taking ownership of product, variation, cart, checkout, account or query state.

**Architecture:** Reuse the existing Woo public surface adapter and per-surface asset model. Extend the R1 settings schema with bounded presentation presets, build a shared product-card token layer, enrich archive/product/cart/checkout/account CSS, and use public hooks/Blocks only; template overrides remain forbidden unless a concrete failing test proves CSS/hooks cannot satisfy the presentation requirement and a source review approves the exception.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, WooCommerce public APIs/hooks/Blocks, CSS, Theme Mod settings from R1, WP-CLI, Playwright/axe.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- WooCommerce remains commerce owner.
- No direct product meta/session/cart/order table reads.
- No wishlist, compare, AJAX filter/search, swatch domain engine, quick-view engine or custom checkout engine.
- Existing W1-W9 ownership/fail-soft contracts must remain green unless a source-approved public contract changes.
- Per-surface asset loading remains mandatory.
- No `woocommerce/` template override directory is added by default.

---

### Task 1: Add bounded commerce presentation settings

**Files:**
- Modify: `inc/theme/settings.php`
- Create: `tests/offline/r4-commerce-settings-contract.php`

**Interfaces:**
- Adds:
  - `commerce_catalog_preset`: `grid|compact-grid|editorial`
  - `commerce_card_density`: `comfortable|balanced|compact`
  - `commerce_product_preset`: `classic|focus|story`
  - `commerce_sticky_summary`: boolean

- [ ] **Step 1: Write RED**

Assert defaults:
```php
assert(\AZnet\Theme\settings_defaults()['commerce_catalog_preset'] === 'grid');
assert(\AZnet\Theme\settings_defaults()['commerce_card_density'] === 'balanced');
assert(\AZnet\Theme\settings_defaults()['commerce_product_preset'] === 'classic');
assert(\AZnet\Theme\settings_defaults()['commerce_sticky_summary'] === false);
```
Invalid enum values must normalize to defaults.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-commerce-settings-contract.php
```

- [ ] **Step 3: Add keys to the existing settings allow-list**

Do not create a Woo-specific option store. Keep all values presentation-only and additive within the current settings schema.

- [ ] **Step 4: Run GREEN + R1/R3 settings regressions**

```bash
php tests/offline/r4-commerce-settings-contract.php
php tests/offline/r1-settings-contract.php
php tests/offline/r3-header-settings-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/settings.php tests/offline/r4-commerce-settings-contract.php
git commit -m "feat: add commerce presentation presets"
```

---

### Task 2: Build Product Card System and catalog preset classes

**Files:**
- Create: `assets/css/components/woocommerce-product-card.css`
- Modify: `assets/css/components/woocommerce-archive.css`
- Modify: `inc/theme/woocommerce-archive.php`
- Modify: `inc/theme/assets.php`
- Create: `tests/offline/r4-product-card-contract.php`

**Interfaces:**
- Produces Theme body/surface classes:
  - `aznet-theme-commerce-catalog--grid|compact-grid|editorial`
  - `aznet-theme-commerce-card--comfortable|balanced|compact`

- [ ] **Step 1: Write RED contract**

Assert the archive helper exposes normalized classes from Theme settings and that the product-card stylesheet is enqueued only on Woo archive/product-block surfaces explicitly supported by the Theme.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-product-card-contract.php
```

- [ ] **Step 3: Implement archive presentation classes**

Add helpers such as:
```php
function woocommerce_catalog_preset(): string {
    $value = setting('commerce_catalog_preset', 'grid');
    return in_array($value, ['grid', 'compact-grid', 'editorial'], true) ? $value : 'grid';
}

function woocommerce_card_density(): string {
    $value = setting('commerce_card_density', 'balanced');
    return in_array($value, ['comfortable', 'balanced', 'compact'], true) ? $value : 'balanced';
}
```
Apply classes through `body_class` or a Theme-owned archive wrapper; do not mutate Woo queries.

- [ ] **Step 4: Implement product-card CSS tokens**

Use semantic values for media ratio, title/price hierarchy, badge slot, rating, CTA, focus and spacing. Example:
```css
.woocommerce ul.products li.product {
    --aznet-theme-product-card-gap: var(--aznet-theme-space-3);
    display: grid;
    gap: var(--aznet-theme-product-card-gap);
    min-width: 0;
}
.aznet-theme-commerce-card--compact ul.products li.product {
    --aznet-theme-product-card-gap: var(--aznet-theme-space-2);
}
```
Do not calculate sale/stock badges in Theme PHP.

- [ ] **Step 5: Run GREEN + retained Woo contracts**

```bash
php tests/offline/r4-product-card-contract.php
php tests/offline/w1-woocommerce-absent-contract.php
php tests/offline/w1-woocommerce-asset-scope-contract.php
```
Run all retained `tests/offline/w*.php` in CI before merge.

- [ ] **Step 6: Commit**

```bash
git add assets/css/components/woocommerce-product-card.css assets/css/components/woocommerce-archive.css inc/theme/woocommerce-archive.php inc/theme/assets.php tests/offline/r4-product-card-contract.php
git commit -m "feat: add Woo product card and catalog presets"
```

---

### Task 3: Implement Single Product Classic / Focus / Story presets

**Files:**
- Modify: `inc/theme/woocommerce-product.php`
- Modify: `assets/css/components/woocommerce-product.css`
- Create: `tests/offline/r4-product-layout-contract.php`

**Interfaces:**
- Produces `woocommerce_product_preset(): string` and Theme-owned product preset classes.

- [ ] **Step 1: Write RED**

Assert `classic|focus|story` normalization, Woo-absent safe behavior, and no use of private product meta/session APIs.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-product-layout-contract.php
```

- [ ] **Step 3: Implement preset class helper**

Use:
```php
function woocommerce_product_preset(): string {
    $value = setting('commerce_product_preset', 'classic');
    return in_array($value, ['classic', 'focus', 'story'], true) ? $value : 'classic';
}
```
Attach only Theme presentation classes around/native to the product surface.

- [ ] **Step 4: Implement CSS compositions**

Classic retains the current two-column baseline. Focus increases gallery share. Story keeps the purchase region first and gives full-width long-form sections below. Keep Woo native tabs/related/upsells semantics intact.

- [ ] **Step 5: Add optional sticky summary as CSS-first enhancement**

When `commerce_sticky_summary` is true and viewport supports it, use `position: sticky` on the native summary with safe top offset from Theme header tokens. No JS is required for purchase state.

- [ ] **Step 6: Run GREEN**

```bash
php tests/offline/r4-product-layout-contract.php
```

- [ ] **Step 7: Commit**

```bash
git add inc/theme/woocommerce-product.php assets/css/components/woocommerce-product.css tests/offline/r4-product-layout-contract.php
git commit -m "feat: add Woo single product presets"
```

---

### Task 4: Polish native gallery, variations and purchase summary without domain logic

**Files:**
- Modify: `assets/css/components/woocommerce-product.css`
- Create: `tests/offline/r4-product-controls-contract.php`

**Interfaces:**
- Consumes native Woo markup only.
- Produces resilient styling for gallery/summary/variations/quantity/CTA/meta/notices.

- [ ] **Step 1: Write RED static selector contract**

Require styling coverage for:
```text
.woocommerce-product-gallery
.flex-control-thumbs
.variations
.quantity
.single_add_to_cart_button
.stock
.product_meta
.woocommerce-message/.woocommerce-error/.woocommerce-info
```
Reject selectors that depend on private data attributes/classes from ConvertFlow or custom Woo internals.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-product-controls-contract.php
```

- [ ] **Step 3: Implement presentation only**

Style native selects rather than inventing swatches. Keep gallery usable if Woo gallery JS is unavailable; images remain visible/scrollable in document order.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r4-product-controls-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add assets/css/components/woocommerce-product.css tests/offline/r4-product-controls-contract.php
git commit -m "feat: polish Woo purchase presentation"
```

---

### Task 5: Upgrade Cart / Checkout / Account presentation

**Files:**
- Modify: `assets/css/components/woocommerce-cart.css`
- Modify: `assets/css/components/woocommerce-checkout.css`
- Modify: `assets/css/components/woocommerce-account.css`
- Create: `tests/offline/r4-commerce-application-surfaces-contract.php`

**Interfaces:**
- Native Woo forms/tables/endpoints remain authoritative.
- Theme only changes layout, hierarchy, responsive behavior and focus/error presentation.

- [ ] **Step 1: Write RED contract**

Assert the three stylesheets cover forms, notices, totals/navigation and mobile table/form behavior while containing no AJAX endpoints, cart mutation hooks or order-state logic.

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-commerce-application-surfaces-contract.php
```

- [ ] **Step 3: Implement Cart responsive hierarchy**

On small screens avoid forcing desktop table widths; use Woo-supported markup with CSS grid/block transformations only where semantics remain readable. Primary CTA/totals stay visually clear.

- [ ] **Step 4: Implement Checkout readability/error states**

Improve field spacing, labels, focus, validation notices and order summary. Do not AJAXify or alter checkout flow.

- [ ] **Step 5: Implement My Account navigation/content panels**

Style native endpoint navigation, forms and tables without changing endpoint routing.

- [ ] **Step 6: Run GREEN**

```bash
php tests/offline/r4-commerce-application-surfaces-contract.php
```

- [ ] **Step 7: Commit**

```bash
git add assets/css/components/woocommerce-cart.css assets/css/components/woocommerce-checkout.css assets/css/components/woocommerce-account.css tests/offline/r4-commerce-application-surfaces-contract.php
git commit -m "feat: polish Woo cart checkout account surfaces"
```

---

### Task 6: Add Woo Blocks/pattern presentation compatibility without global Woo bundle

**Files:**
- Modify: `inc/theme/assets.php`
- Modify: `assets/css/components/woocommerce-product-card.css`
- Create: `tests/offline/r4-woo-blocks-asset-contract.php`

**Interfaces:**
- Product-card styles may load on a non-archive page only when a supported Woo product block is actually present.

- [ ] **Step 1: Write RED block-presence contract**

Stub `has_block()`/block registry and assert:
```text
normal Page without Woo block -> no product-card stylesheet
Page with supported Woo product collection/template block -> product-card stylesheet
Woo absent -> no Woo stylesheet
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/r4-woo-blocks-asset-contract.php
```

- [ ] **Step 3: Implement public WordPress block detection**

Use `has_block()` with exact public Woo block names verified on the tested Woo version. Do not inspect post content manually for plugin-private markup.

- [ ] **Step 4: Run GREEN**

```bash
php tests/offline/r4-woo-blocks-asset-contract.php
php tests/offline/w1-woocommerce-asset-scope-contract.php
```

- [ ] **Step 5: Commit**

```bash
git add inc/theme/assets.php assets/css/components/woocommerce-product-card.css tests/offline/r4-woo-blocks-asset-contract.php
git commit -m "perf: scope Woo product card assets to real surfaces"
```

---

### Task 7: Real WooCommerce runtime/browser matrix

**Files:**
- Create: `tests/browser/r4-woocommerce-l4.mjs`
- Create: `.github/workflows/r4-woocommerce-browser.yml`
- Create: `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`

**Interfaces:**
- Produces L3/L4 evidence on clean WordPress + supported WooCommerce.

- [ ] **Step 1: Provision deterministic Woo fixtures through public APIs/WP-CLI**

Create simple, variable, sale and out-of-stock products using Woo-supported APIs/CLI. Create Cart/Checkout/My Account pages through Woo setup/public facilities; do not seed private tables manually.

- [ ] **Step 2: Test catalog presets and long-content stress cases**

At 1440, 1024, 390 and 320 widths verify long titles, prices, badges and card grids do not overflow.

- [ ] **Step 3: Test all Single Product presets**

Verify gallery, summary, variations, quantity, add-to-cart control, notices, tabs and related sections remain functional. JS-disabled smoke must still expose images and purchase controls even if enhanced gallery behavior is reduced.

- [ ] **Step 4: Test Cart/Checkout/Account keyboard/a11y**

Axe critical/serious = 0 for Theme-controlled regressions; visible focus and validation messages; no unexpected console/page errors.

- [ ] **Step 5: Verify Woo-absent regression**

Run the clean-WP G core runtime/browser workflows with Woo disabled and prove non-commerce routes unchanged.

- [ ] **Step 6: Commit evidence**

```bash
git add tests/browser/r4-woocommerce-l4.mjs .github/workflows/r4-woocommerce-browser.yml docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md
git commit -m "test: verify Woo presentation 2.0"
```

**Exit:** R4 L1-L4 PASS; Woo remains the sole commerce truth owner and no template override/application engine was introduced.
