# AZnet Theme Source Manifest

Canonical source was reconciled for v1.1 through R0 PR #36 and current implementation state is now advanced through merged R2 Native Pattern Library / PR #39.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0-R2 |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals |
| `AZT-02-architecture.md` | v0.5 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries |
| `AZT-03-baseline-provenance.md` | v0.20 | Current canonical implementation baseline/provenance through R2 plus publication-state separation |
| `AZT-04-roadmap-qa-decisions.md` | v0.24 | R0/R1/R2 PASS, R3/R4 readiness, QA layers and accepted D-001..D-022 decisions |
| `AZT-EXEC-MAP.md` | v0.16 | Derived execution map with `R0 -> R1 -> {R2,R3,R4} -> R5 -> R6`; sequential exact next R3 |

Current implementation baseline represented by this manifest: `main@d2e4ae567108b6a224b623febca7a676b7114715`, Theme metadata `1.0.0`, WordPress `6.9+`, PHP `8.1+`, hybrid PHP + `theme.json`.

R2 provenance: PR #39 merged from head `6a40a44faaa8d8207c2a3b850e2bc6d65cd561ee`; head and merge share tree `3f9e934eaa4bdcb058ccf289d7ef1faa7eac1ee5`. Fresh exact-PR-head verification completed 12/12 workflows successfully before merge. No pull-request-triggered workflow run exists on the merge SHA.

R2 delivered exactly 18 portable patterns: 16 WordPress-native/core-block patterns plus 2 Woo-dependent patterns gated by exact public Woo block capability. Theme-switch evidence preserved saved WordPress block content byte-for-byte.

V1.0 publication is a live GitHub fact separate from implementation metadata. A fresh 07/09/2026 check still finds no Git tag and no GitHub Release, therefore publication remains `PUBLICATION_PENDING`.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.
