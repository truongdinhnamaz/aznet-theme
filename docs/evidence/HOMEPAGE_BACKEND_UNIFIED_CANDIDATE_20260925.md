# Unified Homepage Backend candidate — 2026-09-25

## Scope

Bounded reconciliation of three already-approved Homepage directions on top of PR #272 candidate lineage:

- D-040 Template Library admin presentation;
- D-041 shared effective-surface Homepage Map;
- existing PR #272 Homepage authoring / Team / Hero candidate behavior.

Parent implementation checkpoint: `46d07c1a2b7cd82130b813742d9b78d619d7b6ef` (PR #272, Theme 1.3.54 candidate).
Unified branch: `work/homepage-backend-unified-20260925`.

## Implemented candidate

- Template Library card selector with search/category filtering; existing `off`, `law-01`, `curtain-01` IDs retained.
- Shared request-local `homepage_effective_surface_map()` for Law 01.
- Law 01 frontend before/after composition consumes that shared model instead of a separate hard-coded section order.
- Primary wp-admin `Trang chủ đang hiển thị` map consumes the same model.
- Stable Theme-owned frontend anchors/data markers support deep links and admin/frontend parity checks.
- Source switching/migration/technical diagnostics moved behind `Nguồn & cài đặt nâng cao`.
- Effective-source compatibility is centralized: the proven 1.3.53 legacy paths for Law 01 About, Team, Case Analysis and Legal News remain explicit rather than being silently migrated.
- Team directory/Profile/admin source controls consume the same effective Team source.
- New offline contracts, runtime assertion and R5/Homepage browser parity assertions are wired into existing QA infrastructure.

## Ownership

- WordPress remains authoritative for Page/Post/Category/Media/wp_block content and publication state.
- RootProfile, ConvertFlow and WooCommerce ownership is unchanged.
- Theme stores presentation state/typed references only; no parallel Homepage content store or private-provider read was introduced.
- Remote Template Distribution Service remains external owner for D-040 catalog/commercial/entitlement/signing state.

## RED -> GREEN checkpoint

`tests/offline/homepage-surface-map-contract.php` was added before the shared model existed and required the missing resolver/admin/frontend shared-model markers. The implementation was then added on the same isolated branch.

Additional retained/new contracts are now wired into `scripts/verify-v1-core.sh` and the affected browser workflows.

## Evidence depth

At this checkpoint, repository/code structure and source reconciliation are present on the branch, but **fresh executable L1/L2/L3/L4 PASS is not claimed**.

Prior PR #272/#274 GitHub Actions reruns terminated before executing workflow steps (`runner_id=0`, empty step list). That is treated as runner/infrastructure evidence, not as a Theme regression and not as a PASS.

## Remaining gates

- L1/L2: fresh executable PHP/static/contract run.
- L3: WordPress 6.9 runtime including `tests/runtime/homepage-map-runtime.php`.
- L4: R5 Control Center + Law 01 admin/frontend order parity, responsive/keyboard/focus/axe/console.
- Curtain 01 shared Homepage Map parity is not part of this Law 01 bounded slice; its existing path is retained.
- D-040 real Distribution Service L5 remains `BLOCKED_EXTERNAL`.
- Main merge, release publication and production deployment remain separate approval gates.

## Exact next

Open a draft review PR to canonical `main` to trigger the repository-native QA matrix. If runners execute, close L1/L2 first and continue immediately to L3/L4. If jobs again end with zero executed steps/runner ID, record `BLOCKED_EXTERNAL_RUNNER` and keep the branch recoverable.
