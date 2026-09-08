# AZT-03 — Current Baseline và Code Provenance

**Version:** v0.25  
**Status:** Working Source  
**Date:** 08/09/2026  
**Repository:** `truongdinhnamaz/aznet-theme`

## 1. Source ownership

AZT-03 owns the canonical implementation baseline, provenance rules, registered source artifacts and migration evidence model. It does not own product scope, architecture, public contracts or roadmap semantics.

Live implementation facts are resolved from GitHub. Historical evidence is not rewritten merely to make the document look current.

## 2. Current canonical baseline

- Current canonical repository/implementation head after PR #57: `main@12a480311dcb59f3d7a152ef7e72ca927c2f632d`.
- Current canonical implementation tree: `32ebca25a63d511e74250646d333202994c65328`.
- Internal Theme version remains `1.1.0`.
- WordPress floor: `6.9+`.
- PHP floor: `8.1+`.
- Architecture: hybrid PHP theme + `theme.json`.
- Naming family: `AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.

Current production state includes:

- Milestone 0 Source Freeze & Provenance;
- Theme Foundation;
- Semantic Design Tokens;
- Header/Footer presentation;
- Generic Templates Page/Post/Archive/Search/404;
- WooCommerce presentation work W1-W9 including the public ConvertFlow token projection;
- Theme-native WordPress Homepage shell;
- G0-G8 core-v1.0 cleanup/release-candidate closure, including metadata promotion to `1.0.0`;
- R0 v1.0 -> v1.1 source reconciliation;
- R1 Design System 2.0, including one normalized/versioned Theme settings schema, expanded semantic tokens, Default/Editorial/Commerce visual presets and editor/frontend parity evidence;
- R2 Native Pattern Library with 18 shipped patterns: 16 WordPress-native/core-block patterns plus 2 Woo-block-dependent patterns registered only through exact public block capability detection;
- R3 Header System 2.0 with four bounded Theme-owned presets (`standard`, `compact`, `commerce`, `overlay`), three sticky modes, shared primitives/composer, accessible progressive-enhancement mobile navigation and public-Woo fail-soft Commerce actions;
- R4 WooCommerce Presentation 2.0 with bounded catalog/card/product presentation presets, native Woo product/gallery/variation controls, responsive Cart/Checkout/Account presentation and public-capability/content-gated Woo Blocks styling while WooCommerce retains commerce truth;
- R5 Control Center + System Health with WordPress-native Logo/Menu ownership, the single `aznet_theme_settings` presentation schema, guarded save/reset/import/export, public/read-only capability diagnostics and update/theme-switch continuity evidence;
- R6 Performance + Release 2.0 technical closure: measured asset/performance evidence, reusable PR/exact-main verification, deterministic v1.x package infrastructure, bounded workflow retirement, atomic metadata promotion to `1.1.0`, and post-promotion exact-main/package verification;
- P2 Editorial Single-Post Hardening: native WordPress publication presentation, singular-Post scoped article assets and fail-soft native author handling without SEO/domain/query takeover;
- P3 Editorial Listing/Search Hardening: native featured image/date scan presentation and resilient long-title/excerpt styling while preserving WordPress main-query ownership.

PR #34 final verified head is `b2e5cca1461233bcb1a0333c5aa51879c3264756`. Its tree `b716b89f04e45c2012f8e191c7d0edf605c9dd11` is identical to the merge commit tree on canonical `main@f8e1a95c903c3f246528368ae9878eba780539ff`, so the v1.0 technical merge introduced no conflict-resolution production delta.

R0 source reconciliation merged through PR #36 and established `main@fdb227b20d569bc3bcbf81ea570f94c3a7723e87` as the production base for R1.

R1 merged through PR #37 from verified head `a6b36615c9f6391cbe103844dee7659ff0dccb76` to canonical `main@80f28be8042cee0d2995784506ffb685b9eb36cb`. The R1 head tree and merge tree are identical at `880ce360d8c0c5003869d761e76833737d895fff`, so the merge introduced no conflict-resolution delta. Fresh exact-PR-head verification recorded 10/10 successful workflows before merge; no post-merge pull-request-triggered run exists on the merge SHA.

R1 current-state closure merged through PR #38 and established `main@b31f294d349deea62b07f2358c733c649c4c48a2` as the production base for R2.

R2 merged through PR #39 from verified head `6a40a44faaa8d8207c2a3b850e2bc6d65cd561ee` to canonical `main@d2e4ae567108b6a224b623febca7a676b7114715`. The R2 head tree and merge tree are identical at `3f9e934eaa4bdcb058ccf289d7ef1faa7eac1ee5`, so the merge introduced no conflict-resolution delta. Fresh exact-PR-head verification recorded 12/12 successful workflows before merge. No post-merge pull-request-triggered workflow run exists on the merge SHA, so no fresh L2-L4 claim is inferred for the merge commit beyond exact tree equivalence.

R2 current-state closure merged through PR #40 and established `main@29d05e501b6f992793df8f961003d29aa8636af2` as the production base for R3.

R3 merged through PR #41 from verified head `c4e6e623e4fc0113bdbc211fecf34b40528b7f77` to canonical `main@d8d5670d57dc62f7e2399d9e1b2eee70fd85ef9d`. The R3 head tree and merge tree are identical at `ebe79f1dc65ab58794592eab59a5bb2b9b3a1a00`, so the merge introduced no conflict-resolution production delta. Fresh exact-PR-head verification recorded 14/14 successful workflows before merge, including R3 static/browser, retained R1/R2, clean WordPress runtime, core browser/a11y/performance, lifecycle, release-candidate package and native Homepage regression.

R4 merged through PR #43 from final evidence head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` to canonical `main@0ddc6c799391d57db134f04449effdf511d41d1f`. Head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`, so merge introduced no conflict-resolution delta. The verified functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully; the final R4 evidence-only head retained fresh 16/16 success. Detailed evidence is `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 merged through PR #44 from final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` to canonical merge `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`. Head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`, so merge introduced no conflict-resolution delta. The verified functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including authenticated clean-WP/Woo-present Control Center, update continuity, theme-switch continuity and retained R1-R4/core regressions. The final R5 evidence-only head differs from that verified functional/test head only by `docs/evidence/R5_CONTROL_CENTER_L4.md`. Detailed evidence is recorded there.

Immediately after the approved R5 merge, an accidental source-state sentinel file was committed to `main` at `e924e964c719149cf285fad8243331c0a27ab947` and then removed at `484e896cace06d684d29a3f93e3cce85e84a9f80`. Direct compare `6c69def2... -> 484e896...` returns no changed files, so those two no-op history commits did not alter implementation bytes or any PASS claim.

R6 infrastructure merged through PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`. It established measured clean/Woo route and asset baselines, reusable v1 PR/exact-main verification and deterministic release-candidate packaging while retaining the ConvertFlow asset-gating item as `BLOCKED_EXTERNAL_CONTRACT` rather than using private detection.

Post-merge workflow retirement merged through PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`, removing only superseded G workflows after replacement coverage and retaining unmatched lifecycle/version gates.

Owner-approved metadata promotion merged through PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. `style.css` Theme Version and `AZNET_THEME_VERSION` were promoted atomically to exact `1.1.0` after RED -> GREEN version and byte-preservation gates. Fresh exact-main verification succeeded on those promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` also succeeded on that exact main SHA. The deterministic R6 candidate was `aznet-theme-1.1.0.zip`, contained exactly one top-level `aznet-theme/` directory and had SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

PR #49 merged evidence/audit and the law-site production-readiness plan to `main@38843b08bdc5a3d3dccbc2447029879701168db3`; it changed documentation only and therefore did not invalidate the then-verified R6 package bytes.

P2 Editorial Single-Post Hardening merged through PR #51 from final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` to `main@a863da861b6a299920568b2b0c9ca57924727431`. It added bounded native-Post editorial presentation and singular-Post scoped article CSS. During verification, regressions caught an empty native-author link and a Woo/CPT scope leak; both were reduced to Theme-owned root causes and fixed before merge. Fresh exact-main verification succeeded after merge.

P3 Editorial Listing/Search Hardening merged through PR #52 from final verified head `d78091900451176c3815b23d1485036e784bdde9` to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh `V1 Exact Main Verification` run `34195248467` completed successfully on that exact merge SHA, covering reusable static/contracts plus clean WordPress 6.9 runtime/browser/a11y.

Because P2/P3 changed production bytes after the R6 package, SHA-256 `000735...` is now **historical R6 candidate evidence**, not the final package for P4/P5. A new deterministic package/SHA must be produced from the final post-hardening bytes before publication.

No source statement above transfers domain ownership to the Theme.


### Law Site Provisioning v1 candidate provenance — stacked, not canonical main

D-025 provisioning implementation is verified on stacked branch `work/law-site-provisioning`, above Homepage Composer + Law 01 PR #58. The exact production/package head is `6acd8b633503a701eb47ed0df072f6def4e1b03e`; documentation-only evidence commits after that head do not change candidate production bytes.

Fresh exact-head `Law Site Provisioning Candidate Package` run `34258576549` passed the reusable v1 core chain on PHP 8.1.34 with mbstring/mysqli, linted 89 production PHP files, built the candidate twice deterministically, and passed exact-byte unpacked/source comparison. Candidate: `aznet-theme-1.1.1-law-site-provisioning-candidate.zip`, 112 files, SHA-256 `570e4e46294c45621ed5bbbd030c4aa8e12c050254db413f2d34da2855e628dd`, artifact ID `10068904666`.

Fresh exact-head `Law Site Provisioning Browser Quality` run `34258576713` passed WordPress 6.9 / PHP 8.1 / MySQL 8 empty-site, existing-site no-overwrite, rerun idempotency, injected rollback and user-edit-preservation scenarios. Wizard Steps 1-4 and public Law 01 at 1440/1024/390/320 reported zero critical/serious axe blockers. Artifact ID `10068945557`. Detailed evidence: `docs/evidence/LAW_SITE_PROVISIONING_L4.md`.

This candidate provenance does not make the branch canonical `main`, does not imply PR #58/#59 merge, and does not authorize a Git tag, GitHub Release or production deployment.

## 3. Current release-path classification

Under AZT-05 v1.0 and D-016:

- WordPress-clean core Theme readiness is release-critical.
- R0 through R6 core-v1.1 technical implementation/release-candidate closure remains complete.
- P2 and P3 editorial production-hardening implementation is now merged and exact-main verified.
- Theme metadata remains `1.1.0`; package filenames do not substitute for metadata.
- The R6 package hash `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` is retained as historical provenance only because later P2/P3 production bytes invalidate it as a final candidate.
- Release publication is tracked separately from implementation readiness. No GitHub Release or tag publication has been established; publication remains `PUBLICATION_PENDING`.
- Production deployment remains separately approval-gated. Approval to run P4 pilot QA does not imply final production deployment.
- RootProfile E5-C/E5-D remains an optional provider certification/takeover track, not a core-v1.x blocker.
- ConvertFlow F6/F7 actual-package integration evidence remains retained; F8 integrated compatibility remains BLOCKED because provider body output introduces a second document-level `<main>` inside Theme-owned `main#main`.
- R6 ConvertFlow projection asset optimization remains `BLOCKED_EXTERNAL_CONTRACT` until a documented public/versioned Theme-consumable capability exists; safe global bridge behavior is retained.
- External compatibility defects must not be bypassed with private APIs, authoritative heuristics or copied domain logic.
- The pilot System Health evidence shows active AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.24`, primary menu continuity, RootProfile v1/v2 present, RootProfile current-surface absent, Woo absent and ConvertFlow intentionally `unknown`.

Current product-development next is governed by AZT-04. P2/P3 are closed; the exact next is P4 Real Pilot QA. Duplicate-theme cleanup on the pilot remains a separate destructive site-operations gate until completed.

## 4. Registered source artifacts

| ID | Artifact | Role | Inventory status |
| --- | --- | --- | --- |
| S-001 | `convertflow-0.1.0-p5.223-homepage-no-woo-mode.zip` | ConvertFlow historical evidence/provenance candidate | frozen/hash/extract/classify PASS |
| S-002 | `convertflow-pro-0.1.0-alpha.118-p5.162-r1-platform-compatible.zip` | ConvertFlow Pro compatibility/presentation evidence | frozen/hash/extract/classify PASS |
| S-003 | `RootProfile-0.1.0-alpha.99.zip` | RootProfile profile/integration evidence | frozen/hash/extract/classify PASS |
| R-001 | AZnet Theme Code Reuse Handoff from ConvertFlow v1.0 | technical handoff/reference | reviewed |

Historical build labels do not establish file ownership by themselves.

## 5. Provenance inventory baseline

The accepted source-bound inventory contains **817/817 classified candidates**:

- Domain: 454
- Mixed: 203
- Adapter: 97
- Presentation: 63

Reuse classification:

- Keep: 602
- Split: 203
- Port: 9
- Reference: 3

The 9/9 pre-port visual baselines for Header, Footer and Generic content shell remain accepted unless a concrete invalidation is found.

## 6. Classification rules

| Code type | Canonical owner/action |
| --- | --- |
| Presentation-only | Theme may port/move with provenance + regression evidence |
| ConvertFlow Journey/Product experience presentation | Keep with ConvertFlow; Theme may expose semantic tokens only |
| Domain/behavior | Keep with domain owner; do not transfer |
| Mixed presentation + behavior | Split before ownership changes |
| Consumer adapter/compatibility | Keep minimal at the public integration boundary |
| Historical duplicate | Retire only after canonical destination + compatibility/fallback + regression + rollback PASS |

## 7. Extraction and migration invariants

1. Freeze and hash raw evidence before extraction.
2. Never edit registered raw snapshots in place.
3. Inventory origin path/SHA before deciding reuse.
4. Classify from code + call/use sites, not filenames alone.
5. Mixed code must be split before presentation ownership moves.
6. Prefer history-preserving migration when a canonical repository exists.
7. Theme integration consumes public/versioned contracts; direct storage reads are forbidden.
8. Retire historical code only after destination, regression, compatibility/fallback and rollback are proven.

## 8. Evidence depth discipline

- L0/L1/L2 evidence cannot be promoted to runtime/browser/integration/release claims.
- Runtime/browser evidence is retained unless a concrete production-byte or contract invalidation is found.
- Package/release claims are byte-specific and must be refreshed on the final candidate bytes of the release being promoted.
- PRs, Actions runs, commit hashes and package hashes live in GitHub evidence; they do not require AZT-03 version bumps unless the represented canonical baseline/provenance statement changes.
- A Theme version string does not prove publication. Tag/Release existence and target SHA are live GitHub facts and must be checked separately.
- Pilot evidence on WordPress `7.1` / PHP `8.4.24` is current-stack evidence only and does not replace the support-floor `6.9+` / `8.1+` release matrix.

## 9. Canonical evidence locations

Use GitHub in this order for live state:

1. canonical `main`;
2. relevant branch/PR;
3. fresh CI/runtime/browser/integration evidence;
4. release/package artifacts when byte identity matters;
5. `docs/evidence/*` checkpoints.

## 10. Rollback rule

Every release candidate must have a concrete previous package/commit or restoration path. Cleanup cannot delete provenance required to reproduce or reverse an ownership-safe migration.

## 11. Exact next

**P4 — Real Pilot QA from canonical `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0` after source closure. Use the approved pilot execution to prove current-stack routes/responsive/a11y/Rank Math/RootProfile compatibility and measured site behavior. P1 duplicate-theme deletion remains a separate destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**