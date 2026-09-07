# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.14  
**Status:** Working Execution Map / derived  
**Date:** 07/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

## 1. Current state

| Workstream | State | Boundary |
| --- | --- | --- |
| 0 Source Freeze & Provenance | PASS | 817/817 classification + accepted source-bound evidence |
| A Theme Foundation | PASS | WordPress-clean hybrid PHP + `theme.json` |
| B Semantic Tokens | PASS / retained | `--aznet-theme-*` + `theme.json`; R1 may extend semantics without silently repurposing public tokens |
| C Header/Footer | PASS / retained | v1.0 Theme presentation owner; R3 is additive v1.1 preset system |
| D Generic Templates | PASS | Page/Post/Archive/Search/404 through L6 |
| W Woo presentation | PASS / retained | Woo owns commerce truth; R4 is presentation-only v1.1 upgrade |
| E RootProfile Profile/Contact | OPTIONAL COMPAT ACTIVE | External E5-C blocker; E5-D takeover locked; not core-v1.1 critical path |
| F Homepage | CORE PASS / COMPAT ACTIVE | Native Homepage on main; external F8 compatibility defect remains non-blocking under D-016 |
| G Core v1.0 closure | TECHNICAL PASS | G0-G8 merged; publication/tag is separate live state |
| R0 v1.1 Source Reconciliation | ACTIVE | Source-only; exact next is source PR approval/merge |
| R1 Design System 2.0 | PLANNED | Theme-owned settings/tokens/visual presets only |
| R2 Native Pattern Library | PLANNED | Portable WordPress-native content, no builder store |
| R3 Header System 2.0 | PLANNED | Bounded primitives/presets, no Header Builder |
| R4 WooCommerce Presentation 2.0 | PLANNED | Public Woo output -> Theme presentation only |
| R5 Control Center + System Health | PLANNED | Single Theme settings schema + read-only public diagnostics |
| R6 Performance + Release 2.0 | PLANNED | Asset evidence, exact-main CI, deterministic v1.x release closure |
| U Historical Control Center | REFERENCE ONLY | Do not wholesale-merge; R5 owns the accepted v1.1 bounded admin outcome |

Canonical implementation baseline at R0 start: `main@f8e1a95c903c3f246528368ae9878eba780539ff`.

V1.0 publication state at this checkpoint: `PUBLICATION_PENDING` because no Git tag and no GitHub Release exist. This does not reopen G technical PASS.

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

## 4. v1.1 dependency order

`R0 -> R1 -> {R2, R3, R4} -> R5 -> R6`

- R0 is source-only and must merge before v1.1 production code.
- R1 creates the stable settings/token interfaces consumed by later streams.
- R2/R3/R4 may run independently after R1 if they consume only stable R1 interfaces.
- R5 waits for final R3/R4 setting keys so it cannot invent a second schema.
- R6 is the final integration/performance/release closure after production streams are merged.

## 5. R0 — Source reconciliation

**Goal:** reconcile canonical source from stale alpha/G0 facts to the actual v1.0 merged state and ratify the approved v1.1 Native Product System architecture.

**Owner:** AZnet Theme source documents by subject.

**Allowed:** `docs/source/**`, `docs/evidence/R0_*`, live GitHub implementation/publication evidence.

**Forbidden:** production PHP/CSS/JS, changing AZT-05 without new constitutional approval, provider code, takeover.

**Exit:**

- AZT-01 v0.4;
- AZT-02 v0.5;
- AZT-03 v0.18;
- AZT-04 v0.22;
- AZT-EXEC-MAP v0.14;
- SOURCE_MANIFEST synchronized;
- AZT-05 byte-unchanged;
- source-only diff verified;
- explicit owner merge approval.

**Rollback:** source branch/PR can be closed without changing canonical `main`; after merge, revert the bounded source merge commit if a contradiction is found before R1.

**Next:** R1.

## 6. R1 — Design System 2.0

**Goal:** create the stable Theme-owned presentation settings/token foundation for v1.1.

**Produces:**

- one versioned `aznet_theme_settings` Theme Mod schema;
- expanded semantic token vocabulary retaining existing public aliases;
- Default/Editorial/Commerce visual outcomes;
- WordPress-6.9 evidence choosing native style variations or Theme-owned visual-preset mapping;
- editor/frontend parity.

**Forbidden:** FSE takeover, proprietary content schema, provider/domain state in Theme settings.

**Exit:** L1-L4 PASS on final R1 bytes.

**Next:** R2/R3/R4 may start independently.

## 7. R2 — Native Pattern Library

**Goal:** speed up authoring with curated portable block compositions instead of a proprietary builder.

**Launch candidate set:** 18 approved candidate patterns. A candidate that fails the quality gate is removed rather than replaced with filler.

**Boundaries:**

- core/Woo Blocks first;
- no CPT/options/provider state/proprietary serialized content;
- Woo-dependent patterns register only against exact public Woo block capability;
- example content is not authoritative business/trust data;
- saved content remains WordPress block content after theme switch.

**Exit:** L1-L4 PASS with exact shipped count recorded.

**Next:** continue parallel R3/R4 or wait for R5 dependency closure.

## 8. R3 — Header System 2.0

**Goal:** provide four high-quality bounded Header presentations without a Header Builder.

**Presets:** Standard, Compact, Commerce, Overlay.

**Architecture:** public WordPress/Woo data -> primitive renderers -> preset composer -> surface.

**Checks:** long labels, 1440/1024/390/320 viewports, keyboard, focus, mobile JS/no-JS fallback, sticky/reduced motion, overlay safe fallback.

**Forbidden:** slug/title/Page-ID/URL authoritative heuristic, mega-menu application engine, Woo session/private state reads.

**Exit:** L1-L4 PASS.

**Next:** R5 once R4 setting interface is also stable.

## 9. R4 — WooCommerce Presentation 2.0

**Goal:** improve commercial UX while WooCommerce remains the sole commerce truth owner.

**Surfaces:** product cards/catalog, single product, cart, checkout, account, supported public Woo Blocks.

**Allowed:** public Woo APIs/hooks/Blocks, Theme settings/classes, scoped CSS, bounded progressive enhancement.

**Forbidden:** product meta/session/order table reads, wishlist/compare, AJAX filter/search, swatch domain engine, quick-view business engine, custom checkout engine.

**Template override rule:** hooks/CSS/Blocks-first. Add `woocommerce/` override only after a concrete failing test proves public mechanisms cannot satisfy the presentation requirement and source review explicitly approves the exception.

**Exit:** L1-L4 PASS with Woo present plus clean-WP/Woo-absent regression.

**Next:** R5 once R3/R4 settings are final.

## 10. R5 — Control Center + System Health

**Goal:** provide a small presentation settings console and diagnostics surface.

**Storage:** only normalized `aznet_theme_settings` Theme Mod.

**Mutations:** require `edit_theme_options`, nonce and allow-list normalization. Reset/import/export affect Theme presentation settings only.

**Native ownership:** Logo/Menu/Patterns stay WordPress-native; Control Center links to native controls rather than cloning them.

**System Health:** read-only; WordPress environment + Theme settings + public provider capability checks. Unknown external capability is reported as unknown, never privately inferred.

**Import bound:** JSON upload <= 64 KiB; product/schema markers validated; unknown keys dropped; no secrets/provider data.

**Exit:** L1-L4 + update/theme-switch continuity PASS.

**Next:** R6.

## 11. R6 — Performance + Release 2.0

**Goal:** close v1.1 with measured performance/asset evidence and reusable release infrastructure.

**Sequence:**

1. capture route/asset/performance baseline;
2. harden Theme-owned surface-aware asset loading;
3. evaluate ConvertFlow projection loading only through a documented public capability, otherwise record `BLOCKED_EXTERNAL_CONTRACT` and retain safe behavior;
4. add reusable PR CI and exact-main post-merge verification;
5. add deterministic v1.x release-candidate workflow/package builder;
6. retire old milestone-specific workflows only after replacement coverage + fresh evidence;
7. run final exact-main R1-R6 regression before metadata promotion;
8. RED -> GREEN exact `1.1.0` metadata promotion;
9. rerun final exact-main/package verification and stop at explicit release/tag gate.

**Performance rule:** no improvement claim without measured before/after evidence.

**Release rule:** PR-head PASS is not exact-main PASS. Final promotion requires fresh checks on the exact canonical main bytes that will be packaged/tagged.

**Exit:** fresh L0-L6 technical candidate + deterministic package SHA/file count/artifact + rollback reference.

**Hard gate / Next:** explicit owner approval for `v1.1.0` tag/GitHub Release; deployment remains separate unless explicitly included.

## 12. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility remains BLOCKED because provider output introduces a nested document-level `<main>`. Track the provider fix; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain W1-W9 ownership/fail-soft evidence. R4 may deepen presentation while WooCommerce continues to own product/price/stock/variation/cart/checkout/order/account truth.

## 13. Recovery checkpoint

At every R slice, commit bounded changes on a work/feature branch and retain a clear base SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior. Do not rerun retained PASS without an invalidation unless the active exit gate explicitly requires fresh final-candidate evidence.

## 14. Exact next

**R0 — obtain explicit owner approval to merge the source-only reconciliation PR. After merge, create a fresh R1 implementation branch from the latest canonical `main` and execute Design System 2.0.**
