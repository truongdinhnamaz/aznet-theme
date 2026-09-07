# AZnet Theme Source Manifest

Canonical source was reconciled for v1.1 through R0 PR #36 and current implementation state is now advanced through merged R3 Header System 2.0 / PR #41.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0-R3 |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals |
| `AZT-02-architecture.md` | v0.5 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries |
| `AZT-03-baseline-provenance.md` | v0.21 | Current canonical implementation baseline/provenance through R3 plus publication-state separation |
| `AZT-04-roadmap-qa-decisions.md` | v0.25 | R0-R3 PASS, R4 exact-next readiness, QA layers and accepted D-001..D-022 decisions |
| `AZT-EXEC-MAP.md` | v0.17 | Derived execution map with `R0 -> R1 -> {R2,R3,R4} -> R5 -> R6`; sequential exact next R4 |

Current implementation baseline represented by this manifest: `main@d8d5670d57dc62f7e2399d9e1b2eee70fd85ef9d`, Theme metadata `1.0.0`, WordPress `6.9+`, PHP `8.1+`, hybrid PHP + `theme.json`.

R3 provenance: PR #41 merged from verified head `c4e6e623e4fc0113bdbc211fecf34b40528b7f77`; head and merge share tree `ebe79f1dc65ab58794592eab59a5bb2b9b3a1a00`. Fresh exact-PR-head verification completed 14/14 workflows successfully before merge. No pull-request-triggered workflow run exists on the merge SHA, so no post-merge execution layer is inferred beyond exact tree equivalence.

R3 delivered four bounded Theme-owned Header presets (`standard`, `compact`, `commerce`, `overlay`), three sticky modes, normalized Header settings, reusable primitives/composer, accessible no-JS-safe mobile navigation, sticky-compact progressive enhancement and public-Woo fail-soft Commerce actions. Detailed evidence remains in `docs/evidence/R3_HEADER_SYSTEM_L4.md`.

V1.0 publication is a live GitHub fact separate from implementation metadata. A fresh 08/09/2026 check still finds no Git tag and no GitHub Release, therefore publication remains `PUBLICATION_PENDING`.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.
