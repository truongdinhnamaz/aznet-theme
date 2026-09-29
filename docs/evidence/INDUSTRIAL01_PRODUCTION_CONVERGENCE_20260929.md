# Industrial 01 Production Convergence — 2026-09-29

## Scope

This evidence closes the Industrial 01 pilot production checkpoint for `minhnguyen.vn` and records the separation between the deployed exact candidate and the later-moving canonical repository main.

Theme ownership remains presentation-only. No WooCommerce product truth, plugin domain state, RootProfile semantics, ConvertFlow routing/state, or parallel domain store was introduced.

## Repository candidate

- PR #331 head: `0da4af03af3d3362357734b7d0f1dd6a2b1449e3`
- Theme metadata: `1.3.57`
- Exact-head workflow result: **7/7 SUCCESS**
- Deterministic package: `aznet-theme-1.3.57.zip`
- Production files: **213**
- Packaged PHP lint: **149 PASS**
- Package SHA-256: `977330b813bebef21130be19cfa71c1953e117b117356723afad20efdf7e455c`
- Y5 artifact ID: `11044907457`
- Uploaded artifact digest: `0b3d8ab70b1d30f19092b9cc3eddff4d3d4086b0bb736fa577661a6f9f08aed9`

## Production publication

The owner explicitly confirmed a fresh site-wide/host backup and then explicitly approved publication.

WPVibe published the reviewed draft to live `aznet-theme` and retained the previous live Theme directory as `aznet-theme-wpvibe-backup`.

Fresh post-publish state:

- Site: `https://minhnguyen.vn`
- Active Theme: `aznet-theme` **1.3.57**
- Rollback Theme files: `aznet-theme-wpvibe-backup` **1.3.53**
- WordPress: **7.1.2**
- PHP: **8.2.33**
- Maintenance mode: **OFF**
- Rank Math sitemap cache purged and object cache flushed by the publish operation

Fresh production runtime verification:

- Industrial 01 Homepage: PASS
- Woo Shop: PASS, **168 results**
- Representative Woo Product: PASS
- Header/Footer: PASS
- No Theme-owned PHP/runtime blocker observed in the tested surfaces

Fresh mobile Lighthouse on live production:

- Performance: **100**
- Accessibility: **100**
- Best Practices: **100**
- SEO: **100**
- LCP: **1.4 s**
- CLS: **0**
- TBT: **0 ms**
- FCP: **1.4 s**
- Speed Index: **2.2 s**
- Remaining non-blocking opportunity: about **11 KiB** unused CSS

## Provenance boundary

Canonical repository `main` later advanced to `22632cbc4cdf41f447a1050d44ef1785098cf13d` while retaining Theme metadata `1.3.57`.

A fresh comparison shows the deployed candidate and current main are diverged. Current main contains later production changes, including Footer/asset code. Therefore:

- production is bound to the exact candidate/package above;
- current-main byte identity is **not** claimed;
- current published GitHub Release remains `v1.3.23`;
- no new tag/GitHub Release is inferred;
- no provider L5 certification is inferred.

PR #330 and PR #331 are superseded as convergence vehicles by the source-only production-convergence reconciliation branch.

## Known non-Theme item

The representative Product still contains a legacy image URL under `test-minhnguyen.aznet.vn`. This is WordPress/WooCommerce content data and was intentionally not mutated by the Theme production slice.
