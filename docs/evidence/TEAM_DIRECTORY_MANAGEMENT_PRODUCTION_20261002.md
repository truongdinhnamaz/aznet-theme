# Team directory management production checkpoint — 02/10/2026

## Scope

This checkpoint records the production publication and verification of the WordPress-native Team directory management slice merged in PR #359.

## Repository provenance

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Implementation PR: #359 — Team directory: manage all members and reversible delete.
- Merged implementation commit: `eada15dcc8962c364cb6e83697bdebac51c15856`.
- Pre-merge QA on the approved head: 29/29 GitHub checks completed successfully.
- Ownership model is unchanged: WordPress child Pages remain the personnel content source; AZnet Theme provides bounded presentation/authoring only.

## Production publication

Site: `https://lstamduchn.vn`.

- Active theme after publish: AZnet Theme 1.3.65.
- WordPress: 7.1.2.
- PHP: 8.5.6.
- WPVibe published the reviewed draft into `aznet-theme`.
- Rollback copy: `aznet-theme-wpvibe-backup`.
- Rank Math sitemap cache and WordPress object cache were purged after publication.

## Production verification

The Team directory at `/doi-ngu/` rendered seven published WordPress child Pages after publication.

Verified final personnel slugs:

- Trần Chí Thanh: `/doi-ngu/tran-chi-thanh/`
- Nguyễn Văn Thi: `/doi-ngu/nguyen-van-thi/`
- Đỗ Viết Hà: `/doi-ngu/do-viet-ha/`
- Lâm Minh Tuấn: `/doi-ngu/luat-su-lam-minh-tuan/`
- Hoàng Thu Hương: `/doi-ngu/luat-su-hoang-thu-huong/`
- Vũ Thị Kim Dung: `/doi-ngu/luat-gia-vu-thi-kim-dung/`
- Mai Thành Lâm: `/doi-ngu/luat-su-mai-thanh-lam/`

The first three slugs were corrected through WordPress REST so URL identity matches the current Page title. The Team directory frontend was re-read after the corrections and rendered the corrected links.

## Legacy URL disposition

Legacy path `/doi-ngu/ls-mai-thanh-lam/` remains 404.

- WordPress `_wp_old_slug` was tested and did not redirect this hierarchical Page path.
- The temporary protected meta row was then removed with explicit user approval.
- A site-wide dry-run search found no user-facing internal content link pointing at the legacy path.
- The only remaining occurrence is inside Rank Math's `rank_math_indexnow_log`, which is plugin-owned historical log data and was intentionally left unchanged.
- No redirect workaround was added to AZnet Theme.

## QA disposition

- L0/L1/L2: PASS from the merged PR evidence and contracts.
- L3 public production render: PASS for the Team directory and member detail URLs observed above.
- L4 pre-merge browser/a11y gate: PASS in CI.
- Authenticated wp-admin click-through on the production session remains UNKNOWN because the verification browser session did not inherit WordPress admin authentication.
- Redirect ownership for the legacy 404 remains outside the Theme and does not block this Theme slice.

## Rollback

If a production regression is discovered, restore `aznet-theme-wpvibe-backup` or the user's host/site backup, then re-verify the affected QA layer.
