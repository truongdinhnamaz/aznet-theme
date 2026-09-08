# P4 Homepage Blueprint — Visual Review Supersession Checkpoint

**Date:** 2026-09-08  
**PR:** #55  
**Current candidate head:** `52067b3af888fcc19c717ee2d12073024baffec2`  
**Status:** PENDING FRESH GREEN

## Why this checkpoint exists

Automated run `34219272396` on older feature head `e164d56f2cfd3196628d88452181def2b837ce0a` passed its then-current L3/L4 assertions, including overflow, keyboard/focus, axe and runtime checks.

Independent visual inspection of that run's real screenshots subsequently found a Theme-owned presentation defect that those assertions did not cover: the Homepage Hero direct children (eyebrow, H1, supporting copy and CTA group) were laid out horizontally because `.aznet-theme-homepage-blueprint__hero` used `display: flex` directly on the WordPress Group container. On mobile this squeezed CTA links until their text wrapped nearly character-by-character.

Therefore the older `e164d56...` L4 result must not be treated as the final visual acceptance for PR #55, even though its automated assertions were green.

## Root cause and regression

The visual defect was reduced to the Homepage component stylesheet. No WordPress content/query/provider defect was involved.

A browser regression was added on head `438aaaa375074d67216f6d73c32f29a519596c50` to measure Hero geometry and require:

- supporting copy starts below the H1;
- CTA group starts below the supporting copy;
- each Hero CTA has usable width (at least 110 px in the tested viewport).

The minimal production fix on current head `52067b3af888fcc19c717ee2d12073024baffec2` changes only the Hero composition mechanism from row-producing flex layout to vertically auto-placed grid layout while retaining centered vertical alignment.

## Additional retained-gate correction

The same feature also exposed a stale R2 browser workflow guard that hard-coded exactly 16 files under `patterns/`. R2's owned historical set remains 16 core patterns plus exactly two Woo-gated patterns, but the Theme may legitimately add later native patterns such as the P4 Homepage blueprint.

The retained R2 browser gate now keeps the R2 core count as a floor (`>= 16`) and the Woo-gated count exact (`= 2`), matching the already-corrected offline R2 contract. R2 named pattern checks, clean/Woo browser matrices and theme-switch portability assertions remain intact.

## Current verification boundary

Do **not** claim PR #55 final L4 PASS from the older artifact alone.

Fresh GitHub Actions runs are queued for exact head `52067b3af888fcc19c717ee2d12073024baffec2`, including:

- `P4 Homepage Blueprint Browser Quality` run `34221038362`;
- `R2 Pattern Library Browser Quality` run `34221038341`;
- `V1 Core Pull Request CI` run `34221038327`;
- retained Homepage/Header/Control Center/Woo/R1/R6 workflows triggered by the final touched paths.

This checkpoint remains PENDING until the exact-head P4 run passes the new Hero geometry regression and its fresh desktop/mobile screenshots are visually inspected. The earlier `P4_HOMEPAGE_BLUEPRINT_L4.md` remains historical evidence for the older checkpoint, not a final acceptance claim for the current head.

Merge to `main`, tag, GitHub Release and final deployment remain gated.
