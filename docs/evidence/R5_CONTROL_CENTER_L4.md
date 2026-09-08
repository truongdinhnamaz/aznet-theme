# R5 Control Center + System Health — L1-L4 / Continuity Evidence

**Date:** 2026-09-08  
**Scope:** AZnet Theme v1.1 / R5 Control Center + System Health  
**Canonical base:** `main@0ddc6c799391d57db134f04449effdf511d41d1f`  
**Verified functional/test head:** `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c`  
**Verified PR synthetic merge commit:** `b6fd34958b085ead22770178411818080edb508c`  
**Verified tree:** `f66c2e21ab813d1e61bf28924bee25fca4325b35`  
**Branch:** `work/v1.1-r5-control-center-health`  
**PR:** #44

## 1. Result

R5 functional/test bytes satisfy the planned Theme-owned L1-L4 and update/theme-switch continuity gate on the verified head.

Delivered Theme-owned presentation administration:

- WordPress-native `custom-logo` support;
- admin-only AZnet Theme Control Center guarded by `edit_theme_options`;
- screen-scoped Control Center stylesheet;
- Overview / Design / Header / Commerce / System Health sections;
- Quick Setup for Theme-owned visual and Header presentation settings plus links to native WordPress Logo/Menu/Page editor surfaces;
- nonce/capability-protected save and explicit reset;
- bounded JSON settings import/export, maximum 64 KiB;
- read-only System Health using WordPress APIs and public WooCommerce/RootProfile capability functions;
- sanitized support snapshot;
- Commerce section only when the public Woo capability is available;
- ConvertFlow capability reported as `unknown` because no Theme-consumable public capability is established for this R5 scope.

R5 reuses the single normalized `aznet_theme_settings` Theme Mod. It does not create a second Theme settings store.

## 2. Ownership and safety boundary

R5 remains presentation-only.

It introduces no:

- Page, Menu, Product, Profile or Journey creation workflow;
- custom logo or menu data store;
- provider private storage/API read;
- WooCommerce commerce state ownership;
- RootProfile identity/profile state ownership;
- ConvertFlow Journey/resolver/conversion state ownership;
- secret/private provider data in import/export or support snapshot.

Logo and Menu remain WordPress-native. Provider capability diagnostics are public/read-only. Theme switching does not become a domain-data migration mechanism.

## 3. RED -> GREEN and debugging history

### Admin-form boolean normalization

The strengthened R5 contract exposed a real production defect: HTML form values `"0"` / `"1"` were not normalized by the Theme settings boolean path, so an unchecked Header boolean could fail to persist as `false`.

The minimal production fix extended the existing strict boolean normalizer to accept only boolean, integer `0|1`, and string `"0"|"1"` inputs. No settings schema or ownership boundary changed.

The final authenticated browser matrix proves `header_search=false` and `header_utilities=true` persist through the real admin form.

### Missing interactive R5 controls

The first R5 checkpoint also lacked several planned visible behaviors. The static contract was strengthened and failed before implementation. Minimal GREEN added:

- Header search/utilities checkboxes;
- Quick Setup save form;
- explicit reset confirmation checkbox;
- sanitized Support Snapshot output.

The contract also retains no-content/domain-creation and sensitive-leakage guards.

### Support Snapshot accessibility defect

The first authenticated browser/a11y run reproduced an axe `label` violation on the readonly Support Snapshot `<textarea>`.

This was a real Theme-owned R5 accessibility defect. The minimal production fix added an accessible label to that textarea. Subsequent exact-head clean-WP and Woo-present axe checks passed with zero blocking critical/serious violations in the Control Center scope.

### External stock-theme fixture dependency

A later continuity run failed because the harness attempted to install `twentytwentysix`, which the WordPress theme repository did not provide in that environment. This reproduced the same fixture problem already resolved in the retained G6 lifecycle evidence; no Theme lifecycle defect was reproduced.

The R5 harness was corrected to create a deterministic local alternate Theme fixture (`r5-switch-target`) inside the disposable WordPress runtime. This preserves the behavior under test — switch away from AZnet Theme and back — without an unrelated external package dependency.

No production Theme behavior changed for this correction.

### Repeated-login browser harness flake

Another run passed the full 1440 clean matrix, then timed out before the 1024 matrix while opening WordPress login again. Artifact inspection showed the second login form had an autofill mismatch and the runtime received no second login POST.

The browser harness was corrected to authenticate once, capture Playwright storage state, and create each viewport context from that authenticated state. This removes repeated-login noise without weakening any Control Center assertion.

No production Theme behavior changed for this correction.

## 4. Exact verified head and PR merge-byte equivalence

Verified functional/test head:

`d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c`

GitHub PR checks executed against synthetic merge commit:

`b6fd34958b085ead22770178411818080edb508c`

Both commits have the exact same Git tree:

`f66c2e21ab813d1e61bf28924bee25fca4325b35`

A direct compare from functional/test head to the synthetic merge commit returned no changed files. Therefore the tested PR-merge bytes and the verified branch-head bytes are tree-identical.

## 5. Fresh exact-head workflow matrix

Fresh lookup for `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` returned **18/18 completed SUCCESS**:

- R5 Control Center Static Contracts — `34179159272`;
- R1 Design System Static Contracts — `34179159292`;
- G Core Release Candidate Package — `34179159270`;
- R4 WooCommerce Presentation Static Contracts — `34179159191`;
- R2 Pattern Library Static Contracts — `34179159226`;
- R3 Header Static Contracts — `34179159192`;
- F Homepage Native Regression Package — `34179159290`;
- G Core Browser A11y Performance — `34179159239`;
- R1 Visual Preset Feasibility — `34179159266`;
- G Core Static Release Gate — `34179159321`;
- G Core WordPress Clean Runtime — `34179159308`;
- G Core Lifecycle Update Switch Rollback — `34179159306`;
- F Homepage Native Runtime Browser — `34179159233`;
- R1 Design System Browser Parity — `34179159225`;
- R5 Control Center Browser Quality — `34179159335`;
- R3 Header Browser Quality — `34179159274`;
- R2 Pattern Library Browser Quality — `34179159219`;
- R4 WooCommerce Presentation Browser Quality — `34179159286`.

R5 browser job `101914503265`: **SUCCESS**.

## 6. Runtime/browser environment

R5 browser/continuity verification used:

- WordPress `6.9`;
- PHP `8.1.34`;
- MySQL `8.0.46`;
- WooCommerce `10.9.4` for Woo-present checks;
- Node `22.23.2`;
- Playwright `1.55.0` / Chromium `140.0.7339.16`;
- axe Playwright `4.10.2`.

## 7. Authenticated Control Center matrix

### Clean WordPress / Woo absent

Viewports:

```text
1440x1000
1024x768
```

Result: **2/2 PASS**.

At both viewports:

- horizontal overflow: `0px`;
- keyboard focus target active;
- visible outline/focus shadow present;
- Control Center axe blocking critical/serious findings: `0`;
- no recorded harness failure.

The clean matrix also proves the Commerce tab is absent when public Woo capability is absent.

### WooCommerce 10.9.4 present

Viewports:

```text
1440x1000
1024x768
```

Result: **2/2 PASS**.

At both viewports:

- horizontal overflow: `0px`;
- keyboard focus target active;
- visible outline/focus shadow present;
- Control Center axe blocking critical/serious findings: `0`;
- no recorded harness failure.

The Woo-present matrix proves the capability-gated Commerce tab appears and its Theme-owned catalog setting persists.

## 8. Functional admin behaviors proven

Authenticated browser verification proves:

- AZnet Theme admin menu exists on the expected screen;
- Control Center CSS is loaded on that screen;
- native WordPress Logo/Menu/Page-editor links are present;
- Quick Setup persists visual and Header presets;
- Header boolean settings persist `false` and `true` correctly;
- JSON export contains the AZnet Theme product/schema markers and normalized Theme settings;
- import accepts valid bounded Theme settings and ignores an unknown key through canonical normalization;
- explicit reset returns Theme presentation settings to normalized defaults;
- System Health renders environment/theme/configuration/public-capability diagnostics;
- Support Snapshot JSON is parseable and retains `convertflow=unknown` rather than privately inferring capability.

## 9. Update and theme-switch continuity

The R5 workflow first installs the prior R4 Theme bytes under the stable `aznet-theme/` directory, then creates:

- WordPress-native Custom Logo;
- WordPress-native Primary Menu assignment;
- a published Page sentinel;
- normalized `aznet_theme_settings` sentinel values;
- an opaque external-provider option sentinel that does not model or inspect provider internals.

It then replaces the Theme directory in place with exact R5 candidate bytes.

Result: **PASS** — Theme settings, Logo, Menu, Page and external opaque sentinel survive the in-place update.

The workflow then switches to deterministic local alternate Theme `r5-switch-target` and back to AZnet Theme.

Result: **PASS** — WordPress content and opaque external sentinel survive while switched away; on return, AZnet Theme presentation settings, Custom Logo and Primary Menu assignment are still available.

This proves Theme switching does not delete domain/external state. It does not claim provider-specific migration behavior.

## 10. Runtime error boundary and artifact

Final R5 browser artifact:

- name: `r5-control-center-browser-quality`;
- artifact ID: `10038310891`;
- artifact digest: `sha256:1165841439609d572de3418d988b7e38c7ece9c9b7e6cbc376f3aa1057893eaf`;
- artifact size: `104372` bytes.

Independent artifact inspection confirms:

- clean summary: 2/2 PASS, `failures=[]`, overflow `0`;
- Woo summary: 2/2 PASS, `failures=[]`, overflow `0`;
- server log contains no `PHP Fatal error`, `PHP Warning`, `PHP Parse error` or `Uncaught` marker from the R5 run.

## 11. PASS boundary

**R5 Control Center + System Health: L1-L4 / update-theme-switch continuity PASS on the verified functional/test bytes.**

This evidence does **not** promote Theme release metadata and does not establish a Git tag/GitHub Release. Theme version promotion and deterministic final v1.1 release closure remain R6 responsibilities.
