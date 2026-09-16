# P5 Production Deployment Read-Only Verification — 2026-09-16

## Scope

The product owner explicitly approved recognizing the existing `tamduchanoi.aznet.vn` installation as the production deployment of AZnet Theme `v1.1.0`, without re-uploading identical package bytes, followed by read-only verification only.

No Theme upload/update/switch/delete, settings save/reset/import, provisioning, plugin mutation, content publish/delete, or provider mutation was performed by this verification.

## Release identity

- published tag: `v1.1.0`;
- release source anchor: `7dbbb0e8b41c4cb324b04cf6e538e38cb6cf7b78`;
- release source tree: `740406a02ea77a4cb6fdd8f2ee98b7b88f909560`;
- release asset: `aznet-theme-1.1.0.zip`;
- release asset SHA-256: `72ec808b0e35b27fae30b9faf0b470b7fa85748c40f9fbc15e0e82ffb35f2258`;
- verification harness checkout: `main@e7e5a9c2d2a867f631b029f3f77a675e32fad573`, tree `d50c9f04465f7f6990ac3747ee55a848e3187beb`.

The release identity was re-read from GitHub during the verification run. The release asset digest remained identical to the package SHA previously installed on the pilot under the documented P4 replacement chain of custody. No live-filesystem checksum is claimed.

## Fresh production verification

GitHub Actions run `35054176392` completed SUCCESS using the retained read-only harnesses:

- P1 Theme inventory/System Health: `NO_DUPLICATES`; exactly one active AZnet Theme `1.1.0`; inactive candidates `0`; ambiguous identities `0`; mutation flag `false`;
- authenticated Control Center/System Health: PASS; D-027 current report shape; Theme `1.1.0`; Standalone Core `ready`; retained desktop/mobile admin overflow/focus/a11y checks PASS;
- public production matrix: 28 route/viewport checks PASS; RootProfile surface `NOT_OBSERVED_ON_TESTED_ROUTES`; authored-content a11y observations `0`.

Evidence artifact: `10430017585`, digest `sha256:c2066532e341f6243f466f5ed885822e5428d6ec344a0f74af20b9247921785b`.

## Provenance note

After PR #72 merged at `15f25e4f6d8e64c588866405629b793a85ee0323`, an accidental root-level `noop` sentinel was committed and immediately reverted. Restored checkpoint `e7e5a9c2d2a867f631b029f3f77a675e32fad573` has tree `d50c9f04465f7f6990ac3747ee55a848e3187beb`, identical to PR #72 merge tree; compare from `15f25e4f6d8e64c588866405629b793a85ee0323` to `e7e5a9c2d2a867f631b029f3f77a675e32fad573` contains zero changed files. No implementation byte or release asset was changed.

## Boundary

Deployment PASS is limited to the Theme-owned and WordPress/site-operations surfaces actually verified. It does not infer RootProfile, ConvertFlow or WooCommerce provider L5 certification and does not transfer provider/domain ownership to the Theme.

## Result

**P5 PRODUCTION DEPLOYMENT PASS** for `tamduchanoi.aznet.vn` under the owner-approved existing-package deployment disposition.
