# U0 Control Center Final Candidate Verification

Date: 2026-09-03
Scope: AZnet Theme U0 Control Center foundation
Production behavior candidate: `bedc61af6401cb1ed387be3c242139d585b0800b`
Verification branch: `test/u0-final-candidate`
Verification PR: #21 (test-only; must remain unmerged)

## Fresh closure run

- GitHub Actions run: `33756061320`
- Job: `100650641122`
- Test-harness head: `24b491f4dae20d5f0716a5796b0d75b0ec03f574`
- Artifact: `u0-final-candidate-evidence`
- Artifact ID: `9893534809`
- Artifact SHA-256: `3a57e4721fa313a6a1f3783fe52f5847d2c29663b22c43a31097582d04e18e54`

## Runtime matrix

- WordPress: `7.1`
- PHP: `8.1.34`
- MySQL service: `8.0`
- WooCommerce tested: `11.0.1`
- Prior Theme source: `main@540669b440e7d36c1f2a6f33bef2fec80cc60fff` (`0.1.0-alpha.7`)
- Final U0 behavior candidate: `bedc61af6401cb1ed387be3c242139d585b0800b`
- Candidate Theme header remains `0.1.0-alpha.7` pending owner version/release gate
- Node: `22.23.2`
- Playwright: `1.55.0`
- Chromium: `140.0.7339.16`

## Fresh L1-L2 PASS scope

Run `33756061320` passed:

- U0 settings source contract
- U0 admin Control Center contract
- U0 admin write security contract
- U0 admin asset scope contract
- U0 ownership static contract
- U0 native custom-logo support contract
- W1 WooCommerce absent capability regression
- W1 WooCommerce normalized surface regression
- W1 WooCommerce generic asset scope regression
- E5-B ownership/no-takeover regression
- syntax checks for all six changed/in-scope production PHP modules
- final browser harness JavaScript syntax

## In-place alpha.7 upgrade continuity PASS scope

The runtime first installed the exact previous pilot source under directory `aznet-theme/`, activated it, and seeded only test fixture state:

- primary menu location
- native `custom_logo` Theme Mod
- `aznet_theme_settings`
- one unrelated test sentinel used only to detect accidental destructive writes

The same directory was then replaced in place with the final U0 behavior candidate. Fresh runtime assertions passed:

- Theme remained active under `aznet-theme/`
- native `custom-logo` Theme support is available
- `custom_logo` value remained `4242`
- primary menu location remained assigned
- `aznet_theme_settings` remained present and readable through `AZnet\Theme\Settings\get()`
- unrelated database sentinel remained unchanged
- Woo public capability reports unavailable while inactive
- Woo public capability reports available while active
- candidate PHP package tree linted successfully

This negative sentinel is not provider-integration evidence and does not imply L5.

## Candidate package verification

The verification run produced a non-release candidate package solely to prove packaging continuity.

- Candidate package SHA-256: `6fa94c3eaced6f58815f1cbbb79341c2bba8512327ca49b339cd5aaf520551a8`
- Root directory: exactly `aznet-theme/`
- Development paths absent: `.git/`, `.github/`, `docs/`, `tests/`, `scripts/`
- Packaged PHP files: 32
- `style.css` version: `0.1.0-alpha.7`
- `AZNET_THEME_VERSION`: `0.1.0-alpha.7`

This package is verification evidence only. It is not the alpha.8 release package because the version bump/merge/release checkpoint requires explicit owner approval.

## Final browser / visual / a11y PASS scope

Authenticated Control Center browser verification passed for both agreed viewports:

| Viewport | Size | Horizontal overflow | Keyboard-visible focus | Axe critical/serious | Native Logo workflow |
| --- | --- | ---: | --- | ---: | --- |
| desktop | 1440x1000 | 0px | PASS | 0 | PASS |
| compact | 1024x768 | 0px | PASS | 0 | PASS |

For both viewports the browser verified visible Control Center content/actions, followed the `Thiết lập Logo` action to WordPress `customize.php`, and confirmed the real `#customize-control-custom_logo` control is visible.

The downloaded artifact was independently inspected. `browser-summary.json` contains exactly two passing cases; both Axe JSON files contain zero `critical`/`serious` violations.

The following screenshots were opened and manually reviewed:

- `control-center-1440x1000.png`
- `control-center-1024x768.png`

No clipped card, hidden primary action, destructive overlap, admin-chrome collision, or horizontal overflow was observed at the tested viewports.

## Runtime safety

The final run reports:

- no `PHP Fatal error`
- no `PHP Warning`
- no `Uncaught` error

A WooCommerce translation-loading `Notice` can appear during WP-CLI capability probing. It is not used as integration evidence and does not change the U0 fatal/warning gate.

## Historical harness failures

Two test-harness issues were identified and fixed without changing Theme behavior:

1. a prior settings expectation incorrectly expected `sanitize_key('BAD VALUE')` to produce `bad-value`; WordPress correctly produces `badvalue`;
2. final packaging initially changed directory to `wp-content/` while the Theme lives at `wp-content/themes/aznet-theme`; the corrected harness packages from `wp-content/themes/`.

The native `custom-logo` omission was a real Theme presentation/usability defect discovered before final closure. It was fixed by adding WordPress `custom-logo` Theme support and a regression contract before this fresh final run.

## U0 result boundaries

Proven by the fresh final candidate evidence:

- L1 static for U0 and named invalidated regressions: PASS
- L2 contract/TDD for U0 and named invalidated regressions: PASS
- L3 runtime for the final U0 behavior candidate: PASS for tested U0/upgrade/capability scope
- L4 browser/visual/a11y for the final U0 behavior candidate: PASS for the agreed Control Center matrix
- in-place update continuity from alpha.7 source to U0 behavior candidate: PASS in the isolated runtime

Not implied:

- L5 RootProfile/ConvertFlow/provider integration
- E5-C RootProfile current-surface integration closure
- U1 Design/Presets
- U2 Header/Footer Composer
- U3 Content/Woo presentation options
- U4 Gutenberg Patterns
- U5 Integrations/Import-Export
- U6 broad customer-ready/release completion
- production deployment to any customer website

## Next gate

The next action is an owner release/merge checkpoint for U0:

1. open/review the production U0 pull request against current `main`;
2. after explicit owner approval, bump the same Theme installation line from `0.1.0-alpha.7` to `0.1.0-alpha.8` in both `style.css` and `functions.php`;
3. fresh version-consistency/package verification;
4. merge only the production U0 PR;
5. post-merge verification and produce the installable `aznet-theme/` alpha.8 pilot ZIP.

Test-only PR #21 must not be merged.
