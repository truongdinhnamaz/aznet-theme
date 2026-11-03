# ANP Front Page Gutenberg Nested Wide-Block Clipping — Live Root Cause + Theme Fix (2026-11-03)

## Source / ownership / scope
- Site: `https://anpvietnam.vn`, authenticated WPVibe read-only. Active Theme `aznet-theme` **v1.3.99**, WordPress 7.1.4, PHP 8.2.35. Latest official release **v1.3.99**; the new accessibility patch PR #558 is merged into main but not installed on this website.
- Canonical Theme source before fix: `main@bee6cda64148e283002e31c9952cb4b1562cd062`.
- Theme owns front-page full-bleed shell and Gutenberg alignment presentation. WordPress owns native `post_content`; WooCommerce owns product taxonomy/media. No direct edits to WordPress content, CPT/meta/media or plugins.
- NWQS promoted registry `https://nagavia.vn/api/v1/quality-standards/website` inaccessible this turn; only provisional safe baseline applied.

## Verified failure / why previous QA was falsely reassuring
- Published front Page contains native `.anp-home-service-guide.alignwide` and a nested `.wp-block-columns.alignwide`; the Theme presenter `front-page.php` renders native `the_content()` inside `.aznet-theme-entry__content`. This is **not** the custom Theme `#aznet-homepage-industrial-solutions` section (absent from live page).
- Theme stylesheet `assets/css/components/media.css` defines `.aznet-theme-entry__content .alignwide` with `margin-inline-start:50%` and `transform:translateX(-50%)`. Applied to both parent and nested group, the inner wide Columns are translated **twice**. The full-bleed front-page outer shell uses `overflow-x:clip`, so `scrollWidth == innerWidth` even while text is off-screen.
- Read-only live Playwright baseline with exact viewport dimensions:
  - **390px:** parent block at x=18..372; inner columns at x=**-159..195** (width 354); **16** sampled descendants/column nodes clipped.
  - **768px:** parent block at x=40..728; inner columns at x=**-304..384** (width 688); **16** clipped.
- Runtime proof from [run #41604391477](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41604391477); diagnostic screenshots and JSON [artifact #13216325165](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41604391477/artifacts/13216325165).

## Bounded Theme-owned fix / counterfactual
- Add only one higher-specificity rule in `assets/css/components/media.css` scoped to full-bleed front-page authored content: `.aznet-theme-main--front-page .aznet-theme-entry__content .alignwide .alignwide`, restoring `width:100%`, `max-width:100%`, `margin-inline:0`, `transform:none` for **nested** wide blocks.
- Preserve outer `.alignwide` centering, `overflow-x:clip`, native Gutenberg content and semantic structure. Do not alter WordPress Page, Theme composer mappings, Woo, or any plugin domain.
- Tested via in-browser stylesheet override **without writing to production**:
  - 390px: nested columns x=**18..372**, zero sampled clipping offenders.
  - 768px: nested columns x=**40..728**, zero sampled clipping offenders.
  - Tested the **exact production-target CSS selector** in a separate live run [#41605266288](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41605266288) with artifacts [#13216132037](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41605266288/artifacts/13216132037).
  - A different trial that merely forced Columns flex-wrap kept 16 clipped nodes and confirmed root cause is the double translate, **not** flex wrapping.

## TDD / QA evidence
- RED: standalone new `tests/offline/frontpage-nested-alignwide-contract.php` on original CSS returned **FAIL: Nested Gutenberg alignwide inside native front-page content must have scoped CSS fix** under PHP 8.4.27, expected test failure.
- GREEN after one CSS rule: standalone test **PASS**; both relevant PHP files/lint, `bash -n scripts/verify-v1-core.sh`, `git diff --check` PASS.
- Full independent `bash scripts/verify-v1-core.sh` on exact patched Git checkout **PASS / exit 0**, **273 PASS markers**, which includes this new regression.
- Regression added to the existing V1 Core dispatcher directly after X3 Media/Gallery/Embed contract; no new dependency or bundler.
- **NOT YET PASS:** official GitHub Actions exact-head on this patch PR until all required jobs finish; real hosted WordPress post-fix (patch has not been deployed), desktop-specific visual QA beyond current screenshot, hosted rollback.

## Other visible anomalies — different owners / no silent fixes
- Pin Lithium category uses WooCommerce placeholder `/wp-content/uploads/woocommerce-placeholder.png` returning HTTP 404; WooCommerce/site media owner must repair its source image/valid default. Do not mutate taxonomy meta or invent product imagery in Theme.
- Home product cards 05–08 have intentionally empty media placeholders because some WooCommerce products have no native thumbnails; these large blank cards are visible but not the cause of clipped WordPress text. Image source and attachment selection are WooCommerce-owned. Theme presentation of empty media can be handled as a separately bounded improvement, not by assuming those images exist.
- Knowledge article grey areas in prior fullPage screenshots may be below-fold lazy-load placeholders; **not verified missing images** and should not be asserted as data defects.

## Recovery / exact next
Open bounded Theme-only PR; preserve all previous PASS. Require GitHub exact-head static/WordPress/browser QA, then owner approval to merge. The existing v1.3.100 release candidate PR #559 was prepared before this visual defect was discovered and **does not include this fix**; do not publish that candidate as the visual repair. Reconcile source branch before a new release; full release and anpvietnam.vn deployment (with backup/rollback) have separate authorization gates.

## Addendum — verified three-viewport scope and corrections (2026-11-03)

This addendum supersedes two provisional assumptions above without rewriting their historical evidence.

**Desktop 1440px is affected too.** The live native front-page Article was at x=0..768 instead of centred. Its outer Gutenberg wide group started x=-224 and its inner wide Columns started x=-832, causing 12 sampled clipped descendants. Applying only the inner-wide rule left 7 clipped descendants. Restoring Article centering in generic-content.css AND resetting the nested wide translation in media.css puts the Article at x=336..1104 and the nested Columns at x=112..1328: zero sampled clipping. The same two-rule browser-only override yields zero clipped descendants at 390px and 768px. Actual read-only QA: https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41606435295 . No production file was edited.

**CSS ownership gate protected the architectural layer.** Initially placing both rules in media.css triggered the existing X3 fail-closed regression: FAIL: unscoped frontend media selector: .aznet-theme-main--front-page .aznet-theme-entry.aznet-theme-entry--front-page { . The Article centering rule was moved into generic-content.css and only the nested wide-block media rule remained in media.css. Independent X3 test PASS, complete V1 Core PASS exit 0 with 273 PASS markers on branch commit d757fc40695f437e2766d569ad0271bfff660929. Neither X3 nor any production gate was relaxed.

**Correction of earlier image assertion:** Native images are NOT missing from products 05–08. Fresh real-site Playwright scrolled the images into view and verified complete=true and naturalWidth>0 for all 8/8 product images and all 3/3 article images. The grey blank regions in the earlier whole-page screenshot were below-fold lazy-loading capture artifacts, not missing Woo product thumbnails. Read-only evidence: https://github.com/truongdinhnamaz/aznet-theme/actions/runs/41605884877 . No Woo content/media edits are justified for those 11 images. Separately, the Pin Lithium category placeholder returned HTTP 404 and remains a WooCommerce/site-data-owner issue.

**Final bounded Theme fix:** native WordPress Front Page Article centering in assets/css/components/generic-content.css, nested Gutenberg alignwide transform reset in assets/css/components/media.css, single offline contract registered in scripts/verify-v1-core.sh, and evidence only. Actual live anpvietnam.vn remains on published Theme v1.3.99 and does not yet contain this fix. PR #559 release candidate was returned to Draft because it predates the fix; do not publish it as a homepage repair. Merge/release/pilot site update remain independently gated.
