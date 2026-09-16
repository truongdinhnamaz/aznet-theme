# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening, PR #58 Homepage/Law01/Provisioning integration, and D-027 Standalone Core closure through source PR #63 + owner-approved implementation PR #64 with fresh exact-main verification.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.9 | Theme architecture, public integration contracts and D-027 Standalone Core runtime-independence boundary |
| `AZT-03-baseline-provenance.md` | v0.32 | Canonical implementation/provenance through D-027 closure plus P4 public-pilot evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.37 | R0-R6/P2-P3/P4-A/P4-B/P4-C PASS; P4 public QA PASS/authenticated QA BLOCKED; P5 gates and D-001..D-027 |
| `AZT-EXEC-MAP.md` | v0.29 | Derived execution map with P4 public matrix PASS and authenticated pilot QA as exact next |

Current canonical implementation baseline: `main@16563bdc88f172a1d7737b92293c7958670aac96`, tree `d2c4128f2b860bfd59c5efa11565dba38a34458e`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

PR #58 integration verification caught and closed two issues before canonical merge: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

Owner-approved PR #58 merged to canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` with final tree `28c30353e9f01bc51f4474347069738d410b5256`. Fresh D-022 `V1 Exact Main Verification` run `34414878827` completed SUCCESS on that exact SHA; exact-main artifacts: static `10128678294` and clean runtime/browser `10128707347`.

D-027 source ratification PR #63 merged to `main@5d6dc56a756028d696a269ed75dd762daf4b83f2`; owner-approved implementation PR #64 merged final head `4b38f792d7de575bc08733748dac0399372e18c2` to `main@16563bdc88f172a1d7737b92293c7958670aac96`. Head and merge share tree `d2c4128f2b860bfd59c5efa11565dba38a34458e`. Fresh exact-main run `35042767873` completed SUCCESS; artifacts: static `10425656989`, runtime/browser `10426026827`. Final-head exact-package run `35042040620` also succeeded with D-027 candidate SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.

P4 public-pilot QA then completed through GitHub Actions on functional head `758392b03227eb446627cc4669b0b0ddf56ed71e`. Run `35045540722` passed 28 public route/viewport checks; artifact `10427345921`, digest `sha256:cf84a35e722f4b5b8c944c42dee3f1bdb344f191e07529b002636afb42420b78`. Authenticated Admin/System Health remains BLOCKED and full P4 PASS is not inferred. Evidence: `docs/evidence/P4_PUBLIC_PILOT_QA_20260916.md`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. RootProfile E5-C/E5-D and ConvertFlow F8 remain separate optional compatibility gates under their existing ownership rules.

**Exact next:** P4 public pilot QA is PASS at unauthenticated scope. Obtain a secure authenticated WordPress Admin path and complete the remaining System Health/admin-only verification; until then record `P4 PUBLIC QA PASS / AUTHENTICATED QA BLOCKED`. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Git tag/GitHub Release and final production deployment remain separate P5 owner gates.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
