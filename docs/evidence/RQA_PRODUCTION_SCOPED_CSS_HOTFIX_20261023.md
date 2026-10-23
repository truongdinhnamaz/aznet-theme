# RQA production scoped CSS hotfix — WordPress Additional CSS

**Date:** 2026-10-23 (Asia/Bangkok). **Target site:** https://remquocanh.vn  
**Operator authorization:** explicit approval in AZnet Theme conversation to deploy **only the PR #506 mobile WooCommerce grid rule** after preview and rollback check, and not to deploy unreleased Theme main 1.3.96.
**Source patch:** [PR #506](https://github.com/truongdinhnamaz/aznet-theme/pull/506), `assets/css/components/woocommerce-archive.css`.
**State:** narrow LIVE WordPress-native style customization, **not** a Theme zip/release/branch merge.

## Critical deployment gate — existing WPVibe draft is not safe to publish

- Connected WordPress site active Theme `aznet-theme` **1.3.92**. Existing `aznet-theme-wpvibe-draft` also reports 1.3.92; existing `aznet-theme-wpvibe-backup` reports 1.3.87.
- Read-only comparison of the existing draft vs active `inc/admin/provisioning.php`: draft **407 lines**, active **384 lines**. This is unrelated PHP delta that must **not** ship with the scoped CSS fix. The original draft and preexisting backup were preserved untouched. No `publish_draft_theme` call or theme activation/install/update was executed.
- WordPress had **no** `custom_css` posts; active Theme mod `custom_css_post_id=-1` before the change. The native Customizer CSS mechanism can scope the fix to the active Theme without copying plugin code or mutating a Theme package.
- Existing browser-local preview already verified candidate CSS on actual product pages: L2 RED→GREEN and narrow L4 overlay QA across Rèm cuốn, Rèm vải and Rèm cầu vồng at mobile/tablet/desktop sizes; see `RQA_WOO_ARCHIVE_MOBILE_CARD_WIDTH_20261023.md`.
- Operator previously confirmed a full hosting backup exists; independent full restore rehearsal has **not** been performed. The native style customization below also has a direct reversible rollback.

## Exact production write — native WordPress presentation customization

Only a `custom_css` CPT post and a Theme-specific `custom_css_post_id` setting were written by supported WordPress operations:

1. Create `post_type=custom_css`, `post_title=aznet-theme`, `post_status=draft` with Base64-encoded CSS body. WordPress returned **post ID 1133**. The post remained unpublished while contents and site HTML were verified.
2. Read back post **1133** with title `aznet-theme`, type `custom_css`, status `draft`, exact intended rule; initially public pages did not contain `wp-custom-css`.
3. `wp post update 1133 --post_status=publish` confirmed. WordPress generated slug `aznet-theme`; readback confirmed the exact CSS content.
4. `wp theme mod set custom_css_post_id 1133` confirmed, switching Theme-specific Customizer CSS from `-1` to `1133`. This is the only live presentation setting change. WordPress reported object cache purge.
5. Fresh rendered public HTML `head` for `/rem-cuon/`, `/rem-vai/` and `/rem-cau-vong/` confirmed a single `<style id="wp-custom-css">` with the exact PR rule and a provenance comment. All three URLs retained active Theme `1.3.92`.

Exact CSS body now live:

```css
/* AZnet Theme RQA scoped Woo archive grid hotfix; canonical source PR #506. */
.woocommerce ul.products[class*="columns-"] li.product {
    float: none;
    clear: none;
    width: auto;
    margin: 0;
}
```

- **Not changed:** Theme PHP/JS/CSS files, Theme 1.3.92 version, WordPress plugin code, ConvertFlow FAQ/project/spec content, WooCommerce categories/prices/stock/variations, WordPress menu/data, rootprofile, site-wide PHP logic.
- **Not claimed:** full Theme package release, PR merge, production source-package parity, provider CLS remediation, complete accessibility review.

## Production browser L4 result (no injection) — PASS

- [GitHub Actions #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109), branch commit `b8e0ad1fb56c8aa0f475f28d801f7d6e046612b2`, full job `120486961246`: **SUCCESS** after post-hotfix verifier update.
- `tests/browser/rqa-production-css-readback.mjs` read the actual live `<style id="wp-custom-css">`, computed CSS Grid and card bounding boxes **without injecting CSS, modifying WordPress or logging in**.
- **Rèm cuốn**: 3 products, 390px 354/354px, 768px 213.33/213.33px, 1280px 282/282px — 3/3 PASS.
- **Rèm vải**: 4 products, identical correct per-track ratios for 390/768/1280 — 3/3 PASS.
- **Rèm cầu vồng**: 3 products, identical correct per-track ratios for 390/768/1280 — 3/3 PASS.
- All 9 tests returned HTTP 200, Theme custom CSS signature and rule present, 100% card/track ratio for every displayed product, links remain within canonical site and the narrow keyboard continuation test reached the next in-grid link. **`RQA_PRODUCTION_CSS_STATUS PASS 9/9`**.
- The earlier live-overlay test was adjusted to accept an already-hotfixed site rather than requiring a 48%-width RED baseline after production publication. It now reports `phase=hotfix-live`, `deployedCustomCss=true`, before/after ratio `1.0` and PASS.
- Post-deploy `RQA_CLS_CAPTURE PASS` means the diagnostic capture itself executed, **not** that CLS was reduced. ConvertFlow mobile filter still owns the unresolved large first-paint layout shift.
- Narrow **L3+L4 PASS** for this scoped production CSS hotfix; no claim of global Theme release, end-to-end plugin integration, WCAG conformance, unchanged performance score, or final RQA acceptance.

## Post-deploy evidence and quality gates

- **L3 WordPress readback PASS:** post `1133` is published, post name `aznet-theme`, Theme mod `custom_css_post_id=1133` and exact style tag visible in three live public archives.
- **L4 browser-local preview PASS before publication:** [Workflow #39970836010](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39970836010), before/after rendered width on Rèm cuốn at 360/768/1024/1366. Additional category tests [Workflow #39971553559](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39971553559) pass Rèm vải and Rèm cầu vồng at 390/768 with links and keyboard continuation.
- **Post-deploy real-browser L4 PASS:** [run #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109) verifies 9/9 cases without CSS injection, plus link and narrow keyboard checks; exact results above.

## Rollback (bounded and reversible)

Initial state: `custom_css_post_id=-1`, **no** WordPress `custom_css` posts for AZnet Theme.

Restore precisely:
1. `wp theme mod set custom_css_post_id -1` via WPVibe native CLI; verify `<style id="wp-custom-css">` no longer displays on `/rem-cuon/`.
2. `wp post update 1133 --post_status=draft` to disable the hotfix post without destroying the audit trail. Verify post `#1133` is draft and neither CSS signature nor style rule appears in public head.
3. Do not switch theme, delete unrelated WPVibe draft/backup, merge the GitHub PR, modify products, or restore full DB unless the scoped rollback itself fails.

When a **proper Theme release** later incorporates PR #506, first verify deployment L3/L4 of that release and then remove/disable this temporary WordPress Additional CSS to prevent duplicate rules. That future release is a separate governance gate.

## Single next

Obtain fresh **non-injected production browser** evidence for 3 WooCommerce archives at 390, 768 and 1280px, links and narrow keyboard continuation. If any of nine cases fail, reproduce and revert only the Theme mod/WordPress custom CSS post using the rollback steps above. Keep the unrelated ConvertFlow mobile filter first-paint CLS and quote/gallery accessibility violations BLOCKED with their provider owner.
