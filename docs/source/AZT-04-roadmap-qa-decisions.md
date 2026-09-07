# AZT-04 — Roadmap, QA và Decision Log

**Version:** v0.21  
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
| W | WooCommerce presentation shell | PASS / retained | W1-W9 code path on canonical `main`; WooCommerce retains commerce truth; final v1.0 regression will refresh package evidence |
| E | RootProfile Profile / Contact | OPTIONAL COMPAT ACTIVE | E0-E4/E5-B retained PASS; E5-C external BLOCKED; E5-D takeover locked; non-blocking for core v1.0 under D-016 |
| F | Homepage | CORE PASS / COMPAT ACTIVE | Native Theme Homepage merged; ConvertFlow F6/F7 actual integration evidence PASS; F8 integrated compatibility BLOCKED by provider nested `<main>`; non-blocking for core v1.0 |
| G | Core v1.0 Cleanup / Release | ACTIVE | Exact next = G0 |
| U | Control Center | DEFERRED / NON-CRITICAL | Not part of v1.0 critical path unless a future approved source changes scope |

Canonical implementation base for G0: `main@815ffcd43653930d1f302a055961ac24cf5ae843`.

## 3. Core v1.0 critical path

### G0 — Scope freeze and blocker inventory

Freeze the AZT-05 core promise. Inventory only Theme-owned blockers required for v1.0. Reject new feature scope and separate optional provider certification defects.

### G1 — Cleanup inventory and canonical-destination check

Identify historical Theme-local presentation duplicates, dead references, stale compatibility paths and package-only debris. Do not delete anything without provenance, canonical destination and rollback evidence.

### G2 — Bounded retirement

Retire only presentation code/assets proven superseded. Domain/Mixed/Adapter candidates remain unless their owner-safe split and compatibility gate are proven.

### G3 — Static/security/package hygiene

Fresh PHP lint, naming scan, forbidden storage/private API scan, escaping/sanitization review for touched paths, asset-scope checks and package-tree exclusions.

### G4 — WordPress-clean runtime

On WordPress 6.9+ / PHP 8.1+: activation, Home, native front page, Page, Post, Archive, Search and 404 must render without Theme-caused fatal/warning/parse/uncaught errors. Ecosystem plugins off is the release-critical baseline.

### G5 — Browser / responsive / accessibility / performance

Desktop/tablet/mobile core surfaces; keyboard/focus; landmarks; duplicate IDs; overflow; console errors; reduced-motion behavior where relevant; surface-aware asset loading.

### G6 — Update / theme-switch / rollback

Prove in-place update continuity for Theme-owned settings and WordPress-native content/configuration. Theme switch must not destroy external domain data. Verify rollback to the registered prior package/commit path.

### G7 — Full regression + deterministic package

Run retained contracts/regressions, build the v1.0 candidate deterministically, rebuild byte-identically, SHA-256, unzip/exact-byte reverify and activation smoke from extracted package bytes.

### G8 — v1.0 release candidate closure

Update only source/evidence actually affected, set release metadata on the final candidate, run fresh release checks, produce rollback reference and stop at the explicit owner approval gate for release tag/deploy.

## 4. QA layers

| Layer | Purpose | Minimum exit |
| --- | --- | --- |
| L0 — Source & State | Correct baseline, owner, dependencies, exact next | No source/ownership ambiguity |
| L1 — Static | Cheap deterministic defects | Syntax/naming/private-storage/dependency scans clean |
| L2 — Contract / TDD | Behavior boundary | RED fails for intended reason -> minimal GREEN -> related regression PASS |
| L3 — Runtime | Real WordPress execution | No Theme-caused fatal/warning; route/component smoke PASS |
| L4 — Browser / Visual / A11y | Real presentation | Responsive/keyboard/focus/landmark/console intent PASS |
| L5 — Integration | Cross-product boundary | Public contract, ownership and fail-soft behavior PASS for the certification target |
| L6 — Completion / Release | Deliverable integrity | Regression/package/SHA/unzip/reverify/rollback/source state complete |

No PASS may be inferred across layers.

## 5. Release-critical test matrix

| Case | WordPress | Optional providers | Expected result |
| --- | --- | --- | --- |
| C1 | On | All off | Theme activates and renders core site without fatal; this is release-critical |
| C2 | On | Woo off | Non-commerce surfaces remain normal |
| C3 | On | Woo on | Theme presentation works without taking commerce ownership |
| C4 | On | RootProfile absent/error/unsupported | Theme-side adapter fails soft; core Theme remains usable |
| C5 | On | ConvertFlow absent/error/unsupported | Native WordPress Homepage path remains usable |
| C6 | On -> switch Theme | Any optional provider state | Switching Theme must not delete external domain data |
| C7 | In-place Theme update | WordPress-native content + Theme-owned settings | Content/settings continuity and rollback path PASS |

Provider-specific runtime correctness beyond Theme-side fail-soft is required only before claiming that provider/version as certified compatibility, unless the defect is in Theme-owned code.

## 6. QA dimensions

- Runtime: activation, PHP error markers, template hierarchy/fallback, update and theme switch.
- Visual: desktop/tablet/mobile on core surfaces.
- Responsive: navigation, containers, overflow, long labels/content.
- Accessibility: landmarks, heading order, keyboard, visible focus, labels, reduced motion and contrast within Theme control.
- Security: context-correct escaping/sanitization and no private provider leakage.
- Contract: public/version/capability detection and fail-soft paths.
- Ownership: no parallel identity/product/Journey/commerce store in Theme.
- Performance: surface-aware asset loading; no global ecosystem bundle.
- Provenance: migration/retirement remains traceable and reversible.

## 7. Accepted Decision Log

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

## 8. Open questions

- O-004 RootProfile provider shape: CLOSED at E0.
- O-005 Woo override policy: CLOSED; hooks/CSS/Blocks-first, template override by exception only.
- O-007 build tooling: OPEN only when real module/minification needs appear; it is not a v1.0 requirement.

## 9. Non-goals for v1.0 closure

- Do not add a page builder.
- Do not make Control Center U0-U6 a release blocker.
- Do not implement RootProfile or ConvertFlow source code inside Theme.
- Do not claim provider certification without provider/version evidence.
- Do not fix external defects through private API/storage reads or authoritative routing heuristics.
- Do not reopen already-PASS milestones without a concrete invalidation.

## 10. Hard gates

The following remain explicit owner approval gates:

- merge of source/architecture changes to canonical `main` when approval is required;
- release version promotion/tag;
- production deployment;
- provider takeover paths such as E5-D;
- destructive retirement without proven rollback.

## 11. Exact next

**G0 — freeze the AZT-05 core v1.0 scope on canonical main and produce a bounded inventory of only Theme-owned cleanup/release blockers.**
