# R4 WooCommerce Presentation 2.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Raise AZnet Theme's WooCommerce presentation from bounded v1.0 shells to a polished v1.1 product-card/catalog/single-product/cart/checkout/account system without taking ownership of commerce state.

**Architecture:** Preserve the existing `Woo public/native output -> Theme surface adapter -> bounded presentation preset` boundary. Extend the single normalized `aznet_theme_settings` Theme Mod with exactly three backward-compatible commerce presentation keys, project those settings into Theme body classes, and style classic Woo/native Woo Blocks through surface-scoped CSS. No `woocommerce/` template overrides, private Woo state, query replacement, swatches, AJAX commerce engines or duplicated add-to-cart state machines are introduced.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, WooCommerce 10.9.4 for the support-floor matrix, hybrid PHP theme + `theme.json`, native Woo hooks/functions/Blocks, CSS, framework-free JavaScript only if a failing public-capability test later proves it necessary.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md` on approved design branch `design/v1.1-native-product-system`; canonical source boundaries are `docs/source/AZT-02-architecture.md`, `AZT-04-roadmap-qa-decisions.md`, and `AZT-EXEC-MAP.md` on `main`.

## Global Constraints

- Canonical implementation base for R4 is `main@a83eaa026cdac19a7c14742a7e27989ad55d0dba`.
- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.`
- WooCommerce owns product, variation, price, stock, cart, checkout, order, account and query truth.
- Theme consumes public Woo/WordPress APIs, hooks and registered block capabilities only.
- No direct product meta, Woo session/order table, private class/storage inspection, `WP_Query` replacement, `pre_get_posts`, AJAX filter/search, wishlist/compare, swatch engine, quick-view engine or custom checkout engine.
- Hooks/CSS/Blocks first. Do not create a `woocommerce/` template override directory in R4; an override would require a new concrete failing test plus explicit source review/approval.
- Keep one settings store: Theme Mod `aznet_theme_settings`; `schema_version` remains `1` because R4 only adds backward-compatible allow-listed keys.
- Freeze final R4 setting keys as: `woo_catalog_preset`, `woo_product_card_density`, `woo_product_preset`. Do not add a columns/sticky/mobile-bar toggle unless a separate failing requirement proves it necessary.
- Allowed values: `woo_catalog_preset = grid|compact-grid|editorial`; `woo_product_card_density = comfortable|balanced|compact`; `woo_product_preset = classic|focus|story`.
- Defaults: `grid`, `balanced`, `classic`.
- Product/Archive/Cart/Checkout/Account and Woo Block compatibility assets remain separately scoped; no global Woo bundle merely because WooCommerce is active.
- Runtime/browser support-floor verification uses WordPress `6.9`, PHP `8.1`, WooCommerce `10.9.4`.
- Feature/bugfix work follows RED -> intended failure -> minimal GREEN -> retained regression.

---

### Task 1: Freeze Woo presentation settings and projection contract

**Files:**
- Modify: `inc/theme/settings.php`
- Create: `inc/theme/woocommerce-presentation.php`
- Modify: `inc/theme/bootstrap.php`
- Create: `tests/offline/r4-woocommerce-settings-contract.php`
- Create: `.github/workflows/r4-woocommerce-static.yml`

**Interfaces:**
- Consumes: `AZnet\Theme\setting(string $key, mixed $fallback = null): mixed`; `AZnet\Theme\Integrations\WooCommerce\current_surface(): ?string`.
- Produces: `woo_catalog_preset(): string`, `woo_product_card_density(): string`, `woo_product_preset(): string`, `woocommerce_presentation_body_classes(array $classes): array`.
- Body classes on Woo surfaces only: `aznet-theme-woo-catalog--<preset>`, `aznet-theme-woo-card--<density>`, `aznet-theme-woo-product--<preset>` as applicable.

- [ ] **Step 1: Write the failing settings/projection contract**

Create `tests/offline/r4-woocommerce-settings-contract.php` that asserts:

```php
$defaults = settings_defaults();
assert($defaults['woo_catalog_preset'] === 'grid');
assert($defaults['woo_product_card_density'] === 'balanced');
assert($defaults['woo_product_preset'] === 'classic');

$normalized = normalize_settings([
    'woo_catalog_preset' => 'compact-grid',
    'woo_product_card_density' => 'compact',
    'woo_product_preset' => 'story',
    'unknown_woo_key' => 'must-drop',
]);
assert($normalized['woo_catalog_preset'] === 'compact-grid');
assert($normalized['woo_product_card_density'] === 'compact');
assert($normalized['woo_product_preset'] === 'story');
assert(!array_key_exists('unknown_woo_key', $normalized));
```

Also statically require `inc/theme/woocommerce-presentation.php`, its three resolver functions, one `body_class` filter wiring in `bootstrap.php`, and forbid `get_post_meta(`, `get_option(`, `$wpdb`, `WC()->`, `wc_get_orders(` and `_woocommerce_` in the new presentation module.

- [ ] **Step 2: Run the RED through the draft PR workflow**

Create `.github/workflows/r4-woocommerce-static.yml` to execute all `tests/offline/r4-*.php` followed by `bash scripts/verify-g3-core.sh`. Expected RED: missing `inc/theme/woocommerce-presentation.php` or missing `woo_catalog_preset` key, not a syntax/harness error.

- [ ] **Step 3: Implement minimal settings and projection helpers**

Extend `settings_defaults()` and `normalize_settings()` with only the three frozen keys and exact allow-lists. Create `woocommerce-presentation.php` with simple normalized setting readers and a body-class filter that returns unchanged classes when `current_surface()` is `null`; on archive/product/cart/checkout/account it adds only relevant Theme presentation classes.

- [ ] **Step 4: Verify GREEN and retained regression**

Expected: R4 settings contract PASS and `scripts/verify-g3-core.sh` PASS on the same exact head.

- [ ] **Step 5: Commit**

Commit message: `feat: add Woo presentation settings contract`.

---

### Task 2: Product Card System and catalog presets

**Files:**
- Modify: `inc/theme/woocommerce-archive.php`
- Modify: `assets/css/components/woocommerce-archive.css`
- Create: `tests/offline/r4-product-card-catalog-contract.php`

**Interfaces:**
- Consumes: `woo_catalog_preset()` and `woo_product_card_density()` from Task 1; native Woo loop markup (`ul.products`, `li.product`, native price/rating/button markup).
- Produces: presentation classes/tokens only; no product data calculation and no query hooks.

- [ ] **Step 1: Write the failing Product Card/Catalog contract**

Require three catalog class selectors and three density selectors in `woocommerce-archive.css`:

```text
.aznet-theme-woo-catalog--grid
.aznet-theme-woo-catalog--compact-grid
.aznet-theme-woo-catalog--editorial
.aznet-theme-woo-card--comfortable
.aznet-theme-woo-card--balanced
.aznet-theme-woo-card--compact
```

Require presentation coverage for native `.products .product`, `.woocommerce-loop-product__link`, `.woocommerce-loop-product__title`, `.price`, `.star-rating`, `.button`; require visible `:focus-visible`; reject `content:`-based fake sale/stock labels, `WP_Query`, `pre_get_posts`, `woocommerce_product_query`, `get_post_meta(` and product-price arithmetic tokens in the R4 archive helper/CSS.

- [ ] **Step 2: Verify RED**

Expected RED: first missing R4 catalog selector.

- [ ] **Step 3: Implement minimal CSS system**

Use the body classes from Task 1 to set bounded grid/density custom properties and style the native Woo card. `Grid` uses balanced multi-column cards; `Compact Grid` increases desktop density; `Editorial Catalog` gives media/title more breathing room and may reduce columns through CSS only. Do not mutate the Woo query or product object.

- [ ] **Step 4: Verify GREEN + W1-W3 retained contracts**

Run R4 static plus `scripts/verify-w1.sh`, `scripts/verify-w2.sh`, `scripts/verify-w3.sh` through the retained core chain.

- [ ] **Step 5: Commit**

Commit message: `feat: add Woo product card and catalog presets`.

---

### Task 3: Single Product Classic / Focus / Story

**Files:**
- Modify: `inc/theme/woocommerce-product.php`
- Modify: `assets/css/components/woocommerce-product.css`
- Create: `tests/offline/r4-single-product-presets-contract.php`

**Interfaces:**
- Consumes: `woo_product_preset()`; native Woo `.product`, `.woocommerce-product-gallery`, `.summary`, `.product_title`, `.price`, `.woocommerce-product-rating`, `.woocommerce-product-details__short-description`, `.cart`, `.product_meta` output.
- Produces: Classic/Focus/Story layout presentation through body classes/CSS only.

- [ ] **Step 1: Write the failing single-product contract**

Require selectors for:

```text
.aznet-theme-woo-product--classic
.aznet-theme-woo-product--focus
.aznet-theme-woo-product--story
.woocommerce-product-gallery
.summary
.variations
.single_variation_wrap
```

Require desktop-only sticky summary for `focus` through CSS and require it to become non-sticky at mobile width. Reject Woo template overrides, direct meta/session reads, `remove_action('woocommerce_...')`, `add_action('woocommerce_...')` layout rewiring and any custom add-to-cart form/state machine.

- [ ] **Step 2: Verify RED**

Expected RED: missing Classic/Focus/Story selector or sticky-summary contract.

- [ ] **Step 3: Implement minimal layout presets**

Classic preserves familiar gallery/summary balance; Focus gives gallery more space and a desktop sticky native summary; Story keeps the purchase region first and gives native long-form product tabs/content a wider lower-page rhythm. All rely on existing Woo markup/hook order.

- [ ] **Step 4: Verify GREEN + W2 retained contracts**

No `woocommerce/` override directory may appear.

- [ ] **Step 5: Commit**

Commit message: `feat: add Woo single product presets`.

---

### Task 4: Native gallery, purchase summary and variation-control polish

**Files:**
- Modify: `assets/css/components/woocommerce-product.css`
- Create: `tests/offline/r4-product-controls-contract.php`

**Interfaces:**
- Consumes: Woo's native/public gallery and form controls.
- Produces: visual/focus/responsive styling only; no gallery engine and no variation mapping.

- [ ] **Step 1: Write the failing controls contract**

Require presentation for native gallery wrapper/flex viewport/thumbnails, `.variations select`, `.quantity .qty`, `.single_add_to_cart_button`, `.reset_variations`, validation/focus states, and a mobile fallback where gallery images remain in normal usable flow. Explicitly reject new files matching `assets/js/*variation*`, `assets/js/*gallery*`, tokens `VariationForm`, `wc_add_to_cart_variation_params`, term-to-variation maps and swatch markup/classes introduced by Theme.

- [ ] **Step 2: Verify RED**

Expected RED: missing R4 gallery/control selector.

- [ ] **Step 3: Implement CSS-only polish**

Use semantic Theme tokens for thumbnail active/focus state, controls, spacing and CTA prominence. Keep native Woo JavaScript behavior untouched.

- [ ] **Step 4: Verify GREEN + W2 regression**

- [ ] **Step 5: Commit**

Commit message: `feat: polish native Woo product controls`.

---

### Task 5: Cart, Checkout and My Account polish

**Files:**
- Modify: `assets/css/components/woocommerce-cart.css`
- Modify: `assets/css/components/woocommerce-checkout.css`
- Modify: `assets/css/components/woocommerce-account.css`
- Create: `tests/offline/r4-commerce-surfaces-contract.php`

**Interfaces:**
- Consumes: native Woo cart/checkout/account markup and existing surface-scoped asset eligibility functions.
- Produces: responsive presentation only.

- [ ] **Step 1: Write the failing surface contract**

Cart requirements: responsive `.shop_table`, product-name/quantity/coupon/totals hierarchy and primary checkout CTA; at <= 640px tables/rows must not force horizontal overflow.

Checkout requirements: `.woocommerce-error`, `.woocommerce-invalid`, form fields, order review and place-order CTA have visible hierarchy/focus/error styling; no hidden validation state.

Account requirements: endpoint navigation, forms and tables are responsive and keyboard-visible.

Reject `WC()->cart`, `wc_create_order`, `woocommerce_checkout_process`, `woocommerce_checkout_create_order`, `woocommerce_account_menu_items`, `add_rewrite_endpoint`, `wp_ajax_` and private storage calls in Theme R4 production code.

- [ ] **Step 2: Verify RED**

Expected RED: one of the new responsive/error/focus presentation markers is absent.

- [ ] **Step 3: Implement minimal scoped CSS**

Expand only the existing Cart/Checkout/Account CSS files. Do not add JS or hooks unless a later browser RED proves a presentation defect cannot be fixed with native markup/CSS.

- [ ] **Step 4: Verify GREEN + W4/W5/W6 regressions**

Run retained W4, W5 and W6 suites on the exact head.

- [ ] **Step 5: Commit**

Commit message: `feat: polish Woo cart checkout and account surfaces`.

---

### Task 6: Woo Blocks compatibility and exact asset scope

**Files:**
- Create: `inc/theme/woocommerce-blocks.php`
- Modify: `inc/theme/bootstrap.php`
- Modify: `inc/theme/assets.php`
- Create: `assets/css/components/woocommerce-blocks.css`
- Create: `tests/offline/r4-woocommerce-blocks-contract.php`

**Interfaces:**
- Consumes: public `WP_Block_Type_Registry`, WordPress `has_block()`, exact public block names `woocommerce/product-collection` and `woocommerce/product-categories` already certified by R2/Woo 10.9.4.
- Produces: `should_enqueue_woocommerce_blocks_assets(): bool` and one scoped stylesheet handle `aznet-theme-woocommerce-blocks`.

- [ ] **Step 1: Write the failing Woo Blocks contract**

Require `should_enqueue_woocommerce_blocks_assets()` to return false if Woo block types are not registered; false if registered but current post does not contain either certified block; true only when exact registered capability and exact current WordPress content block both exist. Reject plugin-version heuristics, option/meta/private class inspection and global enqueue on every Woo-active page.

Require `woocommerce-blocks.css` to style product-card typography/price/button/focus using Theme semantic variables without replacing Woo query/filter semantics.

- [ ] **Step 2: Verify RED**

Expected RED: missing `inc/theme/woocommerce-blocks.php`.

- [ ] **Step 3: Implement exact public-capability/content detection and scoped CSS**

Use `WP_Block_Type_Registry::get_instance()->is_registered()` plus `has_block()` only. Wire the module before assets and enqueue the stylesheet only when the helper returns true.

- [ ] **Step 4: Verify GREEN + R2 pattern regression**

R2 Woo pattern contract must remain green because R4 consumes the same certified public block names rather than inventing a second capability model.

- [ ] **Step 5: Commit**

Commit message: `feat: add scoped Woo Blocks presentation compatibility`.

---

### Task 7: Real Woo runtime/browser matrix and clean-WP absence regression

**Files:**
- Create: `tests/browser/r4-woocommerce-l4.mjs`
- Create: `.github/workflows/r4-woocommerce-browser.yml`
- Create after GREEN: `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`

**Interfaces:**
- Consumes: all R4 settings/classes/assets from Tasks 1-6.
- Produces: fresh L3-L4 evidence on exact candidate bytes; no production behavior.

- [ ] **Step 1: Build the browser workflow fixtures**

Install WordPress 6.9/PHP 8.1. First run AZnet Theme with Woo absent and prove generic Home/Page/Post routes render without Woo CSS handles or PHP errors. Then install/activate WooCommerce `10.9.4`, create Woo pages with `WC_Install::create_pages()`, one simple product and one variable product through public Woo product APIs, and assign Theme settings for all three catalog density/layout outcomes and all three single-product presets across scenarios.

- [ ] **Step 2: Add browser assertions**

At 1440x1000, 1024x900, 390x844 and 320x800 assert:

```text
HTTP 200 on Shop/Product/Cart/Checkout/My Account fixtures
exactly one main#main
horizontal overflow <= 1px
R4 body classes match normalized settings
native price/title/rating/button/product form remain present
variable-product select remains native select and usable
cart/checkout/account tables/forms fit mobile viewport
first keyboard focus is visible Theme skip link
all interactive Theme/Woo controls have visible keyboard focus where Theme styling applies
axe critical/serious Theme-controlled violations = 0
no unexpected console/page/subresource errors
no Theme PHP fatal/warning/parse/uncaught markers
```

Also render a page containing the certified R2 Woo Product Collection/Category blocks and prove `aznet-theme-woocommerce-blocks` loads there but not on a generic page.

- [ ] **Step 3: Run RED before browser fixes**

If browser verification exposes a production defect, reduce each defect to the shallowest deterministic R4 static/browser regression before changing production CSS/PHP. Do not weaken assertions to make the workflow green.

- [ ] **Step 4: Reach GREEN and run full retained PR suite**

Fresh exact-head expectation: R1/R2/R3 workflows, G core static/runtime/browser/lifecycle/package, F Homepage regressions, R4 static and R4 browser all SUCCESS. Record exact run IDs, artifact ID/digest, WordPress/PHP/Woo versions and browser scenarios.

- [ ] **Step 5: Record evidence-only checkpoint**

Create `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`. If this is the only post-verification commit, compare the verified functional/test head to final PR head and prove only the evidence file changed; do not infer fresh runtime beyond byte equivalence.

- [ ] **Step 6: Finish branch**

Update the R4 PR to Ready for Review and stop at owner merge gate. After approved merge/state closure, R5 Control Center + System Health becomes sequentially unblocked because R3/R4 setting keys are final.

---

## Self-review record

**Spec coverage:** Tasks 1-7 map one-for-one to the approved R4 delivery sequence. Product cards/catalog, Classic/Focus/Story, gallery/summary/variations, Cart/Checkout/Account, Woo Blocks and clean-WP/Woo-present L3-L4 matrices are covered. Mobile sticky purchase bar is deliberately not implemented because the approved spec makes it capability-gated/deferable and no evidence yet proves a safe non-duplicating projection. Toolbar/filter drawer is likewise not added without a concrete public capability/use case.

**Placeholder scan:** No TBD/TODO or unspecified implementation step remains. Any future JS/template override is explicitly blocked until a failing test and source gate justify it.

**Interface consistency:** The three final commerce setting keys are defined once in Task 1 and consumed by Tasks 2-7; `schema_version = 1` remains stable; Woo Blocks use the exact R2-certified block names.
