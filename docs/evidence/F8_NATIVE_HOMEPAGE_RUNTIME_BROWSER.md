# F8 Native Homepage Runtime / Browser / A11y Verification

**Date:** 2026-09-07  
**Scope:** Milestone F / Homepage Shell / F8 native WordPress path  
**Repository:** `truongdinhnamaz/aznet-theme`

## Result

**PASS — L3 runtime + L4 browser/visual/a11y for the native Homepage path.**

This evidence verifies the Theme-owned WordPress fallback/shell with ConvertFlow lifecycle functionality absent. It does **not** prove F6/F7 actual ConvertFlow Save/Preview/Publish, resolver/state, analytics or conversion integration. It also does not close F9, Milestone F, E5-C/E5-D, deployment or release promotion.

## Candidate under test

- Canonical `main`: `11f66c1508254c69bba92f97e9725a08cc64e41e`.
- PR #27 head before F8 evidence-only commit: `efccda6e3442efd770b77680fdd2342b90336f52`.
- GitHub synthetic merge candidate: `4423699b2c0efe2f0c3457cf46c4240a1ad123c6`.
- Test-only branch: `test/f8-native-homepage-runtime-browser-20260907`.
- Workflow-only commit: `91a2ebe2dc640c7097ac5c2a43887b073f50a3fe`.

The test branch starts from the exact synthetic PR #27 merge candidate and adds only `.github/workflows/f8-native-homepage-runtime-browser.yml`; the workflow file is test infrastructure, not production Theme code.

## Runtime matrix

GitHub Actions run `34080758335`: **SUCCESS**.

Runtime environment:

- Ubuntu 24.04 runner.
- PHP `8.1.34`.
- WordPress `6.9` exact.
- MySQL `8.0` service.
- AZnet Theme activated from the candidate production tree.
- Static WordPress Page configured as the front page with a native sentinel body.
- ConvertFlow/RootProfile/WooCommerce plugins are not installed or activated in this native-path run.

## L3 assertions

PASS:

- Homepage returns HTTP 200.
- Native sentinel `F8 native homepage sentinel` is rendered.
- Theme Header is present.
- `main#main.aznet-theme-main--front-page` is present.
- Theme Footer is present.
- No PHP Fatal/Warning/Parse/Uncaught marker is present in the WordPress server log before or after browser verification.
- Canonical merged W8 ConvertFlow token stylesheet is loaded exactly once; no duplicate token stylesheet load is observed.

## L4 browser / responsive / accessibility assertions

Automated Playwright + axe verification PASS for all three required viewports:

- `1440x1000` — PASS.
- `1024x900` — PASS.
- `390x844` — PASS, including opened mobile navigation panel.

For each viewport the harness verifies:

- exactly one `main#main` landmark;
- no duplicate IDs;
- horizontal overflow <= 1px;
- first keyboard focus is the visible Theme skip link targeting `#main` with a visible focus indicator;
- mobile menu does not introduce horizontal overflow on the 390px viewport;
- axe `critical` + `serious` violations = 0;
- browser console errors = 0;
- page errors = 0;
- full-page screenshot captured.

Final automated result:

`PASS: F8 native Homepage browser/visual/a11y automated verification 3/3`

## Artifact

- Artifact ID: `10003616810`.
- Name: `f8-native-homepage-runtime-browser`.
- Artifact wrapper digest: `sha256:4f6409b435f515549687f0b7e7629cae9325c29a6ff9f03bcef7359457fe1237`.
- Contents include browser summary, three viewport screenshots, axe JSON reports, rendered Homepage HTML and WordPress server log.

## Ownership / performance conclusion

- F8 introduces no production bundle or provider adapter.
- Homepage production delta remains Theme-owned `front-page.php`; W8 token projection is inherited from canonical `main` rather than duplicated in PR #27.
- WordPress owns native Page content/query semantics.
- ConvertFlow remains owner of Journey body semantics, lifecycle, state, analytics and conversion behavior.

## Remaining gates

- **F6/F7:** still require actual/current ConvertFlow public integration evidence and remain UNKNOWN/BLOCKED until that runtime is available.
- **F9:** remains blocked by F6/F7 and cannot be claimed from the native F8 run.
- **PR #27 main merge:** remains an explicit owner approval gate.
- **E5-C/E5-D:** unchanged and outside this evidence scope.
