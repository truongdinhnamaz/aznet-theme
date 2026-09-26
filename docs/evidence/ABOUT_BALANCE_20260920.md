# About balance correction — 2026-09-20

## Trigger
User screenshots showed the post-Hero body visually occupying the left half of the viewport: the burgundy statement stopped near the midline and the approach image sat in an undersized left column.

## Root cause
The About body is authored as WordPress Core constrained-layout blocks. Styling the authored section itself as both the full-width background band and the constrained content container left Core layout constraints in the same box that owned the background. That mixed responsibilities: the band could not reliably reach the viewport while the content remained on the Homepage shell axis.

## Structural correction
The correction is now explicit rather than a padding workaround:

`viewport/full-width band -> .aznet-theme-about-shell -> original WordPress-authored section`.

- `ops/about-preview/about-body-bands.php` retains the draft renderer that parses the existing top-level About Page Kit blocks and wraps the eight known sections in presentation-only full-width bands.
- `ops/about-preview/about-body-balance.css` styles only those bands and inner shells.
- The original authored section remains inside the shell and continues to own its WordPress content.
- Hero rendering and Page 34 authored text/links remain unchanged.
- No provider data, identity, metrics, staff claims, routing or domain storage is introduced.

Desktop composition:
- intro statement: 41/59 editorial grid;
- approach: 46/54 text/image grid;
- all bands: 100% viewport width;
- all inner content: existing Theme container-shell axis;
- mobile <=48rem: one-column stacking.

## TDD / evidence history
Earlier RED: missing `about-body-balance.css` caused `AssertionError: RED: balance stylesheet is missing`.
Earlier GREEN: the bounded balance stylesheet contract passed and WPVibe loaded it.

A later user screenshot proved the CSS-only correction was insufficient visually. That invalidated the earlier visual completion assumption and led to the structural band/shell correction above.

Before the structural draft edit, a rendered-HTML probe recorded:
- `red_missing_band_wrapper: true`
- `has_intro: true`
- `has_story: true`

The draft template was then changed to emit `.aznet-theme-about-band` wrappers around the existing authored sections.

## Current verification boundary
Previous WPVibe audits before the structural refactor were:
- mobile Accessibility 100 / Best Practices 100;
- desktop Accessibility 100 / Best Practices 100.

Those scores do **not** verify the new structural refactor.

WPVibe reached its rolling 24-hour Pro call limit immediately after the draft PHP structural edit. The connector explicitly blocked further calls until about 2026-09-21 03:30 UTC. Therefore the final balance stylesheet could not yet be written into the draft and the new exact preview could not yet be re-rendered/audited.

Do not claim the current website/draft is complete or visually PASS until that write and fresh preview verification occur.

## Git provenance
- Full-width band stylesheet retained in `ops/about-preview/about-body-balance.css`.
- Full-width band renderer retained in `ops/about-preview/about-body-bands.php`.
- Draft PR remains #179 on `fix/about-below-hero-parity-20260920`.
- No merge or live publication.

## Rollback
In the draft, restore the previous `the_content()` body render and remove the `aznet-theme-about-body-balance` enqueue. Hero/parity/visual files remain intact.

## Exact next
When WPVibe write access is available again:
1. write the retained `about-body-balance.css` to the draft;
2. confirm the draft template still contains the band/shell renderer;
3. render the exact `/gioi-thieu/` preview and visually verify full-width backgrounds plus centered shell;
4. run fresh mobile/desktop Accessibility + Best Practices;
5. only then create a new PASS checkpoint. No publish/merge without the separate release gate.