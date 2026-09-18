# v1.3.3 Corrective Inheritance Closure — 18/09/2026

Repository: `truongdinhnamaz/aznet-theme`

## Purpose

Correct the package lineage error in the previously delivered ZIP. The erroneous delivery was built from the canonical 1.3.1-era production tree and therefore did not include the approved-but-unmerged v1.3.2 Law 01 unified heading work.

v1.3.3 is the corrective package that combines:

- the v1.3.2 Law 01 unified section-heading system;
- all later canonical Law 01 presentation/accessibility/Footer closure from PR #129-#131;
- the current canonical Theme tree and retained WordPress-native ownership boundaries.

No WordPress content/domain storage is moved into Theme ownership.

## RED evidence

PR #133 first changed the release contracts to require v1.3.3 identity while production metadata/workflows still exposed 1.3.1.

Y5 run `35317606392`, offline job `105512614875`, failed for the intended reason:

`Y5 promoted style.css must be exactly 1.3.3`

This reproduced the release-lineage defect at the shallowest package/version boundary.

## GREEN implementation

PR #133 final verified head:

`905c61bd9a2f1ec88e9750f91bc00f607158401d`

The candidate:

- restores the v1.3.2 Law 01 section-heading scale `clamp(1.35rem, 1.55vw, 1.65rem)`;
- keeps desktop Law 01 section H2s single-line with responsive wrapping restored at <=960px;
- preserves the later Burgundy closure, media-rich QA/accessibility work and Professional Footer order;
- promotes `style.css` and `AZNET_THEME_VERSION` to exactly `1.3.3`;
- updates deterministic-package/runtime release gates for v1.3.3;
- retains explicit historical 1.3.2 promotion support in the X6 boundary;
- aligns stale provisioning/browser expectations with the already-approved Burgundy composition.

## Exact-head verification

All direct final-head workflows observed for PR #133 completed SUCCESS, including:

- V1 Core Pull Request CI `35318352688`;
- Release Version Consistency Gate `35318352774`;
- Homepage Composer Law 01 Browser Quality `35318352687`;
- Law Site Provisioning Browser Quality `35318352797`;
- Law Site Provisioning Candidate Package `35318352707`;
- F Homepage Native Runtime Browser `35318352779`;
- F Homepage Native Regression Package `35318352725`;
- D-027 Standalone Core L4 `35318352690`;
- D-027 Standalone Core Exact Package `35318352898`;
- D-027 Retained Full Regression `35318352776`;
- G Core Lifecycle Update Switch Rollback `35318352838`;
- R1 Visual Preset Feasibility `35318352689`;
- R1 Design System Browser Parity `35318352839`;
- R2 Pattern Library Browser Quality `35318352785`;
- R6 Performance Baseline `35318352983`;
- X3 Media Gallery Embed `35318352719`;
- X4 Pagination Navigation `35318352794`;
- X6 Cross-surface Release Closure `35318352677`;
- Y5 Client Delivery Release `35318352870`.

## Deterministic package

Y5 produced the exact promoted package:

- file: `aznet-theme-1.3.3.zip`;
- production files: 145;
- packaged PHP lint: 108/108 PASS;
- SHA-256: `172a98dc00a52228fbb44a6ad24b79d568b7b543faa484c1ded59511b33e35d7`;
- artifact: `10536675039`;
- artifact wrapper digest: `sha256:6001333d0d20c9ec3c249be8b96e21c73e56f388d6669e852b6c0a5abdbaf760`.

The exact-package lifecycle verified WordPress-owned Page/Post/Menu continuity through switch-away to Twenty Twenty-Five and switch-back to AZnet Theme.

## Canonical merge and exact-main verification

Owner-approved PR #133 squash-merged to:

`main@a66ef1639c299a06e62e9797aa2f44fe4e26cd8d`

Fresh exact-main verification:

- V1 Exact Main Verification `35318696376`: SUCCESS;
- X6 Cross-surface Release Closure `35318696388`: SUCCESS.

## Boundary

- The current canonical repository Theme metadata is now `1.3.3`.
- The previously published GitHub Release and previously verified production deployment remain `v1.3.0`; this corrective source/package checkpoint does not rewrite those historical facts.
- No Git tag/GitHub Release for v1.3.3 is implied.
- Manual website update using the verified v1.3.3 ZIP is a separate site-operations action performed by the product owner.
- RootProfile Team projection remains externally blocked under issue #112.

## Next

Provide the exact verified `aznet-theme-1.3.3.zip` to the product owner for the requested manual website update. Do not use the previously delivered 1.3.1-lineage ZIP.
