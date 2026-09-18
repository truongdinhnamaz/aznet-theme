# Law 01 visual parity — Hero / Trust / Services closure

**Date:** 18/09/2026  
**Scope:** Presentation-only visual parity against the owner-approved law-firm homepage reference.  
**Canonical PR:** #139

## Target

The approved slice focused only on the first visual band of Law 01:

- Hero proportion and hierarchy;
- four-item trust strip;
- six-card Services rhythm;
- responsive geometry at wide desktop, compact desktop and mobile.

No new business/domain semantics or provider ownership were introduced.

## RED → GREEN

The visual-parity contract was written RED first. The initial failure proved that the existing Hero still used the previous `44/56` split rather than the approved near-balanced target.

The GREEN implementation then established:

- wide desktop Hero split: `48% / 52%`;
- Hero visual band: `clamp(29rem, 32vw, 34rem)`;
- compact trust-strip rhythm with four items on desktop and responsive stacking;
- Services: six cards on one row above 1180px;
- Services: responsive 3+3 layout at compact desktop;
- Services: one card per row on mobile;
- compact service-card density: `min-height: 12.25rem`.

Browser geometry assertions were added so the layout is verified at runtime rather than only by static CSS matching.

## QA and provenance

- Final PR #139 tested head: `b7947bf2722db6bde8fd1c29ae377318b8e99c9c`.
- Final-head matrix: 24/24 workflows SUCCESS.
- `Homepage Composer Law 01 Browser Quality`: SUCCESS, including 1440x1000, 1024x900, 390x844 and 320x760 cases.
- Owner-approved merge: `main@ffafe55b90db1d5313b36a5151110ca1e3f67789`.
- Exact-main `V1 Exact Main Verification` run `35343960510`: SUCCESS.
- Exact-main `X6 Cross-surface Release Closure` run `35343960430`: SUCCESS.

## Ownership / blocker boundary

RootProfile-backed authoritative Team membership remains separately blocked under issue #112 because no public/versioned organization-team projection is available. This visual-parity slice does not infer Team identity or membership from WordPress users/authors/slugs/URLs and does not read private RootProfile storage.

## Next visual slice

Continue with Theme-owned presentation parity for the About/Profile band and Latest Posts. Team may receive shell/card presentation refinement only where it does not create, infer or claim authoritative provider data; provider-backed Team integration remains BLOCKED until issue #112 is resolved.
