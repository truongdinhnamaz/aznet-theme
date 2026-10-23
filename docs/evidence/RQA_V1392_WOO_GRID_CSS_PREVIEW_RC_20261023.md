# RQA Theme v1.3.92 CSS-only preview release candidate — bounded checkpoint

**Date:** 2026-10-23 (Asia/Bangkok)  
**Canonical repo:** `truongdinhnamaz/aznet-theme`  
**Candidate branch:** `work/rqa-v1392-woo-grid-rc-preflight-20261023`  
**Source of Truth:** AZT-00 source priority; AZT-01 presentation ownership; AZT-02 public integration boundary; AZT-03 code provenance; AZT-04 QA/decision gate; **AZT-05 approved product/release constitution**.  
**Scope:** Only AZnet Theme WooCommerce archive **presentation** CSS proven by [Draft PR #506](https://github.com/truongdinhnamaz/aznet-theme/pull/506). No ConvertFlow plugin code/private state, WooCommerce price/stock/domain data or new provider contract.

## Current facts and provenance

- Official latest GitHub Release: `v1.3.92` (published 20/10/2026), tag dereferenced source commit `b7738467b21dba1bcd63f62a11b5a248cd7f1ba9`.
- **Immutable official ZIP** `aznet-theme-1.3.92.zip`, SHA-256 `204458e8518096fcf08f01f42c75d0880471f0b6f26ebfbc283f57b894b9b3ed`. Fresh Actions downloaded this actual Release ZIP and verified exact digest, not merely a Git working tree archive.
- Canonical `main` is far ahead of tag `v1.3.92` (157 commits during the measured comparison), so **do not** build the RQA patch by promoting entire newer `main` or deploy an unreviewed 1.3.96 snapshot.
- Branch created from the exact `v1.3.92` tag. `assets/css/components/woocommerce-archive.css` matches PR #506 blob `0af4613718bd815b4d8271cd4405f8238edaed97`; release baseline blob was `8f42dfef662e28a07f6409a56a951a18f261ffd8`. Diff is one **353-byte insertion** of the selector `.woocommerce ul.products[class*="columns-"] li.product` resetting float/clear/width/margin.
- Existing RQA production Theme remains **1.3.92**; the previously approved, native WordPress **Additional CSS** hotfix `custom_css#1133` / `custom_css_post_id=1133` is live and narrow production L4 **9/9**. It must **not** be disabled before a future candidate Theme package is separately approved, installed and verified. The pre-existing WPVibe draft contains unrelated PHP deltas and is preserved; publishing that draft would violate this slice.

## Preview-only artifact — integrity PASS

- [Preflight Actions #39977434452](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39977434452) **SUCCESS**. Artifact `rqa-v1392-woo-grid-preview-rc`, ID `12512450275`.
- Candidate file `RQA_PREVIEW_ONLY_aznet-theme-v1.3.92-woo-grid.zip`, SHA-256 **`958d0c08036957887ef05f948ed8371ced82f7eda820f147ba09fcf514e93a56`**.
- **231 production files**, no additions or removals; exactly **one** changed runtime file: `aznet-theme/assets/css/components/woocommerce-archive.css`. Official release CSS baseline and tag source are byte-matched before patch; candidate CSS is byte-matched to the PR source.
- Build twice from identical official release and patch produces identical ZIP SHA-256. ZIP CRC and content manifest verified. PHP 8.1 lint **162/162 PASS**.
- Candidate retains metadata **1.3.92** intentionally and is marked **PREVIEW ONLY**. It is **not** an approved version promotion, a publicly tagged GitHub Release, a merged `main` tree, or a production Theme update. No user should treat the ZIP as a final release just because it is installable.
- Initial preflight run `#39977153405` failed due the harness comparing an ambiguous diff alignment; exact byte insertion-anchor verifier corrected; source CSS unchanged. Subsequent run above is PASS.

## WordPress-clean L3, theme switching and rollback PASS

- [Disposable staging Actions #39978668223](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39978668223) **SUCCESS**, job `120499889507`.
- Fresh **WordPress 6.9 / PHP 8.1** installed in CI with a disposable MySQL instance. Preview ZIP installed, AZnet Theme activated and all optional plugins disabled (zero active). Server-rendered Home and a WordPress-native Post responded successfully with Theme header/footer/main; WooCommerce/ConvertFlow assets were not loaded on clean Home.
- Switched to a WordPress default Theme and back; the native Post remained intact. Then replaced the candidate package with the **original published v1.3.92 ZIP** under an isolated Theme-switch, verified CSS original digest, reactivated AZnet Theme and verified that the same WordPress Post persisted and Home rendered. No Theme PHP fatal/parse errors were found in local server log.
- Earlier staging runs `#39978250485` and `#39978493416` were CI-only preconditions (malformed SHA-256 path and `head` piping of WP-CLI info), both fixed without changing the candidate runtime bytes. Do not promote those failed runs to production defects.
- **L3 PASS is restricted to this exact clean-WP candidate**, not provider L5 and not RQA Theme-package deployment.

## Browser QA and unresolved external tracks

- Site-specific RQA production mobile/tablet/desktop Woo category cards, link integrity and narrow keyboard continuation remain **9/9 L4 PASS for the already-live Additional CSS hotfix**, [workflow #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109).
- A fresh WordPress-clean browser/axe smoke is running as [Actions #39979139148](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39979139148) at this evidence-authoring checkpoint. **No L4 clean-WP PASS claim until this run completes**; its results belong in a later append-only or updated evidence note.
- ConvertFlow-owned mobile filter first-paint layout shift (CLS ~0.677), project gallery/quote accessibility violations and FAQ/project/spec placeholders are documented in [Draft QA PR #505](https://github.com/truongdinhnamaz/aznet-theme/pull/505). This Theme RC does **not** modify or fix them.
- Business-authoritative hotline/price/product certification remain separate and outside the CSS scope.

## Hard gates and rollback

- **L0/L1:** exact-release provenance, package source identity, one-file CSS-only diff and PHP 8.1 lint PASS.
- **L2:** PR #506 RED → GREEN on CSS Grid card width and live browser-local verification retained.
- **L3:** clean WordPress 6.9 PHP 8.1, activation, theme-switch and restore official package PASS.
- **L4:** narrow site-specific deployed WordPress Additional CSS 9/9 PASS; fresh *clean-WP candidate package* browser/a11y pending as above.
- **L5:** optional provider-specific CLS/a11y defects BLOCKED at ConvertFlow owner.
- **L6 / official release:** **NOT AUTHORIZED / NOT CLAIMED.** AZT-05 requires explicit product-owner approval for merge, tag, Release publication and any production Theme-package deployment.
- **Rollback after a future approved package deployment:** retain a verified site/Theme backup; preserve the WordPress-native CSS hotfix until the new package passes fresh on-site tests; then disable `custom_css_post_id=1133` and retain the post draft as audit trail to avoid duplicate declarations. If the new package regresses, restore prior Theme binary and if necessary re-enable native Custom CSS; do **not** touch provider private stores.

## Single exact Next

Inspect the exact-head fresh WordPress-clean Chromium/axe workflow `#39979139148`. If GREEN, record the six-case browser evidence; then submit the bounded source-only release-candidate evidence to PR #506 for owner review. Do not tag, merge or deploy further without explicit approval.
