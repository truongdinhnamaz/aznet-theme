# Homepage Hero editing UX — verified reconciled checkpoint

**Date:** 19/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Branch:** `feat/homepage-hero-editor-ux`  
**PR:** #151  
**Canonical base after PR #150:** `main@55ca349c8808d0d6a3c96d5201983ae966b1d822`  
**Verified reconciled production/test head:** `82073e715be349f29fe84dbd0405c5af6e1b5c93`

## Goal

Remove the authoring friction that forced Law 01 Hero copy to mirror Site Title or another Page title while preserving the architecture rule that WordPress owns content and AZnet Theme owns presentation/reference selection.

## Ownership

- WordPress remains owner of Hero Page title, excerpt, body and featured image.
- AZnet Theme stores only the existing typed reference `homepage_hero_page` and renders an authoring bridge in the Homepage Control Center.
- No `homepage_hero_title`, `homepage_hero_subtitle`, `homepage_hero_slogan` or `homepage_hero_body` Theme setting is introduced.
- No Page/Post/Term create/update/delete mutation is performed by the Homepage settings screen.
- PR #150 remains the source of the canonical 1.3.10 Hero presentation bytes; PR #151 adds only the authoring bridge/admin presentation and its tests/evidence.

## RED

Commit: `2d63c0c2b1d993e63ccb0a22efcdacd6fc16a373`

Homepage Composer Law 01 Browser Quality run `35411643511` failed for the intended reason:

`Missing Homepage Hero editing UX contract: render_homepage_hero_editor`

This established the missing authoring bridge before production implementation.

## GREEN implementation

Production paths:
- `inc/admin/homepage.php`
- `assets/css/admin/control-center.css`

Behavior:
- explicit current-source state: dedicated Hero vs legacy fallback;
- warning when fallback from Site Title / Front Page is active;
- preview of mapped WordPress Hero Page;
- native **Sửa nội dung Hero** link to the WordPress Page editor;
- native **Tạo Page Hero mới** link when no valid Hero source is mapped;
- responsive Control Center presentation;
- no duplicate Theme-owned copy store.

Test paths:
- `tests/offline/homepage-control-center-contract.php`
- `tests/browser/r5-control-center-l4.mjs`

Coverage proves fallback state, native create-Page action, absence of Theme-owned Hero-copy inputs, mapped Hero state, native Page edit action, responsive layout and serious/critical axe checks.

## Canonical 1.3.10 merge reconciliation

The product owner approved PR #150. It merged to canonical `main@55ca349c8808d0d6a3c96d5201983ae966b1d822`.

Fresh exact-main verification on that merge:
- V1 Exact Main Verification run `35412045895`: **SUCCESS**
- X6 Cross-surface Release Closure run `35412045904`: **SUCCESS**

PR #151 was then reconciled with canonical 1.3.10 through merge commit `82073e715be349f29fe84dbd0405c5af6e1b5c93`. Comparison against canonical main is ahead-only, behind-by-zero, with exactly the Hero authoring bridge/admin CSS/tests/evidence delta.

## Reconciled verification

Exact reconciled production/test head `82073e715be349f29fe84dbd0405c5af6e1b5c93` completed **19/19 triggered workflows SUCCESS** at final disposition.

Key runs:
- R5 Control Center Static Contracts `35412090839`: **SUCCESS**
- Homepage Composer Law 01 Browser Quality `35412090957`: **SUCCESS**
- R5 Control Center Browser Quality `35412090873`: **SUCCESS**
- D-027 Standalone Core L3 `35412090793`: **SUCCESS**
- D-027 Standalone Core L4 `35412090960`: **SUCCESS**
- D-027 Standalone Core Exact Package `35412090808`: **SUCCESS**
- D-027 Retained Full Regression `35412090802`: **SUCCESS**
- V1 Core Pull Request CI `35412090934`: **SUCCESS**
- R6 Performance Baseline `35412090862`: **SUCCESS**

R2 Pattern Library Browser Quality run `35412090864` had one first-attempt disposable-runtime failure at the Woo-present editor navigation (`net::ERR_CONNECTION_RESET`). No production/test bytes were changed. The same run was retried once and attempt 2 completed **SUCCESS**, including the previously failing Woo-present Editorial step plus theme-switch portability and runtime-boundary checks. This is treated as transient CI/runtime infrastructure evidence, not a Theme regression.

## Scope / rollback

Compared with canonical `main@55ca349c8808d0d6a3c96d5201983ae966b1d822`, the verified functional slice changes only:
- `inc/admin/homepage.php`
- `assets/css/admin/control-center.css`
- `tests/offline/homepage-control-center-contract.php`
- `tests/browser/r5-control-center-l4.mjs`
- this evidence file.

Rollback is a revert of PR #151. WordPress content remains untouched by the feature and therefore requires no data migration or rollback.

## Not authorized / not claimed

- PR #151 is not merged to `main`.
- No tag, GitHub Release or production deployment is authorized by this slice.
- No change to RootProfile, ConvertFlow or WooCommerce ownership/contracts.
- Published GitHub Release and verified production deployment remain historical `v1.3.0`; canonical repository metadata is now `1.3.10`.
- RootProfile authoritative Team remains blocked under issue #112.

**NEXT:** canonical merge of PR #151 is the next explicit owner approval gate. Do not tag, publish a GitHub Release or deploy production as part of that merge.
