# AZT-04 — Roadmap, QA và Decision Log

**Version:** v0.26  
**Status:** Working Source  
**Date:** 08/09/2026

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
| D | Generic Templates | PASS | Page/Post/Archive/Search/404 through L6 closure |
| W | WooCommerce presentation shell | PASS / retained | W1-W9 code path on canonical `main`; WooCommerce retains commerce truth |
| E | RootProfile Profile / Contact | OPTIONAL COMPAT ACTIVE | E0-E4/E5-B retained PASS; E5-C external BLOCKED; E5-D takeover locked; non-blocking for core under D-016 |
| F | Homepage | CORE PASS / COMPAT ACTIVE | Native Theme Homepage merged; ConvertFlow F6/F7 actual integration evidence retained; F8 integrated compatibility BLOCKED by provider nested `<main>` |
| G | Core v1.0 Cleanup / Release | TECHNICAL PASS | G0-G8 production/release-candidate closure merged; publication is a separate live GitHub state |
| R0 | v1.0 -> v1.1 Source Reconciliation | PASS | Source-only reconciliation merged through PR #36; v1.1 product/architecture ratified |
| R1 | Design System 2.0 | PASS | PR #37 merged; settings/tokens + Default/Editorial/Commerce outcomes + editor/frontend parity L1-L4 |
| R2 | Native Pattern Library | PASS | PR #39 merged; 18 portable patterns; public Woo-block gating; L1-L4 + theme-switch portability evidence |
| R3 | Header System 2.0 | PASS | PR #41 merged; four bounded presets, three sticky modes, reusable primitives, mobile progressive enhancement and L1-L4 browser/a11y evidence |
| R4 | WooCommerce Presentation 2.0 | PASS | PR #43 merged; catalog/card/product/cart/checkout/account presentation, clean-WP fail-soft and Woo-present L1-L4 evidence |
| R5 | Control Center + System Health | PASS | PR #44 merged; bounded presentation admin, System Health, authenticated browser/a11y and update/theme-switch continuity evidence |
| R6 | Performance + Release 2.0 | READY / EXACT NEXT | Measured asset/performance closure, exact-main CI, deterministic v1.1 package and atomic metadata promotion subject to release gates |
| U | Historical Control Center stream | SUPERSEDED / REFERENCE ONLY | Historical PR/evidence may inform provenance only; no parallel admin/settings architecture |

Canonical implementation baseline after R5 merge and the subsequent no-op history correction: `main@484e896cace06d684d29a3f93e3cce85e84a9f80`, production tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`.

R4 merged through PR #43 to `main@0ddc6c799391d57db134f04449effdf511d41d1f`; final head and merge share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. R4 functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully and the final evidence-only head retained fresh 16/16 success.

R5 merged through PR #44 to `main@6c69def2ff4ac7adb1221de09aec057b63b1adf6`; final head and merge share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. R5 functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully, including R5 authenticated browser/continuity plus all retained R1-R4/core regressions. The final evidence-only head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` differs from the verified functional/test head only by `docs/evidence/R5_CONTROL_CENTER_L4.md` and merged without tree delta.

After R5 merge, a temporary source-state sentinel was accidentally added to `main` and immediately removed in the next commit. Current `main@484e896cace06d684d29a3f93e3cce85e84a9f80` compares to the R5 merge with no changed files; implementation bytes remain the exact R5 production tree. This history event does not reopen any QA layer.

A fresh GitHub release check on 08/09/2026 returns no GitHub Release; no tag evidence has been established. Publication therefore remains **PUBLICATION_PENDING** and Theme metadata intentionally remains `1.0.0` until the R6 promotion gate.

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

**Goal:** make canonical source agree with the v1.0 implementation baseline and ratify the already-approved v1.1 product/architecture direction.

**Allowed:** `docs/source/**`, current-state evidence and source-only PR.

**Forbidden:** production PHP/CSS/JS changes, provider takeover, changing AZT-05 without a new constitutional decision.

**Exit:** PASS — AZT-01/02/03/04, AZT-EXEC-MAP and SOURCE_MANIFEST were reconciled; AZT-05 remained unchanged; source-only PR #36 was approved and merged.

**Next:** R1 — completed.

### R1 — Design System 2.0

Build one Theme-owned versioned presentation settings schema, expanded semantic tokens and Default/Editorial/Commerce visual outcomes. Native style-variation mechanism is evidence-gated on WordPress 6.9 hybrid support; fallback is Theme-owned semantic preset mapping, not FSE takeover.

**Exit:** PASS — L1-L4 and editor/frontend parity proven on R1 candidate bytes; PR #37 merged to canonical `main`.

**Next:** R2/R3/R4 may proceed independently.

### R2 — Native Pattern Library

Ship a curated WordPress-native launch set using core blocks and public Woo blocks where available. Candidate quality beats quota; Woo-dependent patterns fail soft and content remains portable after theme switch.

**Exit:** PASS — 18 patterns shipped; 16 core/native plus 2 public-Woo-block-gated patterns. L1-L4, editor/frontend parity, clean-WP/Woo-present matrices and byte-identical theme-switch content portability proven; PR #39 merged to canonical `main`.

**Next:** R3 — completed; R4 completed.

### R3 — Header System 2.0

Introduce Standard/Compact/Commerce/Overlay presentation presets from reusable primitives. Mobile/sticky behavior uses accessible progressive enhancement and remains usable with JS failure.

**Exit:** PASS — four bounded presets and three sticky modes delivered; mobile no-JS fallback, keyboard/focus, depth-2 navigation, reduced motion, 1440/1024/390/320 responsiveness, Overlay fallback and Woo-present/absent Commerce behavior verified through L1-L4. PR #41 merged to canonical `main` with exact head/merge tree equivalence.

**Next:** R4 — completed.

### R4 — WooCommerce Presentation 2.0

Improve product cards, catalog, single-product, cart, checkout and account presentation through public Woo APIs/hooks/Blocks and surface-scoped CSS. No commerce/domain engine is added.

**Exit:** PASS — public-Woo presentation settings/surfaces delivered without template takeover or commerce state duplication; clean-WP absence regression and Woo-present Theme-owned L1-L4 matrix complete. PR #43 merged to canonical `main`; detailed evidence is `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

**Next:** R5 — completed.

### R5 — Control Center + System Health

Provide a bounded presentation settings console using the single normalized Theme Mod schema. Quick Setup links to/uses native WordPress controls and does not create domain/content state. System Health is read-only and public-capability-based.

**Exit:** PASS — static/contract, real WordPress authenticated browser/a11y, clean-WP/Woo-present capability gating, settings portability, in-place update continuity and theme-switch continuity all verified. PR #44 merged to canonical `main`; detailed evidence is `docs/evidence/R5_CONTROL_CENTER_L4.md`.

**Next:** R6.

### R6 — Performance + Release 2.0

Measure asset/runtime behavior, harden surface-aware loading, add reusable PR/exact-main CI and deterministic v1.x packaging. Provider-specific optimization without a public capability is BLOCKED rather than privately detected.

**Required sequence:**

1. capture route/asset/performance baseline on the latest canonical `main`;
2. harden only Theme-owned surface-aware asset loading where measured evidence justifies change;
3. evaluate ConvertFlow projection loading only through a documented public capability; otherwise record `BLOCKED_EXTERNAL_CONTRACT` and retain safe behavior;
4. add reusable PR CI and exact-main post-merge verification;
5. add deterministic v1.x release-candidate workflow/package builder with stable top-level `aznet-theme/` identity;
6. retire obsolete milestone-specific workflows only after replacement coverage and fresh evidence;
7. run final exact-main R1-R6 regression before metadata promotion;
8. at the source-defined approval gate, promote `style.css` Theme Version and `AZNET_THEME_VERSION` atomically to exact `1.1.0` — never only the package filename;
9. rerun exact-main/package verification on promoted bytes and stop before tag/GitHub Release unless separately approved.

**Exit:** fresh final exact-main L0-L6 evidence, deterministic package identity/SHA/file count and rollback reference.

**Hard gate:** release metadata promotion and later tag/GitHub Release remain explicit owner approvals under Section 12.

## 5. Dependency order

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6`

R0 through R5 are complete on canonical `main`. R4 final setting keys were consumed by R5 without a parallel settings schema. R6 is now the only remaining core-v1.1 implementation/release-closure stream.

## 6. QA layers

| Layer | Purpose | Minimum exit |
| --- | --- | --- |
| L0 — Source & State | Correct baseline, owner, dependencies, exact next | No source/ownership ambiguity |
| L1 — Static | Cheap deterministic defects | Syntax/naming/private-storage/dependency scans clean |
| L2 — Contract / TDD | Behavior boundary | RED fails for intended reason -> minimal GREEN -> related regression PASS |
| L3 — Runtime | Real WordPress execution | No Theme-caused fatal/warning; route/component smoke PASS |
| L4 — Browser / Visual / A11y | Real presentation | Responsive/keyboard/focus/landmark/console intent PASS; editor/frontend parity belongs here for presets/patterns |
| L5 — Integration | Cross-product boundary | Public contract, ownership and fail-soft behavior PASS for the certification target; provider capability detection is L5 only when an external public contract is involved |
| L6 — Completion / Release | Deliverable integrity | Regression/package/SHA/unzip-reverify/rollback/source state complete on final candidate bytes |

No PASS may be inferred across layers.

## 7. Release-critical test matrix

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

Provider-specific runtime correctness beyond Theme-side fail-soft is required only before claiming that provider/version as certified compatibility, unless the defect is in Theme-owned code.

## 8. QA dimensions

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

## 9. Accepted Decision Log

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

## 10. Open questions

- O-004 RootProfile provider shape: CLOSED at E0.
- O-005 Woo override policy: CLOSED; hooks/CSS/Blocks-first, template override by exception only.
- O-007 build tooling: OPEN only when real module/minification needs appear; it is not automatically introduced by v1.1.
- O-008 ConvertFlow projection asset gating: OPEN/BLOCKED unless a documented public/versioned Theme-consumable capability exists; private detection is forbidden.

## 11. Non-goals for v1.1

- Do not add a page builder or FSE architecture takeover.
- Do not create a mega-menu builder, AJAX filter/search engine, wishlist/compare, swatch domain engine, quick-view business engine or custom checkout engine.
- Do not implement RootProfile or ConvertFlow source code inside Theme.
- Do not claim provider certification without provider/version evidence.
- Do not fix external defects through private API/storage reads or authoritative routing heuristics.
- Do not create a second Theme settings store or duplicate WordPress native logo/menu/content data.
- Do not reopen already-PASS milestones without a concrete invalidation.

## 12. Hard gates

The following remain explicit owner approval gates:

- any later product/architecture/public-contract change not already ratified here;
- release metadata/version promotion;
- Git tag/GitHub Release;
- production deployment;
- provider takeover paths such as E5-D;
- destructive retirement without proven rollback.

Completed R0-R5 implementation/source merge approvals are historical cleared gates and are not requested again for those already-merged scopes. R6 may execute safe pre-promotion work, but it must stop before the metadata/version promotion gate unless explicitly approved.

## 13. Exact next

**R6 — Performance + Release 2.0 from the latest canonical `main`. First capture measured route/asset/performance baseline and build exact-main release infrastructure; do not promote metadata or tag/release without the explicit gate in Section 12.**
