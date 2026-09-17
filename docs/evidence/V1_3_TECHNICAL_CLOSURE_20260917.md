# AZnet Theme v1.3 Technical Closure — 17/09/2026

**State:** **TECHNICAL PASS / PUBLICATION GATED**

## Scope

This checkpoint closes the technical implementation and exact-main verification of **AZnet Theme v1.3 Client Delivery System**. It does not publish an annotated `v1.3.0` tag, create a GitHub Release, deploy to production, or claim provider L5 certification.

Ownership remains unchanged: WordPress owns Page/Post/Menu/Media/query state and lifecycle; AZnet Theme owns presentation/composition and bounded consumer adapters only. No private provider storage/API read, copied domain engine, or mandatory third-party runtime dependency is introduced.

## Canonical merge

- PR #98: `test: close v1.3 client delivery candidate`
- exact PR head: `441b5fdbac3847b8c251b6c492a72883a4e31c7e`
- PR-head tree: `020db34ca6f360d5af12e6f15abf4c9ba51179f0`
- owner-approved merge commit / canonical `main`: `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`
- canonical merge tree: `020db34ca6f360d5af12e6f15abf4c9ba51179f0`
- Theme metadata: `1.3.0` in both `style.css` and `AZNET_THEME_VERSION`

The exact PR head and merge commit share the same tree, so the owner-approved merge introduced no content drift.

## Pre-merge promoted Y5 evidence

Exact promoted PR head `441b5fdbac3847b8c251b6c492a72883a4e31c7e` completed the full pull-request verification set successfully. The dedicated **Y5 Client Delivery Release** run `35205992836` passed all three jobs: promoted ownership/static closure, WordPress 6.9 / PHP 8.1 integrated runtime-browser coverage, and deterministic exact-package lifecycle verification.

## Exact-main evidence

### V1 Exact Main Verification

Run `35206627102` — **SUCCESS** on exact `main@afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e`.

- `static-contracts`: SUCCESS
- `clean-runtime-browser`: SUCCESS
- static artifact `10489329396`, digest `sha256:65a833786903e45d8778ebcff28a4015516762ca83379d081ec00f5303a7e80b`
- runtime/browser artifact `10489294556`, digest `sha256:150fa55cea948236d59ee05cbddb68863e2b4a67cd856a50546d2dfeb5c1a082`

### Equivalent exact-main Y5 release path

Task 10 permits the Y5 workflow on the merge SHA **or an equivalent explicit exact-main dispatch path defined before merge**. The pre-existing push-to-main **X6 Cross-surface Release Closure** workflow provides that exact-main release path and ran on the merge SHA as run `35206626992` — **SUCCESS**.

Exact-main X6 evidence:

- checkout SHA `afa8f4eb80d83d6c8111a9dc1bc57c13c6e24b8e` / tree `020db34ca6f360d5af12e6f15abf4c9ba51179f0`
- WordPress `6.9`, PHP `8.1`, zero active third-party plugins
- Theme metadata `1.3.0` consistent
- retained Core plus Y1/Y2/Y3/Y4/Y5 contracts PASS
- integrated browser/axe matrix: **32/32 PASS**
- deterministic package built twice: `aznet-theme-1.3.0.zip`
- package file count: **144**
- package SHA-256: `4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e`
- exact package/source identity: PASS
- packaged PHP lint: **107 files PASS**
- switch to Twenty Twenty-Five preserved WordPress-owned content; switch back to AZnet Theme and repeated smoke: PASS
- runtime error boundary: PASS
- artifact `10489369655`, digest `sha256:92bb97dc6640040ef0a9e8b77a1261a4a77df75ae1354d50c4586277d5d1ddec`

## Release boundary

The canonical repository technical state is now **v1.3 TECHNICAL PASS / PUBLICATION GATED**. The currently published and production-deployed release remains **v1.2.0** until separate owner approvals change those states.

Provider L5 remains separate and unclaimed. RootProfile/ConvertFlow/WooCommerce domain ownership is unchanged.

## Exact next

Request separate product-owner approval for **v1.3.0 publication disposition — annotated tag + GitHub Release only**. Production deployment remains a later, separate approval gate.
