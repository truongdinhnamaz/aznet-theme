# AZnet Theme v1.3.99 release candidate — verification checkpoint (2026-11-02)

## Intent / scope
User explicitly requested a new official Theme release of merged notification-only updater. The old `v1.3.98` tag and release are immutable. This is a **candidate**, NOT published and NOT production deployment.

## Exact source
Canonical main before candidate: `83379de31e893e049f92fc632c8490e7a6788bd4`; release candidate branch `work/upn-notice-v1399-release-candidate-20261102` head `e270f27f6a77dbfbf2737a5cf7807787a0e803bc`, PR #557.

Minimal code delta:
- `style.css` and `functions.php`: 1.3.98 → 1.3.99 exactly.
- `scripts/verify-v1-core.sh`: add strictly-dispatched current 1.3.99 contract, retaining all historical versions.
- `.github/workflows/x6-cross-surface-release.yml`: add explicit fail-closed version-change allowlist for 1.3.98 → 1.3.99; previous cases retained.
- `tests/offline/x6-v1399-release-candidate-contract.php`: intentionally RED on unchanged 1.3.98; GREEN on candidate.

Core Theme updater `inc/theme/core-update-notice.php` remains unchanged, Git blob `a3f118fb987aad11e7072475dfd80e1ac16c4d37`.

## Fresh local independent Git runner
Execution environment: Debian 13, PHP CLI **8.4.27**, repository actually fetched with Git from canonical GitHub and candidate head checked out. Results:
- Strict 1.3.99 fail-closed X6 version contract **PASS**; Theme metadata consistency script **PASS**; `git diff --check` **PASS**.
- `bash scripts/verify-v1-core.sh` **PASS, exit 0** on fresh candidate (272+ PASS markers; complete last line `PASS: reusable v1 core verification`). This is **not** official GitHub Actions CI and uses PHP 8.4 rather than PHP 8.1.
- Deterministic production ZIP built twice with `scripts/build-release-package.py`, bytes identical. **243 files**, archive valid. SHA256: `ffcdc52d68cabf41d72837166e343b98a29f1a414c512471035fe3e2f042a369`. Both builds used exact candidate files.
- PHP lint for **150** scoped production PHP files returned zero errors; reusable Core verifier additionally reported production PHP lint **166/166**.
- No GitHub release asset published. The generated local filenames were `aznet-theme-1.3.99-a.zip` and `aznet-theme-1.3.99-b.zip`; a canonical release ZIP name has not been published or uploaded.
- Existing official latest Release remains `v1.3.98`; attempted Git ref `v1.3.99` lookup returned 404 prior to publication.

## Hard gate
**Official GitHub Actions exact-head FAIL / BLOCKED_EXTERNAL_INFRA:** latest PR #557 ~27 jobs marked failure with **0 job steps** on inspected examples, completing within seconds. Earlier exact-main jobs had same failure-without-steps pattern and GitHub logs returned `BlobNotFound`. Do not assume the code is the cause, and do not quietly waive the CI gate. **Official release NOT permitted until a passing exact-head CI or an explicit approved, audited alternate release/rollback path satisfying AZT-05.**

**UPN4/package upgrade+rollback** and production release deployment verification: **NOT PASS**. Disposable WordPress UPN3 from prior commit demonstrated only notification (no install or upgrade). New candidate WordPress 6.9/PHP 8.1 check is still outstanding unless a new run produces final evidence.

## NEXT
1. Obtain actual PHP 8.1 / WordPress 6.9 candidate runtime result and keep package deterministic.
2. Resolve GitHub Actions runner startup_failure, rerun exact-candidate / exact-main gates, and produce validated ZIP plus SHA256SUMS.
3. Only then merge source candidate with owner approval, run exact-main CI, create immutable `v1.3.99` release with checksum artifact, and verify published tag/ZIP provenance. Production rollout is a separate authorized task.
