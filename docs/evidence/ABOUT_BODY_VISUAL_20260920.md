# About body visual continuation — 2026-09-20

## Scope and approved intent

Continue the already-approved About concept after the user rejected a text-and-box-only body. Retain the Homepage typography, palette, full-width background / constrained content rules. No new scope, provider contract, public release or shared-draft publication.

Latest base: main `ddf7cfe4b3e5f928bf431af77add3a561bc54ebe`; previous PR #179 head `188070f89c43c86afa4096855439c6fa9f20284a`. Visual implementation is committed at `a2d1fcbf2f1ebc16d8861d5b0d4c6bbabb116223`.

## Applied changes

- New draft-only stylesheet `assets/css/components/about-body-visual.css`, mirrored at `ops/about-preview/about-body-visual.css`.
- A guarded enqueue in draft `page-templates/about-preview.php` loads it after `aznet-theme-about-body-parity`, using filemtime for cache invalidation.
- Restores the existing WordPress-authored illustrative image in the approach section, paired with the text and a restrained edge blend. The picture is not claimed to be a real photograph of the firm.
- Open line-icon principles replace repeated ruled boxes.
- Four step labels form a connected desktop timeline, two columns on tablet, and one vertical route on mobile.
- Existing six service summaries retain their content/links and receive decorative line-art motifs instead of borders. CSS ordinal decoration is not authoritative service routing or domain inference.
- Trust section has document icons; the statement has a legal emblem.
- Hero, prior layout CSS, WordPress content, links, Homepage and Services are untouched.
- No generated staff, office-sign imagery, metrics, satisfaction rates, credentials or new client claims from the mockup are used.

Stylesheet SHA-256: `62c48b02f0eba92c0320833a513200bfc5d770722b2166db95563647f83f5030`.

## Verification

1. Local Chromium replay uses previously captured native Page DOM and relevant inherited CSS, revalidated against the current WPVibe-rendered body. A new visual contract first failed on the previous code: hidden source image, missing decorative icons/timeline nodes and retained card borders. Five widths each reported 20 repeated failing assertions. This is not a count of distinct bugs.
2. After implementation, those checks and explicit mobile/tablet timeline-column checks pass at 390, 768, 1024, 1440 and 1920 px.
3. Retained checks across five widths and two shell widths (1240/1536) report zero failures for horizontal overflow, normal solid-background text contrast and CTA focus/target presence. Authored text/link inventory stays unchanged.
4. Site HTML contains `aznet-theme-about-body-visual-css` after the parity stylesheet, asset version `1789884057`.
5. Fresh WPVibe audits on the actual preview return Accessibility 100 and Best Practices 100 on mobile and desktop.

## Limits — not a full visual completion claim

The local environment cannot resolve `lstamduchn.vn`. The replay deliberately blocks external resources; it does NOT verify the actual remote photo bytes, whole-site computed cascade, hover contrast in all gradients, authenticated toolbar geometry, or exact full-page browser screenshots. These limitations are not hidden behind Lighthouse scores. Visual approval of the generated mockup is not approval of a shipped website. No L5/L6 claim.

## Rollback and one Next

Remove only the new `about_visual_css_path` enqueue block from the draft template; the prior body parity and Hero stylesheets were preserved byte-for-byte. Do not publish or delete the shared draft as rollback.

Next: inspect the complete exact preview, including the restored native photo, in an independent browser with network access before publication. Keep PR #179 unmerged; do not rebuild provider data or invent staff evidence to fill missing content.
