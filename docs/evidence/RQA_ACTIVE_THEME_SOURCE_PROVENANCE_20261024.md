# RQA — active Theme source provenance read-only check (2026-10-24)

**Canonical repository:** `truongdinhnamaz/aznet-theme`
**Active production site:** `https://remquocanh.vn`
**Source baseline compared:** published Git tag `v1.3.92`, commit `b7738467b21dba1bcd63f62a11b5a248cd7f1ba9`.
**Proposed candidate:** `main@afdeeacd3927eb0b7763045ab732b2f0cbebd1c7` Theme `1.3.96`, with the already-verified single WooCommerce archive CSS patch.
**Method:** read-only WPVibe `wp-content` active-Theme source reads and GitHub exact-tag `fetch_file`, comparing reconstructed numbered-file text without editing the site. WPVibe warns that known secret patterns may be masked and that returned text must **not** be treated as a byte-for-byte archival copy. No redaction marker was observed in these nine returned files.

## Fresh production state

- `site_info`: active `aznet-theme` **1.3.92**, WordPress **7.1.3**; active WooCommerce **11.2.1** per earlier fresh plugin inventory.
- Theme-owned WordPress Additional CSS still linked to `custom_css_post_id=1133`. This separate hotfix is already production browser-verified 9/9: [Actions #39974941109](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39974941109).
- Active-theme `wp-content` listing counts 162 PHP files and 43 CSS files. The new `digital-01` PHP manifest and Digital 01/about-showcase new CSS assets (added after `v1.3.92`) are not present in those listings.

## Live active source vs v1.3.92 tag — full returned content match

The following **nine** existing production runtime files returned complete, nontruncated text. After stripping WPVibe's line numbers, the full text exactly matched the corresponding canonical GitHub `v1.3.92` source file:

1. `style.css` (metadata `Version: 1.3.92`)
2. `functions.php` (`AZNET_THEME_VERSION = 1.3.92`)
3. `inc/theme/assets.php`
4. `inc/theme/homepage-composer.php`
5. `inc/theme/homepage-surface-map.php`
6. `inc/theme/template-registry.php`
7. `template-parts/footer/site-footer.php`
8. `assets/css/components/page.css`
9. `assets/css/components/woocommerce-archive.css`

These cover the existing files involved in the 1.3.92 → 1.3.96 runtime diff, plus the native WooCommerce CSS reset. The WPVibe read-only source method can establish **text-content parity for the returned data**, not a signed archive-level, filesystem-level or full-install byte parity.

## UNKNOWN — oversized source and unenumerated runtime bytes

- `inc/admin/homepage.php`: active file size about **76.3 KiB**; the live WPVibe `wp-content` read returned **HTTP 413** because its source-file limit is **64 KiB**. This is a tool-size limitation, not evidence of code drift. It is one of the files modified between 1.3.92 and main 1.3.96.
- No site-wide SHA-256 manifest of the actual production Theme directory was extracted: do **not** claim all 231 runtime files match the historical Release ZIP exactly.
- There is an already-existing WPVibe authored draft Theme with unrelated PHP changes and an existing backup Theme that identifies an older version. **Do not publish or delete the existing draft**, and do not equate that older backup with the required exact 1.3.92 rollback package.
- User reported a full hosting backup, but **restore readiness and exact active Theme archive were not independently verified through this connector**.
- RQA-specific ConvertFlow Core/Pro real-provider L5 compatibility with Theme 1.3.96 remains untested; isolated Curtain01+Woo fixtures cannot certify that integration.

## Existing PASS evidence retained

- [Clean WordPress 6.9/PHP8.1 L3/L4 #40070849629](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40070849629): SUCCESS.
- [Curtain 01 RQA-shaped WP 7.1.3/PHP8.4/Woo 11.2.1 upgrade + rollback #40070849548](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/40070849548): SUCCESS. Source-owned Pages/Posts/Menus/Products and Theme presentation settings remained intact in **staging**; careful CSS de-duplication/revival was tested. Detailed checkpoint: `docs/evidence/RQA_V1396_UPGRADE_ROLLBACK_PASS_20261024.md`.
- This evidence does not grant release authorization and is not a real production Theme replacement.

## Gate and one exact next

**PASS:** L0 latest source validation; scoped live-source TEXT provenance 9/9 checked files; L1/L2 candidate CSS; clean L3/L4 and isolated Curtain01+Woo L3/L4; historic native hotfix production L4.
**BLOCKED/UNKNOWN:** oversized live source byte parity, full active archive SHA manifest, separately verified backup recoverability, real RQA provider L5, official new version/Release L6.
**NEXT:** Open a **Draft PR only** from the 1.3.96-based candidate branch into canonical `main` with the exact 353-byte runtime CSS delta and auditable CI/evidence. Before any product-owner release approval or RQA production upgrade, obtain an exact active-theme/backup checksum through an authorized hosting or snapshot route; verify staging with actual provider contracts, or document the L5 BLOCKED gate rather than bypass it with fixtures.
