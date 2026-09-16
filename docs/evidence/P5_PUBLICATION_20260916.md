# P5 v1.1.0 Publication Closure — 2026-09-16

## Scope

Owner-approved publication semantics for AZnet Theme `1.1.0`: Git tag + GitHub Release only. No production-site deployment and no optional-provider L5 certification.

## Canonical publication target

- canonical source commit: `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`;
- canonical source tree: `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`;
- Theme metadata: `1.1.0`;
- final verified package: `aznet-theme-1.1.0.zip`;
- expected/final SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`.

## Publication execution

GitHub Actions run `35052694111` completed SUCCESS. Before publication it reverified the exact canonical target, ran `scripts/verify-v1-core.sh`, rebuilt the package twice byte-identically, asserted the expected SHA-256, exact-compared package/source bytes, verified 121 production files and linted 93 packaged PHP files.

The same run then published and re-read the public GitHub state:

- annotated tag: `v1.1.0`;
- tag object: `3c3e07fd0ba64037f7ec1d79fb6e6276308786a3`;
- tag dereferenced commit: `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`;
- GitHub Release ID: `389622362`;
- release title: `AZnet Theme 1.1.0`;
- release state: `draft=false`, `prerelease=false`;
- release asset ID: `567100593`;
- release asset: `aznet-theme-1.1.0.zip`;
- release asset size: `170944` bytes;
- GitHub asset digest: `sha256:72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`;
- publication evidence artifact: `10428918492`;
- publication evidence artifact digest: `sha256:544895a168e12b7260e086b14f85ecfb3ae4505fcc600feff7d27bbbd6f88814`.

The workflow downloaded the newly published release asset and re-hashed it, matching the expected final package SHA-256.

## Operational cleanup

Publication was executed from isolated branch `ops/publish-v1.1.0`. The temporary publication workflow was removed immediately after the successful run. Cleanup commit `6c7d270bcc5a379522f3927f4101474573fa3f43` has zero net changed files compared with tagged canonical commit `7dbbb0e8...`; no publication helper was merged into `main`.

## Boundary

Publication PASS does not imply final production deployment. It also does not change RootProfile/ConvertFlow/WooCommerce ownership or certify optional-provider L5 integration.

## State

**P5 PUBLICATION PASS / PRODUCTION DEPLOYMENT GATED.**

## Next

Obtain a separate explicit owner decision before any final production deployment.
