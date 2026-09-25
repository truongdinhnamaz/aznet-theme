# Law 01 Hero Frontend Layout Fix — 2026-09-25

**Candidate:** AZnet Theme 1.3.49  
**Production head:** `134514d1026de2efe0361cf041c9bae41a314026`  
**Branch:** `work/homepage-map-v1-3-38-baseline`

## User-visible symptom

After Hero source parity was fixed, the edited Hero content reached the public Homepage but the frontend presentation remained visibly wrong on desktop:

- the Gutenberg Hero content was compressed into a narrow centered content-width;
- the Hero title wrapped earlier than the Law 01 shell allows;
- portrait media became excessively tall;
- the primary Hero CTA inherited the global Theme primary blue instead of the Law 01 palette.

## Root cause

The portable Hero scaffold serialized both its root managed Group and trust Group with Gutenberg `layout.type=constrained`.

WordPress therefore applied content-width constraints to the direct child Columns/Groups. The Law 01 CSS set the outer managed Group to `max-width:none`, but did not clear the generated max-width/margins on those constrained direct children.

Separately, the primary Core Button retained the global `primary` preset color, while the Law 01 frontend only overrode the secondary CTA.

The media rule used only a `min-height`, so tall source images could retain an excessive intrinsic height.

## Fix

- New Hero scaffold Groups now use `layout.type=default`.
- Existing already-saved constrained Hero blocks are fixed presentation-side:
  - managed layout and trust direct children get `width:100%`, `max-width:none`, `margin-inline:0`;
  - trust grid is also unbounded within the Law 01 shell.
- Hero media gets a bounded desktop height `clamp(24rem, 32vw, 34rem)` and responsive mobile bound.
- Primary Hero CTA is scoped to `var(--law01-navy)`, which resolves to the active Law 01 palette (burgundy on the client variant), without changing global Theme button colors.

No WordPress Hero data is copied, inferred or rewritten for existing content.

## TDD

RED on exact 1.3.48 package:

```text
FAIL: Hero frontend presentation missing .aznet-theme-homepage-hero-content > .aznet-theme-homepage-hero-content__layout
```

GREEN after the bounded presentation fix:

```text
PASS: Hero frontend uses full Law 01 shell, bounded media height and scoped CTA presentation.
```

Prepared browser regression now asserts:
- managed Hero content/layout occupies at least 94% of the Law 01 shell;
- primary CTA computed color matches the Law 01 palette;
- desktop Hero media height remains between 360px and 560px;
- existing order, responsive collapse, overflow and axe checks remain.

## Candidate verification

Fresh local verification on the exact package:
- focused frontend layout contract: PASS;
- 136 PHP files lint: PASS;
- admin authoring JS syntax: PASS;
- version: 1.3.49 in Theme header and runtime constant;
- ZIP integrity: PASS.

Git blob parity with production head:
- `assets/css/components/homepage-law-01-variants.css`: `d1eb55d3ad22b7d36edb7e222c09a0680281b53f`
- `functions.php`: `6c1488fc8dd6714e7ad3efa55c0468ae50e267ba`
- `patterns/homepage-hero-content.php`: `867f1dd09a088f089b6513077547eb69c2ae5a70`
- `style.css`: `bd7dba46099fe6bbf2e855ee207c7d285182597a`

Package SHA-256:
`08e5c23205bba9f8c123d176ef57edcf28b7ad5973895da82c8182b8bdd8609b`

## QA boundary

This fixes the root cause in source and has fresh local L1/L2 evidence. Production-browser confirmation remains pending because installing the candidate on the live site is a deployment action and requires explicit owner approval/action. No production deployment or main merge is claimed.
