# Team Directory Technical Closure — 2026-09-25

**D-038 implementation head:** `645ad001fca301e37aebfed823567d5ea1e75bb4`  
**Evidence branch:** `work/homepage-map-v1-3-38-baseline`  
**Release identity remains:** AZnet Theme **1.3.44**. No Team release version has been promoted.

## Delivered production scope

- `inc/theme/team-directory.php`
  - exact mapped Team parent;
  - published direct-child resolver;
  - exact direct-child authoring guard;
  - native `menu_order` append helper.
- `template-parts/team/card.php`
  - shared portrait/name/role card;
  - no fabricated role or portrait.
- Law 01 Homepage Profile
  - max four real published Team child Pages;
  - shared member cards;
  - generated/reference people fallback removed;
  - **Xem tất cả** points to exact mapped Team parent.
- Homepage Control Center
  - visible first four Team members in the same resolver order;
  - bounded name/role/Featured Image edit;
  - **Sửa** per member;
  - **Thêm nhân sự** creates an ordinary draft child Page;
  - WordPress Media Library selector;
  - unrelated Pages rejected from Team child mutation.
- Mapped Team directory Page
  - exact ID-based presentation;
  - all published direct child members;
  - standard WordPress member child Pages remain native Pages.
- Law 01 Thiết lập nhanh
  - Team parent is required;
  - fresh creation title **Đội ngũ của chúng tôi**;
  - existing mapped Team Page is reused without rename;
  - no sample Team members are provisioned.
- Scoped Team card/directory/admin presentation assets.
- Runtime/browser fixtures for draft exclusion, missing portrait, missing role, ordering, admin↔frontend parity and directory all-member rendering.

## Ownership review

Fresh exact-source review on the implementation head confirmed:

- no Team CPT registration;
- no Theme-owned personnel repeater/meta/JSON store;
- no `get_page_by_path()`, slug/title/URL Team ownership heuristic;
- no RootProfile private storage/name dependency;
- no generated/reference Team-person fallback;
- add-member status is `draft`;
- member source is WordPress direct child Page;
- new Team Page mapping reuses the existing `team` source slot; no parallel mapping key was introduced.

The implementation keeps mapped Team-parent summary editing authorized by the exact mapping, while all non-parent Team source mutations require `team_directory_member_is_child()`.

## RED → GREEN evidence

RED conditions were captured before each implementation slice:

1. Team read model/card missing.
2. Homepage had no `team_directory_members( 4 )`.
3. Team admin authoring function/action missing.
4. mapped Team Page presentation missing.
5. Team provisioning still used the previous title/optional readiness behavior.
6. Team member action copy contract required **Sửa** before the helper was generalized.

Focused implementation/static checks are GREEN on the exact source.

## QA layer status

| Layer | Status | Evidence |
| --- | --- | --- |
| L0 Source / ownership | **PASS** | D-038 ratified in AZT-02/AZT-04/AZT-EXEC-MAP/SOURCE_MANIFEST |
| L1 Static / ownership contracts | **PASS** | exact-source contract predicates + forbidden-pattern review |
| L2 Focused contract/TDD | **PASS / bounded** | RED→GREEN slices captured; contract source registered in `scripts/verify-v1-core.sh` |
| L3 WordPress runtime | **UNKNOWN** | GitHub Actions jobs fail before runner allocation; no runtime step executed |
| L4 Browser / visual / a11y | **UNKNOWN** | R5/Homepage/Y1 jobs fail before runner allocation; no browser step executed |
| L5 Integration coexistence | **UNKNOWN retained gate** | no new provider contract; retained workflows did not start |
| L6 Release | **BLOCKED** | L3/L4 fresh evidence missing; version/package/deploy not authorized |

## CI infrastructure evidence

On exact implementation head, these direct gates completed as failure with **0 executed steps**:

- V1 Core PR CI run `36089428494`
- R5 Control Center Browser run `36089428765`
- Homepage Composer Law 01 Browser run `36089428526`
- Y1 Page Experience run `36089428497`
- Law Site Provisioning Browser run `36089428663`

A single retry was requested for each. Retry jobs also returned **0 steps**:
`107928910957`, `107928910963`, `107928918654`, `107928924268`, `107928934168`, `107928934344`, `107928940049`.

This reproduces the failure before runner allocation and provides no code/test failure log. Per QA governance, it remains UNKNOWN and cannot be promoted to PASS.

## Review status

The environment exposes no independent subagent/reviewer dispatcher. A fresh exact-source self-review was performed against the approved spec and plan. Independent reviewer evidence is therefore unavailable and is not claimed.

## Release gate

Do **not** promote 1.3.45, build a release ZIP, publish, merge or deploy while L3/L4 remain UNKNOWN.

Once a runner executes successfully (or an equivalent real WordPress + browser environment supplies fresh evidence), rerun V1 + R5 + Homepage Law 01 + Y1 + provisioning gates. If they PASS and no other patch identity has consumed 1.3.45, release promotion can proceed under a separate explicit owner approval.
