# RQA product_cat #16 — WooCommerce category repair and publication checkpoint

**Date:** 2026-10-23 (Asia/Bangkok).  
**Pilot:** https://remquocanh.vn/  
**Change type:** WooCommerce/WordPress source-owner metadata and menu operations; documentation-only change inside AZnet Theme GitHub.  
**Supersedes operational status of:** `RQA_PRODUCT_CAT_16_QUERY_DIAGNOSTIC_20261023.md` (historical evidence is retained, not rewritten).

## Goal and baseline (RED)

The existing WordPress `product_cat` term `#16` (`Rèm vải`, slug `rem-vai`) was present in native SQL with `count=3` and direct GET/slug lookup, but missing from both WP/WooCommerce collection queries. The live `/rem-vai/` archive returned zero products and `noindex,nofollow`. A previous attempt to classify product `#70` into this term instead fell back to `#27`, requiring restoration. This caused the category menu item to be removed for visitor safety.

Native read-only SQL joining product-category `termmeta` revealed the decisive inconsistency: **term #16 was the only one of all 17 native `product_cat` terms without the key `order`; the other 16 each had `order=0`**. All three original published product relationships (`#608`, `#610`, `#611`) remained intact.

The installed WooCommerce source `plugins/woocommerce/includes/wc-term-functions.php` registers:
- `get_terms_defaults` to use `menu_order` for sortable `product_cat`;
- `pre_get_terms` to translate menu sorting into numeric `termmeta.order`;
- `terms_clauses` intended to support terms without order metadata using an outer join.

The term metadata inconsistency was a stronger bounded source-owner repair hypothesis than injecting a Theme-side query workaround. We cannot independently assert which exact generated SQL string led to the missing term; the repair and its effect were tested directly below.

## Owner-authoritative minimal GREEN

Operation 1 — official WooCommerce REST V3 term update:

```http
PUT /wp-json/wc/v3/products/categories/16
Content-Type: application/json

{"menu_order":0}
```

This sets the native `order=0` metadata through the **owning WooCommerce controller** without changing the desired menu position, slug, name, hierarchy, thumbnail, price, product or plugin data.

**After:** read-only SQL confirms exactly one `termmeta` row `term_id=16, meta_key=order, meta_value=0`.

Observed in the same bounded verification:
- `GET /wp/v2/product_cat?include=16&hide_empty=false` now returns term `#16` (previously 0).
- `GET /wc/v3/products/categories?include=16` now returns term `#16` (previously 0).
- `GET /wc/v3/products?category=16&status=publish` returns **3 original products** (previously 0).
- Public `https://remquocanh.vn/rem-vai/` shows the three products without the empty-category message.
- HTML head for `/rem-vai/` shows `index,follow`, self-canonical and the pre-existing author-approved category SEO title/description.

Operation 2 — restore native WP menu category item:

```text
wp menu item add-term 17 product_cat 16 --parent-id=232 --title="Rèm vải" --porcelain
```

Result: **menu item `#1132`**. Fresh public HTML verification of both `.aznet-theme-site-header__nav` (desktop) and `#aznet-theme-mobile-panel` (mobile) finds the native `/rem-vai/` link nested under `Sản phẩm`, together with `Rèm cuốn` (`#33`) and `Rèm cầu vồng` (`#27`).

Operation 3 — reclassify already-published Woo product `#70` (`Rèm vải hoa văn`) via native WordPress taxonomy mutation:

```text
wp post term set 70 product_cat rem-vai --by=slug
```

Previous product categories were `#34` (Rèm Cuốn Zipscreen) and `#29` (Rèm Roman), inconsistent with product name and the three existing native curtain-fabric products in `#16`.

**After:** WooCommerce GET product `#70` returns category `#16` only. Main category now has 4 products: `#611`, `#610`, `#608`, `#70`. No products remain in categories `#34` or `#29` in the checked published query. Public product `#70` breadcrumb now links to `/rem-vai/`. The previously recorded product image IDs, five variation IDs `[74,75,76,77,78]`, attribute options `[Brooke,Carolina,Donatella,Karl,Kate]`, tag `#38`, product type variable, publish status, stock status instock and price `0` remained unchanged.

## Regression after the three operations

- WP REST complete product_cat list `hide_empty=false`: **17** terms including `#16`.
- WooCommerce REST complete product_cat list: **17** terms including `#16`.
- WooCommerce product query `category=16`: **4**, IDs `[611,610,608,70]`.
- Category `#27`: **3**, IDs `[604,603,522]`.
- Category `#33`: **3**, IDs `[607,606,605]`.
- WooCommerce term `#16` reports `count=4`, `menu_order=0`.
- Rèm vải front end displays 4 product cards; no empty-category notice.
- Public desktop and mobile menu HTML retain three category links in the native nested menu.
- Rèm vải archive `robots=index,follow`, canonical and meta description remain correct.
- No AZnet Theme production PHP/CSS/JS/theme files were modified. Active Theme remained v1.3.92.
- **Layer claims:** L3 source-owner write+readback PASS; L4 public HTML markup PASS for the listed pages; independent browser click/visual/keyboard QA not performed, L5/L6 not claimed.

## Important residual risks / UNKNOWN

- WooCommerce product `#70` still has stored `price=0` and exposes a zero-value price in some commerce/social metadata. User-facing archive card shows a variation chooser, but not a reliable quoted price. **No price mutation is authorized** without business owner confirmation.
- `#70` still renders placeholder ConvertFlow FAQ `Câu hỏi 1`, `Dự án A/B/C` and some placeholder copy; `#522` references ANDREA in FAQ; `#605` displays an internal technical note. Public REST discovery for `choiceguide/v1` and `choiceguide-pro/v1` found read and unrelated write endpoints but no supported API to mutate these domain sections. Theme must not rewrite the plugin's private meta/storage to hide them.
- Conflicting official hotline values, true project details, product certifications and signed acceptance scope still require owner/source validation.
- A pre-existing WPVibe draft Theme and backup Theme exist; they were **not** overwritten or activated. The hosting backup was reported by the operator, but restoration has not been independently rehearsed.
- WPCode was not installed as an active plugin; no diagnostic snippet was created. No provider plugin was disabled, no raw mutating SQL used, no mass term regeneration or new taxonomy created.

## Rollback, provenance and exact next

- Menu item `#1132` is reversible via native WordPress `wp menu item delete 1132`.
- To revert product `#70` taxonomy only (not recommended in current corrected state), use `wp post term set 70 product_cat rem-cuon-zipscreen rem-roman --by=slug` and verify the WooCommerce product endpoint.
- `menu_order=0` metadata is a necessary repair: reverting by deleting it would **reintroduce the defect**. If rollback were actually required, use owner-authoritative WooCommerce/WordPress procedures or the operator's backup; no private SQL write path is pre-approved.
- Evidence branch `work/rqa-acceptance-20261023-checkpoint`; this addition contains **documentation only** and must not imply Theme release.
- **EXACT NEXT:** For existing ConvertFlow product placeholder content, identify an official, versioned **editor/save** capability and owner-supplied product facts; if the capability is absent, mark dependency BLOCKED, then continue independent Theme-owned responsive/keyboard/performance QA without mutating plugin-private storage.

## References

- WooCommerce Product Categories public REST V3: https://developer.woocommerce.com/docs/apis/rest-api/v3/product-categories/
- Prior RED forensic checkpoint: `docs/evidence/RQA_PRODUCT_CAT_16_QUERY_DIAGNOSTIC_20261023.md`
- Historic approved project post and Theme ownership: `docs/evidence/CURTAIN01_PROJECT_SHOWCASE_PRODUCTION_20260923.md`
