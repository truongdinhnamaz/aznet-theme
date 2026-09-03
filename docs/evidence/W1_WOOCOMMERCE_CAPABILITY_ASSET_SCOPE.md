# W1 WooCommerce Capability + Surface-Aware Asset Scope — Local Verification

Date: 2026-09-03

## Closure statement

**W1 STATUS: PASS local L0-L2 only.**

**WOO RUNTIME / BROWSER / INTEGRATION / RELEASE: NOT CLAIMED.**

**E5-C ROOTPROFILE RUNTIME PATH: remains BLOCKED on the external `rootprofile/presentation/current-surface/v1` dependency and is unaffected by W1.**

W1 establishes only a Theme-side public Woo capability/surface adapter and narrows existing generic-content asset eligibility on recognized Woo surfaces. It does not add a Woo stylesheet, Woo template override, Product layout, commerce state, or ConvertFlow Product Journey behavior.

## Verified repository state

- Canonical repository: `truongdinhnamaz/aznet-theme`
- Base `main`: `0249dd9b0403e6a8984c3a1d201cabf0947c4242`
- Work branch: `work/w1-woo-capability-assets`
- Verified pre-evidence branch source head: `77be89f4d0ca4503fab414f641362bd379779a3a`
- W0/O-005 decision evidence: `docs/evidence/W0_WOO_OVERRIDE_POLICY.md`
- Approved design: `docs/superpowers/specs/2026-09-03-woo-override-policy-design.md`
- Implementation plan: `docs/superpowers/plans/2026-09-03-w1-woo-capability-surface-assets.md`

## TDD evidence

### Task 1 — capability + normalized surface

RED before production module existed:

- `w1-woocommerce-absent-contract.php` -> `FAIL: WooCommerce integration module does not exist`
- `w1-woocommerce-surface-contract.php` -> `FAIL: WooCommerce integration module does not exist`

Minimal GREEN added `inc/integrations/woocommerce.php` and one bootstrap require. Public capability/surface APIs only:

- `WC()` capability presence;
- `is_product()` -> `product`;
- `is_shop()` / `is_product_taxonomy()` -> `archive`;
- `is_cart()` -> `cart`;
- `is_checkout()` -> `checkout`;
- `is_account_page()` -> `account`;
- absent Woo / no recognized surface -> `null`.

Every conditional call is guarded by `function_exists()`.

### Task 2 — generic asset scope

RED before the asset guard:

`FAIL: shop archive must not load generic-content asset: expected false, got true`

Minimal GREEN makes `should_enqueue_generic_content_assets()` return false when the normalized Woo surface is non-null, then preserves the existing generic Page/Post/Archive/Search/404 expression unchanged for non-Woo requests.

## Fresh closure verification

Command:

```bash
bash scripts/verify-w1.sh
```

Fresh result at closure:

```text
PASS: W1 WooCommerce absent capability contract
PASS: W1 WooCommerce normalized surface contract
PASS: W1 WooCommerce generic asset scope contract
PASS: W1 WooCommerce ownership / no-override static contract
PASS: W1 offline contracts
PASS: E5 current-surface consumer contract
PASS: E5 current-surface payload-to-model adapters
PASS: E5 dormant current-surface dispatcher
PASS: E5-B ownership / no-takeover static contract
PASS: E5-B offline contracts
PASS: production PHP lint 23/23
PASS: retained E5-B verifier
PASS: production PHP lint 23/23
```

Fresh local log: `/mnt/data/aznet-theme-w1/W1_FINAL_VERIFICATION.log`.

## Production scope

Relative to base `main`, W1 production delta is exactly:

- ADD `inc/integrations/woocommerce.php`
- MODIFY `inc/theme/bootstrap.php`
- MODIFY `inc/theme/content-shell.php`

No `woocommerce/` template override directory is introduced. No Woo-specific CSS/JS asset is introduced in W1.

SHA-256:

- `inc/integrations/woocommerce.php` — `380e824f6f5e181b66de11e9271cd1094a31c9cd177e0bf87a5cec7aa2513c65`
- `inc/theme/bootstrap.php` — `213e7552c11abf85d7690f69c2812ce354da6d38f3ccc373365e51166bb15716`
- `inc/theme/content-shell.php` — `e12935bbb6efc52bb8757a9fe129cd85e776a76dc53214fd72782e89cafa7a77`

## Ownership / no-override gate

PASS confirms W1 production paths contain no direct Woo storage/private access patterns used by the gate (`get_option(`, `get_post_meta(`, `$wpdb`, literal `_woocommerce_` storage keys, `Automattic\\WooCommerce\\Internal\\`). Bootstrap retains only the two pre-existing WordPress action registrations and adds no filter/takeover hook.

WooCommerce remains authoritative for product/price/stock/variation/cart/checkout/order/account state. ConvertFlow Product Journey/Filter/Fit/Fast Conversion is untouched. Theme owns only normalized capability detection and presentation asset eligibility.

## Source state after W1

- `03_Current_Baseline_va_Code_Provenance_v0.12.docx` — SHA-256 `790b6a6744bcea75c96f981224b71ab5576dcfbec986aad80c0515687051f9a5` — render QA 11/11 pages PASS.
- `04_Roadmap_QA_va_Decision_Log_v0.16.docx` — SHA-256 `a8d20fd708b151a0b319ff55dad9d9a53974633aac6514a1ccebbce13854fd1b` — render QA 16/16 pages PASS.
- `AZnet_Theme_Implementation_Slice_Map_v0.8.docx` — SHA-256 `5694a3c3b28c7f512e114fec8c9ab506959fee030a499b6d033b47539e9d8df6` — render QA 15/15 pages PASS after correcting the stale W callout and re-rendering.

Source outcome:

- O-005 CLOSED.
- D-014 ACCEPTED: Workstream W may progress independently while E5-C is blocked by an external dependency, without implying E/F/integration/release PASS.
- Workstream W ACTIVE.
- W0 PASS governance L0.
- W1 PASS local L0-L2; not merged to `main` at this checkpoint.

## BLOCKED / UNKNOWN

- WooCommerce real WordPress runtime: UNKNOWN (L3 not run for W1/W2).
- Woo browser/visual/a11y: UNKNOWN (L4 not run).
- Woo/ConvertFlow integration matrix: UNKNOWN (L5 not run).
- W release/package: UNKNOWN (L6 not run).
- E5-C remains externally BLOCKED; W1 does not change that state.

## NEXT

W2 — Single Product presentation Design Packet and approval before any Product shell implementation.
