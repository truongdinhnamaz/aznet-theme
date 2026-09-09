# AZnet Theme Source Manifest

Canonical source is reconciled through the completed v1.1 R6 technical candidate, merged P2/P3 editorial hardening and the approved P4 real-pilot QA next step.

| Source | Semantic version | Role |
| --- | --- | --- |
| `AZT-05-product-constitution.md` | v1.0 | Highest internal product/release constitution; unchanged by R0-R6/P2-P3 source closure |
| `AZT-00-governance.md` | v0.4 | Source priority, GitHub canonical-medium rule, change control |
| `AZT-01-product-charter.md` | v0.4 | Product scope/ownership plus approved v1.1 Native Product System objective/non-goals; unchanged by editorial pilot hardening |
| `AZT-02-architecture.md` | v0.8 | Theme architecture, public integration contracts and v1.1 settings/pattern/Header/Woo/Control Center boundaries; unchanged by P2/P3 source closure |
| `AZT-03-baseline-provenance.md` | v0.27 | Canonical implementation/provenance plus refreshed stacked Law Site Provisioning 1.1.1 cache-safe full-width candidate evidence; canonical `main` remains unchanged |
| `AZT-04-roadmap-qa-decisions.md` | v0.31 | R0-R6 + P2/P3 PASS, P4/P4-B v1.1 execution, P5 gates, QA layers and accepted D-001..D-026 decisions |
| `AZT-EXEC-MAP.md` | v0.23 | Derived execution map with P4-B Law Site Provisioning v1.1 exact execution order |

Current canonical implementation baseline represented by this manifest: `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`, implementation tree `32ebca25a63d511e74250646d333202994c65328`, Theme metadata `1.1.0`, WordPress floor `6.9+`, PHP floor `8.1+`, hybrid PHP + `theme.json`. The verified Law Site Provisioning `1.1.1` candidate remains stacked on unmerged PR #58/#59 and is provenance evidence, not canonical main.

Fresh `V1 Exact Main Verification` run `34195248467` succeeded on that exact post-P3 main SHA, covering reusable static/contracts plus clean WordPress 6.9 runtime/browser/a11y.

R4 provenance: PR #43 merged from final evidence head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. The R4 functional/test head completed 16/16 workflows successfully. Detailed evidence remains in `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 provenance: PR #44 merged from final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. The verified R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including authenticated clean-WP/Woo-present Control Center, a11y, in-place update continuity, theme-switch continuity and retained R1-R4/core regressions. Detailed evidence remains in `docs/evidence/R5_CONTROL_CENTER_L4.md`.

R6 provenance: performance/release infrastructure merged via PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`; covered legacy verification workflows were retired via PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`; owner-approved atomic metadata promotion merged via PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. Fresh exact-main verification succeeded on promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` succeeded on the same exact main SHA.

Historical R6 verified candidate: `aznet-theme-1.1.0.zip`, exactly one top-level `aznet-theme/` directory, SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`. This hash is **not** the final P5 package hash because later P2/P3 production hardening changed canonical bytes.

P2 provenance: PR #51 merged final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` to `main@a863da861b6a299920568b2b0c9ca57924727431`. Editorial Article presentation remains native-Post scoped and WordPress-owned data stays authoritative.

P3 provenance: PR #52 merged final verified head `d78091900451176c3815b23d1485036e784bdde9` to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`; head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`.

Pilot production-readiness evidence: active AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.24`, `primary_menu=yes`, RootProfile v1/v2 detected, RootProfile current-surface absent, Woo absent and ConvertFlow intentionally `unknown`. This is current-stack pilot evidence, not a replacement for support-floor certification.

Law Site Provisioning candidate provenance: exact production/package head `9a39977a6888f1fc656dd64f202e47dbd441ac3c`; candidate SHA-256 `09d92603e97688abe8a30212ed17b7e5be8e0c642ae1f78674cc95107ecf91c4`; package run `34343371228`; browser/runtime run `34343371237`. This refreshed candidate includes the pilot full-width Law 01 presentation fix plus a content-derived Law 01 stylesheet cache key so same-version asset caches cannot mask changed CSS bytes after fresh HTML is rendered. It remains stacked candidate evidence only; PR #58/#59 remain unmerged and release/deploy remain gated.

Release publication remains separate from implementation metadata. No GitHub Release or tag publication has been established; publication remains `PUBLICATION_PENDING`. Final production deployment remains separately approval-gated.

Post-P3 exact next is **P4 Real Pilot QA**. Owner approval covers P4 pilot QA execution only. P1 duplicate-theme cleanup remains a parallel destructive site-operations task and may delete only inactive legacy theme identities through WordPress core UI after smoke/rollback confidence; Theme code must not automate that cleanup. P5 tag/GitHub Release/final production deployment remain separate gates.

Derived DOCX source set produced before GitHub canonical-medium migration remains archival/export evidence only. From AZT-00 v0.4 onward, canonical source changes occur in `docs/source/` on `main` through reviewed Git history.