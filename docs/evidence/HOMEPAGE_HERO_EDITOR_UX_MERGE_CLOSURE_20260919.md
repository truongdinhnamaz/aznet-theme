# Homepage Hero editing UX — canonical merge closure

**Date:** 19/09/2026
**Repository:** `truongdinhnamaz/aznet-theme`
**Merged PR:** #151
**Final verified PR head:** `b17532c0bc7348317c5044fa25262d28cb9fef54`
**Canonical merge:** `main@39df23c3385118888e262c27a2331de34dda430d`

## Merge result

The product owner explicitly approved canonical merge of PR #151.

PR #151 merged successfully to `main@39df23c3385118888e262c27a2331de34dda430d`.

Git comparison from the final verified PR head to the merge commit reports zero file delta. The merge commit therefore preserves the exact verified PR tree while adding only merge ancestry.

## Verified pre-merge state

Final PR head `b17532c0bc7348317c5044fa25262d28cb9fef54` completed **22/22 triggered workflows SUCCESS, 0 failure**.

The production/test content had already been verified on reconciled head `82073e715be349f29fe84dbd0405c5af6e1b5c93` with 19/19 workflow success. Later commits synchronized source/evidence and removed Markdown trailing whitespace only; no production/test bytes changed after that functional verification.

## Ownership retained

- WordPress owns Hero Page title, excerpt, body and featured image.
- AZnet Theme owns presentation, the typed `homepage_hero_page` reference and the Control Center authoring bridge.
- No Theme-owned Hero title/subtitle/body store exists.
- No RootProfile, ConvertFlow or WooCommerce ownership/public-contract change is introduced.

## Fresh exact-main verification

Fresh `V1 Exact Main Verification` run `35412829723` was emitted for exact merge SHA `39df23c3385118888e262c27a2331de34dda430d`.

The full workflow completed **SUCCESS** on exact merge SHA `39df23c3385118888e262c27a2331de34dda430d`.

Artifacts:
- static/contracts: `10575125302`, digest `sha256:0fae4559000ac195e6f58a5fd50b1070b232c74bd472dd90c36d6abb0a15cb92`;
- clean runtime/browser: `10575290283`, digest `sha256:a2609a4ffee6f7c538402a4850103d5c14f6baa51fe6dceea4f61030ecba9e89`.

Fresh exact-main V1 is therefore **PASS** at its defined L1-L4 core verification scope.

No separate X6 push run was observed for this merge. Prior PR final-head regression evidence remains retained, but is not relabeled as a fresh exact-main X6 run.

## Release boundary

Canonical repository Theme metadata remains `1.3.10`.

Published GitHub Release and verified production deployment remain historical `v1.3.0`. This merge does not authorize tag, GitHub Release or production deployment.

RootProfile authoritative Team projection remains separately blocked under issue #112.

**NEXT:** merge the source-only PR carrying this closure after explicit owner approval. No tag, GitHub Release or production deployment is authorized by this checkpoint.
