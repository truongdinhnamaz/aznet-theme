# F4 Homepage Token Integration Verification

**Date:** 2026-09-07  
**Scope:** Milestone F / Homepage Shell / F4 dependency and token integration boundary  
**Repository:** `truongdinhnamaz/aznet-theme`

## Result

**PASS — branch-candidate L2/static-contract scope.**

F4 was previously blocked only because the Theme-owned ConvertFlow Theme Integration Contract v1 producer mapping lived on unmerged PR #24. PR #24 is now owner-approved and merged into canonical `main`, so the dependency gate is resolved without copying or forking W8 implementation into the Homepage branch.

This checkpoint does **not** claim F6/F7 actual ConvertFlow lifecycle/analytics integration, F8 browser/runtime, F9 release closure, Milestone F completion, E5-C unblock, E5-D takeover, deployment, or release promotion.

## Canonical dependency

- Canonical `main` after PR #24 merge: `11f66c1508254c69bba92f97e9725a08cc64e41e`.
- PR #24 merged W8 Theme producer mapping:
  - `inc/theme/assets.php` blob: `f19e134137412fa79a4865824c89a76c5024ce8a`.
  - `assets/css/integrations/convertflow.css` blob: `39115a63f00a369be72b40dd01dbd988e1d55cc5`.
- Homepage branch does not recreate or modify either production path.

## Current-base candidate

GitHub synthetic merge candidate for current `main` plus PR #27 branch before this evidence-only commit:

`04ede8016df4cd10070d05347f87c07ca696c5a6`

Fresh compare from `main@11f66c1508254c69bba92f97e9725a08cc64e41e` to that candidate shows only the nine existing Homepage Stream H paths. Neither `inc/theme/assets.php` nor `assets/css/integrations/convertflow.css` is in the PR #27 delta.

## Fresh verification

Test-only branch:

`test/f4-homepage-token-integration-20260907`

The branch starts from the synthetic PR #27 candidate and adds only `.github/workflows/f4-homepage-token-integration.yml` for verification.

GitHub Actions run `34080636997`: **SUCCESS**.

Verified on PHP 8.1:

- `PASS: F1 native front-page shell contract`
- `PASS: F2 WordPress body integration boundary contract`
- `PASS: F3 Homepage ownership static contract`
- `PASS: F5 Homepage absent/native fail-soft contract`
- `front-page.php` PHP lint PASS
- `PASS: W8 ConvertFlow public theme-contract bridge`
- exact W8 blob hashes match canonical reviewed bytes
- `front-page.php` contains no ConvertFlow/`choiceguide_*` provider logic, private storage access, takeover hooks, integration stylesheet path, or integration enqueue handle
- final assertion: `PASS: F4 Homepage consumes canonical Theme token bridge without duplicating provider logic`

## Ownership conclusion

F4 is satisfied by composition of already-owned responsibilities:

- AZnet Theme Homepage owns `front-page.php`/site shell/native WordPress body boundary.
- The canonical Theme token producer remains the merged W8 mapping on `main`.
- ConvertFlow remains owner of Journey/body semantics, lifecycle, state, analytics and conversion behavior.
- No parallel domain store, private contract, runtime adapter, resolver, Journey semantics, or provider-owned DOM logic is introduced by F4.

## Remaining gates

- **F6/F7:** require actual/current ConvertFlow public runtime evidence; remain UNKNOWN/BLOCKED until such evidence exists.
- **F8:** real WordPress/browser/a11y evidence remains a separate L3/L4 gate.
- **F9:** cannot close until required integration/runtime gates are evidenced; main merge remains explicit owner approval.
- **E5-C/E5-D:** unchanged; RootProfile dependency/takeover gates remain separate.
