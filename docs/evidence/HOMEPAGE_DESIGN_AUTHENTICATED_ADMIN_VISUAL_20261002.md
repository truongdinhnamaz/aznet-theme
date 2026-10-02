# Homepage Design authenticated admin visual checkpoint — 02/10/2026

## Scope

This checkpoint records authenticated WordPress admin visual evidence for the Law 01 Homepage Design surface on `https://lstamduchn.vn`. It is evidence-only and does not modify Theme production code.

## Evidence basis

The site owner supplied a sequence of authenticated wp-admin screenshots from the live Law 01 site. The screenshots cover the Homepage template selector and the visible Homepage effective-surface authoring map from the top of the page through the advanced-settings boundary.

Observed authenticated surface:

### Homepage template selector

- `Rèm 01`, `Industrial 01` and `Law 01` template cards are visible.
- `Law 01` is visibly selected and marked `Đang dùng`.
- Law 01 variant selector displays `Burgundy + Gold`.
- `Lưu mẫu trang chủ` is visible.
- Copy explicitly states the template applies presentation and does not rewrite WordPress content.

### Homepage Design entrypoint

- `Thiết kế Trang chủ` / `Hero` surface is visible.
- Copy states Core supplies the shared backend while the active template declares its presentation-specific part.
- `Thiết kế Hero` action is visible.

### Effective Homepage surface map

The screenshots visibly cover sections 1 through 8 in order:

1. Hero
2. Dịch vụ
3. Giới thiệu & Đội ngũ
4. Nội dung trang chủ
5. Bài viết
6. Chủ đề
7. Tin pháp luật
8. Liên hệ

Each observed section is marked `Đang dùng` and exposes the expected bounded authoring/view controls for that source, including combinations of:

- `Thiết kế Hero`
- `Chỉnh nội dung`
- `Chỉnh Đội ngũ`
- `Quản lý bài viết`
- `Chỉnh chủ đề`
- `Xem trên trang chủ`

The screenshots also show:

- Law 01 badge `Law 01 · Burgundy + Gold`.
- Team summary `Nhân sự hiển thị công khai: 7 / 7 nhân sự đã thêm`.
- `Xem tất cả trên website` and `Thêm nhân sự`.
- `Nguồn & cài đặt nâng cao` collapsed at the end of the observed map.

## Visual QA disposition

- Authenticated Homepage Design admin visual coverage: **PASS** for the screenshot-observable desktop presentation scope.
- The screenshots show no visible card overlap, clipping, broken grid alignment, missing section shell, unreadable control label or obvious horizontal overflow in the captured desktop viewport.
- The effective-surface order rendered in wp-admin matches the intended Law 01 Homepage map for sections 1→8 at the observed checkpoint.
- The screenshots demonstrate that the shared Homepage Design/Core entrypoint and the Law 01 template projection are visible in the authenticated production admin.

## Not claimed

The screenshots alone do **not** prove:

- successful click-through or save behavior for template selection, Hero editing or any section authoring action;
- keyboard-only focus order;
- live axe/a11y scan;
- mobile/tablet wp-admin responsiveness;
- mutation semantics for WordPress-owned content;
- external-provider L5 behavior.

Those remain separate QA layers and must not be inferred from this visual checkpoint.

## Ownership

The observed admin copy and controls remain consistent with AZnet Theme's presentation boundary: Theme selects/presents template and composition surfaces while WordPress/provider sources retain authoritative content/data ownership. No new provider dependency, private-storage read or parallel domain store is introduced by this evidence checkpoint.

## Rollback

Evidence-only change. Revert this documentation commit if superseded or found inaccurate.
