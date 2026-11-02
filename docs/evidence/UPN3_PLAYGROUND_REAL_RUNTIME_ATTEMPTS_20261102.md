# UPN3 Playground runtime — factual execution checkpoint — 2026-11-02

## Goal and constraints
Prove notification-only AZnet Theme behavior on actual WordPress 6.9+/PHP 8.1, then determine merge/release readiness. Do not touch production.

## Execution evidence (external live disposable WordPress Playground browser)
1. Run ID `b1a2c219-6a61-4e5b-b9d4-7c5cf377f9ee`: WP dashboard and AZnet Theme loaded; runPHP terminated exit 255 after 13 visible PASS lines (version, active Theme, pinned blob, native update filter). Overall **FAIL**.
2. Run ID `267521cd-2c21-4d68-baaf-cc7d2c9e22b2`: Playground displayed a runtime hiccup before PHP verification, so **NO runtime PASS**.
3. Run ID `0dab966c-37f6-488d-a230-f55cc8ff3755`: WP 6.9/PHP 8.1 booted. Test printed 15 PASS lines (including native admin notice action and updater capable admin) followed by `FAIL Installed version: no admin notice` and `UPN3_CAUGHT_FAILURE: RuntimeException`. **Root cause traced to test output capture:** mocked GitHub HTTP callback called `aznet_upn3_assert()` which printed a PASS line into `ob_start()` HTML capture. That test harness failure does not prove production regression.
4. Test-only fix commit `a1fe089d874936b669d7e4e4e80b956f940cbd54` removes success output inside the captured HTTP callback; blueprint updated at `c1e6f038234b5bd0abac368bec24c5adfc975f0b`.
5. Run ID `85ca7280-ccb6-4c54-9112-dc3f9b48909a`: WP dashboard with AZnet Theme loaded, but tool could not capture `runPHP` PASS/FAIL or PHP exit. **UPN3 remains UNKNOWN**, not PASS.

### Reliable results and boundaries
- **UPN2 L2 exact-Git-blob isolated PHP 22/22 PASS** remains valid. Its updater production blob `a3f118fb987aad11e7072475dfd80e1ac16c4d37` was not modified by UPN3 test-fixture changes.
- **UPN3 L3:** WordPress 6.9/PHP 8.1 successfully booted and loaded the pinned Theme and updater in multiple Playground runs; full read-only notification/cache/real native `wp_update_themes()` test **not PASS**.
- **UPN3 L4 browser notice:** UNKNOWN. A screenshot of an authenticated read-only newer-release notice and proof of no native install button are absent.
- **UPN4 package/install/rollback and release:** NOT STARTED.
- GitHub Actions checks on earlier commits failed before available job logs; no proven CI green for the exact candidate.
- Source PR #554 and work PR #556 are Draft/unmerged and require distinct source/main merge approval; no release/deployment approval was provided.

### Stop condition
Do not rerun Playground blindly or claim completion from the dashboard alone. Preserve checkpoint as recoverable. Next safe work is to run exact pinned UPN3 verifier in a runner exposing full stdout/stderr and PHP exit code, **then** obtain required source/merge/release approvals. If runtime cannot be demonstrated, retain BLOCKED and do not merge.
