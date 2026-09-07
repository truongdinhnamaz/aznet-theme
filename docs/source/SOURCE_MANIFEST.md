# AZnet Theme Source Manifest

Canonical source was reconciled for v1.1 through R0 PR #36 and current implementation state is now advanced through merged R1 Design System 2.0 / PR #37.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0/R1 |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals |
| `AZT-02-architecture.md` | v0.5 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries |
| `AZT-03-baseline-provenance.md` | v0.19 | Current canonical implementation baseline/provenance through R1 plus publication-state separation |
| `AZT-04-roadmap-qa-decisions.md` | v0.23 | R0/R1 PASS, R2/R3/R4 readiness, QA layers and accepted D-001..D-022 decisions |
| `AZT-EXEC-MAP.md` | v0.15 | Derived execution map with `R0 -> R1 -> {R2,R3,R4} -> R5 -> R6`; sequential exact next R2 |

Current implementation baseline represented by this manifest: `main@80f28be8042cee0d2995784506ffb685b9eb36cb`, Theme metadata `1.0.0`, WordPress `6.9+`, PHP `8.1+`, hybrid PHP + `theme.json`.

R1 provenance: PR #37 merged from head `a6b36615c9f6391cbe103844dee7659ff0dccb76`; head and merge share tree `880ce360d8c0c5003869d761e76833737d895fff`. Fresh exact-PR-head verification completed 10/10 workflows successfully before merge.

V1.0 publication is a live GitHub fact separate from implementation metadata. A fresh 07/09/2026 check still finds no Git tag and no GitHub Release, therefore publication remains `PUBLICATION_PENDING`.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.
