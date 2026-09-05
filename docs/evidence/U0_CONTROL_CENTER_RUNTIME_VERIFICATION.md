# U0 Control Center Runtime Verification

Date: 2026-09-03
QA layer: L3 Runtime
Production code candidate: `4b4b79e179356cd9db8dca81064e748e931da883`
Verification branch: `test/u0-control-center-runtime`
Verification PR: #18 (test-only; must remain unmerged)

## Fresh closure run

- GitHub Actions run: `33751461107`
- Job: `100635734393`
- Test-harness head: `51f528f0e8a1336aa0dabe1605362c967a480356`
- Artifact: `u0-control-center-runtime-evidence`
- Artifact ID: `9891729202`
- Artifact SHA-256: `9dda6064376b51c6c23c047a556715ab2f758fc852ba723200eaabe90250bdf7`

## Runtime matrix

- WordPress: `7.1`
- PHP: `8.1.34`
- WooCommerce: `11.0.1`
- AZnet Theme header version: `0.1.0-alpha.7`
- MySQL: `8.0` service in isolated GitHub-hosted runtime

## PASS scope

Fresh run and downloaded artifact prove:

- Theme activates in a real WordPress runtime.
- `AZnet\Theme\Settings\get()` returns schema version 1 defaults.
- Theme settings normalization/write removes unknown top-level keys and normalizes `preset` through WordPress sanitization.
- Theme settings reset returns to safe defaults.
- Woo public capability adapter reports unavailable when WooCommerce is inactive.
- Woo public capability adapter reports available when WooCommerce is active.
- Frontend HTTP smoke returns successfully.
- Authenticated administrator session renders `wp-admin/admin.php?page=aznet-theme` with the expected Control Center content while WooCommerce is inactive.
- Control Center admin stylesheet loads on the Control Center screen and does not leak onto `themes.php`.
- After WooCommerce activation behavior is consumed through its own first admin HTTP request, the Control Center renders WooCommerce as `Sẵn sàng`.
- PHP error gate reports zero `PHP Fatal error`, zero `PHP Warning`, and zero `Uncaught` errors.

The downloaded closure artifact was independently inspected. Its Woo activation request headers show a real WordPress `302 Found` response to `wp-admin/admin.php?page=wc-admin`, explaining the previous empty first-response harness failure without any Theme production-code change.

## Non-blocking runtime observation

The runtime debug log contains one WordPress `PHP Notice` about WooCommerce text-domain loading during WP-CLI capability evaluation. The L3 gate for U0 is fatal/warning/uncaught safety; this notice is not used to claim an additional integration layer and did not originate from a U0 production mutation.

## UNKNOWN / not inferred

- L4 Browser / Visual / A11y is not implied by this L3 result.
- L5 provider/coexistence integration is not implied.
- L6 customer-ready release/package is not implied.
- RootProfile E5-C remains separately contract-gated.
- No customer site was deployed or changed by this verification.

## Provenance rule

This evidence commit must not modify production Theme bytes. Browser verification must continue from the exact production code candidate `4b4b79e179356cd9db8dca81064e748e931da883`, or from a tree proven production-byte equivalent to it.
