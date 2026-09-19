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

At this checkpoint:
- static/core job: **SUCCESS**;
- clean runtime/browser job: **IN PROGRESS**.

No exact-main PASS is claimed until the complete workflow reaches SUCCESS.

No separate X6 push run was observed for this merge at this checkpoint. Prior PR final-head regression evidence remains retained, but is not relabeled as a fresh exact-main X6 run.

## Release boundary

Canonical repository Theme metadata remains `1.3.10`.

Published GitHub Release and verified production deployment remain historical `v1.3.0`. This merge does not authorize tag, GitHub Release or production deployment.

RootProfile authoritative Team projection remains separately blocked under issue #112.

**NEXT:** wait for exact-main run `35412829723` to complete. If SUCCESS, update this closure and source documents to exact-main PASS. Publication/deployment remain separate explicit approval gates.
