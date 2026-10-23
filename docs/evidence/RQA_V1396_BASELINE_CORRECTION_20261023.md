# RQA — AZnet Theme current-version reconciliation and 1.3.96 CSS-only preview

**Date:** 2026-10-23. **Repo:** `truongdinhnamaz/aznet-theme`.
**Source owner:** AZT-00/01/02/03/04 and AZT-05 v1.x release constitution. Theme is presentation owner, not WooCommerce/ConvertFlow business state owner.

## Verified current versions — correction to v1.3.92-only candidate

- Canonical `main@afdeeacd3927eb0b7763045ab732b2f0cbebd1c7`: `style.css` and `AZNET_THEME_VERSION` both **1.3.96** (rechecked 2026-10-23).
- Fresh authenticated WPVibe `site_info`: `https://aznet.vn` active Theme **1.3.96**, `https://remquocanh.vn` active Theme **1.3.92**, `https://lstamduchn.vn` active Theme **1.3.92**. The AZnet 1.3.96 publication/owner-approved source sync is recorded under D-062 and merged source PR #501.
- GitHub Release API latest published tag remains **v1.3.92** (published 2026-10-20). A production Theme install (aznet.vn) and canonical `main` 1.3.96 do **not** imply a public GitHub tag/Release v1.3.96.
- The earlier v1.3.92-based `work/rqa-v1392-woo-grid-rc-preflight-20261023` ZIP remains reproducible historical QA evidence only. **SUPERSEDED for the requested newest-Theme RQA upgrade**, and must not be shipped as the next RQA upgrade candidate.
- Existing RQA production CSS hotfix `custom_css` post **#1133**, Theme mod `custom_css_post_id=1133`, was separately owner-approved, deployed, and verified 9/9 real-browser category × viewport cases in [run #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109). Keep this PASS and do not disable until replacement Theme passes fresh production verification.

## New 1.3.96 source-based preview candidate — byte evidence

- Source branch `work/rqa-v1396-woo-grid-rc-preflight-20261023` cut from exact `main@afdeeacd3927eb0b7763045ab732b2f0cbebd1c7` (1.3.96). No changes to `main`.
- The **only changed runtime source file** relative to exact base is `assets/css/components/woocommerce-archive.css`: the same **353-byte CSS insertion** already verified in Draft Theme PR #506; candidate Git blob `0af4613718bd815b4d8271cd4405f8238edaed97`.
- Source-side package preflight [Actions #39984349021](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39984349021) wrote a successful source/ZIP manifest then subsequently **FAILED the L3 workflow** due an inherited stale *harness* status assertion; no WordPress install was reached. Corrected in commit `a55128bb2f8dbeec7087fc4a2fbda65a07f7de40`.
- **Package verification PASS** on those bytes, using canonical deterministic builder: **241 runtime files, exactly 1 changed package file**, two preview builds byte-identical, PHP 8.1 syntax lint **164/164 PASS**, source revision and version 1.3.96 verified.
- **Candidate preview-only ZIP SHA-256:** `50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266`.
- **Baseline exact `main@afdeeacd` builder ZIP SHA-256:** `6c141e109d1d8c8dcef892eb355da1ca080a184961e6cd58ecafe1932815d68f`; **not** a published GitHub Release ZIP.
- GitHub Actions artifact for initial package preflight: `rqa-v1396-css-preview-package-and-l3-l4`, ID `12514702470` (the job's overall status is FAIL at L3 for the stated reason; package-specific PASS is independently recorded in the emitted manifest).
- Latest isolated L3/L4 workflow was launched [#39984680097](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39984680097) from corrected source; **do not call its runtime/browser results PASS without inspecting completed run and exact logs**.

## Ownership, release and rollback gate

- Production site uses the already-PASS scoped CSS hotfix and stays at Theme 1.3.92 until a separate full-Theme upgrade is approved.
- Existing WPVibe `aznet-theme-wpvibe-draft` contains unrelated PHP changes; **do not publish/overwrite it to upgrade to 1.3.96**. Existing `aznet-theme-wpvibe-backup` and the owner-reported full-host backup must be preserved, verified as appropriate.
- D-062 proves 1.3.96 deployed on **aznet.vn**, not 1.3.96 on **remquocanh.vn**. GitHub `main` is source, not a published installable Release by itself.
- Source-mismatch risk remains between v1.3.92 and v1.3.96: cross-version Theme presentation/WordPress options requires fresh RQA-specific preview, a11y/regression and rollback checks; cannot infer compatibility only from a CSS patch.
- AZT-05 requires approval-gated release/tag/merge/production deployment. No public release, tag, merge, WPVibe publish, full Theme install, provider code update or business-data mutation in this slice.
- Provider-owned ConvertFlow mobile filter CLS and quote/project a11y findings remain separately BLOCKED, detailed in Draft QA PR #505. The Woo card-width CSS does not resolve provider filter CLS.

**EXACT NEXT:** Read the completed result of workflow #39984680097; if blocked, reproduce and correct only the isolated test harness without changing candidate runtime bytes; when GREEN, record exact-head candidate L3/L4, then request separate approval for a bounded RQA Theme 1.3.96 production upgrade with verified staging/backup and post-deploy QA. No release publication or Theme-switch before that gate.
