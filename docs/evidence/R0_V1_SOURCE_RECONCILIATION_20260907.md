# R0 v1.0 -> v1.1 Source Reconciliation

**Date:** 07/09/2026  
**Scope:** AZnet Theme source/state reconciliation only  
**Canonical repository:** `truongdinhnamaz/aznet-theme`

## 1. Canonical implementation baseline

- Canonical `main`: `f8e1a95c903c3f246528368ae9878eba780539ff`.
- Theme metadata: `1.0.0` in both `style.css` and `AZNET_THEME_VERSION`.
- WordPress support floor: `6.9+`.
- PHP support floor: `8.1+`.
- Architecture remains hybrid PHP theme + `theme.json`.
- `main` merge commit tree: `b716b89f04e45c2012f8e191c7d0edf605c9dd11`.
- Verified PR #34 final head: `b2e5cca1461233bcb1a0333c5aa51879c3264756`, with the same tree `b716b89f04e45c2012f8e191c7d0edf605c9dd11`.

The merge therefore introduced no conflict-resolution production delta relative to the verified PR-head tree.

## 2. v1.0 technical verification retained

PR #34 is merged/closed. The final PR-head verification set on `b2e5cca1461233bcb1a0333c5aa51879c3264756` includes these successful runs:

| Workflow | Run ID | Result |
| --- | ---: | --- |
| G8 Release Version Gate | `34107940143` | SUCCESS |
| G Core Static Release Gate | `34107940232` | SUCCESS |
| F Homepage Native Regression Package | `34107940273` | SUCCESS |
| G Core WordPress Clean Runtime | `34107940176` | SUCCESS |
| G Core Lifecycle Update Switch Rollback | `34107940281` | SUCCESS |
| G Core Release Candidate Package | `34107940221` | SUCCESS |
| G Core Browser A11y Performance | `34107940079` | SUCCESS |

These are retained byte-specific PR-head results. This R0 document does not invent a post-merge runtime/browser rerun on the merge SHA.

## 3. Publication classification

**Release publication: `PUBLICATION_PENDING`.**

Live GitHub evidence at R0 execution time shows:

- no Git tag under `refs/tags/`;
- no GitHub Release object.

`Version: 1.0.0` therefore means the core implementation/release-candidate metadata is complete; it does **not** mean a public `v1.0.0` tag/release has been published.

## 4. Optional compatibility tracks retained

- RootProfile E5-C remains externally blocked; E5-D takeover remains separately approval-gated.
- ConvertFlow F8 remains an optional compatibility blocker caused by provider nested document-level `<main>` output.
- Neither track is promoted to Theme core ownership or used to redefine standalone Theme readiness.

## 5. Approved next product stream

Approved next product-development stream: **AZnet Theme v1.1 Native Product System**.

Execution order after canonical source reconciliation:

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6`

R1 production work remains blocked until R0 source changes are approved and merged to canonical `main`.

## 6. Constitution and ownership

- AZT-05 remains unchanged.
- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.` remains controlling.
- This R0 slice changes source/state documentation only; it makes no PHP/CSS/JS production change and grants no provider takeover authority.
