# Homepage Hero editing UX — checkpoint

**Date:** 19/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`  
**Branch:** `feat/homepage-hero-editor-ux`  
**PR:** #151  
**Canonical base:** `main@597639e26a2cb242c3c0a71e5459ab5d66a0701d`

## Goal

Remove the authoring friction that forced Law 01 Hero copy to mirror Site Title or another Page title while preserving the architecture rule that WordPress owns content and AZnet Theme owns presentation/reference selection.

## Ownership

- WordPress remains owner of Hero Page title, excerpt, body and featured image.
- AZnet Theme stores only the existing typed reference `homepage_hero_page` and renders an authoring bridge in the Homepage Control Center.
- No `homepage_hero_title`, `homepage_hero_subtitle`, `homepage_hero_slogan` or `homepage_hero_body` Theme setting is introduced.
- No Page/Post/Term create/update/delete mutation is performed by the Homepage settings screen.

## RED

Commit: `2d63c0c2b1d993e63ccb0a22efcdacd6fc16a373`

Homepage Composer Law 01 Browser Quality run: `35411643511` — **FAIL as intended**.

The offline gate failed specifically at:

`Missing Homepage Hero editing UX contract: render_homepage_hero_editor`

This established the missing authoring bridge before production implementation.

## GREEN

Production implementation:
- `inc/admin/homepage.php`
- `assets/css/admin/control-center.css`

Behavior:
- explicit current-source state: dedicated Hero vs legacy fallback;
- warning when fallback from Site Title / Front Page is active;
- preview of mapped WordPress Hero Page;
- native **Sửa nội dung Hero** link to WordPress Page editor;
- native **Tạo Page Hero mới** link when no valid Hero source is mapped;
- responsive Control Center presentation;
- no duplicate Theme-owned copy store.

Browser regression coverage:
- `tests/browser/r5-control-center-l4.mjs`
- fallback state verified;
- native create-Page action verified;
- forbidden Theme-owned Hero copy inputs verified absent;
- mapped state verified after selecting an ordinary WordPress Page;
- native Page edit action verified;
- layout and axe critical/serious accessibility checks retained.

## Verification

- R5 Control Center Static Contracts run `35411765736`: **SUCCESS**
- Homepage Composer Law 01 Browser Quality run `35411765761`: **SUCCESS**
  - offline Homepage contracts PASS;
  - WordPress 6.9 runtime PASS;
  - dedicated + backward-compatible Hero paths PASS;
  - frontend browser/visual/a11y regression PASS.
- R5 Control Center Browser Quality run `35411765771`: **SUCCESS**
  - clean WordPress admin matrix PASS;
  - Woo-present admin matrix PASS;
  - Homepage Hero fallback + mapped authoring bridge PASS at browser depth;
  - no horizontal overflow / serious-or-critical axe violations;
  - theme-switch continuity PASS.

## Scope / rollback

Compared with canonical base, the slice changes only four paths before this evidence file:
- `inc/admin/homepage.php`
- `assets/css/admin/control-center.css`
- `tests/offline/homepage-control-center-contract.php`
- `tests/browser/r5-control-center-l4.mjs`

Rollback is a revert of PR #151. WordPress content remains untouched by the feature and therefore requires no data migration or rollback.

## Not authorized / not claimed

- No merge to `main`.
- No tag, GitHub Release or production deployment.
- No change to RootProfile, ConvertFlow or WooCommerce ownership/contracts.
- No claim that PR #150 / AZnet Theme 1.3.10 is superseded; PR #151 is a separate follow-up slice based on current canonical main 1.3.8 and must reconcile with canonical main before any merge.

**NEXT:** keep PR #151 draft until the canonical-main/version sequence is reconciled; merge remains an explicit owner approval gate.
