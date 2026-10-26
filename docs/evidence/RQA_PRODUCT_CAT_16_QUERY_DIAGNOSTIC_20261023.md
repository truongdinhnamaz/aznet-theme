# RQA product_cat #16 — isolated collection-query diagnostic

**Observation date:** 2026-10-23 (Asia/Bangkok).  
**Affected site:** https://remquocanh.vn/  
**Scope:** WordPress/WooCommerce source-owner diagnostic; **no AZnet Theme code or architecture change**.

## Controlled reproduction — baseline

- WordPress `7.1.3`, PHP `8.4.26`, active AZnet Theme `1.3.92`; WooCommerce and ChoiceGuide plugins active.
- Direct read from WordPress SQL (SELECT only) confirms `terms.term_id=16`, `slug=rem-vai`, `term_group=0`, `term_taxonomy.taxonomy=product_cat`, `term_taxonomy_id=16`, `parent=0`, and stored term count `3`.
- Read-only SQL join over native `term_relationships` shows `product_cat#16` linked to **published** WooCommerce product Posts `#608` (Rèm voan), `#610` (Rèm Vải 2 Lớp), and `#611` (Rèm vải đơn sắc).
- Direct API `GET /wp-json/wp/v2/product_cat/16` and `GET /wp-json/wc/v3/products/categories/16`: succeed.
- Query by slug `GET /wp-json/wp/v2/product_cat?slug=rem-vai` and `GET /wp-json/wc/v3/products/categories?slug=rem-vai`: return exactly term `#16`.
- Query by id `GET /wp-json/wp/v2/product_cat?include=16&hide_empty=false` and `GET /wp-json/wc/v3/products/categories?include=16`: return zero terms.
- Combined query `GET /wp-json/wp/v2/product_cat?slug=rem-vai&include=16` **does** return term `#16`, while `GET /wp-json/wp/v2/product_cat?slug=rem-vai&exclude=16` returns none. This strengthens the suspicion of query-argument- or hook-dependent suppression; **the responsible filter is not identified**.
- Broad taxonomy query `GET /wp-json/wp/v2/product_cat?per_page=100&hide_empty=false` returns 16 terms (IDs 18,27-34,58-64) and omits `#16`. SQL source has 17 such terms.
- Native WP-CLI-emulated `term list product_cat` omits `#16`; term lookup by slug still succeeds.
- Product collection `GET /wp-json/wc/v3/products?category=16&status=publish` and `GET /wp-json/wc/store/v1/products?category=16` return no products. Native public archive https://remquocanh.vn/rem-vai/ renders "Không tìm thấy sản phẩm nào..." and `noindex, nofollow`.
- Control categories `#33` (Rèm cuốn) and `#27` (Rèm cầu vồng) return three products each via the same WooCommerce REST query.
- All three Rèm vải products show `status=publish`, `catalog_visibility=visible` and `stock_status=instock` on native WooCommerce single-product API.
- `default_product_cat` option currently resolves to `27`, not `16`; `woocommerce_hide_out_of_stock_items` is `no`. This is background only, not cause evidence.

## Supported repair and isolation attempts (do not repeat blindly)

1. Earlier `PUT /wp-json/wp/v2/product_cat/16` wrote the same term name; source API acknowledged but collection queries stayed empty.
2. Earlier WooCommerce `clear_transients` succeeded; collection query remained empty.
3. `wp cache type` reported in-request/default object cache (`drop_in=false`). `wp cache flush` succeeded; afterward the query still omitted `#16`.
4. Official WooCommerce `PUT /wc/v3/system_status/tools/recount_terms` reported `success=true`; subsequent API list/query still omitted `#16`. This tools endpoint is documented by WooCommerce.
5. Read-only inspection of `choiceguide/src/Admin/WooCommerce/ProductCategoryOrderingBridge.php` shows it registers admin ordering and uses public `get_terms`; no general `get_terms`-exclusion hook in the inspected file. `choiceguide/src/Frontend/Filter/CategoryFilterController.php` hooks `pre_get_posts` only for a filtered front-end main query; that is not enough to explain WooCommerce REST taxonomy-list failure. **These source inspections do not rule out other active plugins or callbacks.**
6. WPVibe theme-source searches for `pre_get_terms`, `get_terms_args`, `terms_clauses`, `list_terms_exclusions`, `terms_pre_query`, `woocommerce_product_query` reported zero hits in the searched theme files; connector search scope may use an existing draft, so this is not a definitive byte-exact active Theme proof.
7. Previous attempt to assign product `#70` to term `#16` via the owner API / emulated WP-CLI unexpectedly assigned term `#27`. Product `#70` was reverted to original categories `#34` and `#29` and verified. Avoid retrying until ID resolution is understood.

## Outcome and boundaries

**PASS:** existence, source-owner disagreement and two supported maintenance tools evaluated with fresh repeatable readbacks.  
**BLOCKED:** underlying runtime query filter/SQL-clause cause remains unknown; no safe verified production fix yet.  
**UNCHANGED:** no Theme bytes, WooCommerce pricing, plugin-private data, or customer project facts were modified in this diagnostic. Existing Rèm vải menu link remains unpublished.  
**GATES:** L1/L3 source facts and API-level reproduction only; no new L4 visual, L5 integration, or L6 completion claim.

## Single exact next

Acquire a **read-only PHP hook/query trace in an isolated WordPress staging/runtime**, recording active callbacks on `pre_get_terms`, `get_terms_args`, `list_terms_exclusions`, `terms_clauses`, `get_terms`, and the generated `WP_Term_Query` SQL. Identify the term-ID `#16` exclusion before authorizing a source-owner remediation. Do not use a Theme heuristic, raw SQL mutation, parallel domain store, or switch off a production plugin to guess.

## Public reference

- WooCommerce official term-recount tools: https://woocommerce.com/document/understanding-the-woocommerce-system-status-report/tools/
- WooCommerce product-category REST contract: https://developer.woocommerce.com/docs/apis/rest-api/v2/product-categories
- WordPress get_terms query filters: https://developer.wordpress.org/reference/classes/wp_term_query/get_terms/
