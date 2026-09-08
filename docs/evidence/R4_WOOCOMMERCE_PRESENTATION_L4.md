# R4 WooCommerce Presentation 2.0 — L1-L4 Evidence

**Date:** 2026-09-08  
**Scope:** AZnet Theme v1.1 / R4 WooCommerce Presentation 2.0  
**Canonical base:** `main@a83eaa026cdac19a7c14742a7e27989ad55d0dba`  
**Verified functional/test head:** `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a`  
**Verified PR merge commit:** `f2bc573d2c5d1c8139b87fe4847576b412bc6685`  
**Verified tree:** `a6a2dd02a3ce98a5b73419e8eaa5a8834ca1e797`  
**Branch:** `work/v1.1-r4-woocommerce-presentation`  
**PR:** #43

## 1. Result

R4 functional/test bytes satisfy the planned Theme-owned L1-L4 exit gate on the verified head.

Delivered WooCommerce presentation system:

- three normalized catalog presets: `grid`, `compact-grid`, `editorial`;
- three normalized product-card densities: `comfortable`, `balanced`, `compact`;
- three normalized single-product presets: `classic`, `focus`, `story`;
- native Woo product-card/archive presentation without query replacement;
- native gallery, variation select, quantity and add-to-cart presentation without a Theme gallery/variation engine;
- responsive Cart, Checkout and My Account presentation;
- public-capability/content-gated Woo Blocks compatibility for the certified public block names;
- exact surface-scoped Woo assets; generic WordPress routes remain free of Theme Woo CSS;
- clean-WordPress fail-soft behavior when WooCommerce is absent.

WooCommerce remains the sole owner of product, variation, price, stock, cart, checkout, order, account and query truth.

R4 introduces no `woocommerce/` template override directory, private Woo storage/session read, custom product query/filter engine, swatch engine, quick view, wishlist/compare, custom checkout engine or duplicated add-to-cart state machine.

## 2. R4 settings contract

Additive normalized Theme settings:

```text
woo_catalog_preset: grid | compact-grid | editorial
woo_product_card_density: comfortable | balanced | compact
woo_product_preset: classic | focus | story
```

Defaults:

```text
woo_catalog_preset=grid
woo_product_card_density=balanced
woo_product_preset=classic
```

The existing Theme Mod `aznet_theme_settings` remains the only Theme presentation settings store and `schema_version` remains `1` because the R4 keys are backward-compatible additions.

## 3. Task 7 debugging history

The R4 production implementation from Tasks 1-6 was already static-GREEN. Task 7 browser work exposed several harness/fixture/provider-boundary defects before the final matrix could be accepted.

### Product title and Cart/Checkout markup dialects

The first browser harness assumed classic Woo selectors too narrowly:

- the product H1 is Theme-owned `.aznet-theme-entry__title`, while native Woo summary/gallery/variation markup remains intact;
- WooCommerce 10.9.4 Cart and Checkout pages render Woo Blocks by default in this fixture, not only classic form/table markup.

The harness was corrected to verify the Theme-owned single H1 plus the native Woo product controls, and to accept the two public Woo rendering dialects — classic or Blocks — without weakening surface/asset/a11y assertions.

### Gallery fixture and FlexSlider wrapper semantics

The initial fixture PNG had an invalid CRC and was replaced with a valid 64x64 PNG. That defect was real but not the sole cause of the gallery assertion failure.

Temporary browser instrumentation then proved Woo 10.9.4 gallery initialization was healthy:

- Woo `ProductGallery` data present;
- FlexSlider present and initialized;
- one `.flex-viewport` with positive height;
- one `.flex-active-slide` with positive height;
- active product image decoded with positive natural dimensions;
- no gallery JavaScript exception.

The old harness incorrectly waited for `.woocommerce-product-gallery__wrapper` to be Playwright-visible. FlexSlider floats its slides, so that wrapper can legitimately have computed height `0` while the viewport and active slide are fully visible. The gate was corrected to require the actual public visual outcome: visible FlexSlider viewport, visible active slide/image and decoded positive image dimensions.

No Theme production gallery engine or Woo JavaScript override was added.

### Woo Checkout Block provider-owned axe findings

WooCommerce 10.9.4 Checkout Block consistently reports two serious axe rules in the provider-owned block markup:

```text
aria-prohibited-attr
autocomplete-valid
```

The harness does not disable axe and does not globally suppress these rules. It classifies a finding as provider-owned only when every affected node matches the exact known Woo Checkout Block markup inside `.wc-block-checkout`:

```text
.wc-block-components-order-summary[aria-live="polite"][aria-label]
.wc-block-components-skeleton__element[aria-live="polite"][aria-label]
input#email[name="contact_email"][type="email"]
  autocomplete="section-contact contact email"
```

Final artifact summaries record these findings separately as `axeProviderDefects`, while `axeBlocking` remains reserved for unexpected Theme-scope blocking violations.

Observed in each Checkout viewport:

```text
aria-prohibited-attr: serious, 3 nodes
autocomplete-valid: serious, 1 node
```

Therefore the accurate claim is: **R4 Theme-owned L4 gate PASS; WooCommerce 10.9.4 Checkout Block retains known provider-owned accessibility findings outside Theme ownership.** R4 does not claim end-to-end Woo Checkout provider markup is axe-clean.

### Variation async stabilization

A later Product RED was `color-contrast` during the transient native variation-selection state. Woo variation resolution is asynchronous; running axe immediately after selecting an option could inspect a mid-transition reset/add-to-cart state.

The harness was corrected with condition-based stabilization, not a fixed sleep. It waits until native Woo state is actually resolved before axe:

- selected variation value retained;
- native `variation_id` becomes positive;
- add-to-cart button is no longer `disabled` / `wc-variation-selection-needed`;
- reset-variation link reaches its stable visible state.

No Woo variation state machine was copied or replaced by Theme code.

### Temporary diagnostics removed

After root cause was proven and the final matrix was GREEN, the temporary gallery diagnostic workflow step and `tests/browser/r4-gallery-diagnostic.mjs` were removed. The final verified head contains the permanent regression harness only.

## 4. Final exact functional/test head

Verified branch head:

`675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a`

GitHub PR checks execute against synthetic merge commit:

`f2bc573d2c5d1c8139b87fe4847576b412bc6685`

The branch head and synthetic merge commit have the exact same Git tree:

`a6a2dd02a3ce98a5b73419e8eaa5a8834ca1e797`

Thus the tested PR-merge bytes and the verified R4 branch-head bytes are tree-identical.

Fresh exact-head workflow lookup returned **16/16 completed SUCCESS**:

- G Core Static Release Gate — `34154681381`;
- R3 Header Static Contracts — `34154681337`;
- R2 Pattern Library Static Contracts — `34154681399`;
- F Homepage Native Regression Package — `34154681430`;
- R4 WooCommerce Presentation Static Contracts — `34154681373`;
- R1 Design System Static Contracts — `34154681429`;
- R1 Visual Preset Feasibility — `34154681444`;
- G Core Lifecycle Update Switch Rollback — `34154681362`;
- F Homepage Native Runtime Browser — `34154681472`;
- G Core WordPress Clean Runtime — `34154681371`;
- G Core Release Candidate Package — `34154681363`;
- G Core Browser A11y Performance — `34154681476`;
- R1 Design System Browser Parity — `34154681323`;
- R2 Pattern Library Browser Quality — `34154681420`;
- R3 Header Browser Quality — `34154681388`;
- R4 WooCommerce Presentation Browser Quality — `34154681410`.

R4 static job: `101843944201` — SUCCESS.  
R4 browser job: `101843944270` — SUCCESS.

## 5. Runtime/browser environment

R4 browser matrix used:

- WordPress `6.9`;
- PHP `8.1.34`;
- MySQL `8.0.46`;
- WooCommerce `10.9.4`;
- Node `22.23.2`;
- Playwright `1.55.0` / Chromium `140.0.7339.16`;
- axe Playwright `4.10.2`.

Woo fixture products are created through Woo public product APIs. The fixture also confirms the certified public Woo block names are registered:

```text
woocommerce/product-collection
woocommerce/product-categories
```

## 6. R4 L4 matrix

### Clean WordPress, Woo absent

Routes:

```text
/
/r4-generic/
/r4-clean-post/
```

Viewports:

```text
1440x1000
390x844
```

Result: **6/6 PASS**.

The clean matrix verifies no Theme Woo stylesheet handle or `aznet-theme-woo-*` body class leaks when Woo is absent.

### Grid + Balanced + Classic — full commerce surfaces

Viewports:

```text
1440x1000
1024x900
390x844
320x800
```

At every viewport:

```text
Shop PASS
Product PASS
Cart PASS
Checkout PASS (Theme-owned gate; provider findings recorded separately)
My Account PASS
```

Additional capability/scope routes:

```text
Woo Blocks fixture PASS at 1440x1000 and 390x844
Generic WordPress fixture PASS at 1440x1000 and 390x844
```

Result: **24/24 route/viewport cases PASS** at the Theme-owned gate.

### Compact Grid + Compact + Focus

At all four viewports:

```text
Shop PASS
Product PASS
```

Result: **8/8 PASS**.

Desktop Focus summary is sticky; mobile Focus summary is non-sticky.

### Editorial + Comfortable + Story

At all four viewports:

```text
Shop PASS
Product PASS
```

Result: **8/8 PASS**.

### Total

R4 final browser artifact contains **46/46 passing route/viewport cases** at the Theme-owned gate:

```text
6 clean-WP cases
24 Grid/Classic full-surface cases
8 Compact/Focus cases
8 Editorial/Story cases
```

Across the applicable matrix the harness verifies:

- HTTP 200;
- exactly one `main#main`;
- horizontal overflow <= 1px — final summaries report `0px` in every case;
- normalized R4 body classes;
- correct surface-scoped Theme Woo stylesheet handles;
- native Woo title/price/rating/button/product-form controls remain present;
- native variable-product `select` remains usable and resolves a real variation before axe;
- native Woo gallery viewport/active image remains visible and decoded;
- Focus sticky behavior only at desktop widths;
- classic-or-Blocks Cart/Checkout public rendering compatibility;
- Woo Blocks asset loads only on the certified Woo block fixture;
- generic routes do not receive Theme Woo CSS;
- visible first keyboard focus on Theme skip link;
- unexpected axe critical/serious violations = `0` at Theme scope;
- provider-owned Checkout Block findings are preserved separately rather than hidden;
- no unexpected console/page/subresource errors;
- no Theme-caused PHP fatal/warning/parse/uncaught markers.

## 7. Final R4 browser artifact

- Name: `r4-woocommerce-browser-quality`
- Artifact ID: `10030598066`
- Run: `34154681410`
- Head: `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a`
- File count reported by uploader: `79`
- Size: `1,802,617` bytes
- SHA-256: `0e21872765c43cc55269a23c03a6d737e1bb8af75e599b167c896cf4e97e6da8`

Artifact includes scenario summaries, axe JSON, screenshots, fixture output, runtime log and smoke HTML.

## 8. Ownership / scope assertions retained

R4 does not introduce:

- `woocommerce/` template overrides;
- direct product/order meta or Woo private storage reads;
- `WC()->cart`/session ownership in Theme production code;
- custom product query/filter/search engine;
- swatches, quick view, wishlist or compare engine;
- custom checkout/order engine;
- custom gallery or variation JavaScript engine;
- duplicated product/cart/checkout/account domain state;
- global Woo presentation bundle on unrelated WordPress surfaces.

Theme consumes public/native Woo output and owns presentation only.

## 9. Rollback / recovery

R4 is isolated on `work/v1.1-r4-woocommerce-presentation` from canonical base:

`a83eaa026cdac19a7c14742a7e27989ad55d0dba`.

Before merge, rollback is branch/PR abandonment. After an approved merge, rollback is the bounded R4 merge revert. R4 presentation settings/CSS rollback does not delete product, variation, cart, order, account or WordPress content data owned by their source systems.

## 10. Gate and exact next

R4 functional/test scope is technically complete through Theme-owned L4 on verified head `675a4f0a...`.

Known WooCommerce 10.9.4 Checkout Block accessibility findings remain provider-owned and are explicitly recorded; R4 does not claim those provider nodes are clean or mutate them through private/fragile workarounds.

The next action is evidence-only PR closure metadata: verify the final PR head differs from `675a4f0a...` only by this evidence file, update PR #43 from its stale planning-state description, and mark it ready for owner merge review.

Merge into canonical `main` remains an explicit owner gate. After an approved R4 merge/state closure, the sequential exact next is **R5 presentation-only Control Center + System Health**.
