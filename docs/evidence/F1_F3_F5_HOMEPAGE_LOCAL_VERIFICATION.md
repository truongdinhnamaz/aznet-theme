# F1 / F2 / F3 / F5 Homepage Local Verification

Date: 2026-09-05
Branch: `work/f-homepage-shell-parallel`
Base main: `2f22e7d11e6856f9635725a49cc32cdddc01ec9c`

## F1 — Native WordPress front-page shell

### RED

A new contract was written before production implementation:
`tests/offline/f1-front-page-native-shell-contract.php`.

Fresh RED execution against a tree with no `front-page.php`:

```text
FAIL: front-page.php missing
EXIT=1
```

This is the intended failure: the Theme did not yet own an explicit front-page template.

### GREEN

Minimal production change: add `front-page.php` only.

The template:
- uses the existing Theme Header and Footer;
- exposes exactly one Theme-owned `<main id="main" class="aznet-theme-main ...">` shell;
- uses the native WordPress loop;
- preserves `the_content()` as the replaceable Page-body boundary;
- does not detect or call ConvertFlow;
- does not read plugin storage/private internals;
- does not claim or infer a Page by slug/title/Page ID.

Fresh local verification:

```text
PASS: F1 native front-page shell contract
No syntax errors detected in front-page.php
```

F1 claim: **PASS L1-L2**. Runtime/browser/integration are not inferred.

## F2 — WordPress body integration boundary

Contract: `tests/offline/f2-front-page-content-boundary-contract.php`.

The harness stubs WordPress `the_content()` to emit a Journey-body sentinel and verifies that it appears exactly once inside the Theme-owned main shell, while Header/Footer remain Theme-owned outside the replaceable Page body.

Fresh local result:

```text
PASS: F2 WordPress body integration boundary contract
```

F2 claim: **PASS L2** for the public WordPress body boundary. This is not an actual ConvertFlow runtime claim.

## F3 — No Journey semantics clone / ownership static gate

Contract: `tests/offline/f3-homepage-ownership-static-contract.php`.

Production F delta is scanned for forbidden coupling including:
- `choiceguide_*` / `_choiceguide_*` domain identifiers;
- plugin option/meta/table access;
- Homepage resolver/event names;
- canonical Journey section keys;
- takeover hooks.

Fresh local result:

```text
PASS: F3 Homepage ownership static contract
```

F3 claim: **PASS L1**. Theme owns shell/composition only; ConvertFlow Journey semantics/state/analytics/conversion remain outside Theme.

## F5 — ConvertFlow absent / native fail-soft path

Contract: `tests/offline/f5-homepage-absent-failsoft-contract.php`.

The harness defines only WordPress template primitives; no ConvertFlow function/class/hook exists. Native Header, Page content, and Footer all render.

Fresh local result:

```text
PASS: F5 Homepage absent/native fail-soft contract
```

F5 claim: **PASS L2 for the Theme-native absence path**. The Theme does not and must not attempt to catch/reinterpret arbitrary failures inside a foreign `the_content` filter; actual ConvertFlow provider/error isolation remains source-owner behavior and requires integration evidence later.

## F4 dependency checkpoint

PR #24 is still draft/open/unmerged and already implements the AZnet producer mapping for Theme Integration Contract v1 in `assets/css/integrations/convertflow.css` and `inc/theme/assets.php`.

Stream H does not modify or recreate those paths. **F4 remains BLOCKED on PR #24 owner merge decision** while independent F slices continue.

## BLOCKED / UNKNOWN

- No real WordPress L3 runtime has been claimed by these local contracts.
- No browser/visual/a11y L4 has been claimed.
- No actual ConvertFlow Save/Preview/Publish, analytics/conversion, or T2/T4/T5 L5 integration has been claimed.
- F4 remains blocked on unmerged PR #24.
- F9 release closure remains locked until required integration/dependency gates are resolved; `main` merge remains owner approval-gated.

## NEXT

Run the native Homepage candidate through a real WordPress L3/L4 test path if repository CI can do so without touching PR #24 production paths. In parallel, locate a current actual ConvertFlow integration fixture/artifact for F6/F7; if unavailable, record the exact external dependency rather than inventing a contract.
