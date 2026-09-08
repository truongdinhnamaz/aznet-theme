# R6 Reusable PR + Exact-Main CI — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 4 pre-promotion infrastructure  
**Canonical base:** `main@a6a901b5712291330368935d662aeda8370e618b`  
**Theme metadata:** remains `1.0.0`

## 1. Goal

Replace milestone-specific verification assumptions with reusable v1.x CI entry points while preserving the existing Theme-owned quality boundaries.

Delivered infrastructure:

- `.github/workflows/v1-core-ci.yml` — pull-request verification against `main`;
- `.github/workflows/v1-main-verification.yml` — exact canonical-`main` verification after merge;
- `scripts/verify-v1-core.sh` — reusable static/contract chain;
- `tests/offline/r6-ci-trigger-contract.php` — trigger/path/exact-main guard.

No production Theme PHP/CSS/JS or metadata change is part of this task.

## 2. RED -> GREEN

### RED

Commit/head: `91838616978a867137a5cdf224ab64c926a231bf`  
Workflow run: `34182183909`  
Job: `101923327552`

The new contract intentionally ran before the reusable workflows existed and failed at `Verify R6 reusable CI trigger contract`. Earlier R6 asset-scope and ConvertFlow-boundary checks remained green.

This proves the contract failed for the intended missing-infrastructure reason rather than an unrelated Theme defect.

### GREEN

Commit/head: `2e2a4768ad68f75ac8372476334d51a4b81d14ba`  
Workflow run: `34182317043`  
Job: `101923699091`  
Result: **SUCCESS**

The GREEN run verified:

- R6 exact Theme asset-scope contract;
- R6 ConvertFlow public-capability gate;
- reusable CI trigger contract;
- reusable v1 core verification chain;
- WordPress 6.9 clean runtime baseline;
- WooCommerce 10.9.4 baseline.

## 3. Reusable PR CI contract

`V1 Core Pull Request CI` is scoped to pull requests targeting `main` and runs on production/tests/scripts/workflow changes. It uses read-only repository contents permission and has two jobs:

1. static/contracts through `scripts/verify-v1-core.sh`;
2. clean WordPress 6.9 runtime route verification.

It does not publish, deploy or mutate external systems.

## 4. Exact-main contract

`V1 Exact Main Verification` runs only on `push` to `main` for relevant code/verification paths. It records `${{ github.sha }}` and verifies the checkout equals that SHA before making exact-main claims.

It runs:

- reusable static/contracts;
- WordPress 6.9 clean runtime;
- retained `tests/browser/g-core-l4.mjs` browser/a11y verification;
- artifact upload for exact-main evidence.

This preserves the source rule that PR-head PASS is not automatically exact-main PASS.

## 5. Current boundary

Task 4 proves the reusable CI infrastructure on the R6 branch. It does **not** yet prove the new exact-main workflow on canonical `main`, because that trigger can only run after the infrastructure is merged.

Therefore workflow retirement and release promotion must not infer exact-main coverage from branch evidence.

## NEXT

Carry this infrastructure through the R6 pull request, obtain fresh PR-head checks, merge only after the explicit owner gate, then require fresh `V1 Exact Main Verification` evidence on the merge SHA before any release promotion or legacy-workflow retirement.