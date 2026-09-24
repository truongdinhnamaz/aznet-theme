# Curtain 01 Hero performance production checkpoint — 24/09/2026

**Pilot:** `https://remquocanh.vn/`  
**Theme:** AZnet Theme `1.3.31`  
**Canonical implementation:** `main@9b93fd76f7d3238734bec58e247bf98d61851618`  
**Fix PR:** #266  
**Exact PR head:** `9204b4efd9bf7009d0299dde9beeeac4da526df2`

## Scope

This checkpoint records one Theme-owned production correction for the WordPress-owned three-image Curtain 01 Hero.

Theme ownership remains presentation-only:

- WordPress owns Hero block #991 and Media #669/#670/#671;
- Theme owns pre-paint slide geometry, progressive cinematic enhancement and rendered image presentation attributes;
- no Hero content, media attachment, provider/domain state or authoritative source mapping was mutated by the fix.

## Production problem reproduced

Fresh mobile Lighthouse before the fix reproduced a material performance regression twice:

- Performance: **49/100**;
- CLS: **0.902**;
- LCP: **21.3 s**, then **12.6 s** on repeat;
- page transfer was about **6.36 MiB**;
- Accessibility and Best Practices were already **100/100**.

The live Hero initially rendered three 620px Cover blocks in normal document flow. Cinematic JavaScript later converted them to one overlay stage, causing a large layout jump. The manual synced-block render path also emitted the original image `src` values without the responsive/priority metadata required by the new presentation contract.

The three source PNGs remain WordPress-owned and are approximately 1.8–2.0 MiB each. The fix does not rewrite or replace those attachments.

## RED -> GREEN evidence

### RED

Commit `b0c068df54d53e51ed5a8fa53c0402b2877bee67` added the regression contract first.

V1 Core PR CI run `35887050275` failed at the intended assertion:

`FAIL: Curtain 01 Hero must expose stable pre-paint slide geometry and responsive loading metadata: data-aznet-curtain-slide-count`

### GREEN

PR #266 then added the minimal Theme-owned correction:

- server-resolved `data-aznet-curtain-slide-count` for multi-cover Hero blocks;
- CSS grid overlay geometry before JavaScript initializes;
- secondary slides hidden in the pre-init/no-JS presentation state;
- intrinsic width/height;
- WordPress-generated `srcset` and `sizes`;
- primary image `loading="eager"` + `fetchpriority="high"`;
- secondary images `loading="lazy"` + `fetchpriority="low"`;
- `decoding="async"`;
- explicit bounded KSES allowance so those presentation attributes survive sanitization.

An intermediate GREEN attempt correctly failed C5 because KSES stripped the new attributes. Final exact head `9204b4efd9bf7009d0299dde9beeeac4da526df2` fixed that root cause without bypassing the sanitization boundary.

## Repository verification

Exact PR head completed **20/20 triggered workflows SUCCESS**, including:

- V1 Core Pull Request CI `35887972715`;
- C5 Curtain 01 Pilot-shaped Preview `35887972789`;
- R6 Performance Baseline `35887973467`;
- X4 Pagination Navigation `35887973099`;
- F Homepage Native Runtime Browser;
- D-027 L3/L4/Exact Package/Retained Full Regression;
- Curtain 01 C2/C3/Process Rail/Project Showcase/Proof Strip;
- X3, R1, R2 and lifecycle/package regressions.

Owner-approved merge created canonical `main@9b93fd76f7d3238734bec58e247bf98d61851618`.

Fresh post-merge verification also completed:

- V1 Exact Main Verification `35888794704`: **SUCCESS**;
- X6 Cross-surface Release Closure `35888794683`: **SUCCESS**;
- Curtain 01 Project Showcase `35888794974`: **SUCCESS**.

## Production publication

After owner approval, WPVibe cloned the active `1.3.31` Theme into a draft, applied only the PR #266 Hero changes, generated a private preview and verified the real Hero output before publication.

Preview/live verification confirmed:

- `data-aznet-curtain-slide-count="3"`;
- Media #669 is eager/high priority;
- Media #670/#671 are lazy/low priority;
- all three images expose width/height, `srcset`, `sizes` and async decoding.

The owner then explicitly approved publication. WPVibe published the draft to the active `aznet-theme` directory and created/retained `aznet-theme-wpvibe-backup` as the immediate theme-file rollback.

Fresh production readback after publish still reports:

- active Theme: AZnet Theme `1.3.31`;
- WordPress `7.1.2`;
- PHP `8.1.34`;
- Hero block/media ownership unchanged;
- expected Hero responsive/priority attributes present on the public Homepage.

## Fresh production performance after publish

Fresh mobile Lighthouse after publication:

- Performance: **86/100**;
- LCP: **4.2 s**;
- CLS: **0**;
- TBT: **0 ms**;
- FCP: **1.2 s**;
- Speed Index: **2.3 s**.

Lighthouse reported no field/CrUX data for this site, so these are lab measurements only. The remaining reported opportunity was about **11 KiB** of unused CSS; no further production mutation is implied by this evidence.

The material outcome at this checkpoint is the removal of the reproduced CLS regression and a substantially better lab performance result. Small Lighthouse score changes must not be overinterpreted because lab runs vary.

## Rollback

Immediate rollback remains available through the WPVibe-created `aznet-theme-wpvibe-backup` theme-file copy. No database/content rollback is required for this fix because the production mutation was limited to Theme files.

## Boundary / unknowns

- Current published GitHub Release remains `v1.3.23`; this pilot-specific production correction does not silently create a new Release.
- Provider-specific L5 remains separate.
- Independent external pixel-level L4 remains separate.
- Dedicated public-host Vietnamese font loaded-state verification remains separate unless freshly proven elsewhere.
