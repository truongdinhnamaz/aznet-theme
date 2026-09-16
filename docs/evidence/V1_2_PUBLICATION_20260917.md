# AZnet Theme v1.2.0 Publication Closure — 2026-09-17

## Scope

Owner-approved publication semantics for AZnet Theme `1.2.0`: annotated Git tag + GitHub Release only. Production-site deployment remains a separate owner gate. Provider L5 certification/takeover is outside this publication action.

## Canonical publication target

- exact verified technical source commit: `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`;
- exact verified technical tree: `70c04929afe38e87d317bad3414157e9e2bd6f74`;
- post-technical docs-only canonical main before publication: `00061c5171ebd960e737a6367480844464cfabba`;
- Theme metadata: exact `1.2.0`;
- verified package: `aznet-theme-1.2.0.zip`;
- expected/final SHA-256: `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`.

The publication workflow explicitly proved that `c6b1ebac...` is an ancestor of `main@00061c...` and that there is no package/production-byte delta between them outside `docs/**`.

## Publication execution

Temporary isolated branch: `ops/publish-v1.2.0`.

Publication workflow run `35131383107` completed `SUCCESS`. Before creating public release state it:

- checked out exact source `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`;
- revalidated current canonical main `00061c5171ebd960e737a6367480844464cfabba`;
- confirmed version declarations are exact `1.2.0`;
- ran `scripts/verify-v1-core.sh` on PHP 8.1;
- rebuilt `aznet-theme-1.2.0.zip` twice and required byte identity;
- required package SHA-256 `cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`;
- exact-compared packaged production files against source;
- linted exactly `95` packaged PHP files;
- created/verified an immutable annotated tag;
- created/verified the GitHub Release;
- downloaded the published release asset and re-hashed it against the expected SHA-256.

All publication job steps completed SUCCESS.

## Published public state

Fresh GitHub readback after the workflow confirms:

- annotated tag: `v1.2.0`;
- annotated tag object: `62b701ed9e371b23d76b3259d459e76e824206c1`;
- tag object type: `tag`;
- tag dereferenced commit: `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`;
- tag message: `AZnet Theme 1.2.0`;
- GitHub Release ID: `390148740`;
- release title: `AZnet Theme 1.2.0`;
- release state: `draft=false`, `prerelease=false`;
- release asset ID: `568495178`;
- release asset: `aznet-theme-1.2.0.zip`;
- release asset size: `183131` bytes;
- GitHub asset digest: `sha256:cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798`;
- publication evidence artifact: `10461657247`;
- publication evidence artifact digest: `sha256:57c1400145ac09559e9b8974045b31d6fff3b3349e4cf702480df50fffc5b59c`.

The release notes explicitly preserve ownership boundaries: WordPress remains authoritative for native data/semantics and optional RootProfile, ConvertFlow and WooCommerce integrations remain additive; provider L5 is not claimed.

## Operational cleanup

The temporary workflow `.github/workflows/_ops-publish-v1.2.0.yml` was removed immediately after publication. Cleanup branch head `137c6d70dbd8d194495f9db2220ed8a9adb39ca6` has zero net file delta from publication target `c6b1ebacbf848b5a5e6b2d2f792d6aa0a8daefad`.

No publication helper is intended for canonical `main`.

## Boundary

Publication PASS does **not** imply production deployment. No production WordPress site was updated by this action. Provider L5 certification/takeover, destructive site operations and external-product code remain unclaimed.

## PASS

- annotated `v1.2.0` tag exists and dereferences to the exact verified X6 technical commit;
- GitHub Release `AZnet Theme 1.2.0` is published and is not draft/prerelease;
- attached `aznet-theme-1.2.0.zip` digest equals the exact verified X6 package digest;
- publication evidence workflow completed SUCCESS and cleanup has zero net code/file delta.

## State

**v1.2.0 PUBLICATION PASS / PRODUCTION DEPLOYMENT GATED.**

## NEXT

Request a separate explicit product-owner approval before any production deployment of AZnet Theme `1.2.0`. Before deployment, revalidate the target site and exact published release asset; after deployment, perform fresh read-only authenticated/public verification before claiming production PASS.
