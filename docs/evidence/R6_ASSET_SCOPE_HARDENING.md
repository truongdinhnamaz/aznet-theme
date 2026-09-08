# R6 Theme Asset Scope Hardening — 2026-09-08

**Scope:** AZnet Theme v1.1 / R6 Task 2  
**Goal:** preserve and harden the measured Theme-owned surface-aware loading contract without manufacturing an optimization that the data does not justify.

## 1. Result

**PASS — characterization hardening; no production refactor required.**

R6 Task 1 already proved in a real WordPress 6.9 browser matrix that the major Theme asset boundaries behave correctly:

- generic content CSS is absent from Home and present on generic content routes;
- Woo product/archive/cart/checkout/account presentation CSS does not leak onto clean WordPress routes;
- route-specific Woo presentation files load on their corresponding Woo surfaces;
- mobile Header navigation JS and sticky-compact JS are independently gated by their Theme presentation state.

Because the measured behavior is already correct, changing `inc/theme/assets.php` merely to satisfy a refactor quota would add regression risk without producing a measured byte/request reduction. R6 therefore hardens the contract first and leaves production behavior byte-unchanged.

## 2. New exact contract

`tests/offline/r6-asset-scope-contract.php` characterizes 12 exact Theme presentation states:

```text
clean Page / default preset
clean Post / editorial preset
Woo product
Woo archive
Woo cart
Woo checkout
Woo account
Woo Blocks
Header mobile panel
Header sticky-compact
Header sticky non-compact
combined mobile panel + sticky-compact
```

The contract verifies exact style/script handle sets and exact Theme version propagation. It also explicitly rejects any `aznet-theme-woocommerce-*` style leaking into the clean Page/Post cases.

Fresh CI on run `34181962283`, job `101922700370`, completed the new `Verify R6 exact Theme asset-scope contract` step successfully on head `fb2fe7b95dac8e39a854476d776f11aa8a4f29a8`.

## 3. Engineering decision

No production refactor is made in Task 2 because:

1. no failing surface-scope behavior has been reproduced;
2. no before/after request or byte reduction would result from helper extraction alone;
3. the current implementation is already bounded and readable enough for the measured matrix;
4. the new characterization contract now provides stronger regression protection for later release-infrastructure work.

This is intentionally not described as a performance improvement. It is a QA/maintainability hardening step.

## 4. Boundary

The globally loaded Theme-owned ConvertFlow bridge is excluded from a private-detection optimization here. That separate finding is governed by R6 Task 3 / O-008 and is recorded as `BLOCKED_EXTERNAL_CONTRACT` unless a public/versioned Theme-consumable provider capability exists.

## NEXT

Continue with R6 reusable PR/exact-main verification and deterministic release-candidate packaging. Preserve the exact asset-scope contract while doing so.