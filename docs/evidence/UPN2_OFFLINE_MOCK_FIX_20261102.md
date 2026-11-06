# UPN2 Offline QA correction — 2026-11-02

## Verified issue
An isolated test initially failed at "read-only admin notice renders" after its first 17 cases passed. Root cause: its mock `wp_get_theme()` was defined inside `AZnet\\Theme`, while production `function_exists('wp_get_theme')` correctly checks the **global** WordPress function. This is a test-fixture namespace defect, not proof of a production updater bug.

## Correction
PR #556 now uses `tests/offline/upn2-wp-global-stub.php` defining the global WordPress `wp_get_theme`, loaded by the isolated test. Production module remains unchanged in this corrective slice.

## Actual evidence
On PHP 8.4.24, a manually reconstructed local code/test fixture after the mock correction ran through all **19 isolated assertions**, exit 0; `php -l` reported no syntax errors for the global stub. All cases cover stable/newer metadata, cache, draft/prerelease, wrong URL/version/outage, unrelated update clients, native install suppression, read-only admin notice and no broken install link.

**Qualification:** local files were reconstructed from connector-read GitHub source and are not byte-verified against the GitHub blobs. Do not claim exact-head L2, WordPress 6.9/PHP 8.1 L3, real wp-admin or L6 publication. Git clone remains inaccessible from local runtime due to DNS resolution of github.com.

## Next
Run byte-exact test directly from PR #556 commit on a trusted PHP executor and real WordPress 6.9/PHP 8.1 admin. Keep source PR #554 and implementation PR #556 unmerged until their separate gates are met.
