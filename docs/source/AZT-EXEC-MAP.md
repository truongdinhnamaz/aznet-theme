# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.24  
**Status:** Working Execution Map / derived  
**Date:** 09/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

> **D-026 candidate checkpoint:** Law Site Provisioning v1.1 (`law01-v1-1`) completed the approved stacked implementation path with proposal-only smart recommendations, bounded starter editorial/media coverage and index-safe publication. Active sites never receive whole-site noindex for starter content.

**Completed execution:** `P4-B v1.1 source gate → recommendation/classification → starter editorial → plan/admin UX → media/runner → index/readiness → L3/L4 → candidate/evidence = PASS on verified head d564c6e8...`.

## 1. Current state

| Workstream | State | Boundary |
| --- | --- | --- |
| 0 Source Freeze & Provenance | PASS | 817/817 classification + accepted source-bound evidence |
| A Theme Foundation | PASS | WordPress-clean hybrid PHP + `theme.json` |
| B Semantic Tokens | PASS / retained | `--aznet-theme-*` + `theme.json`; R1 extended semantics without silently repurposing public tokens |
| C Header/Footer | PASS / retained | v1.0 Theme presentation owner; R3 adds the bounded v1.1 preset system |
| D Generic Templates | PASS / editorial quality hardening active | Page/Post/Archive/Search/404 technical closure retained; P2/P3 merged, P4 proves professional pilot quality |
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
| R6 Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 complete; exact-main + deterministic `1.1.0` R6 candidate verified; publication/deployment not implied |
| P1 Pilot identity cleanup | SITE-OPS READY | Active `1.1.0` proven; delete only inactive legacy theme identities through WordPress core UI after smoke |
| P2 Editorial Single-Post | PASS | PR #51 merged; native WordPress editorial presentation only; no SEO/domain/query takeover |
| P3 Editorial Listing/Search | PASS | PR #52 merged; native thumbnail/date scan presentation + resilient long titles/excerpts; main query retained |
| P4 Real Pilot QA | READY / EXACT NEXT | Pilot QA execution approved; current-stack law-site route/responsive/a11y/Rank Math/RootProfile evidence required |
| P4-B Law Site Provisioning | V1.1 CANDIDATE PASS / STACKED | `law01-v1-1`: L1-L4 + deterministic candidate PASS on `d564c6e8...`; activation remains mutation-free, existing-site overwrite remains forbidden, PR #61 integration remains owner-gated |
| P5 Publication/Deployment | GATED | Tag/GitHub Release and final production deploy remain explicit owner actions |
| U Historical Control Center | REFERENCE ONLY | Do not wholesale-merge; R5 owns the accepted bounded admin outcome |

Current post-hardening implementation baseline: `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`, Theme `1.1.0`, tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh `V1 Exact Main Verification` run `34195248467` succeeded on that exact SHA.

The previous R6 package SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` is retained as historical R6 candidate evidence only; P2/P3 changed production bytes, so P5 requires a new deterministic package/SHA.

The pilot System Health evidence proves active AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.24`, `primary_menu=yes`, RootProfile v1/v2 present, current-surface absent, Woo absent and ConvertFlow intentionally `unknown`.

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

**Evidence:** System Health proves active `1.1.0` and primary menu continuity.

**Steps:** smoke homepage/Page/Post; inspect inactive `AZnet Theme` cards; delete only inactive legacy versions through WordPress core UI; verify one active Theme remains and System Health still reports `1.1.0`.

**Hard gate:** if any inactive duplicate also reports `1.1.0`, stop and inspect identity. Theme code must not auto-delete installed themes.

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

**State:** READY / EXACT NEXT. Owner approved P4 pilot QA execution on 08/09/2026.

**Goal:** prove final editorial candidate on support-floor generic fixtures and the real current-stack pilot.

**Pilot routes:** homepage, representative long Post, Page, category/archive, search/no-results, 404 and public RootProfile surfaces actually present.

**Pilot environment:** WordPress `7.1` / PHP `8.4.24` is current-stack evidence only; it does not replace `6.9+` / `8.1+` support-floor evidence.

**Quality:** no Theme-caused PHP fatal/warning/uncaught; no horizontal overflow; keyboard/focus/landmarks/headings/labels; Vietnamese diacritics/long citations/wide tables/media; Rank Math independence; provider states accurately reported; update/theme-switch/rollback continuity; measured asset behavior.

**Allowed pilot action:** install/update a QA candidate on the approved pilot as needed for the matrix. This approval does not authorize tag/GitHub Release or final production deployment.

**Exit:** current-stack pilot evidence complete with any defects reduced to the shallowest reproducible layer; P1 duplicate cleanup closed before final pilot production sign-off.

**Next:** P5 only after P4 PASS.



### P4-B — Law Site Provisioning v1.1

**State:** CANDIDATE PASS / STACKED. D-026 source gate and implementation path completed on PR #61; main integration remains owner-gated.

**Goal:** provision or explicitly map the bounded Law 01 starter structure with proposal-only recommendation, starter editorial/media coverage and index-safe publication without transferring ongoing WordPress content ownership to Theme.

**TDD / verification:** source gate -> recommendation/classification -> starter editorial -> plan/admin UX -> media/runner -> index/readiness -> L3 runtime -> L4 browser/a11y -> deterministic candidate = PASS on verified production/test/package head `d564c6e8a43f458a493fe816c4fc768d4917065f`.

**Evidence:** package run `34362997863`; browser/runtime run `34362997879`; candidate `aznet-theme-1.1.1-law-site-provisioning-v1-1-candidate.zip`; SHA-256 `51006edeed48c92be0de84926e033a6d081e8fd493b2be7c03223056da784bf9`; detailed evidence `docs/evidence/LAW_SITE_PROVISIONING_V1_1_L4.md`.

**Rollback:** before successful handoff, restore captured settings/Front Page/menu assignment/indexing and remove only current-run creations. After success, presentation rollback is `homepage_preset=off` or remapping; WordPress-owned content persists.

**Exit:** PASS through L4 + deterministic stacked candidate. No L5 provider certification, canonical-main merge, tag/GitHub Release or production deployment is implied.

**Next:** owner-gated PR #61 integration/merge disposition; P4 real-site pilot and P5 publication/deployment remain separate gates.

### P5 — Final Candidate / Publication / Deployment

Run exact-main verification, deterministic double-build byte identity, unzip/exact-compare/lint/clean-WordPress activation smoke and rollback reference. Stop before tag/GitHub Release and final production deployment unless separately approved.

## 13. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility remains BLOCKED because provider output introduces a nested document-level `<main>`. Track provider fix; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain W1-W9 ownership/fail-soft evidence plus R4 presentation evidence. WooCommerce continues to own product/price/stock/variation/cart/checkout/order/account truth.

## 14. Recovery checkpoint

At every slice, commit bounded changes on a work/feature branch and retain a clear base SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior. Do not rerun retained PASS without an invalidation unless the active exit gate explicitly requires fresh final-candidate evidence.

## 15. Exact next

**P4 — Real Pilot QA from canonical post-P3 `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Prepare an exact candidate for the approved pilot, verify the current-stack route/responsive/a11y/Rank Math/RootProfile matrix, record defects/evidence, and keep P1 duplicate-theme deletion as a separate destructive site-ops gate. Stop before tag/GitHub Release or final production deployment.**