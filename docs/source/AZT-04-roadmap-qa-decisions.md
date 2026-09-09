# AZT-04 — Roadmap, QA và Decision Log

**Version:** v0.34  
**Status:** Working Source  
**Date:** 10/09/2026

## 1. Purpose

AZT-04 owns milestone sequencing, QA layers, release gates, accepted decisions and open questions for AZnet Theme. It does not own external domain semantics or rewrite the product constitution.

AZT-05 v1.0 governs the v1.x release constitution: **WordPress-clean core Theme readiness is release-critical; optional provider compatibility is a separate certification track unless a future approved source explicitly makes it mandatory.**

## 2. Current roadmap state

| ID | Workstream | State | Current meaning |
| --- | --- | --- | --- |
| 0 | Source Freeze & Provenance | PASS | Raw freeze/hash/extraction, 817/817 classification and pre-port baselines accepted |
| A | Theme Foundation | PASS | Hybrid PHP + `theme.json`, WordPress-clean foundation |
| B | Semantic Design Tokens | PASS | Public `--aznet-theme-*` tokens + `theme.json` mapping |
| C | Header / Footer | PASS | Theme-owned presentation with runtime/browser/a11y evidence |
| D | Generic Templates | PASS / QUALITY HARDENING ACTIVE | Page/Post/Archive/Search/404 technical closure remains valid; P2/P3 hardening is merged and P4 must now prove professional editorial quality on the pilot |
| W | WooCommerce presentation shell | PASS / retained | W1-W9 + R4; WooCommerce retains commerce truth |
| E | RootProfile Profile / Contact | OPTIONAL COMPAT ACTIVE | E0-E4/E5-B retained PASS; E5-C external BLOCKED; E5-D takeover locked; non-blocking for core under D-016 |
| F | Homepage | CORE PASS / COMPAT ACTIVE | Native Theme Homepage merged; ConvertFlow F6/F7 actual integration evidence retained; F8 integrated compatibility BLOCKED by provider nested `<main>` |
| G | Core v1.0 Cleanup / Release | TECHNICAL PASS | G0-G8 production/release-candidate closure merged; publication is a separate live GitHub state |
| R0 | v1.0 -> v1.1 Source Reconciliation | PASS | Source-only reconciliation merged through PR #36; v1.1 product/architecture ratified |
| R1 | Design System 2.0 | PASS | PR #37 merged; settings/tokens + Default/Editorial/Commerce outcomes + editor/frontend parity L1-L4 |
| R2 | Native Pattern Library | PASS | PR #39 merged; 18 portable patterns; public Woo-block gating; L1-L4 + theme-switch portability evidence |
| R3 | Header System 2.0 | PASS | PR #41 merged; four bounded presets, three sticky modes, reusable primitives, mobile progressive enhancement and L1-L4 browser/a11y evidence |
| R4 | WooCommerce Presentation 2.0 | PASS | PR #43 merged; catalog/card/product/cart/checkout/account presentation, clean-WP fail-soft and Woo-present L1-L4 evidence |
| R5 | Control Center + System Health | PASS | PR #44 merged; bounded presentation admin, System Health, authenticated browser/a11y and update/theme-switch continuity evidence |
| R6 | Performance + Release 2.0 | PASS / TECHNICAL CANDIDATE | PR #46-#48 complete; exact-main verification + deterministic `1.1.0` candidate PASS; tag/GitHub Release/deployment remain pending |
| P1 | Pilot identity cleanup | SITE-OPS READY | Active pilot Theme `1.1.0` proven; only inactive legacy duplicates may be deleted through WordPress core UI after smoke/rollback confidence |
| P2 | Editorial single-post hardening | PASS | PR #51 merged; native Post editorial presentation + singular-post scoped assets + retained regressions |
| P3 | Editorial archive/search hardening | PASS | PR #52 merged; native thumbnail/date scan presentation + resilient long-title/excerpt styling; WordPress main query retained |
| P4 | Real law-site pilot QA | READY / EXACT NEXT | Owner-approved pilot QA is now the exact next after canonical PR #58 integration and D-022 exact-main PASS |
| P4-A | Homepage Composer + Law 01 | PASS / MERGED | Premium Law01 + Composer merged through PR #58; exact-main static/runtime/browser core verification PASS |
| P4-B | Law Site Provisioning v1.1 | PASS / MERGED | D-026 smart setup merged through PR #58 with Theme metadata `1.1.0`; deterministic package/browser evidence retained and exact-main verification PASS |
| P5 | Publication & production deployment | GATED | Tag/GitHub Release and final production deployment remain separate explicit owner gates |
| U | Historical Control Center stream | SUPERSEDED / REFERENCE ONLY | Historical PR/evidence may inform provenance only; no parallel admin/settings architecture |

Current canonical implementation is `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`, tree `28c30353e9f01bc51f4474347069738d410b5256`, Theme metadata `1.1.0`. Owner-approved PR #58 merge is complete. Fresh `V1 Exact Main Verification` run `34414878827` succeeded on that exact merge SHA; PR-head evidence is retained separately and is not substituted for exact-main evidence.

The earlier deterministic R6 candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b` remains historical release-provenance evidence only. P2/P3 changed production bytes, so P5 must build and verify a new deterministic package from the final post-hardening bytes before publication.

R4 merged through PR #43 to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; final head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. R4 functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully and the final evidence-only head retained fresh 16/16 success.

R5 merged through PR #44 to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; final head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including R5 authenticated browser/continuity plus all retained R1-R4/core regressions.

R6 infrastructure merged through PR #46 to `main@a3dc550bd748acab59e1df2384f8e2ada55541e9`; covered legacy G workflows were retired through PR #47 to `main@27537e520ba15afe771cea86080abd99a7276a35`; owner-approved atomic metadata promotion merged through PR #48 to `main@4673202f67068294fe92d49d1ed66e371d0ecbb3`. Fresh exact-main verification succeeded on promoted bytes. Manual `V1 Release Candidate Package` run `34187975628` succeeded on that exact main SHA with one top-level `aznet-theme/` directory and historical candidate SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

P2 merged through PR #51 from final verified head `dec498b997aec6bc8b08cf91f3e09c42611bbd66` to `main@a863da861b6a299920568b2b0c9ca57924727431`. Verification caught and fixed two Theme-owned defects before merge: an empty native-author link and a Woo/CPT scope leak. The final bounded behavior applies Editorial Article presentation only to native Post and fails soft when native author data is unavailable.

P3 merged through PR #52 from final verified head `d78091900451176c3815b23d1485036e784bdde9` to `main@a1dd42dd9672bd6b7cb07be90ae5fd64a6dd14e0`. Head and merge share tree `8fc309ea8e01bfe727da943754c98164b3f92a58`. Fresh exact-main verification succeeded after merge.

A fresh GitHub release check on 08/09/2026 established no GitHub Release/tag publication. Publication therefore remains **PUBLICATION_PENDING**. Approval to execute P4 pilot QA does not clear P5 publication or final production deployment gates.

## 3. v1.0 closure state

### G0-G8 — Technical PASS

The source-defined G stream is no longer the active implementation path. Its retained meaning is:

- G0-G2: scope/cleanup/retirement inventory closed without ownership-unsafe deletion;
- G3: static/security/package hygiene PASS on verified candidate head;
- G4: WordPress-clean runtime PASS;
- G5: browser/responsive/a11y/performance PASS;
- G6: update/theme-switch/rollback PASS;
- G7: deterministic package candidate PASS;
- G8: metadata promoted atomically to `1.0.0` and final candidate gates PASS.

PR #34 final verified head remains `b2e5cca1461233bcb1a0333c5aa51879c3264756`, and merge commit `f8e1a95c903c3f246528368ae9878eba780539ff` shares tree `b716b89f04e45c2012f8e191c7d0edf605c9dd11` with that verified head.

Publication/tag is not inferred from technical PASS and remains a separate owner action/state.

## 4. v1.1 Native Product System roadmap

### R0 — Source reconciliation

**Goal:** make canonical source agree with the v1.0 implementation baseline and ratify the approved v1.1 product/architecture direction.

**Exit:** PASS — source-only PR #36 merged; AZT-05 remained unchanged.

### R1 — Design System 2.0

**Exit:** PASS — L1-L4 and editor/frontend parity proven; PR #37 merged.

### R2 — Native Pattern Library

**Exit:** PASS — 18 patterns shipped; 16 core/native plus 2 public-Woo-block-gated patterns; L1-L4 and theme-switch portability proven; PR #39 merged.

### R3 — Header System 2.0

**Exit:** PASS — four bounded presets and three sticky modes delivered; mobile no-JS fallback, keyboard/focus, 1440/1024/390/320 responsiveness, Overlay fallback and Woo-present/absent Commerce behavior verified through L1-L4; PR #41 merged.

### R4 — WooCommerce Presentation 2.0

**Exit:** PASS — public-Woo presentation settings/surfaces delivered without template takeover or commerce state duplication; clean-WP absence regression and Woo-present Theme-owned L1-L4 matrix complete; PR #43 merged.

### R5 — Control Center + System Health

**Exit:** PASS — static/contract, authenticated WordPress browser/a11y, clean-WP/Woo-present capability gating, settings portability, in-place update continuity and theme-switch continuity verified; PR #44 merged.

### R6 — Performance + Release 2.0

**Goal:** close v1.1 with measured asset/runtime behavior, reusable PR/exact-main CI, deterministic v1.x packaging and atomic metadata promotion.

**Exit:** PASS — measured clean/Woo route/asset baseline established; Theme-owned surface-aware loading guarded; ConvertFlow projection optimization correctly recorded `BLOCKED_EXTERNAL_CONTRACT`; reusable PR/exact-main verification merged; deterministic main-only candidate builder merged; covered G workflows retired; final exact-main R1-R6 verification passed; owner-approved metadata promotion moved both `style.css` and `AZNET_THEME_VERSION` atomically to `1.1.0`; post-promotion exact-main/package verification passed.

**Historical candidate:** `aznet-theme-1.1.0.zip`, one canonical top-level `aznet-theme/` directory, SHA-256 `000735630403c4b31a1385b7206b5e4433c62fbdd72b91728ad1aaec93625e7b`.

**Not implied:** Git tag, GitHub Release, production deployment or provider certification. Later P2/P3 production hardening requires a new final package before publication.

## 5. Post-R6 production-readiness program

The law-site pilot does not create legal/domain ownership for the Theme. It is a concrete professional editorial use case used to harden generic Theme presentation.

### P1 — Pilot identity cleanup

**Owner:** site operations / WordPress core lifecycle, not Theme runtime code.

System Health evidence proves the active pilot is AZnet Theme `1.1.0` on WordPress `7.1` / PHP `8.4.24`, with `primary_menu=yes`. The multiple `AZnet Theme` cards therefore represent installed legacy folder identities, not multiple active runtimes.

**Exit:** smoke active frontend; inspect inactive cards; delete only inactive legacy versions through WordPress core UI; verify one intended AZnet Theme remains and System Health still reports `1.1.0` + primary menu continuity.

**Hard gate:** deletion is destructive and must not be automated by Theme code. If any inactive card also reports `1.1.0`, stop and inspect identity before deletion.

### P2 — Editorial single-post hardening

**Goal:** production-grade generic long-form Post presentation for professional/editorial sites.

**Allowed:** native WordPress author/date/taxonomy/featured-image/content-pagination/post-navigation APIs; existing Theme shell/tokens; existing public RootProfile consumer only when it can enhance presentation without a new contract.

**Required presentation:** semantic article header, native author, published/modified dates, categories/tags, optional featured image, long-form typography, accessible headings/lists/blockquote/table/media behavior, content pagination, previous/next navigation and WordPress-native author fallback.

**Forbidden:** legal/business semantics; SEO title/meta/canonical/schema/OG generation; custom related-post ranking/query engine; private RootProfile/ConvertFlow/Woo storage/API reads; authoritative slug/title/Page-ID/URL heuristics.

**Execution:** RED contract -> intended failure on previous minimal `single.php` -> minimal GREEN -> retained regression. Article assets remain singular-post scoped.

**Exit:** **PASS** — PR #51 merged to `main@a863da861...`; exact-main verification succeeded. Deeper real-site quality is intentionally deferred to P4 and is not inferred from L1/L2/L3/L4 generic CI.

### P3 — Editorial archive/search hardening

Improve native archive/search card scan quality for long Vietnamese titles, optional native thumbnail/date and excerpt. WordPress main query remains authoritative. Use RED -> GREEN and retain archive/search regression.

**Exit:** **PASS** — PR #52 merged to `main@a1dd42dd...`; exact-main verification run `34195248467` succeeded. The final P3 card retains native WordPress Loop/query ownership and adds presentation only.

### P4 — Real law-site pilot QA

**State:** **READY / EXACT NEXT; pilot QA execution approved by owner on 08/09/2026.**

Run realistic Vietnamese legal/editorial fixtures and the actual pilot stack across homepage, long Post, Page, archive/category, search/no-results, 404 and public RootProfile surfaces. Support-floor evidence remains separate from current-stack pilot evidence.

Minimum quality: no Theme-caused PHP fatal/warning/uncaught, no horizontal overflow, keyboard/focus/landmark/heading/label quality, responsive table/media behavior, Rank Math independence, RootProfile contract accuracy, update/theme-switch/rollback continuity and measured actual-site asset behavior.

P4 may install/update a test candidate on the pilot only as necessary to perform approved QA. It does **not** authorize tag/GitHub Release or final production deployment. Any destructive duplicate-theme deletion remains under P1's separate site-operations gate.


### P4-A — Homepage Composer + Law 01

**State:** **PASS / MERGED TO CANONICAL MAIN.**

Homepage Composer Core + `law-01` Premium presentation merged through PR #58 to `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`. Content Map remains typed WordPress references only; preset switching does not mutate WordPress content; native Post/Page Classic Editor policy remains; `front-page.php` retains exactly one `the_content()` boundary.

Fresh D-022 exact-main run `34414878827` passed reusable static/contracts plus WordPress 6.9 clean runtime/browser/a11y on the exact merge SHA. The integration-only `1.1.1` metadata attempt remains rejected historical evidence; canonical Theme metadata is `1.1.0`.

### P4-B — Law Site Provisioning v1.1

**State:** **PASS / MERGED TO CANONICAL MAIN.**

D-026 `law01-v1-1` remains an additive WordPress-native setup workflow. Recommendations are proposal-only until explicit change-plan confirmation; activation remains mutation-free; existing-site overwrite and whole-site noindex takeover remain forbidden. Starter Pages/Posts/Categories/Attachments become ordinary WordPress-owned content after successful provisioning. New/mostly-empty-site starter publication is coupled to explicit native search-visibility consent; active-site starter Posts fail safe to Draft without a public per-Post SEO contract.

Pre-merge integrated QA remains retained: package run `34413505990` produced Theme `1.1.0` deterministic candidate SHA-256 `19a0411616c67b8795fd08d3590accc571239ca65f4b67f9bc44c3d655170252`; browser/runtime run `34413506017` passed WordPress 6.9 / PHP 8.1 / MySQL 8 provisioning scenarios. Canonical integration is separately proven by exact-main run `34414878827` SUCCESS on `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`.

**Known observation:** the separately recorded WordPress-core `WP_Query::rewind_posts()` warning remains UNKNOWN/non-blocking and does not support a blanket PHP-log-clean claim.

**Next:** P4 Real Pilot QA under the already-approved pilot execution scope. No tag/GitHub Release, L5 provider certification, destructive duplicate-theme deletion or final production deployment is authorized by this merge.

### P5 — Publication and deployment

Only after the final production-hardening candidate has fresh exact-main/package evidence may publication proceed. Git tag/GitHub Release and final production deployment remain separate explicit owner approvals.

## 6. Dependency order

Core v1.1 is complete:

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6 = PASS`

Post-R6 production readiness:

`P1 site-ops cleanup (parallel/destructive gate)`  
`P2 Editorial Single-Post PASS -> P3 Editorial Listing/Search PASS -> P4 Real Pilot QA READY -> P5 Publication/Deployment gates`

P1 does not block safe Theme-owned pilot QA setup, but P1 must close before final pilot production sign-off.

## 7. QA layers

| Layer | Purpose | Minimum exit |
| --- | --- | --- |
| L0 — Source & State | Correct baseline, owner, dependencies, exact next | No source/ownership ambiguity |
| L1 — Static | Cheap deterministic defects | Syntax/naming/private-storage/dependency scans clean |
| L2 — Contract / TDD | Behavior boundary | RED fails for intended reason -> minimal GREEN -> related regression PASS |
| L3 — Runtime | Real WordPress execution | No Theme-caused fatal/warning; route/component smoke PASS |
| L4 — Browser / Visual / A11y | Real presentation | Responsive/keyboard/focus/landmark/console intent PASS; editor/frontend parity belongs here for presets/patterns |
| L5 — Integration | Cross-product boundary | Public contract, ownership and fail-soft behavior PASS for the certification target |
| L6 — Completion / Release | Deliverable integrity | Regression/package/SHA/unzip-reverify/rollback/source state complete on final candidate bytes |

No PASS may be inferred across layers.

## 8. Release-critical test matrix

| Case | WordPress | Optional providers | Expected result |
| --- | --- | --- | --- |
| C1 | On | All off | Theme activates and renders core site without fatal; release-critical |
| C2 | On | Woo off | Non-commerce surfaces remain normal |
| C3 | On | Woo on | Theme presentation works without taking commerce ownership |
| C4 | On | RootProfile absent/error/unsupported | Theme-side adapter fails soft; core Theme remains usable |
| C5 | On | ConvertFlow absent/error/unsupported | Native WordPress Homepage path remains usable |
| C6 | On -> switch Theme | Any optional provider state | Switching Theme must not delete external domain data |
| C7 | In-place Theme update | WordPress-native content + Theme-owned settings | Content/settings continuity and rollback path PASS |
| C8 | Final v1.x merge | Exact canonical `main` bytes | Post-merge static/runtime/browser core verification must run on exact `main` SHA before release-candidate promotion |
| C9 | Professional editorial pilot | Long Vietnamese editorial/legal-form content | Theme-owned single/listing presentation remains readable, responsive, accessible and SEO-owner-neutral |

Provider-specific runtime correctness beyond Theme-side fail-soft is required only before claiming that provider/version as certified compatibility, unless the defect is in Theme-owned code.

## 9. QA dimensions

- Runtime: activation, PHP error markers, template hierarchy/fallback, update and theme switch.
- Visual: desktop/tablet/mobile on core surfaces.
- Responsive: navigation, containers, overflow, long labels/content.
- Accessibility: landmarks, heading order, keyboard, visible focus, labels, reduced motion and contrast within Theme control.
- Security: context-correct escaping/sanitization and no private provider leakage.
- Contract: public/version/capability detection and fail-soft paths.
- Ownership: no parallel identity/product/Journey/commerce store in Theme.
- Performance: surface-aware asset loading; no global ecosystem bundle; improvement claims require measured evidence.
- Provenance: migration/retirement remains traceable and reversible.
- Authoring portability: patterns/settings must not lock WordPress content into proprietary Theme data.
- Release provenance: PR-head PASS and exact-main PASS are distinct evidence points.
- Editorial trust presentation: native publication facts may be rendered, but Theme does not become owner of legal accuracy, SEO metadata or authoritative identity semantics.

## 10. Accepted Decision Log

| ID | Decision | Status |
| --- | --- | --- |
| D-001 | Source owns data; Theme owns presentation; contracts connect them | Accepted |
| D-002 | AZnet Theme is a house/reference theme and not a hard dependency of ecosystem plugins | Accepted |
| D-003 | Provenance inventory precedes redesign/reuse | Accepted |
| D-004 | Theme does not read private internals of plugins | Accepted |
| D-005 | Provider/plugin absence or failure must fail soft/safe | Accepted |
| D-006 | Retire historical code only after destination + fallback/compatibility + regression + rollback | Accepted |
| D-007 | Architecture mode is hybrid PHP theme + `theme.json` | Accepted |
| D-008 | Support floor is WordPress 6.9+ and PHP 8.1+ | Accepted |
| D-009 | Naming family is `aznet-theme` (`AZnet\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`) | Accepted |
| D-010 | Milestone 0 provenance gate and WordPress-clean T1 runtime gate are distinct | Accepted |
| D-011 | Performance is surface-aware; no global frontend ecosystem bundle by default | Accepted |
| D-012 | Execute by evidence depth; do not infer deeper PASS from shallower evidence | Accepted |
| D-013 | RootProfile current-surface context does not grant production takeover | Accepted |
| D-014 | Woo presentation work may progress in parallel with externally blocked RootProfile integration | Accepted |
| D-015 | Homepage Theme-owned stream may progress in parallel without taking ConvertFlow semantics | Accepted |
| **D-016** | **Core v1.0 release independence: WordPress-clean Theme quality defines core readiness; optional provider defects remain compatibility blockers unless Theme-owned or explicitly mandatory** | **Accepted** |
| **D-017** | **v1.1 uses WordPress-native authoring/patterns/presets and does not introduce a proprietary page builder** | **Accepted** |
| **D-018** | **Visual preset outcome is fixed; implementation mechanism is evidence-gated on WordPress 6.9 hybrid support** | **Accepted** |
| **D-019** | **Theme presentation settings use one versioned `aznet_theme_settings` Theme Mod schema with strict allow-list normalization** | **Accepted** |
| **D-020** | **Header v1.1 uses bounded presets/primitives, not drag/drop composition or mega-menu application ownership** | **Accepted** |
| **D-021** | **Woo v1.1 expands presentation only; wishlist/filter/search/swatches/quick-view/custom-checkout engines remain outside Theme** | **Accepted** |
| **D-022** | **Exact-main post-merge verification is required before a v1.x release candidate is promoted** | **Accepted** |
| **D-023** | **Professional editorial/law-site pilot hardening remains generic Theme presentation: WordPress owns publication/content state, SEO owner keeps metadata/schema, and no legal/domain semantics move into Theme** | **Accepted** |
| **D-024** | **Homepage template switching uses a stable typed Content Map plus Theme-owned Presentation Preset; Classic Editor remains for native Post/Page; provisioning is separate from preset application; the Front Page keeps exactly one `the_content()` boundary and no provider/domain semantics are cloned into Theme.** | **Accepted** |
| **D-025** | **Law Site Provisioning v1 is an explicit idempotent WordPress-native bootstrap workflow: activation is mutation-free; new/existing sites use a visible confirmed change plan; created content becomes WordPress-owned; failed-run rollback is current-run bounded; successful setup is not equivalent to Launch Ready.** | **Accepted** |
| **D-026** | **Law Site Provisioning v1.1 may preselect non-authoritative exact-label recommendations, seed bounded WordPress-native starter editorial/media content, and temporarily discourage indexing only on a confirmed new/mostly-empty-site plan; active-site starter content fails safe to Draft without a public per-Post SEO contract, and all authoritative mapping/mutation remains explicit in the confirmed provisioning plan.** | **Accepted** |

## 11. Open questions

- O-004 RootProfile provider shape: CLOSED at E0.
- O-005 Woo override policy: CLOSED; hooks/CSS/Blocks-first, template override by exception only.
- O-007 build tooling: OPEN only when real module/minification needs appear; it is not automatically introduced by v1.1.
- O-008 ConvertFlow projection asset gating: OPEN/BLOCKED unless a documented public/versioned Theme-consumable capability exists; private detection is forbidden.

## 12. Non-goals

- Do not add a page builder or FSE architecture takeover.
- Do not create a mega-menu builder, AJAX filter/search engine, wishlist/compare, swatch domain engine, quick-view business engine or custom checkout engine.
- Do not implement RootProfile or ConvertFlow source code inside Theme.
- Do not claim provider certification without provider/version evidence.
- Do not fix external defects through private API/storage reads or authoritative routing heuristics.
- Do not create a second Theme settings store or duplicate WordPress native logo/menu/content data.
- Do not create legal-domain storage/workflows merely because the pilot is a law website.
- Do not generate SEO canonical/meta/schema/OG output in Theme when SEO ownership remains external/native.
- Do not add a related-post ranking/query engine to Theme.
- Do not reopen already-PASS layers without a concrete invalidation; the editorial pilot quality finding reopened presentation quality only, not prior runtime/ownership conclusions.

## 13. Hard gates

The following remain explicit owner approval gates:

- any later product/architecture/public-contract change not already ratified here;
- Git tag/GitHub Release;
- final production deployment;
- provider takeover paths such as E5-D;
- destructive retirement/deletion without proven rollback;
- merge of future production hardening into canonical `main` after its own fresh verification when the active PR is explicitly owner-gated.

Metadata promotion to `1.1.0` is a historical cleared gate completed through PR #48. Source closure and the post-R6 roadmap were explicitly approved by the product owner on 08/09/2026. P2 and P3 production merges were separately owner-approved. Owner-approved PR #58 canonical integration is cleared at `main@04edd8b312de70e6ee8339c461ae8f16f93e5859` and D-022 exact-main run `34414878827` is PASS. P4 pilot QA execution remains approved. None of these approvals include Git tag/GitHub Release, destructive duplicate-theme cleanup, L5 provider certification or final production deployment.

## 14. Exact next

**P4 — Real law-site pilot QA on canonical `main@04edd8b312de70e6ee8339c461ae8f16f93e5859`. D-022 exact-main verification run `34414878827` is PASS. Prove the real current-stack route/responsive/a11y/Rank Math/RootProfile matrix and measured site behavior. P1 duplicate-theme cleanup remains a parallel destructive site-operations gate. Stop before tag/GitHub Release or final production deployment.**
