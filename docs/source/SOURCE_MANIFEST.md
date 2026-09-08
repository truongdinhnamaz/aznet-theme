# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate and the approved post-R6 professional editorial/law-site production-readiness sequence.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0-R6/source closure |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals; unchanged by editorial pilot hardening |
| `AZT-02-architecture.md` | v0.5 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries; unchanged by source closure |
| `AZT-03-baseline-provenance.md` | v0.23 | Canonical implementation/provenance through completed R6, Theme `1.1.0`, final deterministic candidate and publication-state separation |
| `AZT-04-roadmap-qa-decisions.md` | v0.27 | R0-R6 PASS, post-R6 P1-P5 production-readiness sequence, QA layers and accepted D-001..D-023 decisions |
| `AZT-EXEC-MAP.md` | v0.19 | Derived execution map with core `R0 -> R1 -> {R2,R3,R4} -> R5 -> R6 = PASS` and exact Theme-owned next P2 Editorial Single-Post |

Verified v1.1 implementation/release baseline represented by this manifest: `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`, implementation tree `dca0e3719063cdee22e3e535e16c61b24a8a6046`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Evidence/plan-only PR #49 subsequently merged to `main@38843b08bdc5a3d3dccbc2447029879701168db3` without changing production PHP/CSS/JS/test/workflow bytes. The production/package evidence therefore remains byte-specific to the verified `4673202f...` implementation baseline until a later production code change creates a new candidate.

R4 provenance: PR #43 merged from final evidence head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. The R4 functional/test head completed 16/16 workflows successfully. Detailed evidence remains in `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 provenance: PR #44 merged from final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. The verified R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including authenticated clean-WP/Woo-present Control Center, a11y, in-place update continuity, theme-switch continuity and retained R1-R4/core regressions. Detailed evidence remains in `docs/evidence/R5_CONTROL_CENTER_L4.md`.

R6 provenance: performance/release infrastructure merged via PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`; covered legacy verification workflows were retired via PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`; owner-approved atomic metadata promotion merged via PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. Fresh exact-main verification succeeded on promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` succeeded on the same exact main SHA.

Final verified v1.1 candidate: `aznet-theme-1.1.0.zip`, exactly one top-level `aznet-theme/` directory, SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

Pilot production-readiness evidence: active AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.24`, `primary_menu=yes`, RootProfile v1/v2 detected, RootProfile current-surface absent, Woo absent and ConvertFlow intentionally `unknown`. This is current-stack pilot evidence, not a replacement for support-floor certification.

Release publication remains separate from implementation metadata. A fresh 08/09/2026 GitHub release check returns no GitHub Release and no tag evidence has been established; publication remains `PUBLICATION_PENDING`. Production deployment remains separately approval-gated.

Post-R6 exact Theme-owned next is **P2 Editorial Single-Post Hardening**. P1 duplicate-theme cleanup is a parallel site-operations task and may delete only inactive legacy theme identities through WordPress core UI after smoke/rollback confidence; Theme code must not automate that destructive cleanup.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.