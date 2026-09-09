# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening, and the PR #58 Homepage/Law01/Provisioning integration candidate. Canonical `main` is unchanged until the explicit PR #58 owner gate clears.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership and v1.1 objective/non-goals |
| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and D-026 boundary |
| `AZT-03-baseline-provenance.md` | v0.29 | Canonical implementation/provenance plus PR #58 integrated candidate evidence |
| `AZT-04-roadmap-qa-decisions.md` | v0.33 | R0-R6/P2-P3 retained PASS, P4-A/P4-B PR-head PASS, PR #58 main gate, P5 gates and D-001..D-026 |
| `AZT-EXEC-MAP.md` | v0.25 | Derived execution map with PR #58 integration checkpoint and exact next |

Current canonical implementation baseline: `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, tree `32ebca25a63d511e74250646d333202994c65328`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`.

Historical D-026 stacked evidence remains preserved at verified head `d564c6e8a43f458a493fe816c4fc768d4917065f`, package run `34362997863` and browser run `34362997879`. Its `1.1.1` candidate label is historical only and is not the current integration/release metadata baseline.

The stack is now integrated upward: PR #61 -> #60 at `f10751b5fadd19107bbec7bcd1fafa2bd92d409b`; PR #60 -> #59 at `bb2155d114922f585a1650b6bbdf61b3ce6414a3`; PR #59 -> #58 at `ed087860364b8f8ad542226866d604013aee8ff5`. Each recorded merge introduced no conflict-resolution file delta against its approved head.

PR #58 integration verification caught and closed two issues before main: G8 run `34412609546` rejected unapproved Theme metadata `1.1.1`, fixed by `ec1e86f631a1b341736422296ae4689830a505c6` restoring `1.1.0`; R2 run `34413075882` exposed a disposable PHP-server lifecycle failure, fixed only in the CI harness at `356ef8c00991c63e294d619d42c7094d1074226d`.

Exact PR-head `356ef8c00991c63e294d619d42c7094d1074226d` completed all 21 triggered pull-request workflows successfully. `Law Site Provisioning Candidate Package` run `34413505990` produced `aznet-theme-1.1.0-law-site-provisioning-v1-1-candidate.zip`, 121 files, SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`, artifact `10128165973`. `Law Site Provisioning Browser Quality` run `34413506017` succeeded with artifact `10128198277`.

The known WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking; no blanket PHP-log-clean claim is made. Provider L5 certification, Git tag/GitHub Release and production deployment remain separate gates.

**Exact next:** explicit owner disposition for PR #58 -> canonical `main`. If merged, D-022 requires fresh exact-main verification on the merge SHA before any release-candidate promotion; P4 real-pilot QA then continues under its existing approval.

Derived DOCX source material remains archival/export evidence only. Canonical source changes occur in `docs/source/` through reviewed Git history.
