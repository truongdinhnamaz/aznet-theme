# Curtain 01 category carousel — live production evidence

**Date:** 22/09/2026  
**Pilot:** `https://remquocanh.vn/`  
**Canonical implementation:** `main@e618786c44bd1b160abf32de15b226cd10c1d916`  
**Owner approval:** explicit — “xuất bản”

## Production action

The approved WPVibe draft was published to the live `aznet-theme` directory.

WPVibe created the automatic theme-file rollback copy:

- active Theme: `aznet-theme`, AZnet Theme **1.3.31**;
- rollback copy: `aznet-theme-wpvibe-backup`;
- WordPress object cache was flushed by the publish transaction.

No WordPress/WooCommerce/RootProfile/ConvertFlow domain data was mutated.

## Live runtime readback

Fresh authenticated production readback confirms:

- active Theme remains AZnet Theme **1.3.31**;
- the Homepage category showcase renders the new `data-aznet-curtain-category-carousel` marker;
- the rail has stable id `aznet-curtain01-category-track` and keyboard focusability;
- the four current valid WooCommerce root categories continue to render unchanged:
  - Rèm Cầu Vồng;
  - Rèm Cuốn;
  - Rèm Roman;
  - Rèm sáo.

Because production currently has exactly four eligible cards, previous/next controls are intentionally not rendered. The >4 interaction path was already verified on the exact implementation candidate with the six-category browser fixture before merge.

## QA disposition

**PASS**
- owner-approved live Theme publish;
- L3 authenticated production readback;
- active Theme/version continuity;
- category-showcase live markup;
- existing four category cards preserved;
- rollback copy created by WPVibe.

**UNKNOWN / not inferred**
- live production click/scroll interaction with >4 categories, because the current production dataset contains only four eligible category cards;
- independent pixel-level production L4 visual/a11y verification.

## Rollback

If presentation rollback is required, restore `aznet-theme-wpvibe-backup`. Domain data must not be rolled back for this Theme presentation change.
