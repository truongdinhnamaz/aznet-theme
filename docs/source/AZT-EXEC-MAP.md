# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.64
**Status:** Working Execution Map / derived  
**Date:** 19/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

> **Canonical-main checkpoint:** live `main` is `27e6a2bc3d68bfa4aa51b22c0e1fda2867cec174` after owner-approved source/evidence PR #152. PR #152 changed docs/evidence only, completed 7/7 triggered workflows SUCCESS and has zero file delta from its final head to the merge commit. Canonical implementation bytes remain anchored at owner-approved PR #151 merge `main@39df23c3385118888e262c27a2331de34dda430d`, Theme metadata `1.3.10`, with fresh exact-main V1 run `35412829723` SUCCESS. Published GitHub Release and verified production deployment remain historical `v1.3.0`; RootProfile Team remains blocked under issue #112.

**Completed execution:** `PR #58 canonical integration -> D-027 closure -> P4 public/authenticated PASS -> P1 cleanup PASS -> P5 v1.1 publication/deployment PASS -> X1 -> X2 -> X3 -> X4 -> X5 -> X6 exact-final-head PASS -> PR #87 merge -> exact-main V1/X6 PASS -> owner-approved v1.2.0 publication PASS -> owner-approved v1.2.0 production deployment PASS -> Y1 -> Y2 -> Y3 -> Y4 -> Y5 -> PR #98 merge -> exact-main V1/X6 release-path PASS -> owner-approved v1.3.0 publication PASS -> owner-approved v1.3.0 production deployment PASS -> independent post-deploy read-only verification PASS`. Current v1.3 state: `PUBLICATION + PRODUCTION DEPLOYMENT PASS`; published/GitHub-Release release and verified production deployment are both `v1.3.0` at their respective verified scopes.

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
| F Homepage | CORE PASS / F9 PROVIDER L5 PASS | Native Homepage remains core PASS; F8 L3-L4 retained; ConvertFlow F9-A..E + C1 provider/theme L5 certification PASS at the exact Theme `v1.3.0` reference-producer / Core P5.244 + Pro P5.164 scope. Law 01 live presentation/rerun QA PASS at tested scope. RootProfile-backed Team projection remains separate external blocker #112. |
| G Core v1.0 closure | TECHNICAL PASS | G0-G8 merged; publication/tag is separate live state |
| R0 v1.1 Source Reconciliation | PASS | Source-only reconciliation merged through PR #36 |
| R1 Design System 2.0 | PASS | PR #37 merged; stable Theme settings/tokens/visual presets and L1-L4 parity evidence |
| R2 Native Pattern Library | PASS | PR #39 merged; 18 portable patterns, public Woo-block gating and theme-switch portability evidence |
| R3 Header System 2.0 | PASS | PR #41 merged; bounded presets/primitives, accessible mobile/sticky behavior and L1-L4 browser evidence |
| R4 WooCommerce Presentation 2.0 | PASS | PR #43 merged; public Woo output -> Theme presentation only; clean-WP/Woo-present L1-L4 evidence |
| R5 Control Center + System Health | PASS | PR #44 merged; bounded Theme settings admin, public/read-only health diagnostics and update/theme-switch continuity |
| R6 Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 retained technical provenance; R6 itself did not imply publication/deployment, both of which were later completed under P5 |
| P1 Pilot identity cleanup | PASS | Post-delete checkpoint proved only intended active `aznet-theme` v1.1.0 remained before the later v1.2 deployment; inventory/System Health/public regression PASS |
| P2 Editorial Single-Post | PASS | PR #51 merged; native WordPress editorial presentation only; no SEO/domain/query takeover |
| P3 Editorial Listing/Search | PASS | PR #52 merged; native thumbnail/date scan presentation + resilient long titles/excerpts; main query retained |
| P4 Real Pilot QA | PASS / PILOT SIGN-OFF CLOSED | Public + authenticated QA retained; P1 cleanup closed with fresh post-delete inventory/System Health and 28-check public regression PASS |
| P4-A Homepage Composer + Law 01 | PASS / MERGED | PR #58 merged; exact-main static/runtime/browser core verification PASS |
| P4-B Law Site Provisioning | PASS / MERGED | D-026 merged; Theme `1.1.0`; deterministic package/browser evidence retained; activation mutation-free and active-site overwrite/noindex takeover forbidden |
| P4-C Standalone Core independence | PASS / MERGED | PR #63/#64 merged; zero-plugin L1-L4, exact-package and exact-main PASS; optional integrations remain additive |
| P5 Publication/Deployment | PASS | Tag `v1.1.0` + GitHub Release and owner-approved production deployment disposition for `tamduchanoi.aznet.vn` are complete; fresh read-only run `35054176392` PASS at the tested Theme-owned/site-operations scope |
| X v1.2 WordPress Experience Completion | PUBLICATION + PRODUCTION DEPLOYMENT PASS | X1-X5 retained; PR #87 exact-main V1 + X6 L1-L4/L6 PASS; annotated `v1.2.0` + GitHub Release publication PASS; owner-approved production deployment run `35135759389` + fresh read-only run `35136876330` PASS; provider L5 stays separate |
| Y v1.3 Client Delivery System | PUBLICATION + PRODUCTION DEPLOYMENT PASS | Y1-Y5 merged through PR #98; exact-main V1 + X6 release-path PASS at `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`; owner-approved publication run `35211254633`; owner-approved production deployment run `35222200660` + independent read-only run `35223053733` PASS on `tamduchanoi.aznet.vn`; provider L5 stays separate |
| U Historical Control Center | REFERENCE ONLY | Do not wholesale-merge; R5 owns the accepted bounded admin outcome |

Canonical repository technical baseline is exact Theme `1.3.0` at owner-approved PR #98 merge `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`. Fresh exact-main V1 + X6 release-path verification is PASS. GitHub publication is verified `v1.3.0` through run `35211254633`; production deployment is verified `v1.3.0` through deployment run `35222200660` plus fresh independent read-only run `35223053733`.

X6 synchronization checkpoint: `c145f72cc7ac4783eb9f4a2f0b11c1d26e178445`, tree `3594fab0c89d015711e914325734646d1e7df159`, normal merge parented by prior X6 head plus `main@dddcee1c...`, leaving `behind_by=0` before source/evidence closure. Fresh X6 run `35126908185` passed WordPress 6.9 zero-plugin L1-L4/L6, integrated 32/32 browser/axe, deterministic `aznet-theme-1.2.0.zip` SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`, 95 packaged PHP lint checks and switch-away/switch-back lifecycle continuity. Artifact `10459925538`, digest `sha256:c6b480168282e7dc9eea8f749d303012763cabf16213a6e69619c62f96f16e03`; all 19 observed same-head PR workflows completed SUCCESS.

The previous R6 package SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical. Fresh P5 independently rebuilt the current canonical production file set as deterministic `aznet-theme-1.1.0.zip`, SHA-256 `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`, with exact 121-file source/package identity and 93 packaged PHP files linted.

Fresh v1.3 post-deployment evidence proves active AZnet Theme `1.3.0` on `tamduchanoi.aznet.vn`, Standalone Core `ready`, WordPress menu ID `15` retained at `header-utility`, exact phone `024 3716 4123` -> `tel:+842437164123`, desktop/mobile HTTP 200, one `main`, one H1, overflow `0`, and the retained 28/28 public route/viewport regression. Deployment run: `35222200660`; independent read-only verification: `35223053733`. Provider L5 remains unproven.

Fresh P4 public evidence after Theme replacement retains run `35045540722` with 28/28 route/viewport checks PASS and fresh artifact `10427713118`. Authenticated read-only run `35047440842` PASS adds current Admin/System Health evidence with artifact `10427876863`; PR #67 merged the harness/evidence and exact-main run `35048609753` then PASSed on canonical main. RootProfile public profile surface was not observed in the tested public matrix, so L5 is not inferred.

P2 final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` merged through PR #51 to `main@a863da861b6a299920568b2b0c9ca57924727431` after Theme-owned author-fallback and CPT/Woo scope regressions were fixed.

P3 final verified head `d78091900451176c3815b23d1485036e784bdde9` merged through PR #52 to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`; head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`.

## 1.1 Exact current checkpoint — 18/09/2026

- Canonical `main`: `a66ef1639c299a06e62e9797aa2f44fe4e26cd8d` after corrective PR #133. Theme metadata `1.3.3`; inherited v1.3.2 Law 01 heading system + PR #129-#131 closure; exact-main V1 `35318696376` and X6 `35318696388` PASS.
- F9 optional ConvertFlow provider L5: PASS at the exact certified scope in `docs/evidence/F9_PROVIDER_L5_SOURCE_SYNC_20260918.md`; no ConvertFlow release/deploy or public-contract change is inferred.
- Pilot live Theme metadata: `1.3.0`, with owner-approved post-release WPVibe Theme-file publish and `aznet-theme-wpvibe-backup`. Runtime behavior is verified; live byte identity to release/main is UNKNOWN.
- Law 01 rerun: PASS at L3/L4 evidence available for the tested live flow (`Created: 0 · Reused: 36`, no duplicate current-blueprint starter roles). Homepage content/indexability/Lighthouse readiness is PASS at the tested scope.
- RootProfile Team/member discovery: BLOCKED_EXTERNAL_CONTRACT under issue #112. Theme has no safe authoritative current action until a public/versioned organization-team projection exists.

**Exact Next:** hold Team implementation at #112 and do not invent a workaround. The post-release Law 01 Homepage presentation closure is now PASS on exact `main@8238248f0d97383d0e43461c481ad6bbc1f480a7`. No new Theme core slice is opened by this checkpoint. A future safe Next requires either the RootProfile public contract becoming available or a separately approved Theme roadmap slice.

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

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

**Milestone boundary:** WordPress Core surfaces only. Theme presentation/configuration remains in scope; WordPress-owned semantics/state remain authoritative; provider compatibility/certification tracks stay separate.

**Architecture:** existing shared content/design primitives remain the base. Add bounded surface modules rather than a monolithic experience layer. Use `theme.json` for editor/frontend parity where appropriate and PHP/CSS for native template surfaces where appropriate.

**X1 — Comments Surface:** **PASS at L1-L4** — native comments template/list/form/reply/pagination presentation; WordPress 6.9 zero-plugin runtime and 3/3 browser/a11y matrix verified by run `35060458118`; no comment engine/store. Evidence: `docs/evidence/X1_COMMENTS_SURFACE_20260916.md`.

**X2 — Search / 404 / Empty States:** **PASS at L1-L4** — native WordPress query/routing truth retained; functional closure head `c5c9415f851e2ed77c3069c08ff4f963b7c33c3c`, workflow `35071280839` SUCCESS on WordPress 6.9 with zero active plugins, native HTTP 404, scoped recovery assets and 16/16 Playwright/axe route/viewport cases; artifact `10436541455`, digest `sha256:c1e31a506172f90939d5688317aae0bd1b2ec9fa437dcd8726605c80fc191cd2`. All 23 retained PR workflows observed on that functional head also completed SUCCESS. Evidence: `docs/evidence/X2_SEARCH_404_EMPTY_20260916.md`.

**X3 — Media / Gallery / Embed:** **PASS at L1-L4** — functional closure head `ff6d7a1a025a97d8710644a78b6dcf6d2b34aa69`; X3 workflow `35078254834` SUCCESS on WordPress 6.9 zero-plugin runtime; 12/12 responsive browser/axe cases plus Classic Editor parity PASS; artifact `10439630267`, digest `sha256:2ee736d3b0e9e0182ab935367b55044df35d4c7e0234e56f3e9c273b968e9bd2`. WordPress media/content semantics remain authoritative; no gallery application engine or provider expansion.

**X4 — Pagination & Navigation Completion:** **PASS at L1-L4** — functional closure head `f34db6f89cff505c16c2bb98f479eebba5661079`; dedicated workflow `35083830374` SUCCESS on WordPress 6.9 zero-plugin runtime and 20/20 browser/axe cases; artifact `10440569415`, digest `sha256:16b1b4d69b81a5ce7403d081b565d80c67a4c4e79ddb50538abdc5771928fb84`. WordPress owns main-query pagination state/URLs, Post page splitting, adjacent-Post targets and comment pagination; Theme adds bounded labels/CSS/focus/responsive presentation only. All 25 exact-head PR workflows observed on the functional head completed SUCCESS. Evidence: `docs/evidence/X4_PAGINATION_NAVIGATION_20260916.md`.

**X5 — Native Form Controls:** **PASS at L1-L4** — functional closure head `8aac300f805e83fb7a7e7243aa779fe37376838d`; dedicated workflow `35088686988` SUCCESS on WordPress 6.9 with zero active plugins, scoped `forms.css` on native Comment/Core-block/password/Search/404 surfaces and absent Archive/Home controls, plus a 16/16 Playwright/axe browser matrix across 1440/1024/390/320 widths. Artifact `10442863335`, digest `sha256:88d06c8b3449233e7a5b85421dc2be779eac51dc9cacbe4d694ceab86f2705ac`. WordPress owns form submission/validation semantics and submitted data authority; Theme adds bounded presentation only. All 26 exact-head pull-request workflows observed on the functional head completed SUCCESS. Evidence: `docs/evidence/X5_NATIVE_FORM_CONTROLS_20260916.md`.

**X6 — Cross-surface QA & Release Closure:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS** — exact `1.2.0` technical bytes passed PR-head and exact-main X6 verification; owner-approved publication run `35131383107` published annotated tag `v1.2.0` and GitHub Release `390148740` carrying the exact verified `aznet-theme-1.2.0.zip` SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`. Owner-approved production deployment run `35135759389` installed that exact published package on `tamduchanoi.aznet.vn`; fresh independent read-only run `35136876330` reverified active Theme `1.2.0`, Standalone Core `ready`, retained WordPress-owned logo/menu identity and the 28/28 public matrix. Evidence: `docs/evidence/V1_2_PUBLICATION_20260917.md` and `docs/evidence/V1_2_PRODUCTION_DEPLOYMENT_20260917.md`. Provider L5 is not inferred.

**Execution:** `X1 -> X2 -> X3 -> X4 -> X5 -> X6 -> publication -> production deployment`. X1-X5 retained RED -> GREEN production discipline; X6 technical merge/exact-main closure, v1.2.0 publication and owner-approved production deployment are all PASS at their separately verified scopes.

**Design record:** `docs/superpowers/specs/2026-09-16-v1.2-wordpress-experience-completion-design.md`.

## 13A. v1.3 Client Delivery System

**State:** **PUBLICATION + PRODUCTION DEPLOYMENT PASS.**

**Dependency order:** `Y1 Page Experience 2.0 -> Y2 Professional Page Kits -> Y3 Footer System 2.0 -> Y4 Professional Services Starter Blueprint -> Y5 Client Delivery QA & Release Closure -> publication -> production deployment -> independent post-deploy verification`.

**Source:** D-029 in AZT-04. Design: `docs/superpowers/specs/2026-09-17-v1.3-client-delivery-system-design.md`. Plan: `docs/superpowers/plans/2026-09-17-v1.3-client-delivery-system.md`.

**Y1 exit:** native inner Page header/hero, real-ancestor child breadcrumb, Standard/Wide/Landing variants, surface-aware Page CSS, backward-compatible content, fresh L1-L4.

**Y2 exit:** five portable native professional Page kits, neutral placeholders only, Classic Editor content preservation/editor evidence, theme-switch content portability and responsive/a11y evidence.

**Y3 exit:** Standard/Professional/Compact Footer presets through the existing Theme settings store, WordPress-native identity/menu sources only, empty-region fail-soft and L1-L4 evidence.

**Y4 exit:** generic Professional Services blueprint reusing the existing confirmed/idempotent provisioning engine; clean-site creation/reuse, active-site fail-safe and current-run rollback proven; no fabricated client truth.

**Y5 exit:** zero-plugin clean-site client-delivery matrix, deterministic exact package/source identity, packaged PHP lint, update/theme-switch/rollback, exact-final-head and exact-main gates. Promotion to `1.3.0`, tag/Release publication and production deployment were separately owner-approved and completed.

**Y1-Y5 technical closure:** PASS. PR #98 merged to `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`, tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`. V1 Exact Main `35206627102` and X6 exact-main release path `35206626992` SUCCESS; `aznet-theme-1.3.0.zip` SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`, 144 files, 107 packaged PHP lint PASS, 32/32 browser/axe PASS, lifecycle continuity PASS. Evidence: `docs/evidence/V1_3_TECHNICAL_CLOSURE_20260917.md`.

**v1.3 publication closure:** PASS. Publication run `35211254633` published annotated tag `v1.3.0` (tag object `56f2ae2e8ba6e1fb4e8359447656e88ab6411ba6`) and GitHub Release `390623960`; release asset `570039299` matches exact verified package SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`. Evidence: `docs/evidence/V1_3_PUBLICATION_20260917.md`.

**v1.3 production deployment closure:** PASS. Owner-approved deployment run `35222200660` installed the exact published `v1.3.0` asset on `tamduchanoi.aznet.vn` and completed SUCCESS; deployment artifact `10497483290` digest `sha256:84d041445b606279a0caebef60b9c38206f5a9a88b8e1b759453484dfad1c6b1`. Fresh independent read-only run `35223053733` completed SUCCESS with active Theme `1.3.0`, Standalone Core `ready`, menu ID `15` retained at `header-utility`, approved phone retained and broad public matrix `28/28` with no Theme-owned blocking failure; artifact `10497925626`, digest `sha256:756f765fe70f2ca61978111678853204808ea4cb4cd44fdd3453d50e177313b2`. Temporary ops files were removed at cleanup commit `6ef53f0bd20c69c65b88a753627436e31976d606`; cleaned ops branch had zero net file delta versus `main@2c4e6f0442335ac3e690e97beaca4d4bf6405fa5`. Evidence: `docs/evidence/V1_3_PRODUCTION_DEPLOYMENT_20260917.md`.

**Exact Next:** v1.3 Core release path is closed at technical + publication + production deployment PASS. Begin no new Theme milestone or provider certification without a separately approved slice. Provider L5 remains separate.

## 14. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility is PASS for exact ConvertFlow Core P5.242 + Pro P5.164 against Theme `252554961bc81d22427363aee0048365fb4e116b`: L3 proved exactly one Theme-owned document-level `<main>` and L4 desktop/tablet/mobile + keyboard/focus + console + Axe passed 4/4. Provider merge is canonical at `truongdinhnamaz/convert-flow@64cf2e66d41e6619e2bbcf53c12d8831e2852442`; artifact `10501317335` digest `sha256:a353d11b456e31974be4b5362005a1e02ddad2a5770e2f2c62efd4bea87fbdda`. Historical P5.239 nested-main evidence remains provenance. F9/provider L5 remains separate and unclaimed; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain W1-W9 ownership/fail-soft evidence plus R4 presentation evidence. WooCommerce continues to own product/price/stock/variation/cart/checkout/order/account truth.

## 15. Recovery checkpoint

At every slice, commit bounded changes on a work/feature branch and retain a clear base SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior. Do not rerun retained PASS without an invalidation unless the active exit gate explicitly requires fresh final-candidate evidence.

## 15.1 Law 01 post-1.3.4 corrective and visual-parity checkpoint — 18/09/2026

- PR #138 closed the 1.3.4 upgrade regression by promoting Theme metadata to `1.3.5` and preserving the legacy public WordPress-native Hero projection when no valid dedicated Hero Page mapping exists.
- PR #138 exact-main closure: `main@a9a3578516971abd9a9223c1e8dac69da5657bea`, V1 run `35335680576` SUCCESS, X6 run `35335680518` SUCCESS.
- PR #139 closed the approved Hero / Trust / Services visual-parity slice at `main@ffafe55b90db1d5313b36a5151110ca1e3f67789`.
- PR #139 final head completed 24/24 workflows SUCCESS; exact-main V1 run `35343960510` and X6 run `35343960430` SUCCESS.
- RootProfile Team integration remains BLOCKED at issue #112. Do not convert WordPress-native starter/reference content into authoritative Team identity or membership.

## 15.2 Law 01 About/Profile + Latest visual-parity closure — 18/09/2026

- PR #141 closed the second approved Law 01 visual-parity slice at `main@afbef3d0adc39e4b1595cf2f3506f58bb567bcff`.
- Final verified PR head `0a2826dfc52343f187dc6198d61d83a6608b62d4` completed 25/25 workflows SUCCESS.
- About: dedicated 44/56 editorial copy/media composition with responsive stacking.
- Team: distinct presentation band over existing WordPress-owned mapped Team Page/direct child Page fixtures only; this does not certify or infer RootProfile membership.
- Latest: three WordPress-native posts, equal-height card rhythm, 3:2 media.
- D-027 L4 contrast regression was reduced to Team text/link CSS and fixed before the final clean matrix.
- No fresh post-merge workflow run was emitted for the merge SHA; no independent exact-main rerun is claimed.
- RootProfile Team remains BLOCKED at issue #112.

## 15.3 AZnet Theme 1.3.6 client-delivery candidate — 18/09/2026

- Owner-approved PR #143 promotes Theme metadata from 1.3.5 to 1.3.6 for client-delivery provenance.
- RED head `120cd35e7d245f6a7e564f544df76d987fec2ddd` failed the intended Y5 1.3.6 metadata contract.
- Exact GREEN production head `7db436b316ef5a9f9264fa9dd71f6cfad4d7ad80` completed 14/14 triggered workflows SUCCESS.
- Deterministic package: `aznet-theme-1.3.6.zip`, 145 files, 108 packaged PHP lint PASS, SHA-256 `25271cd4c68f0bc88e236f084acb73d7661a618d1ae1d737f0f2a5202307a84a`.
- Y5 run `35353852165`; promoted-package artifact `10550649207`; artifact digest `sha256:7efe3e575a46950e85abd703bad87e05daac32bd2346265fa2e148569de48cfc`.
- Package creation does not imply tag/GitHub Release or production deployment.
- RootProfile Team remains BLOCKED at issue #112.

## 15.4 Law 01 screenshot-reference parity candidate — 18/09/2026

- Owner clarified that the supplied Tâm Đức - Hà Nội homepage screenshot is the canonical visual reference for this presentation slice.
- PR #146 was closed unmerged as superseded.
- PR #147 branch: `feat/law01-reference-parity-final`.
- Exact verified production head before source-only checkpoint updates: `aacd6fe4abc4d5f3d49b6bfd85570df66688db46`.
- Verification: 18/18 triggered workflows SUCCESS.
- Law 01 Browser Quality run `35363025279`; artifact `10555328476`; digest `sha256:4c0af3b17d8d38768f994f2dacd3dbe20f5da5b693ec28c331df7fc632d520ba`.
- Presentation target: shared `96rem` inner shell; 48/52 Hero; four-item trust; six Services cards; About + Team side-by-side at 48/52; Team CTA below up to four WordPress-owned child-Page presentation slots; three Latest cards; WordPress logo + site-title lockups in Header/Footer; Hero contact row may reuse `header-utility`.
- No fabricated business statistics.
- RootProfile Team remains BLOCKED at issue #112; WordPress Page/direct-child fixtures are presentation inputs only.

## 15.5 Law 01 screenshot-reference canonical merge closure — 18/09/2026

- Owner-approved PR #147 final head: `225e36af27193358b2a252ac7edce4d9867d61ba`.
- Final-head verification: 21/21 triggered workflows SUCCESS, 0 failure.
- Canonical merge: `main@f608c7ec98587b61da570871bb304a9e76d09bbd`.
- Git comparison from verified head to merge commit: zero file delta.
- No fresh post-merge exact-main workflow run is claimed.
- Theme metadata remains `1.3.6`; published GitHub Release remains `v1.3.0`.
- New package/version, tag/GitHub Release and production deployment remain separate approval gates.
- RootProfile Team remains BLOCKED at issue #112.

## 15.6 AZnet Theme 1.3.8 final client-delivery candidate — 19/09/2026

- Owner explicitly approved a new package/version slice after the Law 01 screenshot-reference closure.
- Version target is `1.3.8`, intentionally skipping reuse of the superseded unmerged `1.3.7` package identity.
- RED head `5f188f3fcca6fd32122a1799aff703d4837b89e9` failed the intended Y5 1.3.8 metadata contract.
- Exact GREEN production head `5f3dcf00f0a619b58a966078b2cd969cb7fec45d` completed 14/14 triggered workflows SUCCESS.
- Deterministic package: `aznet-theme-1.3.8.zip`, 145 production files, 108 packaged PHP lint PASS, SHA-256 `0aba4cf0e213150319c8288d62d06cb136481670cd8f53fd0edbfd9276a03238`.
- Y5 run `35403751052`; promoted-package artifact `10571900478`; artifact digest `sha256:2f8b380f8ffa6e88d481d99a14ea1d248f86243ebd1c213bdccb49fda0fc9da4`.
- RootProfile Team remains BLOCKED at issue #112.
- Tag/GitHub Release and production deployment remain separate approval gates.

## 15.7 AZnet Theme 1.3.9 seamless-Hero client-delivery candidate — 19/09/2026

- Owner-approved visual goal: eliminate visible boxed boundaries between the Hero text/image region and the full-width page background while preserving shared-shell alignment.
- Visual RED head `ffd2c8e48498bf0849ec8fa022feddb52da80806` failed the intended no-boxed-background contract.
- Exact visual GREEN head `8cf81cb0ec7955d7605e32913aa36af7fe31b20d` completed 14/14 triggered workflows SUCCESS.
- Desktop image surface reaches the viewport right edge; stacked/mobile image reaches both viewport edges; no horizontal overflow.
- 1.3.8 remains the prior canonical package identity; changed bytes are promoted to `1.3.9`.
- Release RED head `886c3d751326d16a90bddfa54bcaafa7b9af31d4` failed the intended Y5 1.3.9 metadata contract.
- Exact production head `b4ead6ea541074fa76b1657d20de07828c5bfde9` completed 19/19 triggered workflows SUCCESS.
- Deterministic package: `aznet-theme-1.3.9.zip`, 145 production files, 108 packaged PHP lint PASS, SHA-256 `33a70a1d63ae96c21d6efe381f8a0fea299ac0c14a49df56c5edfe8c2eb9404a`.
- Y5 run `35406199947`; promoted-package artifact `10571874751`; artifact digest `sha256:892e96890c189950000367c886bccd70b697de6c0ed442bcbb3527480ae7d9d9`.
- RootProfile Team remains BLOCKED at issue #112.
- Tag/GitHub Release and production deployment remain separate approval gates.

## 15.8 AZnet Theme 1.3.10 polished-Hero client-delivery candidate — 19/09/2026

- Owner-approved polish: compact editorial Hero title plus soft cream-to-image transition; shared-shell geometry remains unchanged.
- Visual RED head `f95fba80020db53c126c41fe52f6fa8020c516eb` failed the intended title-polish assertion.
- Minimal production polish head `bd811fec2619e3a28806540d1b86affa20c36dd6`: title rhythm, decorative non-interactive edge blend, subtle 1.02 image crop.
- 1.3.9 remains a superseded/unmerged package identity; current candidate is `1.3.10`.
- Release RED head `0070f8103ee6b508e2b637983ab1e20b3fd76695` failed the intended Y5 1.3.10 metadata contract.
- Canonical base remains 1.3.8, so X6 validates `1.3.8 -> 1.3.10`.
- Exact production head `a4c3621ec6d651b7e4c4d1085a2b900f8c94454b` completed 22/22 triggered workflows SUCCESS.
- Deterministic package: `aznet-theme-1.3.10.zip`, 145 production files, 108 packaged PHP lint PASS, SHA-256 `9eb8a3ad1c24b7e75a695199de3e45bf3e196bebd7fff8774f7390969283c92d`.
- Y5 run `35410299118`; artifact `10574586211`; digest `sha256:dadb066567c2b6c6d3c1f28f338a22441903fed03a55ad47433ac2d164f58132`.
- RootProfile Team remains BLOCKED at issue #112.
- Tag/GitHub Release and production deployment remain separate approval gates.

## 16. Exact next

**NEXT — No new AZnet Theme implementation slice is opened by this closure. Preserve canonical Theme metadata `1.3.10`. Any tag/GitHub Release, production deployment, takeover or new roadmap slice requires a separate explicit owner gate. RootProfile-backed authoritative Team remains `BLOCKED_EXTERNAL_CONTRACT` at issue #112.**


## 15.9 Homepage Hero editing UX follow-up — 19/09/2026

- Canonical prerequisite closed: owner-approved PR #150 merged 1.3.10 to `main@55ca349c8808d0d6a3c96d5201983ae966b1d822`; fresh exact-main V1 `35412045895` and X6 `35412045904` SUCCESS.
- PR #151 branch: `feat/homepage-hero-editor-ux`.
- Ownership: WordPress owns Hero Page title/excerpt/body/media; Theme owns typed source selection and admin/presentation bridge only.
- RED: `2d63c0c2b1d993e63ccb0a22efcdacd6fc16a373` failed for missing `render_homepage_hero_editor`.
- Reconciled production/test head: `82073e715be349f29fe84dbd0405c5af6e1b5c93`.
- Verification: 19/19 triggered workflows SUCCESS at final disposition. Homepage Composer `35412090957`, R5 Control Center Browser `35412090873`, D-027 L3/L4/Exact Package/retained regression, V1 PR CI and R6 PASS.
- R2 `35412090864`: first attempt hit transient disposable-runtime `ERR_CONNECTION_RESET`; single retry attempt 2 PASS without source changes.
- No Theme-owned Hero copy fields, no WordPress content mutation, no provider ownership transfer.
- Rollback: revert PR #151; no data migration required.
- Merge remains approval-gated; tag/GitHub Release/deploy remain separate gates.


## 15.10 Homepage Hero editing UX canonical merge — 19/09/2026

- Owner-approved PR #151 merged final head `b17532c0bc7348317c5044fa25262d28cb9fef54` to `main@39df23c3385118888e262c27a2331de34dda430d`.
- Final-head verification: 22/22 triggered workflows SUCCESS.
- Git comparison final head -> merge commit: zero file delta.
- Fresh exact-main V1 run `35412829723`: SUCCESS on exact merge SHA; static artifact `10575125302`, clean runtime/browser artifact `10575290283`.
- Theme metadata: `1.3.10`.
- Published release/deployment: historical `v1.3.0`.
- RootProfile Team: BLOCKED at issue #112.
- Release/tag/deploy: separate approval gates.


## 15.11 Post-closure source sync — 19/09/2026

- PR #152 source/evidence closure: owner-approved and merged to `main@27e6a2bc3d68bfa4aa51b22c0e1fda2867cec174`.
- PR #152 final head: `489cf1ee64751690d793144c56b37c0b16699457`.
- PR #152 verification: 7/7 triggered workflows SUCCESS.
- Final head -> merge commit: zero file delta.
- Production implementation anchor remains PR #151 merge `main@39df23c3385118888e262c27a2331de34dda430d` + exact-main V1 `35412829723` SUCCESS.
- Theme metadata remains `1.3.10`.
- No new implementation slice opened.
