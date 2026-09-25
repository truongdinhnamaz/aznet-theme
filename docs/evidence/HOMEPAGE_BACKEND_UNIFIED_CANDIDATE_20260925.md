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

Draft PR #276 was opened against canonical `main` specifically to obtain fresh repository-native verification. The first fresh runs reproduced the external runner failure before any job step executed:

- V1 Core Pull Request CI run `36108772953`: `static-contracts` job `107987242275` and `clean-runtime` job `107987242397` both ended with `runner_id=0`, blank runner name and `steps=[]`;
- R5 Control Center Static Contracts run `36108772680`: job `107987241706`, same zero-runner/zero-step failure;
- R5 Control Center Browser Quality run `36108772757`: job `107987241626`, same zero-runner/zero-step failure;
- Homepage Composer Law 01 Browser Quality run `36108772671`: job `107987241567`, same zero-runner/zero-step failure.

This independently reproduces the earlier PR #272/#274 infrastructure symptom on the new candidate. State is therefore **BLOCKED_EXTERNAL_RUNNER** for executable L1-L4 verification. It is not evidence of a Theme test failure and not a PASS.

## Remaining gates

- L1/L2: fresh executable PHP/static/contract run.
- L3: WordPress 6.9 runtime including `tests/runtime/homepage-map-runtime.php`.
- L4: R5 Control Center + Law 01 admin/frontend order parity, responsive/keyboard/focus/axe/console.
- Curtain 01 shared Homepage Map parity is not part of this Law 01 bounded slice; its existing path is retained.
- D-040 real Distribution Service L5 remains `BLOCKED_EXTERNAL`.
- Main merge, release publication and production deployment remain separate approval gates.

## Exact next

Keep draft PR #276 and branch `work/homepage-backend-unified-20260925` recoverable. Resume with the same exact-head QA chain when GitHub runners can execute jobs: L1/L2 first, then Law 01 L3 and R5/Homepage L4 parity. Do not merge, release or deploy while `BLOCKED_EXTERNAL_RUNNER` remains.

### Exact-head recheck after source-compatibility review

Final production-code checkpoint before this evidence update: `2291207482de4d1fee9b4a4470851ab21e5a17a2`.

That exact head triggered the matrix again and independently reproduced the same infrastructure block:

- V1 Core `36108984511`: `clean-runtime` job `107987889397` and `static-contracts` job `107987889558` — `runner_id=0`, blank runner, zero steps;
- R5 Static `36108984342`: job `107987888165` — same;
- R5 Browser `36108984398`: job `107987888785` — same;
- Homepage Law 01 Browser `36108984549`: job `107987889896` — same.

This exact-head recheck supersedes the earlier PR #276 runner observation for current-code verification. The disposition remains `BLOCKED_EXTERNAL_RUNNER`; no executable Theme assertion has run.

### Owner approval + runner retry — 25/09/2026

Owner approved continuing the candidate at the current gate. The four minimum verification workflows were re-run on current branch head `6d7abe74da8c7976a8730ac16f598bf409b8d1ee`:

- V1 Core `36109081989`, attempt 2: jobs `107988591446` and `107988591667` ended with `runner_id=0`, blank runner name and zero executed steps;
- R5 Static `36109082090`, attempt 2: job `107988609451`, same zero-runner/zero-step result;
- R5 Browser `36109081994`, attempt 2: job `107988624356`, same;
- Homepage Law 01 Browser `36109081848`, attempt 2: job `107988638067`, same.

The retry therefore confirms the external runner block is still active. No executable Theme assertion ran. The candidate remains unmerged; release and deployment remain gated.
