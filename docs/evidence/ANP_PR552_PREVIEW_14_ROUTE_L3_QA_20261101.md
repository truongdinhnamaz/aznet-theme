# ANP Industrial 01 — PR #552 preview L3 route coverage (2026-11-01)

## Scope and source authority

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- Implementation candidate: `36e9f97fe3d896395caaa926b1175fdbf6f40c00` (Draft PR #552); live/main source remains `9a1667dd6ab683e81a5af7fc5fc16d78e17ac405`.
- Preview: WPVibe `aznet-theme-wpvibe-draft`, cloned from active ANP AZnet Theme 1.3.98. The preview token is intentionally omitted from this repository record.
- Authenticated ANP `site_info`: WordPress 7.1.4 / PHP 8.2.35. Public visitor-facing production was **not changed**.
- Render source: authorized WPVibe `get_page_html`, on preview URLs, selecting `main`. This is a **server-rendered HTML / L3 structural sample**, not independent anonymous browser L4, nor HTTP status/final URL evidence.
- Route manifest owner: PR #550 commit `074beb4c0c47e8736cee14d34327f77e07ff9df1`, `docs/evidence/anp-industrial01-sitewide-routes.json` Git blob `4398da76e144a006bb3ae68dc012993081117e4b`.
- Standard: central promoted NWQS endpoint unavailable; use provisional `nagavia-website-quality/references/safe-baseline.md` only. No globally synchronized version claimed.

## Actual preview edits (prior checkpoint, preserved)

Four draft Theme files were patched using exact PR #552 source hunks. Each was read back byte-for-byte equal to its GitHub candidate, with PHP syntax-check accepted by WPVibe:
1. `template-parts/homepage/industrial-01/categories.php` — Git blob `3ce259fc0c7f642ee9452acd1fd1f2c53636ea1d`.
2. `inc/theme/header.php` — `87935d873678998ffff2bb9dabce65a38e65c46e`.
3. `assets/js/catalog-navigation.js` — `01c47765f7484b0f7338088c63f22e1852b43314`.
4. `assets/css/components/catalog-navigation.css` — `dc005e7ebdd7f0ce4e874fa0362708941c3a678b`.

Prior focused exact-blob PHP category contract and Node mock-DOM submenu behavior PASS remain scoped at L2. No assertion of all-code regression PASS.

## Preview route matrix

| Route ID | Path | Main HTML | H1 count | Note |
|---|---|---|---:|---|
| home | `/` | rendered | 1 | 8 category cards; Pin Lithium placeholder IMG absent; Theme dark visual fallback. |
| about | `/gioi-thieu/` | rendered | 1 | 9 H2, 3 IMG; no known fatal marker sampled. |
| contact | `/lien-he/` | rendered | 1 | CF7 form, submit `Gửi yêu cầu`, 3 required inputs; no send/delivery test. |
| toyota | `/xe-nang-toyota-nhat-bai/` | rendered | 1 | 22 H2, 15 IMG. |
| mitsubishi | `/xe-nang-mitsubishi-nhat-bai/` | rendered | 1 | 22 H2, 15 IMG. |
| privacy | `/chinh-sach-bao-mat/` | rendered | 1 | 8 H2. |
| sitemap | `/sitemap/` | rendered (partial capture) | 1 in captured HTML | Main content truncated at 110,000 chars; remaining content unverified. |
| secondary-home | `/trang-chu-2/` | rendered | **2** | Theme page title `Trang chủ` + ConvertFlow-owned hero H1 `ANP Việt Nam`; plugin projection also contains nested `main` landmark. |
| shop | `/san-pham/` | rendered | 1 | Native Woo archive with product list/order form. |
| komatsu-product | `/ac-quy-xe-nang-dien-komatsu-3-tan-chinh-hang-gs` | rendered | 1 | ConvertFlow `Nhận báo giá` points to existing `#dat-hang`; `Hết hàng` absent from sampled main HTML. |
| forklift-category | `/xe-nang-cu-nhat-ban/` | rendered | **2** | Native Woo archive title H1 and WordPress-owned term-description H1. |
| blog-article | `/tai-sao-can-mua-xe-nang-o-nhung-co-so-co-uy-tin.html` | rendered | 1 | 14 H2, 8 IMG. |
| services-archive | `/dich-vu/` | rendered | 1 | 11 H2, 10 IMG. |
| knowledge-archive | `/kien-thuc-xe-nang/` | rendered | 1 | 11 H2, 10 IMG. |

Results: 14/14 paths exposed a `main` element in preview HTML; 12/14 expose exactly one H1 in the sampled content, 2/14 expose two. 13 of 14 requested main captures were complete, Sitemap was truncated. Known fatal/parse-error or missing-CF7 markers were not found in the sampled main markup. These observations **do not establish HTTP 200, canonical final URL, loaded-image integrity, accessibility conformance, responsive CSS or working browser interactions**.

## Triage and ownership

- **Theme candidate:** gallery placeholder suppression, Header Woo page fallback guard and decorative catalog toggle CSS/JS are in the **preview only**.
- **WordPress/WooCommerce source:** term-description inline H1 at `/xe-nang-cu-nhat-ban/`; cannot silently rewrite WordPress-authored content in the Theme repository.
- **WordPress + ConvertFlow provider:** `/trang-chu-2/` combines Theme page heading with ConvertFlow public hero H1; nested `main` originates inside the ConvertFlow-projected content. Do not duplicate provider logic or patch private provider storage from Theme.
- Multiple H1 is a **semantic review finding**, not automatically a search penalty or visual defect; requires public browser/owner assessment.
- No category, product, menu, contact, provider, or site content data mutation.

## Hard release gates

- **L3 partial verified:** PHP-rendered preview HTML from actual WordPress site, 14-route sample (one partial).
- **L4 BLOCKED:** no independent JavaScript-executing screenshots, real DOM/computed styles or interactive/axe checks at 390/768/1440. Local browser executable times out even on data URL; local DNS fails for ANP; connected Browser only supports reading already-open tabs; no preview browser tab shared.
- **L5 UNKNOWN:** no independent ANP/ConvertFlow full integration interaction verification.
- **L6 NOT APPROVED:** no main merge, live release or anonymous post-release QA. Fresh backup and explicit user publication approval have not been verified.
- WPVibe linked-account quotas: main authenticated account 2063/2000 (0 remaining) at observation; another authorized ANP account was used read-only for the nine new routes without changing site state.

**EXACT NEXT:** obtain working authorized Chromium/Playwright/Axe access to the AZnet preview (or user-provided viewport screenshots with URL/provenance and interaction observations) and complete the sitewide 390/768/1440 route matrix, prioritizing Homepage, Komatsu product, category and Contact. Attribute any verified defect by owner before RED → GREEN → regression. Only after L4, backup/rollback evidence and separate owner release approval may publication be considered.
