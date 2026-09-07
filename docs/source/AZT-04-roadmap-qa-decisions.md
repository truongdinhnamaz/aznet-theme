# AZT-04 — Roadmap, QA và Decision Log

**Version:** v0.24  
**Status:** Working Source  
**Date:** 07/09/2026

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
| R3 | Header System 2.0 | READY | Standard/Compact/Commerce/Overlay bounded presets; next sequential production slice |
| R4 | WooCommerce Presentation 2.0 | READY | Product-card/catalog/product/cart/checkout/account presentation improvements; independently unblocked |
| R5 | Control Center + System Health | PLANNED / DEPENDENCY WAIT | Waits for final R3/R4 setting keys |
| R6 | Performance + Release 2.0 | PLANNED | Asset-scope evidence, exact-main CI and deterministic v1.x release closure |
| U | Historical Control Center stream | SUPERSEDED / REFERENCE ONLY | Historical PR/evidence may inform R5; no wholesale merge and no parallel admin/settings architecture |

Canonical implementation baseline after R2 merge: `main@d2e4ae567108b6a224b623febca7a676b7114715`.

R2 merged from head `6a40a44faaa8d8207c2a3b850e2bc6d65cd561ee`; its tree is identical to the merge tree at `3f9e934eaa4bdcb058ccf289d7ef1faa7eac1ee5`. Fresh exact-PR-head verification completed 12/12 workflows successfully. No pull-request-triggered Actions run exists on the merge SHA, so post-merge execution is not inferred beyond the proven tree equivalence.

A fresh GitHub check on 07/09/2026 still finds no Git tag and no GitHub Release for v1.0.0, so **publication = PUBLICATION_PENDING** even though metadata/core technical closure is `1.0.0`.

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

**Next:** R3 sequentially; R4 remains independently READY.

### R3 — Header System 2.0

Introduce Standard/Compact/Commerce/Overlay presentation presets from reusable primitives. Mobile/sticky behavior uses accessible progressive enhancement and remains usable with JS failure.

**Exit:** L1-L4 PASS across preset/browser/keyboard matrix.

### R4 — WooCommerce Presentation 2.0

Improve product cards, catalog, single-product, cart, checkout and account presentation through public Woo APIs/hooks/Blocks and surface-scoped CSS. No commerce/domain engine is added.

**Exit:** L1-L4 PASS with Woo present plus clean-WP/Woo-absent regression.

### R5 — Control Center + System Health

Provide a bounded presentation settings console using the single normalized Theme Mod schema. Quick Setup links to/uses native WordPress controls and does not create domain/content state. System Health is read-only and public-capability-based.

**Exit:** L1-L4 plus update/theme-switch continuity PASS.

### R6 — Performance + Release 2.0

Measure asset/runtime behavior, harden surface-aware loading, add reusable PR/exact-main CI and deterministic v1.x packaging. Provider-specific optimization without a public capability is BLOCKED rather than privately detected.

**Exit:** fresh final exact-main L0-L6 evidence, deterministic package identity and rollback reference; version/tag/release stops at owner gate.

## 5. Dependency order

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6`

R0, R1 and R2 are now complete on canonical `main`. R3/R4 remain independently executable while consuming stable R1 interfaces. R5 waits for final R3/R4 setting keys. R6 is the integration/release closure after production streams land.

Sequential execution chooses R3 as the exact next slice; parallel R4 execution is optional, not required.

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
- release version promotion/tag/GitHub Release;
- production deployment;
- provider takeover paths such as E5-D;
- destructive retirement without proven rollback.

Completed R0, R1 and R2 merge approvals are historical cleared gates and are not requested again.

## 13. Exact next

**R3 — Header System 2.0 from the latest canonical `main`. R4 remains independently unblocked and may proceed later or in an isolated parallel branch.**
