# W8 ConvertFlow Coexistence / Integration Verification

Date: 2026-09-03
Repository: `truongdinhnamaz/aznet-theme`
Base main: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`
Work branch: `work/w8-convertflow-coexistence`
Production-code candidate: `615a35b576e28ee22aacc9524fcc37bd5f1e0a29`
Pre-evidence work head: `9adc990c3eb20474f4bf1ebe213d4e874cd0d610`
QA layer: bounded L5 public-contract coexistence/integration

## Scope

W8 verifies the current AZnet Theme Woo presentation track can coexist with the current ConvertFlow public Theme Integration Contract without transferring domain ownership or reading ConvertFlow private implementation/storage.

Ownership remains unchanged:

- AZnet Theme owns presentation and its semantic presentation values.
- WooCommerce owns Product facts, variation, price, stock, cart, checkout, order and account/auth truth.
- ConvertFlow owns Product Journey, Filter, Fit, Fast Conversion and conversion semantics.
- Theme does not copy ConvertFlow resolver/state/validation/analytics or target ConvertFlow-owned DOM.

## Current ConvertFlow dependency identity

Verified against `truongdinhnamaz/convert-flow` current main:

- ConvertFlow main: `228a2c511223c9f3394e72956b42bebb6e51ff0e`
- `choiceguide/src/Frontend/ThemeIntegration/ThemeIntegrationAssets.php`
  - Git blob: `35e0e474766520f06670991e7e9622f485505786`
- `choiceguide/assets/css/theme-integration.css`
  - Git blob: `f1d2a93583496f49515d8062ee8bf900823e9f86`
- `choiceguide/src/Frontend/Assets.php`
  - Git blob: `0891c46e0029dd4edc2034ed04a1a02ae5cf2eb8`

The current ConvertFlow contract explicitly defines a Theme Integration Contract v1 consumer bridge: compatible themes may expose the public `--convertflow-theme-*` vocabulary; ConvertFlow maps it only inside ConvertFlow-owned surfaces and retains fallbacks in its own stylesheets.

The exact current contract vocabulary contains 33 public presentation custom properties covering typography, colors, radius/shadow, spacing, button presentation, container geometry, motion and focus.

## TDD evidence

### RED

The W8 contract test was written before the production bridge existed.

After correcting a test-file namespace syntax mistake, the valid RED failed for the intended missing behavior:

```text
FAIL: ConvertFlow public theme-contract stylesheet is missing
```

Test commit:

`fb63095bcd2ec93209ab63420eb68fb6df289cb9`

### Minimal GREEN production change

Added only:

- `assets/css/integrations/convertflow.css`
  - current Git blob: `39115a63f00a369be72b40dd01dbd988e1d55cc5`
- one Theme-owned enqueue in `inc/theme/assets.php`
  - current Git blob: `f19e134137412fa79a4865824c89a76c5024ce8a`

Production commits:

- `ebbf26ef2dffb05b6256fefc43bc066ddf9d608d` — expose ConvertFlow Theme Integration Contract v1 presentation variables.
- `615a35b576e28ee22aacc9524fcc37bd5f1e0a29` — enqueue the Theme-owned public bridge after AZnet semantic tokens.

The bridge:

- exposes exactly the 33 current public `--convertflow-theme-*` properties;
- maps them to AZnet Theme semantic tokens or bounded presentation literals;
- does not use `.choiceguide-*` selectors;
- does not enqueue or own ConvertFlow plugin assets;
- does not read plugin options/meta/tables/private classes;
- remains harmless when ConvertFlow is absent because it is only a small CSS custom-property projection.

## Exact-provider coexistence verification

Integration harness:

`tests/integration/w8-convertflow-coexistence.php`

Git blob:

`9fd9241cdeebf61088b92d9c7810d1498b3a064e`

The harness verifies the exact current ConvertFlow Git blobs listed above, compares the provider vocabulary with the Theme vocabulary, checks the real ConvertFlow frontend asset dependency chain, and exercises the bounded matrix:

- ConvertFlow absent + Woo surface absent;
- ConvertFlow public Theme Integration asset present;
- Woo Product presentation + Theme public bridge coexistence;
- provider asset ownership remains in ConvertFlow;
- no Theme private/runtime coupling.

Fresh exact-byte command:

```bash
CONVERTFLOW_ROOT=/mnt/data/w8-convertflow-actual php tests/integration/w8-convertflow-coexistence.php
```

Fresh output:

```text
PASS: W8 exact-byte ConvertFlow coexistence matrix (provider absent/present, Woo off/on)
```

Fresh companion verification:

```text
PASS: W8 ConvertFlow public theme-contract bridge
No syntax errors detected in inc/theme/assets.php
No syntax errors detected in tests/integration/w8-convertflow-coexistence.php
```

Fresh production/test Git blob checks matched the branch values:

- `inc/theme/assets.php` → `f19e134137412fa79a4865824c89a76c5024ce8a`
- `assets/css/integrations/convertflow.css` → `39115a63f00a369be72b40dd01dbd988e1d55cc5`
- `tests/integration/w8-convertflow-coexistence.php` → `9fd9241cdeebf61088b92d9c7810d1498b3a064e`

## Retained Woo regression closure

The first two GitHub Actions attempts exposed retained-test scope drift rather than production defects.

### Run `33760728808`

W8 contract passed, then the historical W6 ownership gate rejected the literal word `convertflow` anywhere in shared `inc/theme/assets.php`.

### Run `33760898763`

After scoping W6 correctly to W6-owned Account files, W2-W5 asset regressions passed and the historical W5 ownership gate revealed the same over-broad shared-file assumption.

Root cause: earlier W2-W6 ownership tests treated the shared Theme asset registry as if it could never gain a later public integration. That assumption became invalid when W8 legitimately added a public presentation bridge.

Test-only correction:

- retained W2-W6 gates still forbid private `choiceguide_` coupling and forbidden Woo/domain behavior;
- ConvertFlow coupling remains forbidden in each Woo surface's own PHP/CSS;
- shared `inc/theme/assets.php` is allowed to register the new public Theme-owned integration projection.

No W8 production byte changed while fixing these retained test harnesses.

### Fresh retained regression run

Test branch: `test/w8-convertflow-coexistence-v2`
Test-harness head: `3e119fe74b8a0f4953a471d6d6831ab5bd2ef57d`
GitHub Actions run: `33761153314`
Job: `100667509616`
Result: `success`
PHP: 8.1

Successful steps include:

- W8 public Theme contract;
- retained W6 regression chain;
- retained W2-W5 ownership gates;
- W8 integration harness lint;
- private ConvertFlow coupling rejection;
- no ConvertFlow-owned DOM selector targeting.

## PASS scope

W8 is PASS for the bounded L5 public-contract coexistence boundary defined by the current execution map:

- current ConvertFlow Theme Integration Contract v1 vocabulary is consumed/provided through a public presentation boundary;
- ConvertFlow absent does not break Theme presentation;
- current ConvertFlow public integration code can register independently without Theme taking ownership;
- Woo Product presentation remains present alongside the public bridge;
- retained Woo presentation ownership tests remain valid after test-scope reconciliation;
- Theme does not copy Journey/Filter/Fit/Fast Conversion semantics or private ConvertFlow implementation.

## Not inferred / not claimed

This W8 result does **not** claim:

- a new full WordPress L3 runtime run for W8;
- a new browser/visual/a11y L4 run for W8 (Woo W7 already owns the retained Woo L3/L4 evidence);
- full ConvertFlow business/Journey runtime behavior validation;
- W9 L6 package/release completion;
- production deployment;
- E5-C RootProfile unblock;
- E5-D Profile/Contact takeover;
- Milestone F Homepage Shell completion.

## Next

W9 — L6 release closure is now the next Theme-owned Woo workstream action.

W9 may prepare full regression, package/SHA/unzip-reverify, source/provenance updates and rollback evidence. Merging into `main` remains a separate owner approval gate.
