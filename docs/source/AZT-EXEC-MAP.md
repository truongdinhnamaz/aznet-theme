# AZnet Theme Implementation Slice Map

**Document ID:** AZT-EXEC-MAP-01  
**Version:** v0.18  
**Status:** Working Execution Map / derived  
**Date:** 08/09/2026

This map is derived from AZT-05/00/01/02/03/04. It cannot change product ownership, domain semantics, public contracts or the release constitution by itself.

## 1. Current state

| Workstream | State | Boundary |
| --- | --- | --- |
| 0 Source Freeze & Provenance | PASS | 817/817 classification + accepted source-bound evidence |
| A Theme Foundation | PASS | WordPress-clean hybrid PHP + `theme.json` |
| B Semantic Tokens | PASS / retained | `--aznet-theme-*` + `theme.json`; R1 extended semantics without silently repurposing public tokens |
| C Header/Footer | PASS / retained | v1.0 Theme presentation owner; R3 adds the bounded v1.1 preset system |
| D Generic Templates | PASS | Page/Post/Archive/Search/404 through L6 |
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
| R6 Performance + Release 2.0 | READY / EXACT NEXT | Measured assets/performance, exact-main CI, deterministic `aznet-theme/` v1.1 candidate, metadata promotion behind owner gate |
| U Historical Control Center | REFERENCE ONLY | Do not wholesale-merge; R5 owns the accepted bounded admin outcome |

Canonical implementation baseline after R5 and the subsequent no-op history correction: `main@484e896cace06d684d29a3f93e3cce85e84a9f80`, production tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`.

R4 final head `bfd383da4fd1e7029d8c901ad807e9ca3c03d5ab` and merge `0ddc6c799391d57db134f04449effdf511d41d1f` share tree `e66c6ebbcc1de7d695ffa67172928d0f0580e443`. R4 verified functional/test head `675a4f0a07b7c6c4796ad359a4b13f9c2d94f74a` completed 16/16 workflows successfully; detailed evidence is `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

R5 final evidence head `fed7429853ce5f6a209a0bf763a4c72f5ed1d376` and merge `6c69def2ff4ac7adb1221de09aec057b63b1adf6` share tree `7798435b12d166f74d91f55ef10fbaf385bcb0ef`. R5 verified functional/test head `d85af1210e79be2ceb48cef23c7d1d3dbb3ebc8c` completed 18/18 workflows successfully. Clean-WP and Woo-present authenticated Control Center matrices, a11y, in-place update continuity, theme-switch continuity and retained R1-R4/core regressions are green. Detailed evidence is `docs/evidence/R5_CONTROL_CENTER_L4.md`.

Two no-op commits after the R5 merge added and immediately removed a temporary source-state sentinel. Direct compare from the R5 merge to current `main` returns no changed files; current production tree is still exactly the R5 tree.

V1.0 publication state remains `PUBLICATION_PENDING`: a fresh 08/09/2026 GitHub release check returns no GitHub Release and no tag evidence has been established. This does not reopen G technical PASS. Theme metadata remains `1.0.0` until R6 reaches the explicit metadata-promotion gate.

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

- R0 source reconciliation is complete.
- R1 stable settings/token interfaces are merged on canonical `main`.
- R2 Native Pattern Library is merged and complete.
- R3 Header System 2.0 is merged and complete.
- R4 WooCommerce Presentation 2.0 is merged and complete; its final settings keys are stable.
- R5 Control Center + System Health is merged and complete using the single existing Theme settings schema.
- R6 is now the only remaining core-v1.1 implementation/release-closure stream.

## 5. R0 — Source reconciliation

**Goal:** reconcile canonical source from stale alpha/G0 facts to the actual v1.0 merged state and ratify the approved v1.1 Native Product System architecture.

**Owner:** AZnet Theme source documents by subject.

**Allowed:** `docs/source/**`, `docs/evidence/R0_*`, live GitHub implementation/publication evidence.

**Forbidden:** production PHP/CSS/JS, changing AZT-05 without new constitutional approval, provider code, takeover.

**Exit:** PASS.

- AZT-01 v0.4;
- AZT-02 v0.5;
- AZT-03 v0.18 at R0 closure;
- AZT-04 v0.22 at R0 closure;
- AZT-EXEC-MAP v0.14 at R0 closure;
- SOURCE_MANIFEST synchronized;
- AZT-05 byte-unchanged;
- source-only diff verified;
- explicit owner merge approval received;
- PR #36 merged.

**Rollback:** revert the bounded R0 source merge if a source contradiction is discovered; no domain/product source from other products is copied into Theme.

**Next:** R1 — completed.

## 6. R1 — Design System 2.0

**Goal:** create the stable Theme-owned presentation settings/token foundation for v1.1.

**Produces:**

- one versioned `aznet_theme_settings` Theme Mod schema;
- expanded semantic token vocabulary retaining existing public aliases;
- Default/Editorial/Commerce visual outcomes;
- WordPress-6.9 evidence selecting Theme-owned visual-preset mapping without FSE takeover;
- editor/frontend parity.

**Forbidden:** FSE takeover, proprietary content schema, provider/domain state in Theme settings.

**Exit:** PASS — L1-L4 on R1 bytes; PR #37 merged to canonical `main`.

**Next:** R2/R3/R4 — completed.

## 7. R2 — Native Pattern Library

**Goal:** speed up authoring with curated portable block compositions instead of a proprietary builder.

**Shipped set:** 18 patterns total — 16 WordPress-native/core patterns plus 2 Woo-dependent patterns registered only when exact public Woo block types are present.

**Boundaries:**

- core/Woo Blocks first;
- no CPT/options/provider state/proprietary serialized content;
- Woo-dependent patterns register only against exact public Woo block capability;
- example content is not authoritative business/trust data;
- saved content remains WordPress block content after theme switch.

**Exit:** PASS — L1-L4, clean-WP/Woo-present browser/editor matrices, 18-pattern exact count and byte-identical theme-switch content portability proven; PR #39 merged.

**Next:** R3/R4 — completed.

## 8. R3 — Header System 2.0

**Goal:** provide four high-quality bounded Header presentations without a Header Builder.

**Presets:** Standard, Compact, Commerce, Overlay.

**Architecture:** public WordPress/Woo data -> primitive renderers -> preset composer -> surface.

**Checks:** long labels, 1440/1024/390/320 viewports, keyboard, focus, mobile JS/no-JS fallback, sticky/reduced motion, overlay safe fallback.

**Forbidden:** slug/title/Page-ID/URL authoritative heuristic, mega-menu application engine, Woo session/private state reads.

**Exit:** PASS — four presets, three sticky modes, strict normalized settings, shared primitives/composer, mobile progressive enhancement and sticky-compact enhancement delivered; PR #41 merged with exact tree equivalence.

**Next:** R4 — completed.

## 9. R4 — WooCommerce Presentation 2.0

**Goal:** improve commercial UX while WooCommerce remains the sole commerce truth owner.

**Surfaces:** product cards/catalog, single product, cart, checkout, account, supported public Woo Blocks.

**Allowed:** public Woo APIs/hooks/Blocks, Theme settings/classes, scoped CSS, bounded progressive enhancement.

**Forbidden:** product meta/session/order table reads, wishlist/compare, AJAX filter/search, swatch domain engine, quick-view business engine, custom checkout engine.

**Template override rule:** hooks/CSS/Blocks-first. Add `woocommerce/` override only after a concrete failing test proves public mechanisms cannot satisfy the presentation requirement and source review explicitly approves the exception.

**Exit:** PASS — clean-WP absence regression and Woo-present L1-L4 matrices complete; three catalog presets, three card densities and three product presets delivered without template override or commerce state duplication; PR #43 merged. Detailed evidence: `docs/evidence/R4_WOOCOMMERCE_PRESENTATION_L4.md`.

**Next:** R5 — completed.

## 10. R5 — Control Center + System Health

**Goal:** provide a small presentation settings console and diagnostics surface.

**Storage:** only normalized `aznet_theme_settings` Theme Mod.

**Mutations:** require `edit_theme_options`, nonce and allow-list normalization. Reset/import/export affect Theme presentation settings only.

**Native ownership:** Logo/Menu/Patterns stay WordPress-native; Control Center links to native controls rather than cloning them.

**System Health:** read-only; WordPress environment + Theme settings + public provider capability checks. Unknown external capability is reported as unknown, never privately inferred.

**Import bound:** JSON upload <= 64 KiB; product/schema markers validated; unknown keys dropped; no secrets/provider data.

**Exit:** PASS — L1-L4 plus in-place update/theme-switch continuity. Exact functional/test head completed 18/18 workflows successfully; PR #44 merged. Detailed evidence: `docs/evidence/R5_CONTROL_CENTER_L4.md`.

**Next:** R6.

## 11. R6 — Performance + Release 2.0

**Goal:** close v1.1 with measured performance/asset evidence and reusable release infrastructure.

**Sequence:**

1. capture route/asset/performance baseline from latest canonical `main`;
2. harden Theme-owned surface-aware asset loading only where measured evidence justifies change;
3. evaluate ConvertFlow projection loading only through a documented public capability, otherwise record `BLOCKED_EXTERNAL_CONTRACT` and retain safe behavior;
4. add reusable PR CI and exact-main post-merge verification;
5. add deterministic v1.x release-candidate workflow/package builder with stable top-level `aznet-theme/` identity;
6. retire old milestone-specific workflows only after replacement coverage + fresh evidence;
7. run final exact-main R1-R6 regression before metadata promotion;
8. at the explicit source-defined owner gate, RED -> GREEN exact `1.1.0` metadata promotion in both `style.css` and `AZNET_THEME_VERSION`;
9. rerun final exact-main/package verification and stop at explicit tag/GitHub Release gate.

**Performance rule:** no improvement claim without measured before/after evidence.

**Package identity rule:** the installable ZIP must contain exactly one canonical top-level `aznet-theme/` Theme directory and its internal WordPress Theme Version must match the promoted release metadata. Package filenames never substitute for Theme metadata.

**Release rule:** PR-head PASS is not exact-main PASS. Final promotion requires fresh checks on the exact canonical main bytes that will be packaged/tagged.

**Exit:** fresh L0-L6 technical candidate + deterministic package SHA/file count/artifact + rollback reference.

**Hard gate / Next:** explicit owner approval for metadata/version promotion, then separate explicit owner approval for `v1.1.0` tag/GitHub Release; deployment remains separate unless explicitly included.

## 12. Optional compatibility tracks

### E — RootProfile

Keep E0-E4/E5-B retained evidence. E5-C remains BLOCKED on the external current-surface publisher; E5-D remains approval-gated. Do not implement RootProfile source in Theme. Resume certification when the external contract is available.

### F — ConvertFlow integrated Homepage

Native Homepage is part of the core. F6/F7 actual-package integration evidence is retained. F8 integrated compatibility remains BLOCKED because provider output introduces a nested document-level `<main>`. Track the provider fix; do not alter Theme ownership or use heuristic/private workarounds.

### W — WooCommerce

Retain W1-W9 ownership/fail-soft evidence plus R4 presentation evidence. WooCommerce continues to own product/price/stock/variation/cart/checkout/order/account truth.

## 13. Recovery checkpoint

At every R slice, commit bounded changes on a work/feature branch and retain a clear base SHA. If a deeper gate fails, reduce it to the shallowest reproducible regression before changing production behavior. Do not rerun retained PASS without an invalidation unless the active exit gate explicitly requires fresh final-candidate evidence.

## 14. Exact next

**R6 — create an isolated implementation branch from the latest canonical `main` and begin with a measured route/asset/performance baseline plus exact-main release-infrastructure review. Stop before metadata/version promotion unless that explicit owner gate is approved.**
