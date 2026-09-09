# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening, and owner-approved PR #58 Homepage/Law01/Provisioning canonical integration with D-022 exact-main verification.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and D-026 boundary |
| `AZT-03-baseline-provenance.md` | v0.30 | Canonical implementation/provenance including PR #58 merge + exact-main evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.34 | R0-R6/P2-P3/P4-A/P4-B PASS, P4 real-pilot exact next, P5 gates and D-001..D-026 |
| `AZT-EXEC-MAP.md` | v0.26 | Derived execution map with canonical PR #58 checkpoint and P4 exact next |

Current canonical implementation baseline: `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`, tree `28c30353e9f01bc51f4474347069738d410b5256`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

PR #58 integration verification caught and closed two issues before canonical merge: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

Owner-approved PR #58 merged to canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` with final tree `28c30353e9f01bc51f4474347069738d410b5256`. Fresh D-022 `V1 Exact Main Verification` run `34414878827` completed SUCCESS on that exact SHA; exact-main artifacts: static `10128678294` and clean runtime/browser `10128707347`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. RootProfile E5-C/E5-D and ConvertFlow F8 remain separate optional compatibility gates under their existing ownership rules.

**Exact next:** P4 Real Pilot QA on canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` under the existing owner approval. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Git tag/GitHub Release and final production deployment remain separate P5 owner gates.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
