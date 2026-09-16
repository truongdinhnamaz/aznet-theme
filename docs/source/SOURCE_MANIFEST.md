# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 program, merged P2/P3 editorial hardening, PR #58 Homepage/Law01/Provisioning integration, D-027 Standalone Core closure, P4 public/authenticated QA, P1 cleanup/final pilot sign-off, and fresh P5 exact-canonical final-candidate technical verification.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.9 | Theme architecture, public integration contracts and D-027 Standalone Core runtime-independence boundary |
| `AZT-03-baseline-provenance.md` | v0.35 | Canonical implementation/provenance through D-027, P1/P4 closure and P5 deterministic final-candidate technical evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.40 | R0-R6/P1-P4 PASS; P5 technical candidate PASS with publication/deployment gates retained |
| `AZT-EXEC-MAP.md` | v0.32 | Derived execution map with P5 technical candidate PASS and publication disposition as exact next hard gate |

Current canonical implementation baseline: `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`, tree `f21884f5989239858261721c5002c5158d6d9be0`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

PR #58 integration verification caught and closed two issues before canonical merge: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

Owner-approved PR #58 merged to canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` with final tree `28c30353e9f01bc51f4474347069738d410b5256`. Fresh D-022 `V1 Exact Main Verification` run `34414878827` completed SUCCESS on that exact SHA; exact-main artifacts: static `10128678294` and clean runtime/browser `10128707347`.

D-027 source ratification PR #63 merged to `main@5d6dc56a756028d696a269ed75dd762daf4b83f2`; owner-approved implementation PR #64 merged final head `4b38f792d7de575bc08733748dac0399372e18c2` to `main@16563bdc88f172a1d7737b92293c7958670aac96`. Head and merge share tree `d2c4128f2b860bfd59c5efa11565dba38a34458e`. Fresh exact-main run `35042767873` completed SUCCESS; artifacts: static `10425656989`, runtime/browser `10426026827`. Final-head exact-package run `35042040620` also succeeded with D-027 candidate SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.

P4 public-pilot QA completed through GitHub Actions on functional head `758392b03227eb446627cc4669b0b0ddf56ed71e`. After the owner replaced historical pilot Theme `1.1.1` bits with the exact D-027 `1.1.0` package, authenticated read-only run `35047440842` completed SUCCESS with artifact `10427876863`, digest `sha256:96d2943661e27ef05ce5b16940a792fbd4727d635849a8f5e9e8589b03d2abe9`; the public workflow was rerun after replacement and again passed 28/28 checks with artifact `10427713118`, digest `sha256:c58d0efebe9fdeea14c2d9d1ba3fa8a4439f2bafffc1dc180cf3b1d1554cbadb`. Owner-approved PR #67 merged authenticated QA to canonical main, and exact-main run `35048609753` succeeded with artifacts `10427853326` (static) and `10427687863` (clean runtime/browser). P4 public + authenticated QA is retained. Owner-approved PR #69 proved the exact legacy duplicate identity; after backup confirmation the owner approved deletion of only `aznet-theme-release-v1.0.0` through WordPress core UI. Fresh post-delete run `35050519817` reports `NO_DUPLICATES`, active `aznet-theme` v1.1.0, Standalone Core `ready`, and a 28-check public regression PASS. P1 and final P4 pilot sign-off are therefore PASS at their defined Theme-owned/site-operations scopes. Evidence: `docs/evidence/P4_PUBLIC_PILOT_QA_20260916.md`, `docs/evidence/P4_AUTHENTICATED_PILOT_QA_20260916.md`, and `docs/evidence/P1_POST_DELETE_CLOSURE_20260916.md`.

P5 final-candidate technical verification run `35051428372` explicitly checked out canonical `main@f0b2d581b16a663e4b422234e6ac15568bcc7985`, tree `f21884f5989239858261721c5002c5158d6d9be0`. Exact-main/core verification and clean WordPress `6.9` zero-plugin runtime/browser gates passed. The final package `aznet-theme-1.1.0.zip` was built twice byte-identically, exact-matched 121 production files, linted 93 packaged PHP files, passed clean WordPress/browser gates, preserved WordPress-owned continuity through `twentytwentyfive` switch-away and passed again after switching back. ZIP SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`; exact-main artifact `10429087315`; final-package artifact `10428893463`. P5 state is TECHNICAL CANDIDATE PASS / PUBLICATION GATED. Evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. RootProfile E5-C/E5-D and ConvertFlow F8 remain separate optional compatibility gates under their existing ownership rules.

**Exact next:** P5 publication disposition. Technical candidate verification is PASS; Git tag and/or GitHub Release require separate explicit owner approval, and final production deployment remains a separate later owner gate.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
