# Curtain 01 — cinematic Hero, About typography and exact-main regression evidence

**Date:** 22/09/2026  
**Pilot:** `https://remquocanh.vn/`  
**Canonical exact-main anchor:** `66c734dc285b6e1e4a1b39e3074466fd4b866818`  
**Owner approvals:** explicit — “xuất bản hero 5 giây”, “Xuất bản sửa chữ”, “duyệt merge #240”, then approval to close the exact-main checkpoint.

## Live presentation state

The live pilot remains on AZnet Theme **1.3.30** with `aznet-theme` active and `aznet-theme-wpvibe-backup` retained. Flatsome and Flatsome Child remain inactive.

Fresh authenticated live checks confirm:

- `homepage_hero_block = 991`;
- Hero synced block #991 is published and renders WordPress Media #669, #670 and #671;
- Curtain 01 cinematic motion asset is present;
- the Hero autoplay interval is 5 seconds with the retained soft crossfade implementation;
- About title remains the WordPress Page #35 title `Giới thiệu về Rèm Quốc Anh`;
- About summary is the explicit Page excerpt, not an auto-trimmed projection of complex Page HTML;
- the prior noisy summary fragments such as “Bắt đầu câu chuyện”, “Khám phá các dòng rèm” and “2000” are absent from the Homepage About projection;
- no visible Flatsome UX Builder shortcode leakage was found in fresh Homepage DOM output.

## D-036 heading-flow closure

D-036 is now canonical Theme governance: on desktop, a heading must not be forced onto a second line by an artificial decorative width cap when the text fits within its actual available container width.

The Curtain 01 implementation therefore:

- removes the desktop `max-width` cap for its principal H1/H2 presentation surfaces;
- preserves natural wrapping when the real container is insufficient;
- does not use `white-space: nowrap` to manufacture one-line output or overflow;
- retains responsive wrapping behavior for smaller viewports.

The browser regression measures a heading's intrinsic single-line width against its real rendered parent width and fails when the text fits but the rendered heading still occupies multiple lines.

## Exact-main verification

### V1 Exact Main Verification

Run **35702406349** on exact main SHA `66c734dc285b6e1e4a1b39e3074466fd4b866818` completed **SUCCESS**.

- static-contracts: SUCCESS;
- clean-runtime-browser: SUCCESS.

### X6 Cross-surface Release Closure

Run **35702406200** on the same exact main SHA completed **SUCCESS**.

All recorded X6 steps completed successfully, including:

- L1-L2 static and retained contracts;
- WordPress 6.9 runtime installation and exact Theme candidate;
- cross-surface runtime and asset-boundary verification;
- integrated Playwright/axe browser verification;
- deterministic package build;
- exact package/source identity and packaged PHP lint;
- exact-package lifecycle and rollback verification;
- ownership/promotion-boundary verification.

Artifact:

- name: `x6-cross-surface-release-evidence`;
- artifact ID: **10683625067**;
- digest: `sha256:4b3a7f256d3fc9ba7246a543c330db08dd2bc34f9b466488af7324bf49be0cb3`.

## QA disposition

**PASS**

- D-036 Theme source/contract closure;
- exact-main static/contract verification;
- exact-main clean WordPress runtime/browser regression;
- X6 integrated browser/a11y/package/lifecycle regression;
- fresh authenticated live runtime/DOM smoke for the current Hero/About projection;
- rollback availability through the retained Theme-file backup and prior WordPress content revisions.

**UNKNOWN / not inferred**

- independent pixel-level visual parity on the protected production hostname outside the authenticated WPVibe path;
- provider-specific L5 certification.

## Rollback

If a presentation rollback is needed:

1. set `homepage_hero_block` from #991 back to retained Hero block #985 if only the cinematic Hero must be reverted;
2. restore the prior Theme directory from `aznet-theme-wpvibe-backup` when Theme files must be rolled back;
3. restore prior WordPress revisions/settings only for the affected WordPress-owned presentation content;
4. do not roll back WooCommerce, RootProfile or ConvertFlow domain data for a Theme presentation rollback.
