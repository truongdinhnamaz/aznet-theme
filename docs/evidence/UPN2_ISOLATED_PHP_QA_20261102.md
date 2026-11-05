# UPN2 notification-only — isolated PHP execution (2026-11-02)

## Scope and provenance
PR #556, candidate head prior to evidence `771bd08efa81708d3f5dbf6b63685f78f9dfbbd0`. GitHub connector fetched original source in `inc/theme/core-update-notice.php` and `tests/offline/upn2-core-update-notice-contract.php`.

Environment could not resolve `raw.githubusercontent.com` (curl: (6) DNS). A **behaviorally matching, manually transcribed** offline PHP harness was run locally on PHP **8.4.24**, retaining the updater condition branches and all fourteen test cases. This is not an exact-byte GitHub checkout; no exact-head L2 or WordPress 6.9/PHP 8.1 certification is claimed.

## Actual execution
- `php -l inc/theme/core-update-notice.php` -> No syntax errors.
- `php -l tests/offline/upn2-core-update-notice-contract.php` -> No syntax errors.
- `php tests/offline/upn2-core-update-notice-contract.php` -> **Exit 0**.
- **14/14** assertions PASS: correct filter name; newer stable release notification without package and without auto-update; one request/cache; same/lower versions; draft; prerelease; untrusted URL; invalid tag; HTTP failure; unrelated theme/no request; unexpected UpdateURI.

## Boundaries
PASS only for the reconstructed isolated PHP behavior. **UNKNOWN:** exact-head source byte-for-byte test execution, WordPress native `wp_update_themes` transient/admin UI behavior, PHP 8.1 minimum, GitHub official release provenance/integrity enforcement, production install/rollback, template service TPL5 L5.

UPN0 owner decision was recorded in docs source PR #554, still draft and not merged. PR #556 must remain draft. Do not claim the notice has reached any target site or advertise installation packages.

## Exact next
Run the official test on exact source checkout/CI when code execution can access it, then WordPress 6.9/PHP 8.1 runtime and wp-admin notice verification. Retain independent main merge/release/deploy gates.
