# R6 Post-Merge Verification and Legacy Workflow Retirement — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 post-merge verification and Task 6 retirement review  
**Canonical main:** `a3dc550bd748acab59e1df2384f8e2ada55541e9`  
**Canonical tree:** `88a653fb854bc2ba52ea2087353e78bcbfb3d167`  
**Theme metadata during this checkpoint:** `1.0.0`  
**State:** `READY_FOR_RETIREMENT_PR`

## 1. Exact-main verification

`V1 Exact Main Verification` run `34185341561` completed successfully on the exact canonical main SHA `a3dc550bd748acab59e1df2384f8e2ada55541e9`.

Fresh evidence covers:

- reusable v1 static/contracts on exact main;
- WordPress 6.9 clean runtime routes;
- retained core browser/a11y verification;
- no Theme-caused PHP Fatal/Warning/Parse/Uncaught marker in the verified runtime.

The downloaded exact-main browser artifact was independently inspected after the run. The browser summary contains 18/18 passing cases, zero blocking axe violations and zero horizontal-overflow failures.

## 2. Main-only release-candidate workflow exercise

`V1 Release Candidate Package` workflow-dispatch run `34185937074` completed successfully on the same exact main SHA and tree while metadata remained `1.0.0`.

Run evidence:

- exact checkout SHA: `a3dc550bd748acab59e1df2384f8e2ada55541e9`;
- exact checkout tree: `88a653fb854bc2ba52ea2087353e78bcbfb3d167`;
- parsed Theme version: `1.0.0` in both `style.css` and `AZNET_THEME_VERSION`;
- deterministic package built twice with byte-identical result;
- package: `aznet-theme-1.0.0.zip`;
- production package file count: `83`;
- packaged PHP file count: `63`;
- package SHA-256: `477b4f398c3072bfe1d5553ecbbc70591a1129719d7eed68e91632df6a85b2af`;
- exact unzip/source-tree comparison: PASS;
- single top-level package directory: `aznet-theme/`;
- extracted package activation/render on WordPress 6.9: PASS;
- no packaged-Theme PHP Fatal/Warning/Parse/Uncaught marker.

Artifact `10040504800` (`v1-release-candidate-1.0.0`) was independently downloaded and inspected; its embedded package SHA matched the workflow result exactly.

This `1.0.0` artifact is a workflow/provenance exercise only. It is not a v1.1 release and is not authorized for publication/deployment.

## 3. Replacement coverage decision

Fresh exact-main evidence is sufficient to retire only the following superseded G workflows:

| Legacy workflow | Replacement evidence | Decision |
| --- | --- | --- |
| `g-core-static.yml` | `v1-main-verification.yml` static job + reusable `verify-v1-core.sh` | RETIRE |
| `g-core-runtime.yml` | `v1-main-verification.yml` clean WordPress 6.9 runtime routes | RETIRE |
| `g-core-browser.yml` | `v1-main-verification.yml` retained `g-core-l4.mjs` browser/a11y gate | RETIRE |
| `g-core-release-candidate.yml` | `v1-release-candidate.yml` deterministic package + unzip + PHP lint + package-byte WP 6.9 activation/render | RETIRE |

## 4. Gates retained

Do **not** retire:

- `g-core-lifecycle.yml`: update-in-place, theme-switch continuity and exact rollback semantics are not yet reproduced by the reusable v1 workflows;
- `g8-release-version.yml`: this is the existing exact `1.0.0` metadata gate and must not be silently repurposed or removed before the separate v1.1 metadata-promotion slice defines its replacement.

No F/R/W milestone workflow is retired by this checkpoint. No production Theme PHP/CSS/JS is changed.

## 5. PASS / BLOCKED / NEXT

**PASS:** exact-main verification and main-only `1.0.0` candidate workflow are proven on canonical `main@a3dc550...`; four G workflows have fresh replacement coverage.

**BLOCKED:** metadata promotion to `1.1.0`, tag, GitHub Release and deployment remain separate owner hard gates.

**NEXT:** open and verify a bounded PR that deletes only the four covered G workflows and adds this evidence checkpoint. Retain lifecycle and G8 version gates unchanged.
