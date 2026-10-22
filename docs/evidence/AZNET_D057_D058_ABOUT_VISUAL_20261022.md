# AZnet /gioi-thieu — D-057 Header + D-058 Landing in-place recovery (22 Oct 2026)

## User authorization, scope, and layer ownership

User explicitly directed **repair the existing published `https://aznet.vn/gioi-thieu` Page 18897 only**, no new Page. The subsequent "duyệt" approved continued Header desktop/mobile checks and the release route subject to QA/safety gates. WordPress native is the source owner of Page content, identity, revisions, media and permalink. AZnet Theme owns presentation CSS. Rank Math owns SEO metadata. No RootProfile, ConvertFlow or WooCommerce authoritative data was changed.

## WordPress evidence — published in place, no replacement Page

- Before this visual correction: Page ID **18897**, published slug `gioi-thieu`, title "Giới thiệu", native Gutenberg content **33,980 chars**, modified `2026-10-22 11:23:09` Asia/Bangkok, one H1 and no literal Flatsome shortcode. Original pre-migration `[section]` body preserved exactly in unpublished draft Page **43537**; separate authoring draft Page **43538** is also unpublished.
- To provide **explicit Theme-owned inverse presentation opt-in**, added class token `aznet-theme-page-inverse` to the four existing first-level core/group sections with original dark backgrounds `#101f2b` (3) or `#192e3b` (1), in both Gutenberg block attributes and saved HTML classes. Preserved the first block's `aznet-theme-page-hero` marker; no text, links, SVGs, IDs, ordering, title or SEO metadata changed. This was an in-place REST content update with WordPress revision; immediate WP-CLI read-after-write confirmed exact candidate equality.
- Current Page ID **18897**, slug `gioi-thieu`, `publish`, content **34,222 chars**, modified **`2026-10-22 11:48:28` Asia/Bangkok**, four opt-in sections (eight class occurrences in serialized Gutenberg), one H1, nine native Page areas, no Flatsome shortcode. Original permalink remains the same; featured-media ID 23485 and Page template Landing remain unchanged.

## Root causes proven on **real public WordPress page** (L4)

- Actual Theme **1.3.95** at 1366px: Header DOM has both native Primary and Utility WordPress menus with long item label. Production screenshot and Playwright rectangles showed Header horizontal overflow and page-level horizontal scroll.
- Landing Hero before Theme CSS repair at 1600px: `getBoundingClientRect().x=-800`, width 1600px; mobile 390px `x=-195`, width 390px. The generic `.aznet-theme-entry__content .alignfull` transform from `media.css` leaked into a Page shell which is already full-width. The Page-specific direct Group rule in `page.css` overrode width/margins but omitted the counteracting `transform:none`; user visibly saw half the Hero/sections offscreen.
- Dark Hero heading and paragraph elements declared native white inline, but browser `getComputedStyle` returned `rgb(23,24,27)` due WordPress legacy high-specificity `has-text-color` CSS. Adding `has-surface-color` dynamically did **not** fix the computed color; a scoped Theme `!important` color rule for the explicit `aznet-theme-page-inverse` sections did, without recoloring nested white cards.

## Bounded Theme candidate and verification

- GitHub Draft PR [#497](https://github.com/truongdinhnamaz/aznet-theme/pull/497), branch `work/d057-digital01-inner-header-overlap-20261022`. Modified only `assets/css/presets/digital-01.css` and `assets/css/components/page.css` for production presentation behavior; added two offline contracts, wired into V1 CI, and added browser workflow `.github/workflows/d057-live-header-l4.yml`.
- Chrome Playwright read-only public L4: baseline production compared to exact candidate CSS injected into the browser, no WP file/domain mutation. Run [39724695833](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39724695833) SUCCESS; uploaded screenshot/geometry artifact `12402194035`. **15 of 15** candidate viewports from **360–1920px** passed: official logo loaded, header no overlap/overflow, mobile Menu click/keyboard Escape worked, all first-level Landing groups anchored at x=0, Hero correct viewport width, native H1 white on inverse background. Full-page desktop 1600/mobile 390 PNGs captured and visually reviewed. This proves candidate-overlay L4 only, **NOT** live Theme release L4.
- One initial CI chain with hard-coded `#ffb49c` in generic Page CSS failed the existing Y2 token-governance test. Corrected to `color-mix(in srgb, var(--aznet-theme-on-inverse) 78%, var(--aznet-theme-accent) 22%)`; rerunning exact-head CI for `16f4c2c...` at the time of this checkpoint. Do not claim all 33 flows PASS without fresh final results.

## Hard gate: preserve an unrelated Theme draft without accidental deploy

- WordPress live Theme remains AZnet Theme **1.3.95**. The WPVibe **unpublished Theme draft** contains **236 files**; canonical `main@0cd22f0` runtime contains **235**. Exhaustive relative path-set comparison shows exactly one extra draft file: `assets/css/components/homepage-digital-01-brand-2026.css` (Brand Light 2026 opt-in, unrelated to the About fix). That file may represent separate in-progress user work. It is preserved unchanged at independent GitHub branch `work/digital01-brand-light-2026-preserve-20261022@a907c332`, and remains untouched on the WPVibe draft. **DO NOT silently delete or ship** it merely to pass an exact provenance gate.
- The Theme draft's modified `assets/css/components/page.css` is now byte-equal to the PR candidate; `assets/css/presets/digital-01.css` was previously updated from the PR candidate. No complete live production theme replace has been attempted. A Theme publish would overwrite the previous WPVibe Theme backup folder. An approved clean versioned release, recovery plan and new version gate are required first.
- Unresolved SEO owner fault: existing Rank Math `rank_math_canonical_url=https://aznet.vn/` on this About Page, Lighthouse SEO 92, A11y 96 (remaining contrast finding). Neither Page content rewrite nor Theme CSS attempted to mutate Rank Math direct storage. Existing authored marketing figures `200+`, `8 năm`, `99%` were preserved but not independently fact checked.

## PASS / BLOCKED / NEXT

**PASS:** published native Page ID/permalink/content source invariants (WordPress L0/L1), exact source archival recovery, candidate Theme Header+Landing public Chrome test at 15 viewport widths (candidate-overlay L4).  
**BLOCKED:** clean Theme release/deploy (pre-existing unrelated draft file + version/release gate), live Theme 1.3.95 still exhibits old Header/landing CSS.  
**UNKNOWN:** final 1.3.96 release and full cross-site QA, entire Theme production L4, exact Lighthouse failing node and Rank Math canonical remediation.

**EXACT NEXT:** Finish latest V1/offline/L4 CI and correct any legitimate regressions; obtain minimal hard-gate authorization for handling the single preserved unrelated Brand Light CSS file in the sole WPVibe Theme draft (or an independently safe clean packaging path). Only then prepare a clean, versioned 1.3.96 candidate with proper rollback/QA and authorized release to AZnet production. Re-check Page 18897 identity/SEO, Header, all nine native sections and mobile keyboard interactions after real deploy. No PR merge or release claim before its explicit gate.
