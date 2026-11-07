# RQA — ConvertFlow-owned accessibility defects (L4 evidence, 2026-10-23)

**URL:** https://remquocanh.vn/rem-vai-hoa-van/  
**Source owner:** ConvertFlow Core/Pro output; AZnet Theme consumes presentation contracts only.  
**Test source:** read-only Chromium/axe-core [GitHub Actions run #39961771437](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39961771437), artifact `rqa-live-l4-element-trace` (ID 12505350966), product JSON.

## Reproducible L4 findings

Axe-core reported Accessibility failures (Lighthouse mobile score 97 at the latest cited lab run). These are **not** ownership claims about WooCommerce data or Theme-generated native elements:

1. **WCAG 1.4.3-like contrast issue, serious** — ConvertFlow quote channel's small detail text:
   - `.choiceguide-quote-channel--phone .choiceguide-quote-channel__detail`: computed `#647b95` on `#f1f4f7`, contrast **3.95:1**, required 4.5:1 for normal ~13.12 px text.
   - `.choiceguide-quote-channel--zalo .choiceguide-quote-channel__detail`: computed `#4580d6` on `#edf4ff`, contrast **3.57:1**, required 4.5:1.
   - Installed ConvertFlow asset `plugins/choiceguide/assets/css/product-quote.css` assigns `color: color-mix(in srgb, currentColor 72%, transparent)` for `.choiceguide-quote-channel__detail`. The opacity mixing plausibly reduces legibility; the exact computed values above are authoritative for this site/viewport.
2. **Accessible name / visible label mismatch, serious**:
   - ConvertFlow Pro gallery trigger buttons `.choiceguide-pro-project--featured .choiceguide-pro-project__gallery-trigger` and `.choiceguide-pro-project--companion-2 .choiceguide-pro-project__gallery-trigger`. Their `aria-label` replaces the accessible name and fails to include visible text.
   - ConvertFlow phone `.choiceguide-quote-channel--phone` (e.g. its visible channel text includes `Gọi ngay`) and Zalo `.choiceguide-quote-channel--zalo` (e.g. its visible channel text includes `Tư vấn nhanh qua Zalo`) also have accessible names that do not match the complete visible label sequence.
   - The phone and Zalo destinations and numbers are **source-owned business data**; this evidence is **not** consent to choose the authoritative number or alter contact routing.

## Constraints, gate and next

- **L4 evidence PASS:** two contrast violations and four name/visible-label mismatch nodes reproduced with actual browser and precise selectors.
- **No remediation PASS:** no ConvertFlow code, renderer output, metadata or Theme CSS changed here; no new Lighthouse score claimed.
- **Owner-action request:** ConvertFlow owner should preserve the public visual labels in accessible names; meet minimum 4.5:1 contrast using a source-owned token or rule; add provider-side regression for phone/Zalo and project-gallery controls. Avoid invented project labels (e.g. Dự án A/C) in shipping content.
- **Theme boundary:** do not rewrite `aria-label` in Theme JS, do not make private plugin meta edits, do not hard-code CSS for unpublished provider internals and call it integrated.
- **After provider release:** independently rerun axe-core mobile (and keyboard testing) on actual site, check color contrast and name matching; only then claim the corresponding L4 PASS. Provider L5 is untested.

Related source-owner CLS finding: [RQA_CONVERTFLOW_FILTER_FIRST_PAINT_CLS_20261023.md](RQA_CONVERTFLOW_FILTER_FIRST_PAINT_CLS_20261023.md).
