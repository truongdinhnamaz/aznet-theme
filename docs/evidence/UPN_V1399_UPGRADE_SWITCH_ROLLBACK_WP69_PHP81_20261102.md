# AZnet Theme 1.3.99 — Real WordPress upgrade/switch/rollback QA — 2026-11-02

## Scope and provenance
Canonical repo `truongdinhnamaz/aznet-theme`, PR #557 candidate. Test blueprint: `tests/wordpress/upn4-v1399-upgrade-rollback-blueprint.json` introduced at immutable commit `d08ed9e5dd5fefbfd0c9fac68ee7280083d43822`. Within blueprint the **candidate source** was pinned to exact Git commit `31f845942aae7d3d180edb3f0bed31379e2e8ded`; no moving branch. The older baseline installer is the published official `v1.3.98` ZIP asset (654161 bytes; GitHub release asset digest `sha256:dcca1a3a84707b0ec1ea7c11a75870ece44fba6e96dd0b75494c052b47341360`).

Live disposable WordPress Playground execution: https://agent.tinyfish.ai/runs/d9ecd59c-5374-48fa-aae9-a106d9b033d5 .

## Actual runtime and report
- WordPress **6.9.11**, PHP **8.1.34**; single-site test environment, **not production**.
- Test report located in disposable Playground: `/wp-content/uploads/azt-upgrade-rollback-report.html`.
- **STATUS=PASS**. Output included `AZT_BASELINE_PASS`, `AZT_UPGRADE_PASS`, `AZT_SWITCH_AWAY_PASS`, `AZT_SWITCH_BACK_PASS`, `AZT_ROLLBACK_PASS`.
- Verified baseline Theme v1.3.98 (official ZIP), pinned source-based overwrite to candidate 1.3.99, active Theme switching to Twenty Twenty-Five and back, and recovery to old v1.3.98 official ZIP.
- WordPress-owned native Page `post_content`, `postmeta`, a native option, and AZnet Theme customizer Theme mod survived every transition. During candidate verification, updater module Git blob SHA matched `a3f118fb987aad11e7072475dfd80e1ac16c4d37`.
- This is a functional upgrade/Theme-switch/rollback proof using the **Git candidate directory**, plus the existing v1.3.98 ZIP. It is **not a test of installing the exact deterministic 1.3.99 ZIP**.

## Companion QA already obtained on same candidate product bytes
- Independent Git checkout at `e270f27f6a77dbfbf2737a5cf7807787a0e803bc` (same product files as later docs/test-only commits): full V1 Core regression PASS, exit 0, PHP CLI 8.4.27; 166 production PHP files linted.
- Deterministic `1.3.99` ZIP produced twice, byte-identical, 243 files, local SHA-256 `ffcdc52d68cabf41d72837166e343b98a29f1a414c512471035fe3e2f042a369`; `unzip -t` PASS.
- X6 release 1.3.98 → 1.3.99 fail-closed metadata contract RED before promotion then GREEN on candidate. No domain-owner code/state changed.

## Explicit hold point
- **CI exact-head: BLOCKED**, GitHub Actions fails before any job step and retry did not recover. No trusted exact-head GitHub CI PASS.
- **Exact 1.3.99 ZIP install/rollback on WP support floor: UNKNOWN.** Candidate Git-based overwrite does not prove ZIP path. Rerun with byte-reviewed 1.3.99 ZIP before claiming installer gate PASS.
- **Production release, release tag, merged main, customer-site deployment:** NOT performed and not implied by this QA.
- **WordPress hosted MySQL variants/multisite/other provider overlay:** not covered by this disposable Playground result.

## Next
Resolve GitHub Actions infrastructure startup gate or obtain an explicitly approved audited substitute that meets the AZT-05 release constitution; separately verify the exact candidate 1.3.99 ZIP using WordPress native install/upgrade+rollback, then merge PR #557 under existing gate, reverify exact-main package/CI, and publish new immutable v1.3.99 GitHub Release only with verified SHA256SUMS. Production site rollout requires separate site-specific approval and rollback.
