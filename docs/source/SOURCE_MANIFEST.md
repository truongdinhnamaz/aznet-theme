# AZnet Theme Source Manifest

Canonical source was reconciled for v1.1 through R0 PR #36 and current implementation state is now advanced through merged R5 Control Center + System Health / PR #44.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0-R5 |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals |
| `AZT-02-architecture.md` | v0.5 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries |
| `AZT-03-baseline-provenance.md` | v0.22 | Current canonical implementation baseline/provenance through R5 plus publication-state separation |
| `AZT-04-roadmap-qa-decisions.md` | v0.26 | R0-R5 PASS, R6 exact-next readiness, QA layers and accepted D-001..D-022 decisions |
| `AZT-EXEC-MAP.md` | v0.18 | Derived execution map with `R0 -> R1 -> {R2,R3,R4} -> R5 -> R6`; sequential exact next R6 |

Current implementation baseline represented by this manifest: `main@484e896cace06d684d29a3f93e3cce85e84a9f80`, production tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`, Theme metadata `1.0.0`, WordPress `6.9+`, PHP `8.1+`, hybrid PHP + `theme.json`.

R4 provenance: PR #43 merged from final evidence head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. The R4 functional/test head completed 16/16 workflows successfully. Detailed evidence remains in `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 provenance: PR #44 merged from final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. The verified R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including authenticated clean-WP/Woo-present Control Center, a11y, in-place update continuity, theme-switch continuity and retained R1-R4/core regressions. Detailed evidence remains in `docs/evidence/R5_CONTROL_CENTER_L4.md`.

After the approved R5 merge, a temporary source-state sentinel was accidentally added and immediately removed on `main`. Current `main@484e896cace06d684d29a3f93e3cce85e84a9f80` compares to the R5 merge with no changed files, so the represented implementation tree is still exactly the R5 tree.

Release publication remains separate from implementation metadata. A fresh 08/09/2026 GitHub release check returns no GitHub Release and no tag evidence has been established; publication remains `PUBLICATION_PENDING`. Theme metadata remains `1.0.0` until R6 reaches the explicit metadata/version-promotion gate. Final installable v1.1 packaging must retain exactly one canonical top-level `aznet-theme/` directory and internal Theme metadata must match the promoted release version.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.
