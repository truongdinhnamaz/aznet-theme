# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.39
**Status:** Working Execution Map / derived  
**Date:** 16/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

> **Canonical-main checkpoint:** live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` is `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`; publication closure PR #72 merged at `15f25e4f6d8e64c588866405629b793a85ee0323`, and restored post-noop checkpoint `e7e5a9c2d2a867f631b029f3f77a675e32fad573` has identical tree `d50c9f04465f7f6990ac3747ee55a848e3187beb`. P5 technical run `35051428372`, publication run `35052694111`, and production read-only run `35054176392` close the v1.1 release-critical path. Optional integrations remain additive and ownership boundaries are unchanged.

**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> PR #70 -> P5 exact-main + deterministic package/lifecycle PASS run 35051428372 -> owner-approved PR #71 -> publication run 35052694111 -> annotated tag v1.1.0 + GitHub Release PASS -> owner-approved production deployment disposition -> read-only run 35054176392 PASS -> P5 PASS`.

## 1. Current state

| Workstream | State | Boundary |
| --- | --- | --- |
| 0 Source Freeze & Provenance | PASS | 817/817 classification + accepted source-bound evidence |
| A Theme Foundation | PASS | WordPress-clean hybrid PHP + `theme.json` |
| B Semantic Tokens | PASS / retained | `--aznet-theme-*` + `theme.json`; R1 extended semantics without silently repurposing public tokens |
| C Header/Footer | PASS / retained | v1.0 Theme presentation owner; R3 adds the bounded v1.1 preset system |
| D Generic Templates | PASS / retained | Page/Post/Archive/Search/404 technical closure plus P2/P3 editorial hardening and P4 professional pilot quality evidence are retained |
| W Woo presentation | PASS / retained | Woo owns commerce truth; R4 deepens presentation only |
| E RootProfile Profile/Contact | OPTIONAL COMPAT ACTIVE | External E5-C blocker; E5-D takeover locked; not core-v1.1 critical path |
| F Homepage | CORE PASS / COMPAT ACTIVE | Native Homepage on main; external F8 compatibility defect remains non-blocking under D-016 |
| G Core v1.0 closure | TECHNICAL PASS | G0-G8 merged; publication/tag is separate live state |
| R0 v1.1 Source Reconciliation | PASS | Source-only reconciliation merged through PR #36 |
| R1 Design System 2.0 | PASS | PR #37 merged; stable Theme settings/tokens/visual presets and L1-L4 parity evidence |
| R2 Native Pattern Library | PASS | PR #39 merged; 18 portable patterns, public Woo-block gating and theme-switch portability evidence |
| R3 Header System 2.0 | PASS | PR #41 merged; bounded presets/primitives, accessible mobile/sticky behavior and L1-L4 browser evidence |
| R4 WooCommerce Presentation 2.0 | PASS | PR #43 merged; public Woo output -> Theme presentation only; clean-WP/Woo-present L1-L4 evidence |
| R5 Control Center + System Health | PASS | PR #44 merged; bounded Theme settings admin, public/read-only health diagnostics and update/theme-switch continuity |
| R6 Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 retained technical provenance; R6 itself did not imply publication/deployment, both of which were later completed under P5 |
| P1 Pilot identity cleanup | PASS | Only intended active `aznet-theme` v1.1.0 remains among AZnet Theme identities; post-delete inventory/System Health/public regression PASS |
| P2 Editorial Single-Post | PASS | PR #51 merged; native WordPress editorial presentation only; no SEO/domain/query takeover |
| P3 Editorial Listing/Search | PASS | PR #52 merged; native thumbnail/date scan presentation + resilient long titles/excerpts; main query retained |
| P4 Real Pilot QA | PASS / PILOT SIGN-OFF CLOSED | Public + authenticated QA retained; P1 cleanup closed with fresh post-delete inventory/System Health and 28-check public regression PASS |
| P4-A Homepage Composer + Law 01 | PASS / MERGED | PR #58 merged; exact-main static/runtime/browser core verification PASS |
| P4-B Law Site Provisioning | PASS / MERGED | D-026 merged; Theme `1.1.0`; deterministic package/browser evidence retained; activation mutation-free and active-site overwrite/noindex takeover forbidden |
| P4-C Standalone Core independence | PASS / MERGED | PR #63/#64 merged; zero-plugin L1-L4, exact-package and exact-main PASS; optional integrations remain additive |
| P5 Publication/Deployment | PASS | Tag `v1.1.0` + GitHub Release and owner-approved production deployment disposition for `tamduchanoi.aznet.vn` are complete; fresh read-only run `35054176392` PASS at the tested Theme-owned/site-operations scope |
| X v1.2 WordPress Experience Completion | X4 PASS / X5 NEXT | X1 Comments, X2 Search/404/empty states, X3 Media/Gallery/Embed and X4 Pagination/Navigation L1-L4 PASS; X5 Native Form Controls plan next; Core WordPress surfaces only and no provider integration expansion |
| U Historical Control Center | REFERENCE ONLY | Do not wholesale-merge; R5 owns the accepted bounded admin outcome |

Live `main` HEAD is resolved from GitHub at execution time. Stable release anchor `v1.1.0` is `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`, tree `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`; publication closure PR #72 merged at `15f25e4f6d8e64c588866405629b793a85ee0323` and restored post-noop checkpoint `e7e5a9c2d2a867f631b029f3f77a675e32fad573` has identical tree `d50c9f04465f7f6990ac3747ee55a848e3187beb`. Theme metadata is `1.1.0`. P5 run `35051428372` verified the final package, publication run `35052694111` published tag/Release, and production read-only run `35054176392` verified the owner-approved deployment disposition.

The previous R6 package SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical. Fresh P5 independently rebuilt the current canonical production file set as deterministic `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`, with exact 121-file source/package identity and 93 packaged PHP files linted.

Fresh authenticated pilot evidence proves active AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.25`, D-027 current System Health shape, Standalone Core `ready`, `primary_menu=yes`, RootProfile v1/v2 available, current-surface absent, Woo absent and ConvertFlow intentionally `unknown`.

Fresh P4 public evidence after Theme replacement retains run `35045540722` with 28/28 route/viewport checks PASS and fresh artifact `10427713118`. Authenticated read-only run `35047440842` PASS adds current Admin/System Health evidence with artifact `10427876863`; PR #67 merged the harness/evidence and exact-main run `35048609753` then PASSed on canonical main. RootProfile public profile surface was not observed in the tested public matrix, so L5 is not inferred.

P2 final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` merged through PR #51 to `main@a863da861b6a299920568b2b0c9ca57924727431` after Theme-owned author-fallback and CPT/Woo scope regressions were fixed.

P3 final verified head `d78091900451176c3815b23d1485036e784bdde9` merged through PR #52 to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`; head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`.

## 2. Slice discipline

Every slice must state:

- ID;
- one goal;
- Theme ownership vs external ownership;
- dependencies;
- allowed APIs/files;
- forbidden scope;
- concrete deliverable;
- evidence layer;
- PASS/FAIL exit;
- rollback;
- exactly one Next.

Production feature/bugfix behavior follows RED -> intended failure -> minimal GREEN -> regression.

Do not start production implementation from an unmerged source decision when the slice changes product/architecture/public-contract/release governance.

## 3. QA layer map

| Layer | Evidence |
| --- | --- |
| L0 | source/state/ownership/current base |
| L1 | lint/static/naming/private-storage/dependency scans |
| L2 | RED/GREEN contract/unit/template-render behavior |
| L3 | real WordPress runtime |
| L4 | browser/responsive/keyboard/focus/a11y/console + editor/frontend parity where applicable |
| L5 | provider/theme-switch/integration boundary and public capability correctness |
| L6 | full regression/package/SHA/unzip-reverify/rollback/release evidence on final bytes |

No layer may be inferred from another.

## 4. Completed v1.1 dependency order

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6 = PASS`

- R0 source reconciliation complete.
- R1 settings/token interfaces complete.
- R2 Native Pattern Library complete.
- R3 Header System 2.0 complete.
- R4 WooCommerce Presentation 2.0 complete.
- R5 Control Center + System Health complete.
- R6 performance/release technical closure complete on verified `1.1.0` R6 candidate bytes.

## 5. R0 — Source reconciliation

**Goal:** reconcile canonical source from stale alpha/G0 facts to the actual v1.0 merged state and ratify the approved v1.1 Native Product System architecture.

**Exit:** PASS — source-only PR #36 merged; AZT-05 byte-unchanged.

## 6. R1 — Design System 2.0

**Goal:** create the stable Theme-owned presentation settings/token foundation for v1.1.

**Produces:** one versioned `aznet_theme_settings` Theme Mod schema; expanded semantic tokens retaining public aliases; Default/Editorial/Commerce visual outcomes; editor/frontend parity.

**Forbidden:** FSE takeover, proprietary content schema, provider/domain state in Theme settings.

**Exit:** PASS — L1-L4; PR #37 merged.

## 7. R2 — Native Pattern Library

**Goal:** speed up authoring with curated portable block compositions instead of a proprietary builder.

**Shipped set:** 18 patterns total — 16 WordPress-native/core patterns plus 2 Woo-dependent patterns registered only when exact public Woo block types are present.

**Exit:** PASS — L1-L4, clean-WP/Woo-present browser/editor matrices and byte-identical theme-switch portability proven; PR #39 merged.

## 8. R3 — Header System 2.0

**Goal:** provide four high-quality bounded Header presentations without a Header Builder.

**Presets:** Standard, Compact, Commerce, Overlay. Three sticky modes retained.

**Forbidden:** slug/title/Page-ID/URL authoritative heuristic, mega-menu application engine, Woo session/private state reads.

**Exit:** PASS — L1-L4; PR #41 merged.

## 9. R4 — WooCommerce Presentation 2.0

**Goal:** improve commercial UX while WooCommerce remains sole commerce truth owner.

**Surfaces:** product cards/catalog, single product, cart, checkout, account, supported public Woo Blocks.

**Forbidden:** product meta/session/order table reads, wishlist/compare, AJAX filter/search, swatch domain engine, quick-view business engine, custom checkout engine.

**Exit:** PASS — clean-WP absence regression and Woo-present L1-L4 matrices; PR #43 merged.

## 10. R5 — Control Center + System Health

**Goal:** provide a small presentation settings console and diagnostics surface.

**Storage:** only normalized `aznet_theme_settings` Theme Mod.

**System Health:** read-only WordPress environment + Theme settings + public provider capability checks; unknown external capability remains unknown.

**Exit:** PASS — L1-L4 plus in-place update/theme-switch continuity; PR #44 merged.

## 11. R6 — Performance + Release 2.0

**Goal:** close v1.1 with measured performance/asset evidence and reusable release infrastructure.

**Completed sequence:**

1. measured route/asset/performance baseline captured;
2. Theme-owned surface-aware asset loading hardened only where justified;
3. ConvertFlow projection optimization retained as `BLOCKED_EXTERNAL_CONTRACT` rather than using private detection;
4. reusable PR CI and exact-main post-merge verification added;
5. deterministic v1.x release-candidate workflow/package builder added with stable top-level `aznet-theme/` identity;
6. obsolete milestone-specific workflows retired only after replacement coverage + fresh evidence;
7. final exact-main R1-R6 regression passed before metadata promotion;
8. owner-approved RED -> GREEN atomic metadata promotion completed in both `style.css` and `AZNET_THEME_VERSION` to exact `1.1.0`;
9. final exact-main/package verification passed on promoted bytes.

**Historical candidate:** `aznet-theme-1.1.0.zip`; SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

**Exit:** PASS technical R6 candidate. Tag/GitHub Release and deployment remain separate gates; later P2/P3 production bytes require a new P5 package.

## 12. Post-R6 production-readiness sequence

### P1 — Pilot identity cleanup

**Goal:** leave one intended active AZnet Theme install on the pilot without giving Theme code destructive lifecycle ownership.

**Evidence:** PR #69 read-only identity proof plus post-delete run `35050519817`. The owner confirmed backup availability and explicitly approved deletion of only inactive `aznet-theme-release-v1.0.0` v1.0.0 through WordPress core UI.

**Exit:** **PASS** — `NO_DUPLICATES`; active `aznet-theme` v1.1.0; zero inactive/ambiguous AZnet identities; Standalone Core `ready`; fresh 28-check public regression PASS.

**Boundary:** Theme code must not auto-delete installed themes; `flatsome` remains unrelated/outside P1 cleanup scope.

### P2 — Editorial Single-Post Hardening

**Goal:** generic production-grade long-form Post presentation suitable for professional/editorial sites, including the law-site pilot.

**Owner:** AZnet Theme presentation; WordPress owns Post/author/taxonomy/media/editorial lifecycle.

**Allowed APIs:** native WordPress author/date/taxonomy/featured-image/content-pagination/post-navigation APIs; existing public RootProfile presentation path only if it can enhance author presentation without a new contract.

**Required:** semantic article header; native author; published + changed modified date; categories/tags; optional featured image; long-form content measure; accessible headings/lists/blockquote/table/media; pagination; previous/next navigation; WordPress-native author fallback; singular-post scoped assets.

**Forbidden:** legal/business semantics, Theme SEO title/meta/canonical/schema/OG, related-post ranking/query engine, private provider storage/API reads, authoritative route heuristics.

**TDD:** RED on previous minimal `single.php` -> minimal GREEN in focused content parts + scoped article CSS -> retained regressions.

**Exit:** PASS — PR #51 merged to `main@a863da861...`; final candidate constrained P2 to native Post and fails soft when native author data is unavailable.

**Rollback:** revert bounded P2 merge; WordPress content remains unchanged.

**Next:** P3 — completed.

### P3 — Editorial Listing/Search Hardening

**Goal:** improve archive/search scan presentation for long Vietnamese titles and editorial content without replacing WordPress query ownership.

**TDD:** RED contract -> optional native thumbnail/date + resilient title/excerpt presentation -> regression.

**Exit:** PASS — PR #52 merged to `main@a1dd42dd...`; fresh exact-main verification run `34195248467` succeeded.

**Rollback:** revert bounded P3 merge; WordPress content/query state remains unchanged.

**Next:** P4.

### P4 — Real Pilot QA

**State:** PASS / PILOT SIGN-OFF CLOSED.

**Goal:** prove final editorial candidate on support-floor generic fixtures and the real current-stack pilot.

**Pilot routes:** homepage, representative long Post, Page, category/archive, search/no-results, 404 and public RootProfile surfaces actually present.

**Pilot environment:** WordPress `7.1` / PHP `8.4.25` is current-stack evidence only; it does not replace `6.9+` / `8.1+` support-floor evidence.

**Quality:** no Theme-caused PHP fatal/warning/uncaught; no horizontal overflow; keyboard/focus/landmarks/headings/labels; Vietnamese diacritics/long citations/wide tables/media; Rank Math independence; provider states accurately reported; update/theme-switch/rollback continuity; measured asset behavior.

**Allowed pilot action:** install/update a QA candidate on the approved pilot as needed for the matrix. This approval does not authorize tag/GitHub Release or final production deployment.

**Exit:** PASS at the tested Theme-owned scope. Public + authenticated evidence is retained and P1 cleanup is closed by fresh post-delete inventory/System Health/public regression evidence. Provider L5 status remains separate.

**Next:** P5 publication disposition. Technical package gates are PASS; stop before Git tag, GitHub Release or final production deployment without separate owner approval.

### P4-B — Law Site Provisioning v1.1

**State:** PASS / MERGED. D-026 implementation integrated upward through PR #61 -> #60 -> #59 -> #58 and is canonical at `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`.

**Goal/boundary:** provision or explicitly map the bounded Law 01 starter structure with proposal-only recommendation, starter editorial/media coverage and index-safe publication without transferring ongoing WordPress content ownership to Theme.

**Retained verification:** exact PR production/test head `356ef8c00991c63e294d619d42c7094d1074226d` completed 21/21 triggered PR workflows. Package run `34413505990`; browser/runtime run `34413506017`; Theme `1.1.0` candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; package artifact `10128165973`; browser artifact `10128198277`.

**Canonical verification:** owner-approved PR #58 merged to `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`, tree `28c30353e9f01bc51f4474347069738d410b5256`. D-022 exact-main run `34414878827` passed static/contracts and clean WordPress 6.9 runtime/browser/a11y; artifacts `10128678294` and `10128707347`.

**Regression closure:** G8 rejected candidate-only metadata `1.1.1` and the minimal fix restored `1.1.0`; R2 portability failure was reduced to a disposable CI-server lifecycle issue and fixed only in the harness. No ownership/domain workaround was introduced.

**Rollback:** before successful provisioning handoff, restore captured settings/Front Page/menu assignment/indexing and remove only current-run creations. After success, presentation rollback is `homepage_preset=off` or remapping; WordPress-owned content persists.

**Exit:** PASS through canonical-main integration plus D-022 exact-main verification. P4 real-site pilot and P5 publication/deployment remain separate gates.

**Next:** P4 Real Pilot QA when pilot access is restored; it remains access-blocked in parallel.

### P4-C — Standalone Core Independence

**State:** PASS / MERGED. D-027 source and implementation closed on canonical main 16/09/2026.

**Goal:** turn the existing WordPress-clean principle into an executable product/release contract: WordPress + AZnet Theme only must complete install/activate/setup/provision/author/render without mandatory third-party plugins or external runtime services.

**Core boundary:** retain Theme-owned Design System, Header/Footer, native Homepage Composer, `law-01`, Quick Setup/Provisioning, native editorial templates, Control Center/System Health and settings portability as zero-plugin Core. Optional WooCommerce/RootProfile/ConvertFlow/SEO integrations stay additive and public-contract-based.

**QA:** L1 dependency/nag scan; L2 provider-absent contracts; L3 full clean-WP provisioning/runtime; L4 setup/frontend responsive+a11y; final P5 package must repeat the zero-plugin path on exact package bytes.

**UX:** System Health separates Standalone Core from Optional Integrations; optional `Not present` does not downgrade Core. No required-plugin nags, marketplace redirects or setup blockers for absent providers.

**Rollback:** bounded tests/admin presentation only; preserve existing provider adapters and WordPress-owned provisioning outputs.

**Exit:** PASS — source PR #63 + implementation PR #64 merged; final-head L1-L4 and exact-package gates PASS; exact-main run `35042767873` PASS on `main@16563bdc88f172a1d7737b92293c7958670aac96`.

**Historical next at D-027 closure:** resume P4 pilot when access existed, then seek separate P5 publication/deployment approvals. Those release-critical steps are now closed.

### P5 — Final Candidate / Publication / Deployment

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

Technical run `35051428372` verified the final `aznet-theme-1.1.0.zip`: deterministic double-build, 121 production files exact source/package match, 93 packaged PHP files lint PASS, clean WordPress `6.9` zero-plugin runtime/browser and switch-away/switch-back continuity. SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`. Evidence: `docs/evidence/P5_FINAL_CANDIDATE_VERIFICATION_20260916.md`.

Owner-approved publication run `35052694111` published annotated tag `v1.1.0` at release source anchor `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78` and GitHub Release `389622362` with exact verified asset `aznet-theme-1.1.0.zip` (`567100593`). Owner-approved existing-package production deployment disposition for `tamduchanoi.aznet.vn` then passed fresh read-only verification run `35054176392`. Evidence: `docs/evidence/P5_PUBLICATION_20260916.md` and `docs/evidence/P5_PRODUCTION_DEPLOYMENT_20260916.md`.

## 13. v1.2 WordPress Experience Completion

**State:** X4 PASS / X5 NEXT.

**Milestone boundary:** WordPress Core surfaces only. Theme presentation/configuration remains in scope; WordPress-owned semantics/state remain authoritative; provider compatibility/certification tracks stay separate.

**Architecture:** existing shared content/design primitives remain the base. Add bounded surface modules rather than a monolithic experience layer. Use `theme.json` for editor/frontend parity where appropriate and PHP/CSS for native template surfaces where appropriate.

**X1 — Comments Surface:** **PASS at L1-L4** — native comments template/list/form/reply/pagination presentation; WordPress 6.9 zero-plugin runtime and 3/3 browser/a11y matrix verified by run `35060458118`; no comment engine/store. Evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`.

**X2 — Search / 404 / Empty States:** **PASS at L1-L4** — native WordPress query/routing truth retained; functional closure head `c5c9415f851e2ed77c3069c08ff4f963b7c33c3c`, workflow `35071280839` SUCCESS on WordPress 6.9 with zero active plugins, native HTTP 404, scoped recovery assets and 16/16 Playwright/axe route/viewport cases; artifact `10436541455`, digest `sha256:c1e31a506172f90939d5688317aae0bd1b2ec9fa437dcd8726605c80fc191cd2`. All 23 retained PR workflows observed on that functional head also completed SUCCESS. Evidence: `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md`.

**X3 — Media / Gallery / Embed:** **PASS at L1-L4** — functional closure head `ff6d7a1a025a97d8710644a78b6dcf6d2b34aa69`; X3 workflow `35078254834` SUCCESS on WordPress 6.9 zero-plugin runtime; 12/12 responsive browser/axe cases plus Classic Editor parity PASS; artifact `10439630267`, digest `sha256:2ee736d3b0e9e0182ab935367b55044df35d4c7e0234e56f3e9c273b968e9bd2`. WordPress media/content semantics remain authoritative; no gallery application engine or provider expansion.

**X4 — Pagination & Navigation Completion:** **PASS at L1-L4** — functional closure head `f34db6f89cff505c16c2bb98f479eebba5661079`; dedicated workflow `35083830374` SUCCESS on WordPress 6.9 zero-plugin runtime and 20/20 browser/axe cases; artifact `10440569415`, digest `sha256:16b1b4d69b81a5ce7403d081b565d80c67a4c4e79ddb50538abdc5771928fb84`. WordPress owns main-query pagination state/URLs, Post page splitting, adjacent-Post targets and comment pagination; Theme adds bounded labels/CSS/focus/responsive presentation only. All 25 exact-head PR workflows observed on the functional head completed SUCCESS. Evidence: `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md`.

**X5 — Native Form Controls:** shared production-ready presentation for WordPress-native controls; no private plugin-form skinning contract.

**X6 — Cross-surface QA & Release Closure:** L1-L4 retained regressions plus L6 deterministic package/source identity, lifecycle/rollback and exact-main verification; only then may the `1.2.0` promotion gate be considered.

**Execution:** `X1 -> X2 -> X3 -> X4 -> X5 -> X6`. Every X1-X5 production slice uses RED -> intended failure -> minimal GREEN -> regression and has a bounded rollback.

**Design record:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`.

## 14. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility remains BLOCKED because provider output introduces a nested document-level `<main>`. Track provider fix; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain W1-W9 ownership/fail-soft evidence plus R4 presentation evidence. WooCommerce continues to own product/price/stock/variation/cart/checkout/order/account truth.

## 15. Recovery checkpoint

At every slice, commit bounded changes on a work/feature branch and retain a clear base SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior. Do not rerun retained PASS without an invalidation unless the active exit gate explicitly requires fresh final-candidate evidence.

## 16. Exact next

**X4 Pagination & Navigation Completion is PASS at L1-L4 on verified functional head `f34db6f89cff505c16c2bb98f479eebba5661079`; dedicated run `35083830374` and all 25 observed exact-head PR workflows completed SUCCESS. Owner approval for PR #83 canonical merge is granted. Complete fresh exact-final-head verification after source closure, merge PR #83 with expected-head protection, and complete fresh exact-main verification. Exact Next after integration is a reviewed implementation plan for X5 Native Form Controls before production code.**
