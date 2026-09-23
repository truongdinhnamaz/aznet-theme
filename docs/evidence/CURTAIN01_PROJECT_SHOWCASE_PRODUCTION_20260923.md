# Curtain 01 — Project Showcase production checkpoint

**Date:** 23/09/2026
**Pilot:** `https://remquocanh.vn/`
**Theme:** AZnet Theme `1.3.31`
**Canonical implementation:** `main@f84d45a58261ec41d9b0c11a5e02a0873edb28ff`
**Scope:** Theme-owned Curtain 01 presentation + WordPress-owned project content mapping

## Owner approvals

The owner explicitly approved:

1. publishing WordPress Post #999 and mapping the Curtain 01 project source;
2. after reviewing the WPVibe preview and confirming a fresh site-wide backup, publishing the Theme draft to production.

## WordPress-owned source

- Post #999 is published at `/hoan-thien-rem-cuon-cong-trinh-y-te/`.
- Category `Công trình rèm` is term #80 and contains the published project Post.
- Theme Mod readback is `homepage_curtain01_projects_term = 80`.
- The project copy remains limited to facts supported by existing WordPress-native media/source context. Site name, location, customer identity, scale and technical configuration are not inferred.

## Repository implementation provenance

PR #263 merged the fail-soft Project Showcase to canonical `main@f84d45a58261ec41d9b0c11a5e02a0873edb28ff`.

Fresh exact-main runs after merge:

- V1 Exact Main Verification `35870034014` — SUCCESS.
- X6 Cross-surface Release Closure `35870034255` — SUCCESS.
- Dedicated Curtain 01 Project Showcase `35870033789` — SUCCESS.

## Production publish and reconciliation

The owner-confirmed backup existed before the WPVibe publish.

The first publish exposed one stale draft-only presentation delta: Curtain 01 CSS still contained `.aznet-theme-curtain01-process__heading h2 { max-width: 22ch; }`, which is absent from canonical main and conflicts with the accepted D-036 natural desktop heading-flow rule.

The stale delta was detected by direct live-to-canonical byte comparison, not by visual guesswork. A fresh draft was cloned from the just-published live Theme, Curtain 01 CSS and JS were reset to exact canonical main bytes, the preview was reverified, and the corrective publish completed under the same owner-approved production operation.

WPVibe retained `aznet-theme-wpvibe-backup` as Theme-file rollback.

## Fresh production verification

Post-correction readback proves:

- active Theme: AZnet Theme `1.3.31`;
- live composer order: `about -> category-showcase -> catalogue -> process -> projects -> knowledge -> final-cta`;
- live `projects.php` exists and consumes the explicit Category mapping;
- live Homepage renders `.aznet-theme-curtain01-projects` with Post #999;
- mapping remains exactly `80`;
- live Curtain 01 CSS is byte-identical to canonical main;
- live Curtain 01 JS is byte-identical to canonical main;
- live `theme.json` is byte-identical to canonical main;
- mobile Lighthouse: Accessibility 100 / Best Practices 100;
- desktop Lighthouse: Accessibility 100 / Best Practices 100.

The production Homepage head also emits the canonical Roboto Vietnamese face URLs for Regular, Medium and Bold. This checkpoint proves declaration/runtime emission; it does not independently claim a dedicated browser `FontFace.status=loaded` assertion on the public host.

## Ownership boundary

This production checkpoint does not transfer or infer ownership from WooCommerce, ConvertFlow, RootProfile or any other provider. WordPress owns project Posts/category/media/content. AZnet Theme owns only typed reference consumption, composition and presentation.

## Residual unknowns

- Independent pixel-level external production-browser verification remains separate.
- Provider-specific L5 certification remains separate.
- Dedicated public-host assertion that all Vietnamese font faces reach browser `loaded` state remains UNKNOWN unless separately measured.

## Result

**PASS** — Project Showcase content publication, mapping, Theme production publish and scoped live L3/L4 accessibility/best-practices verification.
