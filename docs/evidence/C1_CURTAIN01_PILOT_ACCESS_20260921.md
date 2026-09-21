# C1 Curtain 01 pilot inventory — access checkpoint

**Date:** 21/09/2026  
**State:** BLOCKED_EXTERNAL_ACCESS  
**Canonical source anchor:** `main@d82e229fa30ca0b14d8bba731b143e4bc2c8efae`  
**Accepted decision:** D-034 — Rèm 01 curtain/interior pilot

## Goal

Perform the C1/M0 read-only inventory required before any production implementation may claim Rèm Quốc Anh parity or safe Flatsome migration readiness.

Pilot target: `https://remquocanh.vn/`.

## PASS retained

- D-034 is owner-approved and merged to canonical `main`.
- Rèm 01 ownership boundary is accepted: Theme presentation only; WordPress/WooCommerce/provider truth remains with its owner.
- No production Theme code has been opened for Rèm 01 before C1.
- No live-site mutation, theme switch, content rewrite, plugin change or deployment has occurred.
- Public business-directory search can find references to a business named Rèm Quốc Anh, but those third-party descriptions are not treated as authoritative website/domain state and are not written into Theme data.

## C1 attempts

### Public web retrieval

Direct retrieval of `https://remquocanh.vn/` through the available web path returned an upstream `502 Bad Gateway`.

Search for the exact domain did not return an indexed copy sufficient to inventory current WordPress pages, theme/plugin state, UX Builder content or current visual DOM.

### Independent network check

A separate execution environment could not resolve `remquocanh.vn` or `www.remquocanh.vn` at the time of the check. HTTP/HTTPS header requests therefore could not proceed.

This is access evidence only; it does not prove the domain is globally offline.

### Authenticated WordPress path

The pilot is not among the currently connected WPVibe sites.

A one-click WordPress authorization flow was generated for `https://remquocanh.vn`, but completion requires the site owner to approve it in WordPress. No credentials were requested or entered in chat.

The WPVibe response also reported that its site plugin is not currently installed. REST-backed content reads may work after authorization; deeper file/theme/WP-CLI inventory would additionally require the WPVibe plugin or another approved authenticated mechanism.

## UNKNOWN / not yet proven

Until authenticated/public access succeeds, the following remain UNKNOWN:

- exact Flatsome version;
- child-theme state;
- active plugin list;
- WordPress/PHP version;
- Front Page assignment;
- menu/widget assignments;
- current Pages/Posts/Categories;
- WooCommerce catalogue/product/category/shop-page state;
- exact UX Builder / Flatsome shortcode footprint;
- which content is portable as native WordPress data versus trapped in builder presentation;
- current desktop/tablet/mobile screenshot baseline;
- exact URLs/permalinks that must remain stable;
- current RootProfile/ConvertFlow public capability state on the pilot;
- safe Flatsome rollback identity for production cutover.

## Gate result

**C1/M0: BLOCKED_EXTERNAL_ACCESS.**

Do not claim Rèm Quốc Anh parity, migration readiness, Flatsome retirement readiness, runtime PASS, browser PASS or production cutover readiness from the generic design alone.

## Safe next

Complete the one-click WordPress authorization for `remquocanh.vn`.

After authorization, run read-only inventory in this order:

1. site/theme/plugin/runtime metadata;
2. Front Page, menus, Pages, Posts, Categories and Media inventory;
3. WooCommerce public catalogue/shop state if present;
4. exact search for Flatsome/UX Builder shortcodes in WordPress-owned content;
5. representative route/DOM/screenshots;
6. classify each surface as KEEP / MAP / REBUILD PRESENTATION / OWNER CONTRACT / RETIRE LATER.

No production mutation is required for C1.
