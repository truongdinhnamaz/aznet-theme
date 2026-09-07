# AZT-03 — Current Baseline và Code Provenance

**Version:** v0.17  
**Status:** Working Source  
**Date:** 07/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`

## 1. Source ownership

AZT-03 owns the canonical implementation baseline, provenance rules, registered source artifacts and migration evidence model. It does not own product scope, architecture, public contracts or roadmap semantics.

Live implementation facts are resolved from GitHub. Historical evidence is not rewritten merely to make the document look current.

## 2. Current canonical baseline

- Canonical `main`: `815ffcd43653930d1f302a055961ac24cf5ae843`.
- Internal Theme version: `0.1.0-alpha.7`.
- WordPress floor: `6.9+`.
- PHP floor: `8.1+`.
- Architecture: hybrid PHP theme + `theme.json`.
- Naming family: `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.

Current production state on `main` includes:

- Milestone 0 Source Freeze & Provenance;
- Theme Foundation;
- Semantic Design Tokens;
- Header/Footer v1.5;
- Generic Templates Page/Post/Archive/Search/404;
- WooCommerce presentation work W1-W9 including the merged public ConvertFlow token projection from PR #24;
- Theme-native WordPress Homepage shell from merged PR #30.

No source statement above transfers domain ownership to the Theme.

## 3. Current release-path classification

Under AZT-05 v1.0 and D-016:

- WordPress-clean core Theme readiness is release-critical.
- RootProfile E5-C/E5-D remains an optional provider certification/takeover track, not a core-v1.0 blocker.
- ConvertFlow F6/F7 actual-package integration has evidence PASS outside canonical main.
- F8 integrated compatibility remains BLOCKED because provider body output introduces a second document-level `<main>` inside Theme-owned `main#main`.
- The F8 provider defect is tracked as external compatibility evidence; Theme must not bypass it with private APIs, authoritative heuristics or copied domain logic.

Exact core next: **G0 — core-v1.0 scope freeze and Theme-owned cleanup/release inventory**.

## 4. Registered source artifacts

| ID | Artifact | Role | Inventory status |
| --- | --- | --- | --- |
| S-001 | `convertflow-0.1.0-p5.223-homepage-no-woo-mode.zip` | ConvertFlow historical evidence/provenance candidate | frozen/hash/extract/classify PASS |
| S-002 | `convertflow-pro-0.1.0-alpha.118-p5.162-r1-platform-compatible.zip` | ConvertFlow Pro compatibility/presentation evidence | frozen/hash/extract/classify PASS |
| S-003 | `RootProfile-0.1.0-alpha.99.zip` | RootProfile profile/integration evidence | frozen/hash/extract/classify PASS |
| R-001 | AZnet Theme Code Reuse Handoff from ConvertFlow v1.0 | technical handoff/reference | reviewed |

Historical build labels do not establish file ownership by themselves.

## 5. Provenance inventory baseline

The accepted source-bound inventory contains **817/817 classified candidates**:

- Domain: 454
- Mixed: 203
- Adapter: 97
- Presentation: 63

Reuse classification:

- Keep: 602
- Split: 203
- Port: 9
- Reference: 3

The 9/9 pre-port visual baselines for Header, Footer and Generic content shell remain accepted unless a concrete invalidation is found.

## 6. Classification rules

| Code type | Canonical owner/action |
| --- | --- |
| Presentation-only | Theme may port/move with provenance + regression evidence |
| ConvertFlow Journey/Product experience presentation | Keep with ConvertFlow; Theme may expose semantic tokens only |
| Domain/behavior | Keep with domain owner; do not transfer |
| Mixed presentation + behavior | Split before ownership changes |
| Consumer adapter/compatibility | Keep minimal at the public integration boundary |
| Historical duplicate | Retire only after canonical destination + compatibility/fallback + regression + rollback PASS |

## 7. Extraction and migration invariants

1. Freeze and hash raw evidence before extraction.
2. Never edit registered raw snapshots in place.
3. Inventory origin path/SHA before deciding reuse.
4. Classify from code + call/use sites, not filenames alone.
5. Mixed code must be split before presentation ownership moves.
6. Prefer history-preserving migration when a canonical repository exists.
7. Theme integration consumes public/versioned contracts; direct storage reads are forbidden.
8. Retire historical code only after destination, regression, compatibility/fallback and rollback are proven.

## 8. Evidence depth discipline

- L0/L1/L2 evidence cannot be promoted to runtime/browser/integration/release claims.
- Runtime/browser evidence is retained unless a concrete production-byte or contract invalidation is found.
- Package/release claims are byte-specific and must be refreshed on the final v1.0 candidate.
- PRs, Actions runs, commit hashes and package hashes live in GitHub evidence; they do not require AZT-03 version bumps unless the baseline/provenance rule itself changes.

## 9. Canonical evidence locations

Use GitHub in this order for live state:

1. canonical `main`;
2. relevant branch/PR;
3. fresh CI/runtime/browser/integration evidence;
4. release/package artifacts when byte identity matters;
5. `docs/evidence/*` checkpoints.

## 10. Rollback rule

Every release candidate must have a concrete previous package/commit or restoration path. Cleanup cannot delete provenance required to reproduce or reverse an ownership-safe migration.

## 11. Exact next

**G0:** inventory only the remaining blockers that are owned by AZnet Theme and required by the AZT-05 core release constitution. Optional provider certification defects remain tracked separately and do not authorize scope transfer.
