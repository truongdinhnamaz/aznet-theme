# AZnet Theme v1.3.0 Publication Closure — 2026-09-17

## Scope

Owner-approved publication semantics for AZnet Theme `1.3.0`: annotated Git tag + GitHub Release only. Production-site deployment remains a separate owner gate. Provider L5 certification/takeover is outside this publication action.

## Canonical publication target

- exact verified technical source commit: `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`;
- exact verified technical tree: `020db34ca6f360d5af12e6f15abf4c9ba51179f0`;
- post-technical docs-only canonical main before publication: `22e822a50c2f8ab764f6044838e8acce69f50846`, tree `d5ae69b1f96119afe3be72723e9ecdee978af91e`;
- Theme metadata: exact `1.3.0`;
- verified package: `aznet-theme-1.3.0.zip`;
- expected/final SHA-256: `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`;
- exact package file count: `144`;
- packaged PHP lint count: `107`.

The publication workflow proved that the exact technical target is an ancestor of the publication-time canonical main and that no production/package-relevant bytes changed after that verified target outside `docs/**`.

## Publication execution

Temporary isolated branch: `ops/publish-v1.3.0`.

Publication workflow run `35211254633`, job `105169031095`, completed `SUCCESS`. Before creating public release state it:

- checked out exact source `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`;
- revalidated canonical `main@22e822a50c2f8ab764f6044838e8acce69f50846`;
- confirmed version declarations are exact `1.3.0`;
- ran the reusable core verification;
- rebuilt `aznet-theme-1.3.0.zip` twice and required byte identity;
- required package SHA-256 `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`;
- exact-compared packaged production files against source;
- required exactly `144` package files and linted exactly `107` packaged PHP files;
- created/verified an immutable annotated tag;
- created/verified the GitHub Release;
- downloaded the published release asset and re-hashed it against the expected SHA-256.

All publication job steps completed SUCCESS.

## Published public state

Fresh GitHub readback after publication confirms:

- annotated tag: `v1.3.0`;
- annotated tag object: `56f2ae2e8ba6e1fb4e8359447656e88ab6411ba6`;
- tag object type: `tag`;
- tag dereferenced commit: `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`;
- tag message: `AZnet Theme 1.3.0`;
- GitHub Release ID: `390623960`;
- release title: `AZnet Theme 1.3.0`;
- release state: `draft=false`, `prerelease=false`;
- release asset ID: `570039299`;
- release asset: `aznet-theme-1.3.0.zip`;
- release asset size: `196953` bytes;
- GitHub asset digest: `sha256:4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`;
- publication evidence artifact: `10492411096`;
- publication evidence artifact digest: `sha256:0a18d2169ad8d92a587421289ede773031e56d3646ddac44ba326ebe4ea6fd75`.

The release notes preserve the ownership boundary: WordPress remains authoritative for Page/Post/Menu/Media/query state and lifecycle; optional RootProfile, ConvertFlow and WooCommerce integrations remain additive; provider L5 is not claimed.

## Operational cleanup

The temporary publication workflow was removed from `ops/publish-v1.3.0` immediately after publication at cleanup commit `b4dea5c0ed23ae48c44fa86fa80c0bd0525d59b7`. The cleanup branch has zero net file delta against the publication target even though branch history diverges from later docs-only `main` history.

Canonical `main` remained `22e822a50c2f8ab764f6044838e8acce69f50846` throughout publication.

## Boundary

Publication PASS does **not** imply production deployment. No production WordPress site was updated by this action. Provider L5 certification/takeover, destructive site operations and external-product code remain unclaimed.

## PASS

- annotated `v1.3.0` tag exists and dereferences to the exact verified Y5 technical commit;
- GitHub Release `AZnet Theme 1.3.0` is published and is not draft/prerelease;
- attached `aznet-theme-1.3.0.zip` digest equals the exact verified Y5 package digest;
- publication workflow completed SUCCESS and the temporary publication helper was cleaned from its ops branch.

## State

**v1.3.0 PUBLICATION PASS / PRODUCTION DEPLOYMENT GATED.**

## NEXT

Request a separate explicit product-owner approval before any production deployment of AZnet Theme `1.3.0`. Before deployment, revalidate the target site and exact published release asset; after deployment, perform fresh read-only authenticated/public verification before claiming production PASS. Provider L5 remains separate and unclaimed.
