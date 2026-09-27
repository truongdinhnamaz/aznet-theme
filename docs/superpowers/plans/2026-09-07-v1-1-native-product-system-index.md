# AZnet Theme v1.1 Native Product System — Implementation Plan Index

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement each sub-plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Deliver AZnet Theme v1.1 as a WordPress-native product system with stronger authoring, Header, WooCommerce presentation, Control Center, performance and release lifecycle while preserving Theme-only presentation ownership.

**Architecture:** Keep the accepted hybrid PHP + `theme.json` architecture. Decompose v1.1 into source reconciliation plus six independently reviewable implementation plans; each plan must land on its own work branch/PR, retain prior PASS unless concretely invalidated, and stop at any source/ownership/public-contract hard gate.

**Tech Stack:** WordPress 6.9+, PHP 8.1+, PHP template hierarchy, `theme.json`, CSS custom properties, vanilla JavaScript only where progressive enhancement requires it, Gutenberg/core blocks, WooCommerce public APIs/Blocks, WP-CLI, GitHub Actions, Playwright, axe-core.

**Spec:** `docs/superpowers/specs/2026-09-07-v1-1-native-product-system-design.md`

## Global Constraints

- AZT-05 v1.0 remains the highest internal product/release constitution.
- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.`
- WordPress minimum remains 6.9+; PHP minimum remains 8.1+.
- Theme architecture remains hybrid PHP + `theme.json`; no page builder or FSE architecture takeover.
- Naming remains `AZnet\\Theme`, `aznet_theme_`, `AZNET_THEME_`, `aznet-theme-*`, `--aznet-theme-*`.
- WordPress owns Post/Page/Menu/Media/query/editorial lifecycle.
- WooCommerce owns product/price/stock/variation/cart/order/account truth.
- RootProfile/ConvertFlow and other providers retain their domain semantics/state.
- No private option/meta/CPT/table/class reads, authoritative URL/slug/Page-ID heuristics or parallel domain stores.
- Optional providers must fail soft; external provider defects do not authorize Theme workarounds that violate ownership.
- Production feature/bugfix slices use RED -> intended failure -> minimal GREEN -> regression.
- Do not reopen prior PASS without a concrete byte/contract/runtime invalidation.
- Release/deploy/public-contract changes remain explicit approval gates when source requires them.

---

## Delivery order

| Stage | Plan | Depends on | Exit |
| --- | --- | --- | --- |
| R0 | `2026-09-07-v1-1-r0-source-reconciliation.md` | Approved spec | Canonical source accurately records v1.0 state and approved v1.1 roadmap/architecture |
| R1 | `2026-09-07-v1-1-r1-design-system.md` | R0 | Design System 2.0 + Default/Editorial/Commerce visual presets + editor/frontend parity |
| R2 | `2026-09-07-v1-1-r2-pattern-library.md` | R1 | Curated native pattern library with portability/responsive/a11y gates |
| R3 | `2026-09-07-v1-1-r3-header-system.md` | R1 | Standard/Compact/Commerce/Overlay Header presets with accessible progressive enhancement |
| R4 | `2026-09-07-v1-1-r4-woocommerce-presentation.md` | R1; may run after R2/R3 independently | Product-card/catalog/product/cart/checkout/account presentation 2.0 |
| R5 | `2026-09-07-v1-1-r5-control-center-health.md` | R1 + finalized R3/R4 setting keys | Presentation-only Control Center, Quick Setup, safe settings import/export, System Health |
| R6 | `2026-09-07-v1-1-r6-performance-release.md` | R1-R5 | Asset-scope regression, exact-main CI, deterministic v1.x release workflow and final v1.1 closure |

## Parallelism rule

After R1 is merged, R2 and R3 are independent and may be developed in parallel. R4 may also proceed in parallel if it only consumes the stable R1 token/settings interfaces. R5 waits for the setting keys exposed by R3/R4 so it does not invent a second settings schema. R6 is the integration/release closure and therefore runs after production slices are merged.

## Branch / PR rule

Create a fresh branch from the latest valid `main` for each R stage. Do not stack production branches unless a plan explicitly says so. Each PR must identify:

```text
baseline main SHA
scope owner
source decisions consumed
QA layers freshly proven
known BLOCKED/UNKNOWN items
rollback/reference commit
exact next
```

## Completion rule

V1.1 is not complete when individual feature PRs are green. Completion requires R6 fresh exact-main L0-L6 evidence on the final v1.1 candidate bytes, deterministic package identity, rollback reference and owner-approved version/tag/release gate. Optional provider-specific certification remains separate unless source explicitly makes it mandatory.
