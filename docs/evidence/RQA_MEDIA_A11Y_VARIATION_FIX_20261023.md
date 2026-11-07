# RQA live product-gallery accessibility and variation alignment — 2026-10-23

**Pilot:** https://remquocanh.vn/  
**Branch:** `work/rqa-acceptance-20261023-checkpoint` (documentation only).  
**Scope:** WordPress-native media metadata, WooCommerce-native product-variation image assignments, public API/HTML QA, provider capability inventory.  
**Source ownership:** WordPress owns media metadata; WooCommerce owns product/variation state; ConvertFlow owns its own FAQ/projects/spec content. AZnet Theme remains a presentation consumer with no code mutation in this slice.

## Live baseline / capability discovery

- Active Theme `aznet-theme 1.3.92` on WordPress `7.1.3` / PHP `8.4.26`.
- ConvertFlow Core `0.1.0` / runtime build `P5.252`, ConvertFlow Pro `0.1.0-alpha.120`.
- WP Abilities discovery enumerated **49 abilities** but no ConvertFlow/ChoiceGuide ability to edit FAQ, product project cards or specification copy.
- Public `/choiceguide/v1` REST namespace exposes related-content actions, fit terms and diagnostics; `/choiceguide-pro/v1` exposes decision events. **Neither exposes a versioned editor/save route for the target FAQ/project/spec fields.** The installed plugin does have WordPress admin metabox/editor facilities; these are not equivalent to a public REST write contract. No private plugin meta, option or table was edited.
- Three previously observed source-owner placeholders remain outside verified repair: product `#70` generic FAQ/projects, `#522` ANDREA wording, and `#605` internal specification notes. Editing them via an unpublished/private storage key is prohibited.

## RED — WordPress media accessibility inventory

- Native WooCommerce product query returned ten published products with **35 distinct media IDs** across their featured/gallery image sets. Two assets are shared between different product entries: ID `721` (Rèm cuốn, #607/#606) and ID `949` (Rèm cầu vồng, #604/#603).
- Product `#70` (Rèm vải hoa văn) had 10 distinct displayed gallery images; nine WordPress `alt_text` fields were blank. Asset `#73` already had a meaningful label and was untouched.
- The other 25 distinct media assets had 24 empty `alt_text` fields; asset `#949` carried `Rèm cầu vồng xuyên sáng`, inaccurate on product `#604` (Rèm cầu vồng chống cháy) using that same asset. File/media titles and filenames were the only naming evidence used, not independent visual or product certification proof.
- Images in product `#70` were sizeable. Original media `#73` is a 1000x750 PNG, approx. 1,142,935 bytes; other large originals include `#83` 1,601,310 bytes and `#89` 1,343,637 bytes. This is **not** a verified optimization plan or a claim that originals are all downloaded; browser payload attribution is still needed.

## Owner-authoritative GREEN — native WP REST media updates

- Via **POST /wp/v2/media/{id}** with `alt_text` only, updated nine previously-empty IDs on product #70: `72,80,81,83,84,86,87,89,90`. Labels refer to the product and original media name (Karl, Kate, Carolina, Donatella); no images or file URLs were replaced.
- Filled 24 additional empty media labels: `721,651,652,653,677,680,681,678,676,675,674,673,679,699,700,701,702,703,704,705,706,624,526,525`.
- Adjusted the previously nonempty shared-media `#949` label from `Rèm cầu vồng xuyên sáng` to neutral `Rèm cầu vồng – hình ảnh sản phẩm`, avoiding misleading alt text on product `#604`; shared `#721` also received neutral `Rèm cuốn – hình ảnh sản phẩm`.
- **34 media records mutated** (33 empty-field fills + one neutral correction), one existing label (`#73`) preserved.
- Read-back from `GET /wp/v2/media?include=...&_fields=id,alt_text`: **35/35 nonempty**.
- Verified HTML `.woocommerce-product-gallery` on all ten product permalinks: **10/10 gallery instances PASS**, with 3,9,8,1,1,1,1,1,2,10 rendered gallery images respectively and no empty `alt` attributes. This is HTML verification, not a claim of a new Lighthouse score or visual semantic correctness of uninspected images.
- Product `#70` WooCommerce `images` array contains duplicate ID #73 but the public gallery renders only one copy; **no asset deletion or product-gallery list mutation was performed**.

## RED -> minimal GREEN — two incorrectly assigned variation images

- Native WooCommerce variation GET established product `#70` (Rèm vải hoa văn) misassignment:
  - Variation `#77` had option `Donatella` but image ID `87` (`Carolina 1`).
  - Variation `#78` had option `Carolina` but image ID `90` (`Donatella 1`).
- Corrected through the owning WooCommerce REST V3 variation controller, preserving each variation's full existing attribute option:
  - `PUT /wc/v3/products/70/variations/77` — `image.id=90`, `attributes=[{id:1,option:"Donatella"}]`.
  - `PUT /wc/v3/products/70/variations/78` — `image.id=87`, `attributes=[{id:1,option:"Carolina"}]`.
- GREEN WooCommerce read-back checks all five variations `#74-78`. Existing prices remain `0`, status `publish`, `stock_status=instock`, `sku=BF-1325`, sort order and options unchanged. The 11-entry parent product image array, its category `#16`, attributes and five variation IDs remained unchanged.
- Public product form `data-product_variations` HTML parsed successfully. Option `donatella` now maps to `Rem-vai-hoa-van-Donatella-1-510x383.png` and `carolina` to `Rem-vai-hoa-van-Carolina-1-510x383.jpg`. **Actual mouse/keyboard selector interaction in a browser has not been independently executed.**

## Narrow Lighthouse QA (mobile lab; no CrUX field data)

| Page | Performance | Accessibility | Best practices | SEO | LCP | CLS |
|---|---:|---:|---:|---:|---:|---:|
| `/rem-vai/` | 84 | 100 | 100 | 100 | 3.7 s | 0 |
| `/rem-cuon/` | 74 | 100 | 100 | 100 | 2.4 s | 0.69 |
| `/rem-vai-hoa-van/` | 64 | 97 | 100 | 100 | 6.9 s | 0.013 |
| `/rem-cau-vong-can-sang/` | 71 | 100 | 100 | 100 | 6.0 s | 0.011 |

- Product `#70`: measured transfer ~7,691 KiB; Lighthouse flagged unminified/unused assets and main thread time; Accessibility flagged **color-contrast** and **label-content-name-mismatch**. A browser element-level trace is still required to establish whether the defect belongs to Theme or a public plugin presentation surface. These audits were **before** the image-alt updates; no unearned post-fix Lighthouse score is claimed.
- Rèm cuốn taxonomy CLS ~0.69: no offending DOM element or reliable owner attribution provided by the audit. Do not modify Theme CSS/JS to guess at the fix. No standalone real-user measurements were available.

## Rollback / gates / exact next

- The operator previously confirmed a site-wide backup exists; no independent restore rehearsal was conducted.
- Media `alt_text` can be reverted with `POST /wp/v2/media/{id}` using prior exact values (33 previously blank, #949 previously `Rèm cầu vồng xuyên sáng`). Not a validated full rollback.
- Variation image swaps reversible via the same public controller: set `#77.image.id=87`, `#78.image.id=90` with unchanged original options. **This would restore a known defect and is not recommended; rollback information is kept for disaster recovery only.**
- **PASS L3:** owner REST operations, all 35 media readbacks, all five variation readbacks. **PASS narrow L4 HTML:** all ten rendered galleries nonempty `alt`, option/image mapping in public form. **UNKNOWN:** browser interaction/visual semantics, actual speed improvement, keyboard test, L5 provider integration. **BLOCKED:** official ConvertFlow target-section edit contract, commerce price policy, verified product certifications/photos, canonical hotline, signed acceptance scope.
- **EXACT NEXT:** obtain an element-level browser layout-shift and accessibility-failure trace for `/rem-cuon/` and `/rem-vai-hoa-van/`; assign the offending surface to Theme or provider, write the narrowest RED regression, then implement/test Theme-owned presentation fix only if the owner is Theme. ConvertFlow target-section edits must remain with the plugin's supported admin or public contract.
