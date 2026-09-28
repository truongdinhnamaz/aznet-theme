# Industrial 01 — Minh Nguyên Pilot Production — 29/09/2026

## Scope
Site-side production pilot for Industrial 01 on `https://minhnguyen.vn/`. This does not claim canonical release-package parity.

## Publish
- WPVibe published the approved draft to live `aznet-theme`.
- Previous theme files were retained as `aznet-theme-wpvibe-backup`.
- Theme-owned settings are `visual_preset=industrial-01` and `homepage_preset=industrial-01`.
- Preview-only draft forcing was removed before publication.

## Homepage migration
The live Front Page still contained Flatsome/UX Builder shortcodes. AZnet Theme does not own or execute that schema, so they appeared literally after publish. Page ID 2 content was cleared with a normal WordPress post update, preserving revision-based recovery rather than absorbing Flatsome semantics into Theme.

## Fresh live verification
- Homepage renders Industrial 01 hero, 7 populated Woo product categories, current Woo products and final quote CTA.
- Shop `/san-pham/` renders 168 WooCommerce results with pagination.
- Representative Product `/may-ep-nguoi-thuy-luc-50-tan-cu-da-qua-su-dung/` renders native Woo gallery, title, description, taxonomy, tabs and related products.
- Industrial preset body class is active on Woo surfaces.

Fresh mobile Lighthouse on the live Homepage:
- Accessibility 100/100
- Best Practices 100/100
- SEO 100/100
- Performance 95/100
- LCP 2.3 s
- CLS 0
- TBT 0 ms
- FCP 2.3 s
- Speed Index 3.1 s

## Ownership
WordPress remains owner of Page/site identity/content. WooCommerce remains owner of Product/catalog/commerce truth. Industrial 01 consumes native/public data only. No Minh Nguyên identity is hard-coded into reusable Theme code.

## Provenance limitation
The live pilot reports Theme metadata `1.3.53` because this publication is a bounded delta over the installed 1.3.53 pilot baseline.

PASS: site-side Industrial 01 L3/L4 pilot production behavior.

NOT CLAIMED: byte-identical deployment of canonical Theme `1.3.57`, fresh release package, GitHub Release, or L6 release parity.

## Recovery
- Theme files: `aznet-theme-wpvibe-backup`
- Front Page content: restore the pre-clear Page 2 revision or site backup
- Full rollback: use the fresh site-wide backup created before publish

## Exact next
Converge `minhnguyen.vn` from the bounded 1.3.53 pilot patch to a canonical packaged Theme build only in a separately verified update/release slice.
