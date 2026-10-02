# Team directory authenticated admin visual checkpoint — 02/10/2026

## Scope

This checkpoint records authenticated WordPress admin visual evidence for the Team directory management surface introduced by PR #359. It does not add or modify Theme production code.

## Evidence basis

The site owner supplied authenticated wp-admin screenshots from the live Law 01 site `https://lstamduchn.vn`.

Observed admin surface:

- Summary reports `Nhân sự hiển thị công khai: 7 / 7 nhân sự đã thêm`.
- The management list visibly contains all seven personnel:
  - Luật sư Trần Chí Thanh
  - Luật sư Nguyễn Văn Thi
  - Luật sư Đỗ Viết Hà
  - Luật sư Lâm Minh Tuấn
  - Luật sư Hoàng Thu Hương
  - Luật gia Vũ Thị Kim Dung
  - Luật sư Mai Thành Lâm
- Every visible row exposes `Sửa` and `Xóa` actions.
- The list renders member image, title and description without visible overlap, clipping or broken card layout.
- `Thêm nhân sự`, `Chỉnh Đội ngũ`, `Xem trên trang chủ`, and `Xem tất cả trên website` controls are visible in the authenticated admin flow.
- The destructive `Xóa` action is visually distinct from `Sửa`.

## QA disposition

- L3 authenticated server-side: PASS at the verified access/data scope. The connected WordPress administrator identity has the capabilities needed for the bounded Team authoring/delete flow, and all seven managed Team child Pages are readable in edit context.
- L4 authenticated admin visual coverage: PASS for 7/7 personnel at the screenshot-observable presentation scope.
- L4 functional interaction remains NOT CLAIMED from screenshots alone. No production click-through of `Sửa`, `Xóa` confirmation/Trash behavior, keyboard-only focus traversal or live axe scan is inferred from the images.
- Existing pre-merge CI remains the evidence for the retained browser/a11y contracts and reversible delete implementation.
- Public production Team directory rendering and final personnel slugs remain covered by `docs/evidence/TEAM_DIRECTORY_MANAGEMENT_PRODUCTION_20261002.md`.

## Visual note

The `Sửa` button is visually larger than `Xóa`; this is a non-blocking polish observation, not a functional failure.

## Ownership

WordPress child Pages under the mapped Team parent remain the content source. AZnet Theme owns only the bounded presentation/authoring surface. No RootProfile dependency, parallel Team store, private provider read or redirect workaround is introduced by this checkpoint.

## Rollback

Evidence-only change. Revert this documentation commit if the checkpoint is later superseded or found inaccurate.
