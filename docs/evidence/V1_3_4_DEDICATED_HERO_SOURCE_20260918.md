# AZnet Theme v1.3.4 — Dedicated Law 01 Hero Source Closure

**Date:** 18/09/2026  
**Scope:** Theme-owned Law 01 presentation/reference slice only.  
**Canonical repository:** `truongdinhnamaz/aznet-theme`

## 1. Ownership outcome

PR #135 replaces the implicit Law 01 Hero dependency on Site Title, Site Tagline and Front Page title/excerpt with a dedicated WordPress-owned Page reference:

- Theme setting: `homepage_hero_page`;
- WordPress Page owns Hero title, excerpt, authored body and featured image;
- Theme owns only presentation/composition and the typed reference;
- invalid/unmapped Hero source fails soft by omitting the Hero surface;
- no provider/private storage, parallel content store or domain ownership transfer is introduced.

Historical `law01-v1-1` provisioning behavior was not rewritten. Retained D-027 fixtures were adapted only at the test/workflow layer to exercise the current dedicated Hero Page contract.

## 2. Final-head verification

Exact final PR head:

`d0781a2c25a3b15bbc535184e0b03bafa2141a9b`

All 34 GitHub Actions workflows triggered for this head completed `SUCCESS`, including:

- D-027 Standalone Core Offline, L3, L4, Exact Package and Retained Full Regression;
- V1 Core Pull Request CI;
- Homepage Composer Law 01 Browser Quality;
- Law Site Provisioning Browser Quality and Candidate Package;
- F Homepage Native Runtime Browser and Regression Package;
- X6 Cross-surface Release Closure;
- Y5 Client Delivery Release;
- Release Version Consistency Gate;
- retained R1-R6, X1-X5 and Y1/Y3/Y4 gates triggered by the candidate.

The two retained D-027 failures encountered during the slice were fixture drift, not production Theme failures. The retained Law 01 contract was first aligned to the dedicated Hero source. D-027 L4/Exact Package fixtures were then updated to create/map a WordPress-owned Hero Page while leaving the registered v1.1 blueprint unchanged. Fresh runs after those fixes passed.

## 3. Package evidence

Y5 exact-package lifecycle on the final head produced deterministic package bytes twice:

- package: `aznet-theme-1.3.4.zip`;
- file count: `145`;
- SHA-256: `5cf0041ab09a6b9718e4354d2aa9c00850aa93e83c9ae775a640dc613caf3f83`;
- exact-package runtime/lifecycle/browser verification: PASS.

The uploaded Y5 artifact archive reported digest:

`sha256:9a32bc30cc8b281fbb224c1aaf768bfcd83e9066932a5daa021990b1569ea7f4`

## 4. Canonical merge

Owner-approved PR #135 merged to:

`main@d008f66cfa7357f3d6e4bede492e41a5d49a4112`

Comparison from final verified head `d0781a2...` to the merge commit reports:

- `ahead_by=1` for the merge commit;
- zero changed files.

Therefore the merged production/test tree is byte-identical to the final verified PR head. No fresh exact-main workflow run was observed for the merge commit at this checkpoint, so this evidence does **not** claim a separate exact-main rerun.

## 5. Release/deployment boundary

This checkpoint does not publish a Git tag or GitHub Release and does not deploy to production.

Published/GitHub-Release and verified production deployment remain historical `v1.3.0` until separately approved and executed. RootProfile Team integration remains separately `BLOCKED_EXTERNAL_CONTRACT` under issue #112.
