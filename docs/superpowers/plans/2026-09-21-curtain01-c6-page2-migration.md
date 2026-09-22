# Curtain 01 C6 — Rèm Quốc Anh Front Page migration plan

**Date:** 21/09/2026  
**Pilot:** `https://remquocanh.vn/`  
**Target Page:** WordPress Page #2 — current Front Page  
**Canonical source anchor:** `main@a55cfc2f9235edd1653bac9c8d9fe37b38b0ac94`  
**Decision:** D-034 Accepted  
**Execution state:** PLAN ONLY — NO LIVE WRITE

## 1. Goal

Prepare a reversible migration of the current Rèm Quốc Anh Front Page from stored Flatsome/UX Builder shortcodes to the accepted Rèm 01 composition without making AZnet Theme interpret Flatsome at runtime and without moving WordPress/WooCommerce/provider truth into Theme storage.

This C6 plan is a production-change plan, not authorization to execute the live change.

## 2. Fresh read-only pilot facts

Authenticated WordPress REST evidence on 21/09/2026 confirms:

- `show_on_front = page`;
- `page_on_front = 2`;
- Page #2 title: **Rèm Quốc Anh**;
- Page #2 status: `publish`;
- Page #2 current legacy template: `page-transparent-header-light.php`;
- Page #2 last modified: `2026-09-01T15:52:04`;
- Page #2 raw content length: **4006 characters**;
- Page #2 WordPress revision history count: **83**;
- Page #2 predecessor revision: **935**;
- current stored shortcode families:
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

Relevant existing WordPress sources:

- About Page: **#35** — `/gioi-thieu/`;
- Collection Page: **#29** — `/bo-suu-tap/`;
- Projects Page: **#41** — `/cong-trinh/`;
- Knowledge Page: **#43** — `/kien-thuc/`;
- Contact Page: **#45** — `/lien-he/`;
- Shop Page: **#130** — `/san-pham/`;
- Quote Page: **#726** — `/bao-gia-rem/`;
- Knowledge category with current published Posts: **term #57 — Tư vấn chọn rèm**, count 5.

## 3. Current Page #2 content classification

### 3.1 Hero — REBUILD AS WORDPRESS-OWNED HERO SOURCE

Current Page #2 uses one `ux_slider` containing three `ux_banner` items with existing WordPress Media URLs:

- `/wp-content/uploads/2026/08/rem-quoc-anh-03.png`;
- `/wp-content/uploads/2026/08/rem-quoc-anh-02.png`;
- `/wp-content/uploads/2026/08/rem-quoc-anh-01.png`.

Destination:

- create or select one WordPress-owned synced Hero block;
- reference existing Media Library assets;
- Rèm 01 only renders the public WordPress block through the existing typed Hero reference;
- do not hardcode these media URLs in Theme source.

### 3.2 Brand story / About — MAP, DO NOT DUPLICATE INTO THEME

Current Page #2 embeds editorial copy such as:

- "Hơn hai thập kỷ gìn giữ nghề rèm";
- "Hai thế hệ, một nếp nghề";
- the current story copy and quotation;
- the current story image;
- links to `/gioi-thieu/` and `/bo-suu-tap/`.

Destination:

- Rèm 01 About section maps to WordPress Page #35;
- before deleting the legacy Page #2 copy, compare its editorial facts/media with Page #35;
- any fact that exists only in Page #2 must be preserved in WordPress-owned content only after explicit content-migration review;
- Theme must not store the "2000", two-generation, quotation or other Rèm Quốc Anh-specific claims.

### 3.3 Product categories — OWNER: WOOCOMMERCE

Current Page #2 uses `ux_product_categories`.

Destination:

- Rèm 01 catalogue consumes WooCommerce product taxonomy through the existing public consumer adapter;
- no category IDs, product facts or category semantics are copied into Theme settings;
- missing WooCommerce => section fails soft.

### 3.4 Product listing — OWNER: WOOCOMMERCE

Current Page #2 uses `ux_products`.

Destination:

- Rèm 01 consumes public WooCommerce product objects;
- Homepage does not reinterpret zero-price / quote-first semantics;
- price, stock, purchasability, variations and conversion remain WooCommerce / accepted provider concerns.

### 3.5 Knowledge — MAP TO WORDPRESS CATEGORY

Current Page #2 uses `blog_posts`.

Destination:

- map Rèm 01 Knowledge to explicit WordPress term **#57**;
- WordPress query remains authoritative;
- no slug/title heuristic lookup at runtime.

### 3.6 Contact / final CTA — OWNER HANDOFF

Current Page #2 has no accepted standalone final CTA source that should become Theme-owned content.

Destination:

- use explicit Contact Page #45 and existing public contact projection where available;
- quote/lead/conversion state remains with its owner;
- Theme only renders the handoff.

## 4. Target Page #2 state after approved migration

The migration should make Page #2 portable WordPress content rather than a Flatsome compatibility surface.

Target:

- Page #2 remains the assigned static Front Page;
- Page #2 remains WordPress-owned;
- legacy `page-transparent-header-light.php` assignment is normalized to the WordPress/default Theme template;
- `homepage_preset = curtain-01`;
- `visual_preset = curtain-01`;
- explicit typed mappings are configured for Hero / About / Knowledge / Contact;
- the Theme continues to execute exactly one native `the_content()` boundary;
- Page #2 stored body contains only intentionally retained native WordPress blocks/content.

If every current visible section is represented by accepted mapped/owner sources, an empty native Page body is valid. Do not add placeholder or duplicate marketing copy merely to make `the_content()` non-empty.

## 5. Required pre-write rollback capture

Before any live mutation, create a dated rollback bundle containing:

1. exact raw Page #2 `post_content`;
2. Page #2 title, slug, status, template, modified timestamp and revision identifiers;
3. Front Page assignment;
4. current menu assignments;
5. active Theme name/version;
6. current Rèm 01/Homepage/visual preset settings if already present;
7. exact Hero source reference if already present;
8. current screenshots/DOM if browser access is available;
9. exact package/source identity intended for the candidate;
10. confirmation that inactive Flatsome/Flatsome Child remain available for rollback until C6 closure.

No destructive cleanup of Flatsome, old Page revisions or legacy metadata is allowed in the same migration transaction.

## 6. Proposed execution transaction

The live operation, when separately approved, should be bounded as follows.

### Step A — verify no drift

Re-read Page #2 and abort if any of these changed from the approved migration snapshot:

- content;
- template;
- modified timestamp;
- Front Page assignment;
- target typed source IDs;
- active Theme/package identity.

A drifted Page must be re-reviewed; do not overwrite it.

### Step B — prepare owner sources first

Before touching Page #2:

- confirm/create WordPress-owned Hero source;
- confirm Page #35 as About source;
- confirm term #57 as Knowledge source;
- confirm Page #45 as Contact handoff;
- confirm WooCommerce catalogue availability.

If an owner source is missing, stop that mapped section rather than inventing a Theme fallback fact.

### Step C — apply Theme-owned presentation settings

Apply only accepted Theme settings:

- Homepage preset: `curtain-01`;
- Visual preset: `curtain-01`;
- explicit typed Hero / About / Knowledge / Contact references.

Preset application must not mutate Page/Post/Product/provider data.

### Step D — replace Page #2 builder body with native portable content

Only after Steps A-C are ready:

- replace the legacy UX Builder body with the approved native Page #2 body;
- normalize the stale Flatsome Page template to the default/current Theme template;
- preserve Page #2 ID and Front Page assignment;
- do not alter Woo products, Posts, categories, RootProfile or ConvertFlow storage.

### Step E — immediate smoke verification

Verify:

- `/` resolves Page #2;
- no visible Flatsome shortcode text;
- Rèm 01 Hero/About/Catalogue/Knowledge/CTA render from the intended owners;
- Header/Footer present;
- Shop and Single Product still resolve;
- no Theme-caused PHP warning/fatal;
- no unexpected redirect/permalink change.

If any critical check fails, execute rollback immediately rather than continuing cleanup.

## 7. Rollback transaction

Rollback must be possible without deleting owner data.

Restore, in order:

1. captured Page #2 raw content;
2. captured legacy Page template assignment;
3. captured prior Theme presentation settings;
4. prior active Theme only if the approved cutover transaction changed it;
5. flush/reverify public routes only as needed.

Do not roll back WordPress Posts, Woo products, RootProfile or ConvertFlow data merely because presentation rollback is required.

## 8. Post-write QA gates

A successful write is not C6 PASS.

Required fresh evidence after the live candidate change:

- L3 real WordPress runtime;
- L4 desktop 1440, tablet 1024, mobile 390, narrow 320;
- zero horizontal overflow;
- keyboard/focus/landmark/heading checks;
- axe serious/critical = 0 for required surfaces;
- Homepage, Shop and Single Product;
- no Flatsome runtime asset dependency;
- no visible UX Builder shortcode leakage;
- URL continuity;
- owner-source continuity for WooCommerce, RootProfile and ConvertFlow public surfaces where applicable;
- verified rollback bundle remains usable.

OneShield/browser access remains a blocker to claiming real-pilot L4 if the challenge cannot be bypassed through an approved browser path.

## 9. Explicitly forbidden in C6

- runtime Flatsome/UX Builder interpreter in AZnet Theme;
- hardcoding Rèm Quốc Anh identity/content into the reusable Theme;
- direct reads of provider private options/meta/tables;
- product/contact/profile copies into Theme storage;
- heuristic Page/category discovery by slug/title;
- deleting Flatsome or historical revisions as part of the migration transaction;
- production release/deploy/cutover without a separate owner gate.

## 10. C6 exit condition

C6 may be called **PASS** only when:

- the migration bundle and exact write plan are frozen;
- target source mappings are verified;
- live candidate mutation is separately approved and executed;
- fresh runtime/browser/integration evidence passes on the real pilot;
- rollback is verified available.

Until then the state is:

**C6 PLAN: PASS**  
**C6 LIVE MIGRATION: NOT EXECUTED**  
**C6 REAL-PILOT L4/L5: BLOCKED/UNKNOWN**

## Exact next

Create the pre-write rollback snapshot/artifact for Page #2 and the intended mapping values **without changing the live site**. After that snapshot is complete, the next hard gate is explicit approval to execute the Page #2 live migration transaction.
