# RQA acceptance: owner-boundary live checkpoint (2026-10-23)

**Site:** https://remquocanh.vn/  
**Scope:** live WordPress/WooCommerce/Rank Math editorial fixes and Theme pilot boundary diagnostics.  
**Status:** dated evidence only; not a change to Theme architecture, a new public integration contract, or proof of acceptance.

## Baseline and sources

- Canonical Theme repository: `truongdinhnamaz/aznet-theme`.
- Live RQA AZnet Theme: `1.3.92`; latest published GitHub Release: `v1.3.92`; default branch has newer Theme metadata `1.3.96` but no matching published release at this checkpoint.
- Hosting backup availability was confirmed by the operator in conversation; independent restore testing was **not** performed.
- Historic owner-approved project Post `#999` and Category `#80` are documented in `docs/evidence/CURTAIN01_PROJECT_SHOWCASE_PRODUCTION_20260923.md`. No customer identity, site location, project scale or certified product specification is inferred from that post.
- Source authority and isolation: `docs/source/AZT-00-governance.md` / AZT-02 (if paths differ, consult SOURCE_MANIFEST). Theme is a presentation consumer, not the WordPress/WooCommerce/ConvertFlow/Rank Math data owner.

## PASS — source-owner edits and fresh readback

1. WooCommerce product `#604` (`/rem-cau-vong-chong-chay/`): added a short description using the published WooCommerce product-update ability; public `.woocommerce-product-details__short-description` displays the new copy. It **does not** claim fire certification without product documentation. Price remains blank; categories and media unchanged.
2. Rank Math product description overrides were set via the plugin's `rankmath/v1/updateMeta` endpoint for products `#611`, `#610`, `#607`, `#606`, `#605`, `#604`, `#603`, and `#70`. Fresh public-head checks on each URL confirm distinct plain-text descriptions, lengths **128–136** characters, correct self-canonicals and `index, follow`. Existing separately authored descriptions for products `#608` and `#522` were not overwritten.
3. WordPress page `#41` (`/cong-trinh/`) now links exactly once to the previously approved, already published post `#999` (`/hoan-thien-rem-cuon-cong-trinh-y-te/`). The page's statement that detailed project dossiers are not yet ready remains in place. Updated through WPVibe's match-once post-content edit; verified from the public DOM.
4. A prior bounded pass updated product `#70` WooCommerce long/short descriptions and product `#522` long description through source-owner APIs. Their price, variants and images were not deliberately changed. **Do not mislabel WooCommerce long-description persistence as proof of front-end rendering**, because this pilot's single-product presentation does not currently show these long descriptions.
5. Earlier work cleaned three internal-editorial phrases from WordPress posts `#854`, `#850`, and `#847` and verified public HTML. No rework performed in this slice.
6. WordPress primary menu `#17`, under parent item `#232`, currently contains live taxonomy links for `Rèm cuốn` (term `#33`, menu item `#1129`) and `Rèm cầu vồng` (term `#27`, menu item `#1130`). Both were verified in desktop and mobile navigation HTML. The attempted `Rèm vải` link was removed when its archive was confirmed empty.
7. Rank Math term titles and descriptions for terms `#16`, `#27`, `#33`, and descriptions for WP Pages `#41` and `#130` were updated in a prior bounded pass. Correct metadata rendering on terms `#27/#33` and Pages `#41/#130` was confirmed; **term `#16` is not an SEO PASS** (see blocker below).

## BLOCKED — source-level WooCommerce taxonomy anomaly

- Native term `product_cat#16` (`Rèm vải`, slug `rem-vai`) exists in WP terms and term_taxonomy tables, with term-taxonomy ID `16` and stored count `3`. Term relationships to published product Posts `#608`, `#610`, `#611` were observed from a read-only WordPress database query.
- Direct WordPress REST lookup `/wp/v2/product_cat/16` succeeds and the WooCommerce single-product API reports these product-category relationships.
- In contrast, **all source-owner collection/query surfaces exclude the term**: `/wp/v2/product_cat?include=16&hide_empty=false` yields empty; `/wc/v3/products/categories?include=16` yields empty; `/wc/v3/products?category=16` yields zero; the public `/rem-vai/` archive has no products, and its `robots` meta is `noindex, nofollow`.
- A same-value term save through the official WP REST controller and WooCommerce's `clear_transients` tool did not fix it.
- In an earlier bounded attempt, assigning `Rèm vải` via WooCommerce REST and WP-CLI emulation unexpectedly selected `Rèm Cầu Vồng` instead; original product `#70` categories `#34/#29` were restored and verified. **Do not repeat a term assignment until term resolution has been diagnosed.**
- **Do not** inject a Theme-side SQL/term heuristic, create a duplicate domain term, override authoritative WooCommerce queries, or claim indexing PASS. The source-owner-side term query/filter/root cause remains UNKNOWN.

## Other BLOCKED / UNKNOWN

- ConvertFlow product `#70` still renders placeholder FAQ, project labels A/B/C, and highlights; `#522` FAQ mentions ANDREA; `#605` specification notes require confirmation. Only public plugin API capabilities may be used; no private meta/storage rewriting or Theme-owned replacement domain store.
- Business-approved hotline, offer/pricing policy, cart-versus-quote decision, verified project portfolios and qualified author attribution remain owner-provided information. Previous checks found conflicting hotline numbers. Do not invent business facts.
- One mobile Lighthouse lab audit of the `Rèm cuốn` archive reported CLS `0.692`. Root shifting element and repeatability are unverified, therefore no Theme CSS fix or L4 performance PASS is claimed.
- Independently verified recovery restore test, complete contract/acceptance comparison, provider L5 integration and L6 release/acceptance remain UNKNOWN or BLOCKED.

## Recovery and safety

- The hosting backup was reported present, not independently restore-tested.
- WordPress native revisions are available for content Pages/Posts; page `#41` link addition is reversible by a match-once editor change.
- WooCommerce product `#604` previously had an empty short description and can be reverted through the same public product owner ability.
- Rank Math product description overrides can be removed through Rank Math's public write path if rollback becomes necessary. This plan is **not** a tested rollback.
- Menu items `#1129/#1130` can be removed individually using WordPress native menu tools.
- No Theme production file was modified, no product price changed, no private domain storage written, and no GitHub `main` merge or Theme release was performed in this checkpoint.

## QA and exact next

- **L0** PASS: current GitHub release and live Theme version readback, ownership mapped.
- **L3** PASS for listed WordPress source-owner saves; direct term lookup anomaly separately BLOCKED.
- **L4/HTML** PASS only for the eight SEO head checks, `#604` public short description, `#41` public link and current navigation DOM. No whole-site visual or cross-provider claim.
- **L5/L6** NOT CLAIMED.
- **EXACT NEXT:** isolate which WordPress/WooCommerce query filter removes existing `product_cat#16` from collection queries, using read-only source-owner diagnostics. Repair only through an authoritative supported path; restore `Rèm vải` menu link and verify archive/products/robots after that fix. If owner-side repair remains blocked, continue with independent Theme-owned evidence work rather than implementing a heuristic.
