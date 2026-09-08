# R6 Legacy Workflow Retirement Gate — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 6  
**State:** `DEFERRED_POST_MERGE`

## 1. Decision

Do **not** delete the retained milestone-specific G workflows in the R6 infrastructure pull request.

The source plan permits retirement only after replacement coverage and fresh evidence. The new reusable workflows cannot produce exact canonical-`main` evidence until they themselves are merged to `main`.

Deleting the old workflows before that evidence would create a verification gap and violate the no-gate-jumping rule.

## 2. Current replacement coverage

R6 now provides:

- `v1-core-ci.yml` for reusable PR static/contracts + clean WordPress runtime;
- `v1-main-verification.yml` for exact-main static + clean WordPress runtime + retained core browser/a11y;
- `v1-release-candidate.yml` for deterministic package, unzip, lint and package-byte WordPress 6.9 smoke;
- `r6-performance-baseline.yml` for measured clean/Woo route/asset evidence during R6 development.

These cover major functions previously spread across milestone workflows, but branch-level existence is not exact-main proof.

## 3. Lifecycle coverage gap

The current reusable workflows do not yet replace the full update/theme-switch/rollback semantics of `.github/workflows/g-core-lifecycle.yml`.

Therefore `g-core-lifecycle.yml` must not be retired merely because the new static/runtime/browser/package workflows exist.

After R6 infrastructure lands, there are two ownership-safe options:

1. retain `g-core-lifecycle.yml` as the lifecycle regression gate; or
2. migrate its proven behavior into the reusable v1 exact-main suite with a fresh RED/GREEN/continuity verification before deletion.

Until one of those is proven, lifecycle retirement remains not authorized.

## 4. Required post-merge sequence

1. Merge the R6 infrastructure pull request only after its full PR-head gate is green and owner approval is received.
2. Require `V1 Exact Main Verification` SUCCESS on the exact merge SHA.
3. Exercise `V1 Release Candidate Package` on canonical `main` while metadata is still `1.0.0`, proving the workflow itself without claiming a v1.1 release.
4. Compare fresh replacement evidence against each candidate legacy workflow.
5. Retire only workflows whose behavior is demonstrably covered; retain lifecycle or other unmatched gates.
6. Keep the separate metadata `1.1.0` promotion gate closed until explicit approval.

## 5. PASS / UNKNOWN

**PASS:** the retirement decision is ownership/QA-safe: no old workflow is removed prematurely.

**UNKNOWN:** exact-main execution of the new workflows and complete replacement equivalence, because those facts cannot exist before merge.

## NEXT

Open and verify the R6 infrastructure PR. Do not retire legacy workflows inside that PR.