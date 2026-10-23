# About below-Hero parity checkpoint — 20 September 2026

## Authority and scope

User retained the approved concept and explicitly requested the About body to follow the Homepage design principles. Source review: `docs/SOURCE_GOVERNANCE.md`, AZT-02 section 5.1 (shared typography), existing PR #175, and current Law 01 Homepage CSS. This is a bounded preview correction, not a new architecture or public provider contract.

Preserved: Hero markup and stylesheet, authored Page 34 content, all eight authored body sections and their order, existing links, Homepage/Services/Header/Footer workstreams. No Page/meta/provider mutation, no new staff/identity claims, no release or merge.

## Root cause

1. Core `.is-layout-constrained` kept nested heading/card rows at the content width even when the outer band was full width.
2. Three intro children (eyebrow, heading, paragraph) were placed into an implicit two-column grid, so the paragraph fell into the wrong row.
3. Equal-specificity legacy Custom CSS could replace dark backgrounds while the preview forced white text. Earlier repeated contrast-color changes did not resolve this cascade problem.
4. The old body introduced a separate Georgia heading system, oversized headings and large fixed-height bands, drifting from the Homepage.

## Applied preview delta

- Created draft `assets/css/components/about-body-parity.css`.
- Added a guarded enqueue to draft `page-templates/about-preview.php`, dependent on `aznet-theme-about-fullbleed-preview`, versioned by filemtime.
- New stylesheet is entirely scoped to `#aznet-theme-about-content`; Hero selectors and global settings are untouched.
- The reviewed source is retained at `ops/about-preview/about-body-parity.css` in this branch; it is not automatically installed by the canonical Theme.
- Style SHA-256: `e59ab2009e743aa10fb38fac910d0be27108587bdb44effbd0c1d69084a55687`.

The body uses the Theme font-family tokens, Homepage heading/card scale and palette, a shared content axis, full-width section backgrounds, explicit editorial grid positions, compact principles/process rows, responsive service cards and a simple team teaser without stock-office background presented as team evidence.

## Evidence and limits

### Isolated browser replay

An isolated Chromium replay used captured Page 34 DOM with the relevant Core, Theme and conflicting pilot CSS rules. It is NOT an exact full-site screenshot, a real WordPress execution, provider integration or release test.

- Viewports: 390, 768, 1024, 1440, 1920 pixels.
- Container settings: 1240 and 1536 pixels, ten combinations.
- RED: 212 failed conditions across those combinations, including constrained row width, heading-axis drift, different typography and contrast. These are repeated assertion results, not 212 distinct defects.
- GREEN: ten combinations passed all defined checks after applying the correction.
- Checks include full-width bands, shared axis, heading family/scale, grid widths, column overlap, mobile stacking, computed solid-background text contrast, 44-pixel CTA target minimum and visible keyboard focus.
- Exact captured text and link inventory remained unchanged.
- Screenshots were inspected locally. Captured input DOM SHA-256: `6ec0e4af0e8a142fca09ad72b7654c28503f1004105982ed19dc6451d5c36ed9`.
- GREEN result JSON SHA-256: `03789f3b7b5a29c930d5710eaf72cba658a879173f3c31035caa42642d329a23`.
- Full local replay source, fixtures, before/after results and screenshots are retained in the conversation QA artifact, not substituted for external browser evidence.

### Authenticated site verification

- WPVibe accepted the draft file/enqueue changes; PHP edit passed tool syntax validation.
- Returned preview HTML contains `aznet-theme-about-body-parity-css` and asset version `1789880330` after the original About stylesheet.
- Fresh WPVibe Lighthouse audit: mobile Accessibility 100 / Best Practices 100; desktop Accessibility 100 / Best Practices 100. These results do not establish visual approval.
- Page 34 remains published, template empty, modified `2026-09-20T03:35:20`; no content update was performed.

### BLOCKED / UNKNOWN

The independent GitHub-hosted browser probe, run `35490085707`, job `106023425906`, timed out after 30 seconds reaching the site. It produced no screenshots. This infrastructure failure is neither a valid RED nor a browser PASS. The external probe is retained as manual-only to avoid repeating a known network blocker. Full exact-preview visual verification and independent review remain open; no L5/L6 claim.

## Rollback

Remove the new guarded enqueue block from draft `page-templates/about-preview.php`. The previous About CSS file was not overwritten, so the prior preview is recoverable. The new CSS can remain unreferenced; no destructive cleanup is necessary. Do not publish the shared WPVibe draft as a rollback action.

## Next

When an independent browser can reach the exact WPVibe preview, verify the complete page (including inherited site CSS, Hero boundary and hover/focus states) before any publication or canonical promotion. Preserve the measured isolated PASS scope; do not treat equal Lighthouse scores as visual parity.
