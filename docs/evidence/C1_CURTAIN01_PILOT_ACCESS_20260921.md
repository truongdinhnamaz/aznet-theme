# C1 Curtain 01 pilot inventory — read-only checkpoint

**Date:** 21/09/2026  
**State:** PARTIAL PASS / BROWSER BASELINE BLOCKED_BY_WAF  
**Canonical source anchor:** `main@d82e229fa30ca0b14d8bba731b143e4bc2c8efae`  
**Accepted decision:** D-034 — Rèm 01 curtain/interior pilot  
**Pilot:** `https://remquocanh.vn/`

## Goal

Perform the C1/M0 read-only inventory required before any production implementation may claim Rèm Quốc Anh parity or safe retirement of legacy Flatsome presentation dependencies.

## Key correction to the original assumption

The pilot is **not currently running Flatsome**.

Fresh authenticated WordPress evidence shows:

- active Theme: **AZnet Theme 1.3.30**;
- inactive Theme: **Flatsome 3.20.7**;
- inactive child Theme: **Flatsome Child 3.0**.

Therefore the real problem is no longer "switch the live Theme from Flatsome to AZnet Theme". The site has already switched Theme. The remaining migration problem is **legacy Flatsome/UX Builder content and template residue inside WordPress-owned content**, plus unfinished AZnet Theme presentation for those surfaces.

This distinction is material: Flatsome remains rollback evidence, not the active presentation owner.

## Runtime / environment

Authenticated read-only evidence:

- WordPress: **7.1.1**;
- PHP: **8.1.34**;
- HTTPS: PASS;
- permalink structure: `/%postname%/`;
- single site;
- WooCommerce: active, **11.1.1**;
- ConvertFlow: active, **0.1.0 / build P5.238**;
- ConvertFlow Pro: active, **0.1.0-alpha.120**;
- RootProfile: active, **0.1.0-alpha.114**;
- Rank Math + PRO: active;
- Contact Form 7: active;
- Variation Swatches for WooCommerce: active.

The site has a public RootProfile organization projection and a public ConvertFlow diagnostics endpoint. Theme integration must continue to consume only accepted public contracts.

## WordPress-owned site structure

Static Front Page mode is active:

- `show_on_front = page`;
- `page_on_front = 2`;
- Page #2 is **Rèm Quốc Anh** and resolves to the site root.

Published top-level Pages found:

- Front Page — ID 2;
- Giới thiệu — ID 35;
- Bộ sưu tập — ID 29;
- Công trình — ID 41;
- Kiến thức — ID 43;
- Liên hệ — ID 45;
- Sản phẩm — ID 130;
- Báo giá rèm — ID 726;
- Chính sách bảo hành - bảo trì — ID 814;
- Chính sách giao hàng - lắp đặt — ID 820;
- Hướng dẫn mua hàng - thanh toán — ID 823;
- Giỏ hàng — ID 30;
- Thanh toán — ID 31;
- Tài khoản — ID 32.

Primary menu `Main menu` is assigned to `primary` and maps to:

1. Trang chủ;
2. Giới thiệu;
3. Sản phẩm;
4. Báo giá;
5. Công trình;
6. Kiến thức;
7. Liên hệ.

## Editorial content

Published native Posts observed: 5, all currently in category **Tư vấn chọn rèm**.

WordPress categories observed:

- Công trình rèm;
- Kiến thức về rèm;
- Tư vấn chọn rèm.

This is WordPress editorial truth and may be mapped by explicit term IDs; Theme must not infer semantic ownership from labels/slugs.

## WooCommerce catalogue

Public Woo Store API returned **10 products**.

Observed product families include:

- Rèm vải;
- Rèm cuốn;
- Rèm cầu vồng;
- Rèm Roman;
- Rèm sáo / related catalogue categories.

Several products intentionally expose price `0` and/or are not purchasable through the public Store API. Theme must not reinterpret that as authoritative "free product" or invent price/stock/quote semantics. WooCommerce and ConvertFlow remain the owners of commerce and quote/decision behavior.

## RootProfile public organization projection

Public `rootprofile/v1/organization` is available and exposes the authoritative organization projection, including:

- organization identity;
- logo;
- public description;
- address;
- contact points;
- social profiles;
- one public team member projection;
- policy links;
- canonical organization URL.

Rèm 01 must consume this public projection where a Theme integration already supports it; do not copy these facts into Theme settings or hardcode them in the reusable preset.

## Flatsome / UX Builder residue classification

### Front Page — Page #2 — REBUILD PRESENTATION

Stored `post_content` still contains extensive Flatsome/UX Builder shortcodes, including:

- `section`;
- `ux_slider`;
- `ux_banner`;
- `text_box`;
- `row`;
- `col`;
- `ux_html`;
- `ux_product_categories`;
- `ux_products`;
- `title`;
- `blog_posts`.

The stored Page template is also a Flatsome template slug: `page-transparent-header-light.php`.

Because Flatsome is inactive, the current AZnet Theme cannot treat these shortcodes as a supported presentation contract. The Front Page is the clearest migration target for Rèm 01.

### Bộ sưu tập — Page #29 — REBUILD PRESENTATION

Stored content contains Flatsome/UX Builder shortcodes including:

- `section`;
- `ux_slider`;
- `ux_banner`;
- `text_box`;
- `row`;
- `col`;
- `ux_product_categories`;
- `ux_products`.

Stored template: `page-transparent-header-light.php`.

### Kiến thức — Page #43 — REBUILD PRESENTATION

Stored content contains Flatsome/UX Builder shortcodes including:

- `section`;
- `ux_banner`;
- `text_box`;
- `row`;
- `col`;
- `ux_html`;
- `blog_posts`.

Stored template: `page-transparent-header-light.php`.

### Giới thiệu — Page #35 — MAP / PRESENTATION HARDENING

No Flatsome shortcode dependency was found in the current stored content.

The Page contains custom `rqa-*` HTML composition and uses the stale Flatsome template slug `page-transparent-header-light.php`. The authored copy/media remain WordPress-owned. Theme may provide reusable presentation primitives, but must not convert these Rèm Quốc Anh-specific facts into generic Theme truth.

### Công trình — Page #41 — MAP / PRESENTATION HARDENING

No Flatsome shortcode dependency was found in the current stored content.

The Page contains custom `rqa-*` HTML composition and uses the stale Flatsome template slug `page-transparent-header-light.php`.

### Policy Pages — KEEP CONTENT / RETIRE TEMPLATE RESIDUE LATER

The three policy/help Pages contain ordinary authored content, not Flatsome shortcodes, but still carry legacy Flatsome template slugs such as `page-blank-title-center.php`.

Their content is portable; template metadata cleanup is a later explicit WordPress-content migration action, not a Theme runtime heuristic.

### Woo core Pages — KEEP

Cart / Checkout / Account use native Woo blocks/shortcodes and do not need Flatsome reconstruction.

### Liên hệ / Sản phẩm / Báo giá — OWNER SURFACE / KEEP ROUTE

Their stored Page bodies are empty or minimal. The route and authoritative rendering behavior belongs to WordPress/WooCommerce/ConvertFlow/other accepted provider surfaces. Rèm 01 should style the public owner output instead of inventing parallel content.

## Legacy Theme template residue

Several Pages still store template slugs belonging to Flatsome:

- `page-transparent-header-light.php`;
- `page-blank.php`;
- `page-blank-title-center.php`.

AZnet Theme must **not** add fake templates with those names merely to satisfy old metadata. The correct migration is to rebuild/map the destination presentation, then explicitly normalize Page template assignment only when a confirmed content-migration plan is approved.

## Provenance mismatch

Canonical GitHub `main` at the D-034 merge is Theme metadata **1.3.28**.

The pilot reports active **AZnet Theme 1.3.30**.

GitHub currently has open 1.3.30 candidate work in PR #225/#226, but exact byte identity between the pilot's installed 1.3.30 and those repository candidates has **not** been proven with the current read-only REST access.

Therefore:

- do not use the live 1.3.30 installation as source code truth;
- canonical repository `main` remains the implementation authority;
- do not claim package-byte parity between the pilot and any 1.3.30 branch.

## Browser baseline blocker

Direct page rendering through the available browser path is intercepted by **OneShield** security challenge.

Therefore fresh desktop/tablet/mobile screenshots and real DOM/a11y parity are not yet available from this execution path.

This is a **C1 browser-baseline blocker only**. It does not block repository-side C2/C3 Theme work that can be proven with deterministic WordPress fixtures.

## C1 classification summary

- **KEEP:** WordPress Posts/Categories, Woo products/catalogue, menus, media, Woo cart/checkout/account, policy content.
- **MAP:** About, Projects, explicit editorial/category sources, RootProfile public organization projection.
- **REBUILD PRESENTATION:** Front Page, Collection Page, Knowledge Page.
- **OWNER CONTRACT:** quote/decision/fit/contact/profile semantics remain with ConvertFlow/RootProfile/Woo/WordPress owner surfaces.
- **RETIRE LATER:** stale Flatsome template metadata and inactive Flatsome runtime only after destination + rollback + regression evidence.

## Gate result

**C1 DATA/OWNERSHIP INVENTORY: PASS.**  
**C1 FRESH VISUAL/BROWSER BASELINE: BLOCKED_BY_WAF.**

The remaining browser blocker does not justify stopping Theme-owned C2 implementation. It does prevent any claim of real-pilot visual parity or production cutover readiness.

## Exact next

Proceed with **C2 — Rèm 01 Theme preset foundation** on canonical repository `main` using RED -> minimal GREEN:

1. add `curtain-01` to the strict Homepage presentation preset allow-list;
2. keep preset switching mutation-free;
3. make Homepage Composer activation support the new Theme-owned preset without weakening static Front Page validation;
4. add a scoped `homepage-curtain-01.css` asset loaded only on the active Rèm 01 Front Page;
5. do not yet rewrite the pilot's WordPress content;
6. retain exactly one native Front Page `the_content()` boundary;
7. leave Woo/RootProfile/ConvertFlow ownership unchanged.

Real pilot browser parity remains a later C5 gate after WAF/browser access is available.
