# RQA — Theme 1.3.92 → 1.3.96 full upgrade/rollback rehearsal, isolated Curtain 01 pilot

**Date:** 2026-10-24 (Asia/Bangkok). **Status:** PASS **only in disposable WP/Woo staging**. Not a production release.
**Canonical repo:** `truongdinhnamaz/aznet-theme`; branch `work/rqa-v1396-woo-grid-rc-preflight-20261023`; exact tested code commit `18aa550ff9b5b9e3215250f87f7f882ec178de87`.
**Evidence:** [GitHub Actions #40070849548](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40070849548), job `120803747455`, SUCCESS. [Parallel exact-head clean-WP #40070849629](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40070849629), SUCCESS.
**Artifact:** `rqa-v1396-curtain01-woo-isolated-preview-18aa550ff9b5b9e3215250f87f7f882ec178de87`, artifact ID `12551266504` (ZIP CRC independently checked, 26 files including screenshots, ownership JSON, preview and log; artifact retention is temporary).

## Source and package identity

- Baseline candidate `main@afdeeacd3927eb0b7763045ab732b2f0cbebd1c7`, Theme **1.3.96** in metadata; the **only candidate Theme runtime difference** is the 353-byte WooCommerce card-grid CSS rule from [PR #506](https://github.com/truongdinhnamaz/aznet-theme/pull/506).
- Candidate deterministic preview ZIP: `RQA_PREVIEW_ONLY_aznet-theme-v1.3.96-woo-grid.zip`, SHA-256 `50a4189be371cda131eda1eb0dd95ff739c43e4e4228015e1b4d4a958703a266`, 241 runtime files, 1 changed vs exact GitHub main. PHP 8.1 syntax **164/164 PASS**.
- Official *prior* GitHub Release `v1.3.92` ZIP, SHA-256 `204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed`.
- **No official 1.3.96 Release/tag exists** as of the measured canonical state. ZIP is preview only and must not be published under an already-used version without product-owner release/version decision.

## Verified isolated runtime upgrade

- Disposable **WordPress 7.1.3 + PHP 8.4 + WooCommerce 11.2.1**. No live RQA content or provider credentials copied. C5 pilot-shaped fixture uses WordPress native Page/Post/Menu/Media and public WooCommerce product APIs, with Theme `visual_preset=curtain-01`, `homepage_preset=curtain-01`.
- Installed the *exact official* 1.3.92 ZIP, simulated Theme-owned WordPress `custom_css` hotfix, recorded `ownership-before.json`.
- Installed the exact SHA-verified 1.3.96 candidate ZIP, recorded `ownership-after-upgrade.json`, **byte-for-byte equal** (JSON canonical representation). The source-owner snapshot tracks **13 Pages, 7 Posts, 12 Woo products, 2 menus**, product IDs/categories/gallery, menu items/locations, front-page option, Theme presentation settings, logo media ID and native Custom CSS post reference.
- The 1.3.96 Theme CSS digest verified **`c6fa157bb4ad7ab4118ba40ea6922c532013d5721239147aa77ba51c8cb301a4`** before disabling the temporary WordPress CSS duplication.
- Browser preview on upgraded candidate: Curtain01 Home at **320/390/1024/1440 px**, all 10 expected section surfaces, shop, variable product and Admin authoring. No horizontal overflow, no *serious/critical* axe failures; some nonserious axe findings remained on shop/product, not incorrectly reported as full WCAG conformance. Browser run and PHP server log boundary PASS.

## Corrected CSS transition invariant — critical regression discovery

The first naive rollback design only marked the native `custom_css` post **Draft** and set `custom_css_post_id=-1`. The isolated browser then revealed the **original CSS comment/rule was still publicly emitted** after the C5 admin authoring flow. WordPress may re-associate the `custom_css_post_id` to the draft post (observed as ID `79` in this fixture), and a draft post is **not sufficient proof** of no redundant CSS in the page.

Final verified *staging only* transition:
1. Archive exact previous WordPress Custom CSS body in the isolated job and verify it matches the original baseline.
2. After checking the new 1.3.96 candidate CSS digest, set the temporary custom_css post status Draft **and clear its content**, leaving an auditable post ID. Verify status and empty content. The first-paint public `/san-pham/` HTML must not contain the old CSS signature, regardless of the retained/re-associated Theme mod ID.
3. Run Curtain01 browser and WordPress admin QA, then capture `ownership-before-rollback.json` after any legitimate admin authoring changes.
4. Restore exact official 1.3.92 Theme ZIP through a fallback Theme. Capture `ownership-after-theme-restore.json`: **byte-for-byte equals** pre-restore snapshot.
5. Repopulate the archived native CSS body exactly via supported WordPress CLI, publish the old custom_css post and restore its Theme mod reference. Public Home HTML verifies Curtain01 and the custom CSS signature. Snapshot `ownership-after-rollback.json` persists the original Theme CSS post reference. **`RQA_1396_ROLLBACK_STAGE_PASS`**.
6. This proves the rehearsal's intended rollback only. It does **not** certify a hosting restore or the exact real production WordPress object graph.

Rollback assertions from final run: `RQA_1396_UPGRADE_STAGE_PASS`, `RQA_1396_ROLLBACK_STAGE_PASS`, no PHP fatal/parser marker, job SUCCESS. Interim failed runs before #40070849548 were deliberately left as diagnostic RED evidence and were corrected only in the **staging test harness**; candidate Theme runtime CSS, PHP and deterministic ZIP SHA are unchanged.

## Current RQA production / ownership gates

- Fresh WPVibe authenticated `site_info` shows `remquocanh.vn` active Theme **1.3.92**, WP 7.1.3; native Theme `custom_css_post_id=1133` still active, not modified in this exercise. Prior real-site browser check of the scoped CSS hotfix **9/9 PASS** ([Actions #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109)). Do not remove it until the future replacement Theme has independently passed post-deploy live browser/HTML checks.
- RQA's active Theme source files have **not yet** been fully byte-compared against published v1.3.92; version metadata alone does not guarantee no drift. Pre-upgrade provenance and current backup recovery must be checked before full Theme replacement.
- L0/L1/L2 candidate package PASS; L3 WordPress-clean PASS; L3/L4 *synthetic RQA Curtain01 + WooCommerce* upgrade/rollback PASS; live RQA provider-specific **L5 ConvertFlow integration remains UNKNOWN/BLOCKED**. The ConvertFlow-owned filter CLS ~0.677, a11y failures, placeholder copy and business-authoritative price/hotline are not changed.
- L6 merge/release/prod full Theme upgrade remains **approval-gated** under AZT-05. The user approved continuation of QA, **not** silently creating official v1.3.96 with different bytes, overwriting the polluted WPVibe draft, or deploying the complete new Theme.

## EXACT NEXT

Read-only provenance check of the **actual RQA production active Theme** versus the exact v1.3.92 GitHub Release source, especially the 14 changed runtime files from 1.3.92 to main 1.3.96. If untracked production drift exists, save an exact backup and ownership-safe reconciliation before any full Theme upgrade. Then prepare a new-version release request with explicit approval and post-release disabling of native CSS #1133 only after real-site QA.
