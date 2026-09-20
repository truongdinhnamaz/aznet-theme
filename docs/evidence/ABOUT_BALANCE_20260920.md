# About balance correction — 2026-09-20

## Trigger
User screenshot showed the post-Hero body visually occupying the left half of the viewport: the burgundy statement stopped near the midline and the approach image sat in an undersized left column.

## Root cause
The About body is nested in WordPress Core constrained-layout wrappers while three preview stylesheets progressively restyle the same authored blocks. The existing full-width rule was not strong enough at every wrapper/child boundary, and the story columns retained inherited flex/layout constraints. The visible symptom was a full-width band being laid out within a constrained ancestor.

## TDD
A one-off balance contract was written before the fix.
RED: missing `about-body-balance.css` caused `AssertionError: RED: balance stylesheet is missing`.
GREEN: the contract passed after adding the bounded balance layer.

## Minimal correction
- Force only the About preview main/article/content/Page Kit/sections to a 100% non-constrained width.
- Neutralize WordPress Core constrained child max-width/margins inside those bands.
- Keep section backgrounds full width while content padding follows the existing shell axis.
- Stabilize the statement at 41/59 and the approach at 46/54 on desktop.
- Force the story columns and image to fill their assigned tracks.
- Stack statement and approach to one column at <=48rem.
- Hero and WordPress-authored content remain unchanged.

## Fresh verification
- Preview HTML loads `aznet-theme-about-body-balance-css` after the retained visual layer.
- WPVibe mobile audit: Accessibility 100 / Best Practices 100.
- WPVibe desktop audit: Accessibility 100 / Best Practices 100.
- The final visual acceptance still belongs to the exact preview in the user's browser; Lighthouse does not replace visual review.

## Rollback
Remove the `aznet-theme-about-body-balance` enqueue from the draft About template and leave the prior Hero/parity/visual files intact.

## Next
User reviews the same draft preview. No merge or live publication before explicit publish approval and backup gate.
