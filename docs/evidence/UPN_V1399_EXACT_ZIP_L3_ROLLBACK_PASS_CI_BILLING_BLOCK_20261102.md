# AZnet Theme 1.3.99 — Exact ZIP upgrade/rollback PASS; GitHub CI billing lock (2026-11-02)

## Product/authority
- Canonical repository `truongdinhnamaz/aznet-theme`, PR #557 (Draft); branch `work/upn-notice-v1399-release-candidate-20261102`.
- Main before release remains `83379de31e893e049f92fc632c8490e7a6788bd4` (Theme v1.3.98); latest official GitHub Release is `v1.3.98`.
- Owner asked to release v1.3.99, **but existing AZT-05 Core release constitution and AZT-04 exact-head CI gates remain in force**. No permission to falsify CI PASS or silently override a failed release gate.

## Exact candidate package provenance
- Version transition only in `style.css` and `functions.php`; X6 1.3.98→1.3.99 fail-closed contract, historical versions retained.
- ZIP reproducibly built **twice**, 243 files, identical bytes, from real Git checkout of candidate code commit `e270f27f6a77dbfbf2737a5cf7807787a0e803bc`.
- SHA-256: `ffcdc52d68cabf41d72837166e343b98a29f1a414c512471035fe3e2f042a369`; file length 655885 bytes, unzip integrity PASS.
- On later exact candidate branch commit `d9238b639414a3fd925e62335e79760109c15c08`, independent Git checkout rebuilt the 243-file ZIP again; hash **identical**. New commits since production-code checkpoint only modify excluded docs/tests; no product ZIP drift.
- Source-only V1 Core verification `bash scripts/verify-v1-core.sh` passed with exit 0 using PHP CLI 8.4.27, including reported PHP lint 166/166; strict v1.3.99 X6 contract and version consistency PASS. This does **not** equal GitHub Actions or PHP 8.1 lint for all files.

## Exact candidate 1.3.99 ZIP installer QA — PASS
- The candidate ZIP, verified by SHA-256, was uploaded to a temporary **test-only** binary URL, then separately downloaded again by HTTP and hash-verified: exact digest `ffcdc52d68cabf41d72837166e343b98a29f1a414c512471035fe3e2f042a369`.
- Disposable test blueprint: `tests/wordpress/upn4-v1399-exact-zip-upgrade-rollback-blueprint.json`, introduced at immutable commit `d9238b639414a3fd925e62335e79760109c15c08`.
- Browser execution: https://agent.tinyfish.ai/runs/414f9094-78f6-476b-9c55-ad7f24fa5796
- Actual environment **WordPress 6.9.11 / PHP 8.1.34**, WordPress Playground (disposable WASM/SQLite, not hosted MySQL).
- Persistent report `/wp-content/uploads/azt-upgrade-rollback-report.html` returned **STATUS=PASS**, package digest shown, and all five steps:
  - `AZT_BASELINE_PASS`: install official v1.3.98 ZIP and seed native Page, postmeta, WordPress option, Theme mod.
  - `AZT_UPGRADE_PASS`: install **exact 1.3.99 ZIP** via WordPress Playground installer into same Theme, verify active v1.3.99, native-data/Theme-mod continuity and unchanged updater Git blob `a3f118fb987aad11e7072475dfd80e1ac16c4d37`.
  - `AZT_SWITCH_AWAY_PASS`: switch to Twenty Twenty-Five, native data unchanged.
  - `AZT_SWITCH_BACK_PASS`: return to AZnet Theme v1.3.99, native data + Theme mod preserved.
  - `AZT_ROLLBACK_PASS`: reinstall official v1.3.98 ZIP, native data + Theme mod still preserved.
- Separate source-directory upgrade/rollback QA also PASS, browser run https://agent.tinyfish.ai/runs/d9ecd59c-5374-48fa-aae9-a106d9b033d5. It is superseded for actual installer-zip proof by the exact ZIP test above.

## CI infrastructure root cause — VERIFIED / HARD BLOCK
- GitHub Actions **did not execute any job steps** on the candidate or on merged main (zero steps, runner ID zero).
- GitHub Check Run annotations were fetched through the connected GitHub app. Both candidate check-run `125138332898` (head `31f845942aae7d3d180edb3f0bed31379e2e8ded`) and main check-run `125127045367` (head `83379de31e893e049f92fc632c8490e7a6788bd4`) report exactly:
  > The job was not started because your account is locked due to a billing issue.
- This confirms **external account billing lock**, not a test failure after checkout. A targeted job rerun remained `failure` with zero steps. There is no exact-head GitHub CI GREEN, and no authorization to amend AZT-05 release governance or to invent alternate CI success.
- The independent source/ZIP tests do **not** unblock the separately mandated GitHub Actions gate.

## Scoped acceptance and remaining work
- **PASS:** independent PHP/Core, strict v1.3.99 version contract, reproducible package, actual exact ZIP install, Theme switch and rollback with owner-data preservation on disposable WordPress 6.9/PHP 8.1.
- **BLOCKED_EXTERNAL_BILLING:** GitHub Actions PR/main CI, consequently PR merge and official release.
- **UNKNOWN:** production host-specific MySQL/PHP integrations; full multi-viewport/a11y fresh exact-release installer; other optional provider L5 certification.
- **NOT PERFORMED:** merging PR #557, creating tag/release v1.3.99, overwriting v1.3.98, or any customer production site deployment.

## One exact next / recovery
Restore GitHub Actions account billing status (owner-controlled GitHub account operation), then re-run exact PR #557 CI. Once CI is GREEN, apply normal owner-approved merge gate, reverify exact-main CI and deterministic ZIP SHA with source provenance, publish *new* immutable v1.3.99 GitHub Release with `aznet-theme-1.3.99.zip` and `SHA256SUMS.txt`, verify Git tag and asset hash. Site deployments stay separately gated. Do not re-run accepted offline/Playground PASS without code invalidation.
