# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.13  
**Status:** Working Execution Map / derived  
**Date:** 07/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

## 1. Current state

| Workstream | State | Boundary |
| --- | --- | --- |
| 0 Source Freeze & Provenance | PASS | 817/817 classification + accepted source-bound evidence |
| A Theme Foundation | PASS | WordPress-clean hybrid PHP + `theme.json` |
| B Semantic Tokens | PASS | `--aznet-theme-*` + `theme.json` |
| C Header/Footer | PASS | Theme presentation owner |
| D Generic Templates | PASS | Page/Post/Archive/Search/404 through L6 |
| W Woo presentation | PASS / retained | Woo owns commerce truth; final v1.0 regression refreshes bytes |
| E RootProfile Profile/Contact | OPTIONAL COMPAT ACTIVE | External E5-C blocker; E5-D takeover locked; not core-v1.0 critical path |
| F Homepage | CORE PASS / COMPAT ACTIVE | Native Homepage on main; external F8 compatibility defect non-blocking under D-016 |
| G Core v1.0 closure | ACTIVE | Exact next = G0 |
| U Control Center | DEFERRED | Not a v1.0 blocker |

Canonical base: `main@815ffcd43653930d1f302a055961ac24cf5ae843`.

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

Production feature/bugfix behavior follows RED -> minimal GREEN -> regression.

## 3. QA layer map

| Layer | Evidence |
| --- | --- |
| L0 | source/state/ownership/current base |
| L1 | lint/static/naming/private-storage/dependency scans |
| L2 | RED/GREEN contract/unit/template-render behavior |
| L3 | real WordPress runtime |
| L4 | browser/responsive/keyboard/focus/a11y/console |
| L5 | provider/theme-switch/integration boundary |
| L6 | full regression/package/SHA/unzip-reverify/rollback/release evidence |

No layer may be inferred from another.

## 4. Core v1.0 sequence

### G0 — Core scope freeze / blocker inventory

**Goal:** define the finite set of remaining Theme-owned work needed by AZT-05 v1.0.

**Allowed:** source review, canonical GitHub tree, existing evidence, static inventory.

**Forbidden:** new product features, Control Center scope, RootProfile/ConvertFlow source changes, private integration workarounds.

**Exit:** a bounded inventory classifying each remaining item as Theme-owned core blocker, already PASS, optional compatibility, or non-v1.0/deferred.

**Next:** G1.

### G1 — Cleanup candidate inventory

**Goal:** locate Theme-local superseded presentation code/assets/references and stale package debris.

**Exit:** each deletion candidate has provenance, canonical destination, use-site evidence and rollback reference; unsafe/unknown candidates remain untouched.

**Next:** G2.

### G2 — Bounded retirement

**Goal:** remove only candidates proven safe to retire.

**Method:** for behavior-affecting removal, create a failing regression or prove existing guard coverage, apply minimal deletion, rerun retained regressions.

**Exit:** no ownership drift, no missing fallback, rollback preserved.

**Next:** G3.

### G3 — Static/security/package hygiene

**Goal:** close cheap deterministic release defects.

**Evidence:** all production PHP lint; forbidden private storage/API scan; naming/prefix scan; touched-path escaping/sanitization review; asset eligibility; package exclude list.

**Exit:** L1 PASS.

**Next:** G4.

### G4 — WordPress-clean runtime

**Goal:** prove core Theme works independently.

**Environment:** WordPress 6.9+ / PHP 8.1+, optional ecosystem plugins off.

**Surfaces:** Home/native front page, Page, Post, Archive, Search, 404; Header/Footer; Theme assets.

**Exit:** HTTP/runtime PASS, no Theme-caused Fatal/Warning/Parse/Uncaught markers.

**Next:** G5.

### G5 — Browser / responsive / a11y / performance

**Goal:** prove presentation quality on the core surfaces.

**Viewports:** desktop, tablet, mobile.

**Checks:** overflow, landmarks, duplicate IDs, keyboard/focus, headings/labels, console/page errors, reduced-motion where relevant, surface-aware asset loading.

**Exit:** L4 PASS.

**Next:** G6.

### G6 — Update / theme-switch / rollback

**Goal:** prove lifecycle safety.

**Checks:** in-place update continuity for WordPress-native content and Theme-owned settings; theme switch does not delete external domain data; prior release/candidate rollback path works.

**Exit:** lifecycle/rollback PASS.

**Next:** G7.

### G7 — Full regression and deterministic package

**Goal:** create the exact v1.0 release candidate bytes.

**Checks:** retained contract/regression suite; PHP lint; deterministic rebuild twice; package file list; SHA-256; unzip/exact-byte reverify; activation smoke from extracted package.

**Exit:** L6 technical candidate PASS.

**Next:** G8.

### G8 — v1.0 closure

**Goal:** promote only the final verified candidate metadata and evidence.

**Actions:** update release metadata together; rerun version/package checks; record package hash, rollback reference and source/evidence changes actually required.

**Exit:** v1.0 release candidate complete.

**Hard gate / Next:** explicit owner approval for release tag/deploy.

## 5. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility remains BLOCKED because provider output introduces a nested document-level `<main>`. Track the provider fix; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain Woo presentation and public token coexistence evidence. WooCommerce owns product/price/stock/variation/cart/checkout/order/account truth. Final G regression may exercise Woo on/off as compatibility evidence, but core Theme still has to work with Woo absent.

## 6. Deferred work

Control Center U0-U6 and any page-builder-like capability are outside the v1.0 critical path. They require a separately accepted source decision before becoming release requirements.

## 7. Recovery checkpoint

At every G slice, commit bounded changes on a work branch and retain a clear parent SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior.

## 8. Exact next

**G0 — produce the finite Theme-owned core-v1.0 blocker inventory from canonical `main@815ffcd43653930d1f302a055961ac24cf5ae843`.**
