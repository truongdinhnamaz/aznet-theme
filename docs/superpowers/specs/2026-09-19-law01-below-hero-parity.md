# Law 01 — Below-Hero Reference Parity Spec

Date: 2026-09-19

## Goal

Continue the approved Law01 homepage reference parity **below the Hero** while preserving the existing AZnet Theme implementation and ownership boundaries. The supplied Tâm Đức screenshot is the presentation reference. The Hero is frozen for this slice.

## Reference order

1. Trust strip
2. Services
3. About + Team
4. Latest legal knowledge
5. Footer

## Ownership constraints

- WordPress owns page/post/menu/media content and ordering.
- RootProfile remains authoritative for organization/team identity semantics when its public/versioned projection exists.
- Theme owns only composition, visual hierarchy, responsive behavior, decorative iconography and fail-soft presentation.
- Do not fabricate lawyer names, roles, portraits, business statistics, address, email, social links, testimonials or client claims.
- Do not reorder service data by title/slug heuristics.
- Do not direct-read private option/meta/CPT/table storage.
- Missing Team or business metrics must remain fail-soft instead of being replaced with demo truth.

## Frozen

- Existing approved Hero composition and media are not changed by this slice.
- Header behavior from the verified 1.3.13 candidate is retained.

## Trust strip

- Four-item horizontal strip on desktop with vertical separators.
- Gold legal/service iconography at the left of each message.
- Two columns on tablet; one column on narrow mobile.
- Text remains source-owned/current; this slice does not replace it with unsupported client-specific claims.

## Services

- Keep the existing WordPress-mapped Services parent and up to six direct published child Pages.
- Preserve source order.
- Reference composition: eyebrow + section title + “Xem tất cả dịch vụ”, then six equal-height compact cards.
- Each card: circular gold presentation icon, title, optional source excerpt, “Xem chi tiết →”.
- No extra service intro in the compact Burgundy composition.
- No semantic routing or authoritative classification from title/slug.

## About + Team

- Keep 48/52 reference split when legitimate Team member inputs exist.
- About remains text-led; the Theme may add a decorative law-scale watermark.
- No fake business metrics. Stats shown in the screenshot remain blocked unless a legitimate public source is introduced by the data owner.
- No fake people. Team remains source-backed and fail-soft; when no legitimate members exist, About expands cleanly.
- Team cards keep a four-portrait desktop rhythm when real mapped presentation inputs exist.

## Latest legal knowledge

- Three WordPress-native posts.
- Add a source-backed “Xem tất cả bài viết →” only when WordPress has a published Posts Page.
- Make featured media part of the card link surface without duplicating accessible text.
- Keep category/date/title/excerpt/read-more hierarchy and equal-height cards.

## Footer

- Burgundy four-column reference composition using only existing WordPress Footer projections.
- Preferred visual order when populated: Identity → Contact → Quick links → Social.
- Law01 headings: “Thông tin liên hệ”, “Liên kết nhanh”, “Kết nối với chúng tôi”.
- Empty projections collapse; never create placeholder business data.
- Mobile stacks to one column.

## QA

- L1 static contracts.
- L2 contract/TDD with witnessed RED then GREEN for every behavior change.
- L3 WordPress 6.9 runtime retained.
- L4 Law01 browser/a11y at desktop/tablet/mobile.
- Full retained workflow set on the exact final candidate.
- Version/package promotion occurs only after presentation regression is green.
- No deployment, merge, release publication or live-site mutation in this slice.
