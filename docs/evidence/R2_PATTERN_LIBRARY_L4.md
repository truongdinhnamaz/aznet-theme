# R2 Native Pattern Library — L1-L4 Evidence

**Date:** 2026-09-07  
**Scope:** AZnet Theme v1.1 / R2 Native Pattern Library  
**Canonical base:** `main@b31f294d349deea62b07f2358c733c649c4c48a2`  
**Verified functional/test head:** `abb7d6eff2e7667a14cfa982c12297e63d308a64`  
**Branch:** `work/v1.1-r2-pattern-library`  
**PR:** #39

## 1. Result

R2 functional/test bytes satisfy the planned L1-L4 exit gate on the verified head.

The shipped candidate set is exactly **18 patterns**:

- 16 WordPress-native/core-block patterns under `patterns/`;
- 2 WooCommerce-dependent patterns under `inc/patterns/woocommerce/`, registered only when the exact public Woo block type is present.

Candidate quality was kept above quota. Approved-spec candidates not shipped in this slice remain deferred rather than replaced with filler:

- `trust-stats`;
- `cta-split`;
- `content-featured-articles`;
- `commerce-benefits`;
- `utility-newsletter`.

Theme-owned pattern categories:

- `aznet-theme-hero`;
- `aznet-theme-trust`;
- `aznet-theme-content`;
- `aznet-theme-commerce`;
- `aznet-theme-utility`.

## 2. Ownership and architecture boundary

R2 remains presentation/authoring composition only.

- No proprietary page builder or serialized Theme content schema.
- No CPT, parallel domain store, provider state or private WooCommerce state.
- No direct `get_option()`, `get_post_meta()`, `$wpdb` or private Woo storage access in the pattern registry.
- Core-only patterns are normal WordPress pattern files.
- Woo-dependent patterns are registered only after exact public block capability detection through `WP_Block_Type_Registry`.
- Example trust/contact/authority copy is visibly replaceable sample content and is not treated as authoritative business, identity or evidence data.
- Saved page content remains ordinary WordPress block content and survives Theme switching byte-for-byte.

## 3. RED -> GREEN history

### Task 1 — pattern registry/categories

RED head: `9df80f3384b3b87ea01203983d74e58af9d56bba`  
Run: `34135062952`  
Job: `101783968592`

Expected RED:

```text
FAIL: R2 pattern registry missing inc/theme/patterns.php
```

Minimal GREEN introduced `inc/theme/patterns.php`, the five Theme-owned categories and `init` wiring.

### Task 2 — Hero patterns

RED head: `41930571427b18f9b3a77ba7e39ae38dc2a0f5ca`  
Run: `34135271482`  
Job: `101784638054`

Expected RED: first missing Hero pattern file.

GREEN production head: `479556f92e81ea8152076d17b480704dcf7f34e3`

Shipped:

- `hero-centered`;
- `hero-split`;
- `hero-inverse`;
- `hero-commerce`.

### Task 3 — Trust and CTA patterns

RED head: `a93c6104aca916ef794df1c13fe4d381b0cf827f`  
Run: `34135562707`  
Job: `101785569192`

GREEN production head: `3c9430f004f1cc7e1441628c118cf2f02bdb7c58`

Shipped:

- `trust-logo-strip`;
- `trust-feature-grid`;
- `trust-testimonials`;
- `cta-full-width`.

Contracts reject hard-coded external URLs and unqualified authoritative trust claims.

### Task 4 — Content patterns

RED head: `4bd2143b6982d2333120f37cc2ef8a02c2ab352d`  
Run: `34135809315`  
Job: `101786374188`

GREEN production head: `08cdccf64bb7fff341abac20e3d6c01cf297d261`

Shipped:

- `content-intro`;
- `content-alternating`;
- `content-article-grid`;
- `content-faq`;
- `content-authority-quote`.

The article grid uses the native Query block; the FAQ pattern does not inject FAQ schema/domain behavior; insertable content patterns do not hard-code an H1.

### Task 5 — Commerce/Utility + fail-soft Woo patterns

RED head: `9e354128e17d422d44b123809f90613aa587cd35`  
Run: `34136139529`  
Job: `101787443283`

Expected RED: missing `commerce-promotion.php`.

Shipped core patterns:

- `commerce-promotion`;
- `utility-contact`;
- `utility-footer-cta`.

Woo-gated patterns:

- `aznet-theme/commerce-category-grid` -> requires public `woocommerce/product-categories` block;
- `aznet-theme/commerce-featured-products` -> requires public `woocommerce/product-collection` block.

The contract proves four capability states independently: neither block, category only, collection only and both blocks.

## 4. Systematic-debugging corrections

R2 exposed several harness/test assumptions. Each was reduced to the shallowest reproducible cause before changing behavior.

### Retained W1/W3/W4/W5/W6 false-positive

The old ownership guards matched the entire `add_action(...)` line and treated the callback name `register_woocommerce_patterns` as though it were a WooCommerce hook. The real hook is generic WordPress `init`.

The guards were narrowed to inspect only the first hook-name argument. Existing bans on private storage, commerce behavior, template takeover and Woo-specific lifecycle hooks remain in place.

### Browser fixture sentinel

The first browser workflow smoke check looked for text not included in the representative pattern fixture. The sentinel was changed to content actually emitted by the selected Hero pattern.

### Editor fixture sentinel

The browser test repeated the same invalid content assumption for editor parity. It was corrected to assert content present in the representative fixture rather than weaken editor verification.

### WooCommerce version compatibility

The unpinned WordPress.org install selected WooCommerce 11.1.0, which requires WordPress 7.0+ and cannot activate on the Theme support-floor matrix of WordPress 6.9. This was a test-environment version-selection issue, not a Theme defect.

The matrix was pinned to **WooCommerce 10.9.4**, whose support floor includes WordPress 6.9. Runtime still proves the exact required public block capabilities before registering Theme Woo patterns.

## 5. Fresh static evidence — exact functional/test head

Head: `abb7d6eff2e7667a14cfa982c12297e63d308a64`

R2 static workflow:

- Run: `34137967714`
- Job: `101793240465`
- Conclusion: `success`

It includes all `tests/offline/r2-*.php` contracts plus the retained `scripts/verify-g3-core.sh` regression chain.

## 6. Fresh runtime/browser evidence — exact functional/test head

Browser run: `34137967596`  
Job: `101793240106`  
Conclusion: `success`

Environment:

- WordPress `6.9`;
- PHP `8.1.34`;
- WooCommerce `10.9.4` for the Woo-present matrix.

### Clean WordPress / Default preset

```text
PASS: Woo-dependent patterns absent without WooCommerce
PASS: clean WordPress R2 pattern fixture created
PASS: R2 default clean frontend 1440x1000
PASS: R2 default clean frontend 1024x900
PASS: R2 default clean frontend 390x844
PASS: R2 default clean editor parity (iframe)
PASS: R2 default clean pattern library L4
```

### Woo-present / Editorial preset

Observed public capabilities:

```text
PUBLIC_WOO_BLOCK=woocommerce/product-categories
PUBLIC_WOO_BLOCK=woocommerce/product-collection
THEME_WOO_PATTERN=aznet-theme/commerce-category-grid
THEME_WOO_PATTERN=aznet-theme/commerce-featured-products
```

Browser/editor matrix:

```text
PASS: R2 editorial Woo frontend 1440x1000
PASS: R2 editorial Woo frontend 1024x900
PASS: R2 editorial Woo frontend 390x844
PASS: R2 editorial Woo editor parity (iframe)
PASS: R2 editorial Woo pattern library L4
```

### L4 checks covered

The browser harness verifies representative R2 surfaces for:

- exactly one Theme `main#main`;
- no duplicate IDs;
- horizontal overflow <= 1 px;
- uploaded media loads and remains within viewport width;
- native Query block produces article cards;
- FAQ native Details blocks render;
- first keyboard focus reaches the visible Theme skip link;
- visible focus indicator is present;
- mobile reduced-motion preference collapses Theme motion tokens to `0ms`;
- axe critical/serious violations = 0;
- no unexpected console/page/subresource failures;
- editor semantic-variable parity and representative pattern roots.

## 7. Theme-switch portability

The fixture page was switched from AZnet Theme to a deterministic alternate Theme and back.

Saved `post_content` remained byte-identical:

```text
PORTABILITY_CONTENT_SHA=2bfeafa7c6055f2828570fa56316c2a02af8c0c881b237ea7c86eb5f072f1adb
PASS: R2 saved block content survives theme switch byte-for-byte
```

This demonstrates that R2 composition is stored as native WordPress block content rather than a Theme-private authoring store.

## 8. Exact candidate count and runtime boundary

```text
PASS: R2 exact shipped candidate count 18 and runtime boundary clean
```

No Theme-caused PHP fatal/warning/parse/uncaught marker was found during the R2 runtime matrix.

## 9. Browser artifact

- Artifact: `r2-pattern-library-browser-quality`
- Artifact ID: `10024798908`
- Size: `1,251,538` bytes
- SHA-256: `3ccbfead4550ed9605127cf36bb46d7a805c04cf94bea6937f284711b4bec328`
- Run: `34137967596`
- Head: `abb7d6eff2e7667a14cfa982c12297e63d308a64`

## 10. Fresh retained exact-head regression set

A fresh workflow lookup on `abb7d6eff2e7667a14cfa982c12297e63d308a64` returned **12/12 completed SUCCESS**:

- R1 Design System Static Contracts — `34137967601`;
- R2 Pattern Library Static Contracts — `34137967714`;
- F Homepage Native Regression Package — `34137967616`;
- G Core Static Release Gate — `34137967606`;
- G Core WordPress Clean Runtime — `34137967627`;
- G Core Lifecycle Update Switch Rollback — `34137967604`;
- R1 Visual Preset Feasibility — `34137967605`;
- G Core Browser A11y Performance — `34137967611`;
- R1 Design System Browser Parity — `34137967626`;
- G Core Release Candidate Package — `34137967598`;
- F Homepage Native Runtime Browser — `34137967615`;
- R2 Pattern Library Browser Quality — `34137967596`.

## 11. Rollback / recovery

R2 is isolated on `work/v1.1-r2-pattern-library` from canonical base `b31f294d349deea62b07f2358c733c649c4c48a2`.

Rollback before merge is branch/PR abandonment. After an approved merge, rollback is the bounded R2 merge revert; native page content remains WordPress-owned and is not deleted by Theme rollback or Theme switching.

## 12. Gate and exact next

R2 functional/test scope is technically complete through L4 on the verified head. The next action is to attach this evidence-only checkpoint to PR #39, verify that no production/test/workflow bytes changed after the verified head, update PR metadata, and stop at the explicit owner merge gate.

After an approved R2 merge, R3 Header System 2.0 and R4 WooCommerce Presentation 2.0 remain independently READY under the accepted dependency map.
