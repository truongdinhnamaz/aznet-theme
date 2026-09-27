# Curtain 01 About presentation authoring correction — 27/09/2026

## Scope

Bounded D-041 backend-completeness correction after PR #282 closure. No architecture, ownership, public contract, release or deployment change.

Canonical base: `main@e13647e031f9630e3890b169b7feebb8aa00473b`, Theme metadata `1.3.54`.

## Root cause

The Curtain 01 About frontend renders four Theme-owned presentation values in addition to the WordPress-owned About Page excerpt/content:

- `homepage_about_kicker`;
- `homepage_about_heading`;
- `homepage_about_quote`;
- preset-scoped `about_image` reference.

The unified Homepage Map exposed Page excerpt/native Page editing, but no authoring path for those Theme-owned presentation values. The rendered frontend therefore had editable state that was not reachable from the primary Homepage backend.

## RED -> GREEN

`tests/offline/curtain01-about-presentation-authoring-contract.php` was committed before implementation.

GREEN candidate:

- adds `Sửa phần giới thiệu` inside the effective Curtain About card;
- exposes kicker, presentation heading, quote and About presentation image;
- preserves WordPress ownership of Page title/excerpt/content;
- writes image through the preset-scoped `about_image` key;
- merges only the bounded Theme-owned presentation fields into existing settings and normalizes through the existing schema;
- uses nonce + `edit_theme_options` capability checks;
- redirects back to `#homepage-map-about` after save;
- adds retained browser assertions and core-contract wiring.

## Verification

- runner-independent exact-branch source verification: **PASS, 0 failures**;
- isolated `remquocanh.vn` WPVibe draft accepted all three changed admin PHP files;
- draft compile-source endpoint completed without reported compile error;
- fresh public draft readback remained healthy: one `main`, no WordPress critical error, Theme assets `ver=1.3.54`, exact ten-surface Curtain order unchanged.

Authenticated draft-admin interaction remains UNKNOWN because the WPVibe preview token still does not authenticate the draft Theme into wp-admin.

## Safety

- Live `remquocanh.vn` remains Theme `1.3.31`.
- No draft publish, production Theme switch, release publication or production content mutation occurred.


## Merge closure

- Owner-approved PR #283 merged by squash to canonical `main@9794080339dedd3950aa390a87107088e8df204c`.
- Reviewed head `a7470bba9f951aabaf4f7e032f4fe6ce7cae12bc` and merged main share exact tree `374f795a7ffcd8399d91eed2ec654e4055c8e4ac`.
- Direct exact-main source verification repeated after merge: **PASS, 0 failures**.
- L3 authenticated draft-admin interaction remains **UNKNOWN**.
- No release publication or production deployment is claimed.
