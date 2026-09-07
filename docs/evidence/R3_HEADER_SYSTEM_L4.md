# R3 Header System 2.0 — L1-L4 Evidence

**Date:** 2026-09-07  
**Scope:** AZnet Theme v1.1 / R3 Header System 2.0  
**Canonical base:** `main@29d05e501b6f992793df8f961003d29aa8636af2`  
**Verified functional/test head:** `3941c01fc549478ac6fd914b9638ea6cf8373748`  
**Branch:** `work/v1.1-r3-header-system`  
**PR:** #41

## 1. Result

R3 functional/test bytes satisfy the planned L1-L4 exit gate on the verified head.

Delivered Header presentation system:

- four bounded presets: `standard`, `compact`, `commerce`, `overlay`;
- three sticky modes: `off`, `sticky`, `sticky-compact`;
- one normalized Theme-owned settings store: existing `aznet_theme_settings`, schema version remains `1`;
- reusable Header primitives for brand, primary navigation, search, commerce actions and mobile panel;
- one composer using shared primitives rather than four duplicated Header templates;
- accessible framework-free mobile navigation progressive enhancement;
- framework-free sticky-compact enhancement;
- Overlay safe fallback: requested Overlay resolves to Standard outside the WordPress front page;
- Woo account/cart URLs consumed only through public Woo functions and fail soft when Woo is absent.

No Header Builder, mega-menu application engine, domain state, provider state, direct Woo storage/session read or authoritative route heuristic was introduced.

## 2. R3 settings contract

Additive normalized settings:

```text
header_preset: standard | compact | commerce | overlay
header_sticky: off | sticky | sticky-compact
header_search: bool
header_utilities: bool
```

Defaults:

```text
header_preset=standard
header_sticky=sticky
header_search=true
header_utilities=true
```

The existing `aznet_theme_settings` store and schema version `1` are retained. Strict normalization accepts actual booleans or integer `0/1`; foreign keys and invalid enums are dropped/fallback-normalized.

## 3. RED -> GREEN history

### Task 1 — Header presentation settings

RED head: `14e6f7ce8f17409b6e9f2234b6eb38cc23be744b`  
Run: `34140506006`  
Job: `101801194187`

Expected RED:

```text
FAIL: missing R3 settings default header_preset
```

Minimal GREEN head: `1e171ffee944ea380393e54769055a258621b084`  
Run: `34140658750` — SUCCESS.

### Task 2 — resolver, context, primitives and composer

RED head: `27db5863d59025789cd19a2e674b033fcd779292`  
Run: `34140744089`  
Job: `101801930542`

Expected RED:

```text
FAIL: R3 header resolver missing inc/theme/header.php
```

Minimal GREEN head: `b59a3a73a0b5f68cdd2cc33d02d847eff7cb31ea`  
Run: `34141056787` — SUCCESS.

The resolver uses:

- normalized Theme settings;
- WordPress site title/home/custom logo/menu;
- `is_front_page()` only for the approved Overlay safe fallback;
- public Woo `wc_get_page_permalink( 'myaccount' )` and `wc_get_cart_url()` when available.

It does not use slug/title/Page-ID/URL heuristics, Woo session/cart objects or private storage.

### Task 3 — four Header preset styles

RED head: `b2305973fa2e1b4151d904f2746ca39c8778a6b8`  
Run: `34141132528`

Expected RED: missing `.aznet-theme-site-header--standard` and related preset style contract.

Minimal GREEN head: `70cd3076e9f59fe58b8a52c658b197c169a2db92`  
Run: `34141252892` — SUCCESS.

Overlay uses a solid inverse surface fallback before its translucent enhancement and explicitly retains inverse text contrast.

### Task 4 — accessible mobile navigation

RED head: `cd0e38237ebade11070f813cf77ac3f7c897bc21`  
Run: `34141381965`  
Job: `101803912557`

Expected RED:

```text
FAIL: missing assets/js/header-navigation.js
```

Minimal GREEN head: `d342a6b02a2e3cc103b189e064518c6a1a14ea33`  
Run: `34141618071` — SUCCESS.

Progressive enhancement behavior:

- server-rendered mobile panel remains reachable before JS initialization;
- real button uses `aria-expanded` + `aria-controls`;
- JS hides panel only after successful initialization;
- Enter/click opens;
- focus moves inside;
- Tab is contained while open;
- Escape closes and returns focus to trigger;
- body scroll lock is restored on close/pagehide;
- script has no jQuery/React/Vue dependency;
- script loads only when the same server-side mobile-panel capability resolver says the panel will render.

### Task 5 — sticky-compact enhancement

RED head: `09621376506ccdec696c4e324edfbf1e005a30d1`  
Run: `34141707327`  
Job: `101804919832`

Expected RED:

```text
FAIL: missing assets/js/sticky-header.js
```

Minimal GREEN head: `23b5432fcd57600550f03ddf5c3b0adfe77bd88e`  
Run: `34141881789` — SUCCESS.

The enhancement uses passive scroll + `requestAnimationFrame`, toggles one presentation class, does not measure/mutate Header height in JS, has no framework dependency and is enqueued only for `sticky-compact`. Reduced-motion CSS disables the transition.

## 4. Task 6 debugging and regressions

### WordPress 6.9 fixture shape

Initial browser workflow failed before browser execution because the fixture passed `menu-item-classes` as arrays to `wp_update_nav_menu_item()`. WordPress 6.9 expects a string and called `explode()` on the value.

This was a test-fixture defect, not Theme production behavior. The fixture was corrected from array -> string at head:

`16e46257d6196898b5d1dc72a1801c3c3b92ea77`.

### Production defect 1 — depth-2 keyboard navigation

Browser run `34142341132` showed the depth-2 child link existed correctly in the DOM but keyboard Tab skipped it.

Root cause: submenu `visibility` was included in a `160ms` transition. Immediately after focus entered the parent, the child was still treated as not keyboard-visible.

A shallow static regression was added first. Test-only head:

`48875947fec64f14417fe4274bf3e55a0a36da2f`  
Run: `34142662586`  
Job: `101807857751`

Correct RED:

```text
FAIL: submenu visibility must not be delayed by a transition because keyboard Tab can skip depth-2 links
```

Minimal production correction removes `visibility` from the transition while keeping opacity/transform enhancement.

### Production defect 2 — long mobile site title overflow

The same browser run showed horizontal overflow at 390px and 320px. Screenshot/DOM evidence localized the cause to the Header brand: `flex: 0 0 auto` prevented a long site title from shrinking and pushed the Menu trigger outside the viewport.

The same shallow CSS regression requires:

```text
mobile brand: flex: 1 1 auto; min-width: 0
brand span: overflow hidden + text-overflow ellipsis + white-space nowrap
```

Minimal production fix head:

`38c2049c94dd9635cbe58e86fc6fd64794417971`

Fresh R3 static: run `34142757643` — SUCCESS.  
Fresh R3 browser: run `34142757712` — SUCCESS.

### Retained G5 harness invalidation

R3 intentionally replaces the old `<details><summary>` mobile Header with a real button/panel progressive enhancement. Retained G5 still searched:

```text
.aznet-theme-site-header__mobile > summary
```

Previous run `34142341048` proved the stale harness: desktop cases passed while six mobile cases timed out on that selector.

Harness-only correction updated G5 to the R3 public markup contract:

```text
[data-aznet-theme-nav-trigger]
[data-aznet-theme-nav-panel]
```

It now verifies Enter/open, `aria-expanded`, focus into panel, no overflow, Escape/close and focus return without weakening the retained core assertions.

### Retained Homepage harness invalidation

After G5 was corrected, retained F Homepage browser exposed the same stale `<summary>` assumption at run `34143121909`: L3 + desktop/tablet passed, mobile timed out on the old selector.

Harness-only correction updated only the mobile branch to the R3 button/panel contract while retaining all Homepage surface/main/overflow/a11y assertions.

These harness corrections do not restore the old `<details>` markup and do not weaken the new R3 accessibility contract.

## 5. Final exact functional/test head

Verified head:

`3941c01fc549478ac6fd914b9638ea6cf8373748`

Fresh exact-head workflow lookup returned **14/14 completed SUCCESS**:

- F Homepage Native Regression Package — `34143360769`;
- R2 Pattern Library Static Contracts — `34143360743`;
- G Core Static Release Gate — `34143360795`;
- R1 Design System Static Contracts — `34143360782`;
- R3 Header Static Contracts — `34143360780`;
- R1 Visual Preset Feasibility — `34143360791`;
- G Core WordPress Clean Runtime — `34143360760`;
- G Core Lifecycle Update Switch Rollback — `34143360773`;
- F Homepage Native Runtime Browser — `34143360776`;
- R1 Design System Browser Parity — `34143360797`;
- G Core Release Candidate Package — `34143360787`;
- G Core Browser A11y Performance — `34143360790`;
- R3 Header Browser Quality — `34143360748`;
- R2 Pattern Library Browser Quality — `34143360741`.

R3 static job: `101809998115` — SUCCESS.  
R3 browser job: `101809997902` — SUCCESS.

## 6. Runtime/browser environment

R3 browser matrix used:

- WordPress `6.9`;
- PHP `8.1.34`;
- MySQL `8.0.46`;
- Chromium / Playwright `1.55.0`;
- axe Playwright `4.10.2`;
- WooCommerce `10.9.4` for the Woo-present Commerce scenario.

Public Woo URLs were observed at runtime before the Woo-present Header check:

```text
PUBLIC_WOO_URL=myaccount:http://127.0.0.1:8080/my-account/
PUBLIC_WOO_URL=cart:http://127.0.0.1:8080/cart/
```

## 7. R3 L4 matrix

Clean WordPress scenarios:

### Standard + sticky-compact

```text
PASS: 1440x1000
PASS: 1024x900
PASS: 390x844
PASS: 320x800
PASS: no-JS mobile fallback
```

### Compact + sticky

```text
PASS: 1440x1000
PASS: 1024x900
PASS: 390x844
PASS: 320x800
PASS: no-JS mobile fallback
```

### Overlay + sticky

Front page:

```text
PASS: 1440x1000
PASS: 1024x900
PASS: 390x844
PASS: 320x800
```

Non-front Page route with stored Overlay preset:

```text
PASS: Standard safe fallback at 1440x1000
PASS: Standard safe fallback at 1024x900
PASS: Standard safe fallback at 390x844
PASS: Standard safe fallback at 320x800
PASS: no-JS mobile fallback
```

### Commerce + sticky, Woo absent

```text
PASS: 1440x1000
PASS: 1024x900
PASS: 390x844
PASS: 320x800
PASS: no-JS mobile fallback
PASS: no account/cart actions leak when Woo is absent
```

### Commerce + sticky, WooCommerce 10.9.4 present

```text
PASS: 1440x1000
PASS: 1024x900
PASS: 390x844
PASS: 320x800
PASS: no-JS mobile fallback
PASS: account/cart actions render from public Woo URLs
```

Total R3-specific matrix: **24 browser route/viewport cases + 5 no-JS fallbacks**.

Across the applicable matrix the harness verifies:

- exactly one banner landmark;
- no duplicate IDs;
- horizontal overflow <= 1px;
- long site title + depth-2 navigation;
- desktop depth-1/depth-2 keyboard traversal and visible focus;
- first keyboard focus reaches Theme skip link;
- mobile trigger, open state, focus containment, Escape close and focus return;
- no-JS primary navigation remains keyboard reachable;
- sticky-compact class activation after scroll;
- measured layout shift assertion remains <= `0.05` in the harness;
- reduced-motion disables sticky transition;
- Overlay has a non-transparent contrast-safe computed surface on the front page and does not leak to the inner route;
- Woo absent/present fail-soft behavior;
- axe critical/serious violations = 0 for Theme Header scope;
- no unexpected console/page/subresource errors;
- no Theme-caused PHP fatal/warning/parse/uncaught markers.

## 8. R3 browser artifact

Final functional/test-head artifact:

- Name: `r3-header-browser-quality`
- Artifact ID: `10026773061`
- Run: `34143360748`
- Head: `3941c01fc549478ac6fd914b9638ea6cf8373748`
- File count reported by uploader: `62`
- Size: `1,595,376` bytes
- SHA-256: `b6749c5d8f0b2f0999dec35bb52c285275b04cb08cdcd431d702b4f063d0d72e`

## 9. Ownership / scope assertions retained

R3 does not introduce:

- Header Builder or drag/drop composition state;
- mega-menu application/domain engine;
- private Woo storage/session/cart reads;
- RootProfile/ConvertFlow state reads;
- slug/title/Page-ID/URL authoritative Header routing;
- second Theme settings store;
- JS framework dependency;
- global ecosystem JS bundle.

Header content sources remain WordPress/Woo public data; Theme owns presentation only.

## 10. Rollback / recovery

R3 is isolated on `work/v1.1-r3-header-system` from canonical base:

`29d05e501b6f992793df8f961003d29aa8636af2`.

Before merge, rollback is branch/PR abandonment. After an approved merge, rollback is the bounded R3 merge revert. No WordPress menu/logo/content or Woo domain data is deleted by R3 settings/presentation rollback.

## 11. Gate and exact next

R3 functional/test scope is technically complete through L4 on the verified head.

The next action is evidence-only PR closure metadata: verify the final PR head differs from `3941c01f...` only by this evidence file, then mark PR #41 ready for owner merge review.

Merge into canonical `main` remains an explicit owner gate. After an approved R3 merge/state closure, the sequential exact next is **R4 WooCommerce Presentation 2.0**. R5 remains blocked until both R3 and R4 setting interfaces are final.
