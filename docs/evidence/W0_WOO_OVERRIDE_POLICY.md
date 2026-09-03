# W0 Woo Override Policy — Decision Evidence

Date: 2026-09-03

## PASS scope

**W0 STATUS: PASS — governance/source decision only (L0).**

O-005 is CLOSED by owner approval. The accepted policy is hooks/CSS/Blocks-first; a WooCommerce template override is an exception and must record reason, upstream template/version, scope, regression coverage and rollback.

v1.0 Woo presentation scope is:
- Single Product;
- Shop / Product Archive / Category / Tag;
- Cart;
- Checkout;
- My Account.

WooCommerce remains authoritative for product/price/stock/variation/cart/checkout/order/account state. ConvertFlow remains authoritative for Product Journey/Filter/Fit/Fast Conversion. AZnet Theme owns presentation only.

D-014 permits Workstream W to progress in parallel while E5-C is blocked by an external RootProfile contract. W progress cannot be used to claim E/F, integration, release or takeover PASS.

## Source artifact

- AZT-04 candidate: `/mnt/data/04_Roadmap_QA_va_Decision_Log_v0.15.docx`
- SHA-256: `f6bb91fd79049d65832761161c297fc574a80225bbe7720cbe732c260ae3008c`
- Render QA: 16/16 pages visually inspected; no clipping/overlap/broken tables observed.

## Repository baseline

- Canonical repo: `truongdinhnamaz/aznet-theme`
- Base `main`: `0249dd9b0403e6a8984c3a1d201cabf0947c4242`
- Work branch: `work/w1-woo-capability-assets`

## BLOCKED / UNKNOWN

- E5-C remains BLOCKED on the external RootProfile current-surface contract.
- W1+ implementation was not claimed by this W0 decision checkpoint.
- Woo runtime/browser/integration/release remains UNKNOWN until the corresponding W gates run.

## NEXT

W1 — Woo capability + surface-aware generic asset boundary.
