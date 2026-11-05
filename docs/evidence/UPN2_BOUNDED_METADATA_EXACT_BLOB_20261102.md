# UPN2 — Bounded remote metadata RED→GREEN / exact Git blob QA (2026-11-02)

## Scope and source of truth
Repository: `truongdinhnamaz/aznet-theme`. Candidate: draft PR #556, branch `work/upn2-notification-only-20261102`. Production remains unchanged. UPN0 owner acceptance is on source PR #554 (separate merge gate).

## Defect class and RED
The existing `wp_remote_get()` fetched a GitHub Release JSON body without a maximum response size. Added a bounded-data assertion into the existing isolated test. The test initially terminated with **exit 1** at:

```
FAIL release metadata response capped at 256 KiB
```

No production code change preceded this RED verification.

## Minimal GREEN
Added `'limit_response_size' => 262144` (256 KiB) to the fixed-HTTPS GitHub Releases metadata HTTP request. Oversized/truncated JSON yields no update notice (fail soft); no package URL and no automatic installation are introduced.

Extended two isolated access-path tests: an unauthorized actor does not see the notice or cause a network request; an absent `aznet-theme` install neither shows a notice nor causes a network request. The test mock `current_user_can` / `WP_Theme::exists` conditions are controllable.

## Exact-byte provenance
The local executed files' `git hash-object` values exactly match GitHub connector's returned content blob SHA after writes:

- `inc/theme/core-update-notice.php`: `a3f118fb987aad11e7072475dfd80e1ac16c4d37`
- `tests/offline/upn2-core-update-notice-contract.php`: `e32f662d3502a0124a54698f532bd68078607017`
- `tests/offline/upn2-wp-global-stub.php`: `7fa7bb0cca9c71f9fa2d8ecb4a6b6fa276b2c161`

GitHub code commits: RED test `664f50492b8e935bfc4ac6c76b9642eca6501f8b`, minimal GREEN `3b596592324466e1d42b8d03cb3e0bda607c58aa`.

## Fresh execution
- PHP CLI **8.4.24** (not the PHP 8.1 minimum).
- All three files `php -l`: **3/3 PASS**.
- `php -d display_errors=1 -d error_reporting=32767 tests/offline/upn2-core-update-notice-contract.php`: **22/22 PASS**, **exit 0**.
- No prior PASS cases were removed.
- WordPress HTTP request bound documented at https://developer.wordpress.org/reference/classes/WP_Http/request/ ; update-host hook at https://developer.wordpress.org/reference/hooks/update_themes_hostname/ .

## Gate interpretation
**PASS: exact-blob isolated L2 PHP (22/22), bounded metadata request, permission and missing-theme regression.** Full Git clone did not run because container DNS cannot resolve GitHub; the three executed source bytes were independently authenticated using Git blob hashing.

**UNKNOWN/BLOCKED:** WordPress 6.9/PHP 8.1 runtime; authenticated wp-admin L3/L4; real network and newer official release positive UI; UPN4 package integrity/rollback and Template Distribution Service TPL5. No WordPress installation, Docker, PHP 8.1 or external DNS access was present in the current executor. No merge, release, deployment or site mutation.

## Next
Run **UPN3** in an authorized disposable WordPress 6.9+/PHP 8.1+ installation with controlled official-release metadata: same-version no notice, newer stable release read-only notice, unauthorized no notice, missing-theme no notice, and no actionable WordPress updater button. Preserve independent source PR #554 and code PR #556 merge gates.
