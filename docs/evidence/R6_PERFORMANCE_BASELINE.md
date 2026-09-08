# R6 Performance + Asset Baseline — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 1 pre-promotion measurement  
**Canonical base:** `main@a6a901b5712291330368935d662aeda8370e618b`  
**Verified R6 head:** `870ebda30ae473176ac563be2d53e667f7b0b68d`  
**Verified tree:** `2f9927c51693e3a821379f006674ea05aaa34703`  
**Branch:** `work/v1.1-r6-performance-release`

## 1. Purpose and boundary

This checkpoint establishes a measured browser/asset baseline before any R6 performance optimization or release metadata promotion.

It records observed Theme asset requests, transferred/decoded bytes where Chromium exposes them, failed subresources, console/page errors, horizontal overflow and CLS observations. These are baseline measurements, not marketing targets and not proof that every observed layout shift is Theme-owned.

No production Theme PHP/CSS/JS behavior was changed to obtain this baseline. Theme metadata remains `1.0.0`.

## 2. Fresh GREEN evidence

GitHub Actions:

- Workflow: `R6 Performance Baseline`
- Run: `34181693407`
- Job: `101921908739`
- Result: **SUCCESS**
- Exact head: `870ebda30ae473176ac563be2d53e667f7b0b68d`
- Exact tree: `2f9927c51693e3a821379f006674ea05aaa34703`

Artifact:

- Name: `r6-performance-baseline`
- Artifact ID: `10039147061`
- Artifact digest: `sha256:29979d27d543fa3907e1aed25a21b5fac56a91d8456ced8c754361de420b56fa`

Runtime:

- WordPress `6.9`
- PHP `8.1.34`
- WooCommerce `10.9.4`
- Chromium via Playwright `1.55.0`

Final artifact server log contains no Theme/runtime `PHP Fatal error`, `Warning`, `Parse error` or `Uncaught` marker.

## 3. Route and viewport matrix

Viewports:

```text
1440x1000
390x844
```

Clean WordPress routes:

```text
/
/sample-page/
/sample-post/
/category/sample/
/?s=sample
/404-fixture/
```

WooCommerce routes:

```text
/shop/
/product/r4-simple-product/
/cart/
/checkout/
/my-account/
```

The intentional 404 route accepts the expected document-status console noise only. Non-document HTTP failures, request failures, unexpected console/page errors and horizontal overflow remain blocking.

## 4. Clean WordPress baseline

Result: **12/12 PASS**.

Every clean case recorded:

```text
request failures = 0
failed subresources = 0
unexpected console errors = 0
page errors = 0
horizontal overflow = 0px
CLS = 0
Woo Theme component CSS on clean routes = 0
provider-owned ConvertFlow/ChoiceGuide assets = 0
```

Observed Theme transfer footprint is stable across both viewports:

| Route | Theme assets | Theme transfer bytes | CLS |
| --- | ---: | ---: | ---: |
| Home | 6 | 25,350 | 0 |
| Page | 7 | 29,424 | 0 |
| Post | 7 | 29,424 | 0 |
| Category | 7 | 29,424 | 0 |
| Search | 7 | 29,424 | 0 |
| 404 | 7 | 29,424 | 0 |

The difference between Home and generic content routes is the Theme-owned `generic-content.css`, proving the existing generic content asset remains surface-scoped in this matrix.

## 5. WooCommerce baseline

Result: **10/10 PASS**.

Every Woo case recorded:

```text
request failures = 0
failed subresources = 0
unexpected console errors = 0
page errors = 0
horizontal overflow = 0px
```

Theme transfer footprint is identical at desktop/mobile for each route:

| Route | Theme assets | Theme transfer bytes | Desktop CLS | Mobile CLS |
| --- | ---: | ---: | ---: | ---: |
| Shop | 7 | 30,348 | 0 | 0 |
| Product | 7 | 33,756 | 0.15715 | 0.31758 |
| Cart | 8 | 31,743 | 0.02962 | 0.06066 |
| Checkout | 7 | 30,502 | 0.00578 | 0.00002 |
| My Account | 7 | 31,217 | 0 | 0 |

Observed Woo Theme transfer range is `30,348–33,756` bytes; median per-route value is `31,217` bytes.

The Woo CLS numbers above are observations only. This checkpoint does not assign ownership of those shifts to AZnet Theme because Woo/native/provider rendering may contribute; no optimization claim may be made without a narrower root-cause measurement.

## 6. Surface-aware asset findings

The measured baseline confirms the existing major Theme surface boundaries are already working:

- no Woo component stylesheet is loaded on the clean WordPress route matrix;
- `generic-content.css` is absent from Home and present on generic content routes;
- Woo product/archive/cart/checkout/account presentation assets remain route-specific;
- the Header navigation JS remains part of the rendered shell in this fixture;
- no provider-owned ConvertFlow/ChoiceGuide asset is loaded when the provider is absent.

Therefore R6 Task 2 must preserve these proven boundaries. A behavior-neutral refactor must not be described as a performance improvement unless a measured before/after reduction exists.

## 7. ConvertFlow Theme bridge observation

The Theme-owned compatibility bridge:

```text
assets/css/integrations/convertflow.css
handle: aznet-theme-convertflow-contract
```

is present in every measured clean and Woo case.

Observed per-page cost:

```text
transfer: 2,820 bytes
decoded: 2,520 bytes
```

This is not a provider-owned asset and does not violate the provider-absent assertion. It is, however, the only measured optional-integration bridge currently loaded globally by the Theme.

Optimization of this bridge is **not authorized through private detection**. R6 Task 3 must first resolve the source-owned O-008 gate: use a documented public/versioned Theme-consumable provider capability if one exists; otherwise record `BLOCKED_EXTERNAL_CONTRACT` and retain the safe bridge behavior.

## 8. Harness RED -> GREEN history

Three failures were reduced to test-harness causes before the final GREEN run:

1. Initial fixture creation collided with WordPress core's pre-existing `sample-page`; the harness was corrected to update the existing Page when present.
2. The intentional 404 route generated expected browser document-status console noise; classification was aligned with the retained core browser harness while preserving blocking subresource/error checks.
3. Woo Cart/Checkout seeding initially used the measured page, so navigation away from `?add-to-cart=...` produced expected aborted WordPress/Woo requests that polluted the measurement. Cart seeding now occurs in a separate page sharing the same browser context/session.

No AZnet Theme production defect was reproduced by these RED runs and no production Theme code was changed to make Task 1 GREEN.

## 9. PASS / boundary / next

**PASS — R6 Task 1 measured asset/browser baseline.** Clean WordPress is `12/12` GREEN and WooCommerce is `10/10` GREEN on the exact verified R6 head, with zero failed subresources, unexpected console/page errors or horizontal overflow.

**UNKNOWN / not claimed:** the root cause of Woo Product/Cart CLS observations; any universal KB/performance target; any before/after performance improvement; provider-specific ConvertFlow asset gating.

**NEXT:** R6 Task 2 — lock the current proven asset-scope matrix with an offline contract, then refactor only if doing so improves maintainability without weakening the exact surface behavior. ConvertFlow bridge optimization remains Task 3 and public-contract-gated.