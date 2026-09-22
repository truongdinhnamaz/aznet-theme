# C6 Curtain 01 — live cutover evidence

**Date:** 22/09/2026  
**Pilot:** `https://remquocanh.vn/`  
**Canonical source anchor:** `main@b20339009cef2acaae6832c941a4841986126c07`  
**Owner approval:** explicit — “đã backup, xuất bản Rèm 01”

## Production action

The owner confirmed a fresh site backup before cutover.

The WPVibe draft candidate was published to the live `aznet-theme` directory. WPVibe created an automatic theme-file rollback copy:

- active Theme: `aznet-theme`, AZnet Theme **1.3.30**;
- theme-file rollback copy: `aznet-theme-wpvibe-backup`, AZnet Theme **1.3.30**;
- Flatsome Child **3.0** remains inactive;
- Flatsome **3.20.7** remains inactive.

The live Theme candidate includes the accepted D-035 geometry: full-bleed Front Page outer shell with the first Curtain 01 Hero flush directly below the Header, while inner content remains container-constrained.

## Live Theme settings

`theme_mods_aznet-theme.aznet_theme_settings` was written with the accepted presentation mappings:

- `visual_preset = curtain-01`;
- `homepage_preset = curtain-01`;
- Hero `wp_block #985`;
- About Page `#35`;
- Knowledge Category `#57`;
- Contact Page `#45`;
- Woo catalogue remains WooCommerce-owned.

No RootProfile, ConvertFlow or WooCommerce authoritative storage was copied into Theme settings.

## Front Page migration

WordPress Page **#2** remains the static Front Page.

After cutover:

- status: `publish`;
- template: WordPress/default Theme template (`template = ""`);
- native `post_content`: empty;
- Page ID / URL continuity retained;
- exactly one native `the_content()` boundary remains in the Theme template.

Legacy UX Builder content remains recoverable from WordPress revisions:

- revision **#988**: 4006-character legacy UX Builder body;
- revision **#935**: 4006-character legacy UX Builder body.

No historical revision was deleted.

## Fresh live runtime evidence

Authenticated live DOM checks after the cutover confirm:

### Homepage

- `aznet-theme-homepage--curtain-01` renders;
- Hero renders from synced block #985 and Media attachment #669;
- About section renders from Page #35;
- Woo catalogue/categories/products render from WooCommerce;
- Knowledge renders from Category #57;
- final CTA renders to Contact Page #45 and public phone projection;
- no visible `[ux_*]`, `[section]`, `[row]`, `[col]` or `[blog_posts]` shortcode leakage;
- Header and `<main>` are directly adjacent in rendered DOM with no intervening Theme-owned element.

### WooCommerce smoke

- `/san-pham/` renders the live WooCommerce catalogue;
- `/rem-vai-don-sac/` renders the live single-product surface and product name;
- no product facts were migrated into Theme storage.

## QA disposition

**PASS**
- production publish transaction;
- Page #2 native-content migration;
- D-035 full-bleed/flush-hero source implementation;
- L3 live WordPress runtime smoke for Homepage, Shop and Single Product;
- shortcode-leak regression;
- rollback availability through site backup, WPVibe Theme backup, and WordPress revisions.

**UNKNOWN / not claimed**
- fresh independent pixel-level L4 browser matrix on the live production hostname;
- fresh live axe/keyboard visual verification;
- provider-specific L5 certification.

The available direct-browser path remains constrained by the site's protection layer; no L4/L5 PASS is inferred from DOM/runtime evidence.

## Rollback

If rollback is required:

1. restore Page #2 from revision #988 or #935;
2. restore legacy template assignment `page-transparent-header-light.php`;
3. remove/restore `aznet_theme_settings` from the pre-write snapshot;
4. restore `aznet-theme-wpvibe-backup` or the owner-confirmed full-site backup if Theme files need rollback;
5. reverify Front Page, Shop and Single Product.

WordPress Posts, WooCommerce products, RootProfile and ConvertFlow domain data must not be rolled back merely for a presentation rollback.
