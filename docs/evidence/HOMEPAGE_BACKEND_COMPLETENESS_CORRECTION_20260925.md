# Homepage Backend completeness correction — 25/09/2026

## Scope

Bounded D-041 correctness slice after canonical PR #276/#277. No new page-builder capability, domain ownership, release or deployment behavior.

## Root findings

1. The native WordPress Front Page `the_content()` boundary renders between Law 01 Theme-composed before/after surfaces, but it was absent from the primary `Trang chủ đang hiển thị` map. The backend therefore did not fully describe what the frontend actually rendered.
2. Process and FAQ Homepage presentation reads list/details blocks from the mapped WordPress Page `post_content`, while the backend exposed only bounded title/excerpt Quick Edit. Administrators therefore lacked a direct path from the Homepage card to the WordPress content that actually renders.

## RED

- `tests/offline/homepage-native-content-surface-contract.php` was committed before implementation.
- `tests/offline/homepage-process-faq-authoring-completeness-contract.php` was committed before implementation.

## GREEN implementation

- shared Law 01 effective-surface model now includes `front-page-content` between Profile and after-content surfaces;
- native Front Page article carries the same stable `data-aznet-homepage-surface` marker only while Homepage composition is active;
- Homepage Map exposes `Chỉnh nội dung trang` through the native WordPress Page editor;
- Process/FAQ keep bounded title/excerpt Quick Edit but also expose `Sửa các bước` / `Sửa câu hỏi` native WordPress editor actions;
- runtime order assertion accepts the explicit `native` boundary and expects the native surface;
- R5 browser assertions compare admin/public ordering including the native marker and require the new edit actions;
- retained core/Law 01/R5 workflows are wired to the new contracts.

## Ownership

WordPress remains authoritative for Front Page, Process and FAQ Page content, publication state and native edit lifecycle. AZnet Theme only projects the effective surface/order and provides bounded/native editor links. No duplicate Homepage content store or provider-private read is introduced.

## Verification state

Direct exact-branch source/contract verification **PASS** on snapshot `69a067d487e09e7196a910da3f8eea069e51d804`: 14 affected production/test/source files checked, 0 failures. The check covered native surface/model/marker order, Front Page edit reachability, Process/FAQ full-content editor actions, ownership-forbidden reads, runtime/browser assertions, retained gate wiring and source-governance updates. GitHub-hosted runner availability is not a prerequisite for this L1/L2 source evidence. L3 WordPress runtime and L4 browser/a11y remain UNKNOWN and are not inferred from source checks.

## Branch

`fix/homepage-native-content-surface-20260925`
