# Digital 01 Brand Light 2026 — screenshot implementation checkpoint

Date: 2026-10-22 (Asia/Bangkok)

## Requested approved design

Implement the user-approved screenshot `AZnet_Trang_chu_Logo_Mau_sac_Dong_bo(1).png` as native WordPress Homepage presentation: white Header with AZnet native logo, navy type, red highlights/CTA, light hero with CSS-drawn laptop/illustration, four service cards and benefits grid. The screenshot is a **visual reference**, not authority for invented achievements or sales metrics.

## Ownership and source

- Theme only implements **opt-in presentation** stylesheet `assets/css/components/homepage-digital-01-brand-2026.css`. Its activation class is authored by WordPress, not inferred from site name/URL/domain identity.
- `inc/theme/assets.php` enqueues this only when the current WordPress Page has the opt-in presentation class and active homepage preset is Digital 01. Its own draft preview can reuse base Digital 01 CSS without changing registered template manifests or any other site.
- Native WordPress Page ID **43515** is **draft** `AZnet Brand Light 2026 - Giao dien tham chieu`, candidate content ~25,996 chars, with new Hero/services/benefits ahead of preserved native Process/Profile/Products/Query/FAQ/CTA. This Page is **not** the published Front Page. Preserved content comes from the existing Page 2; no mutation of Page 2.
- Live Page 2 remains authority for site SEO, canonical and front-page identity. Theme does not own any provider identity, WooCommerce state, Route, page SEO or customer data.
- The user-provided light logo exists as WordPress Media ID **43442** `https://aznet.vn/wp-content/uploads/2026/08/logo-nen-sang-1.png`; production `custom_logo` is still null, so visual logo parity in the header is not yet verified. The Theme code continues to rely on the native WordPress custom logo setting rather than hard-coding a site-specific URL.
- CSS laptop is a semantic decorative illustration, not the photographic laptop seen in the screenshot. Fine-grain pixel parity and responsive browser screenshots not yet verified.

## QA

- New contract `tests/offline/digital01-brand2026-presentation-contract.php`.
- RED GitHub run `39698440067`: failed because stylesheet absent, as intended.
- Intermediate GREEN attempt `39698716117`: caught bad `\\WP_Post` syntax, repaired at commit `05ad53e`.
- GREEN run `39698764699`: success for PHP lint, new presentation contract and retained Digital 01 offline contract.
- WPVibe isolated draft theme `aznet-theme-wpvibe-draft` contains both files fetched from the candidate branch and is **not** published.
- Draft Theme preview URL: `https://aznet.vn/?wpvibe_preview=AStxGSRVBdnvrfKJQHN4lWX6ju6KzW4O` (token may expire). To inspect unpublished Page 43515, an authenticated WordPress preview of this Page also needs to apply the Theme preview token. A public unauthenticated `page_id` request did **not** render this draft; no browser L4 PASS claimed.
- WordPress draft Page 43515 readback: post_status=draft, opt-in class present, native wp:query and FAQ retained.
- This remains code candidate + content draft, **not** production release.

## BLOCKED / UNKNOWN

1. Exact visual browser QA of unpublished WordPress draft Page 43515 under draft Theme and custom logo. WPVibe public HTML selector returned no draft content; needs authenticated admin preview.
2. The logo is not yet assigned to native WordPress custom_logo; optional dark variant supplied by owner is not registered in Media Library.
3. No final mobile/tablet/browser/axe audit. CSS contract is L1/L2 only.
4. No released version or package. All production mutation/merge requires a subsequent explicit release gate.

## EXACT NEXT

Inspect authenticated combined Page 43515 + draft Theme visual preview; correct real browser issues; assign existing Media 43442 through the native WordPress logo setting only in the explicitly approved site rollout; re-verify Core, X6, L3/L4 and provider-safe smoke. Do not overwrite the live 1.3.95/backup, merge, or deploy without owner release approval.
