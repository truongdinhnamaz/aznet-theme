# Digital 01 Brand Light — Ba lối khám phá dịch vụ và sản phẩm
Date: 2026-10-26
Status: Owner-approved visual structure (owner response "d"), candidate only.

## Intent and approved choice
AZnet.vn sells three distinct offerings: (1) AZnet professional services, (2) first-party digital products developed by AZnet, and (3) tools in the wider ecosystem supplied/curated/integrated by AZnet. The owner explicitly approved displaying all three as peer presentation blocks directly below the Brand Light Hero. The site is not a service-booking-only landing page.

## Approach
Selected: WordPress-authored native Gutenberg Group section with 3 equal cards and links, styled through the existing explicit Digital 01 Brand Light opt-in CSS. This is the shallowest Theme-owned presentation change and requires no plugin/commerce state logic.
Rejected: Building separate Theme-managed catalogs/stores, inferring ownership from Woo category IDs, or hard-coding product/price/license status in PHP/JS (violates source authority). Rejected: Cloning Flatsome shortcodes or forcing a global Homepage redesign.

## Presentation
- Retain existing Brand Light white Header, light Hero, laptop art, navy/red palette and existing downstream service cards, trust/process, person, products, posts/FAQ/CTA.
- Insert an accessible, H2-led, three-card region immediately after the native Hero block and before the existing services section; 3 equal columns on wide desktop and 1 stacked column at <=900px. Cards have a small category label, H3 title, concise explanation, grounded deep links and a primary link, with keyboard-visible focus and minimum tap targets.
- Dịch vụ AZnet card: published WordPress /dich-vu, /thiet-ke-website, /dich-vu-seo. Six WooCommerce service products dated 2026-10-25 remain Draft and are not advertised as instantly buyable.
- Sản phẩm AZnet phát triển card: already published Woo /website-intelligence and /plugin-tinh-gia-xay-dung-cho-wordpress; no invented price or instant-checkout claim when price is 0.
- Công cụ hệ sinh thái card: published Woo /ewww-image-optimizer explicitly described as a selected external tool, and published editorial /cong-cu-video-ai-chon-loc-boi-aznet-viet-nam as informational. Do not represent EWWW as AZnet-authored or imply all AI tools are currently sold in WooCommerce.
- Use local WordPress-native presentation class for region, not a Theme-controlled domain/content taxonomy.

## Scope and safety
- AZnet Theme owns layout CSS only; WordPress owns sections, copy, pages and editorial links; WooCommerce owns products, prices, availability, order/checkout and entitlements.
- Change only opted-in Brand Light page CSS and its test fixture. Modify native draft Page #43515 using an exact one-time server-side content patch; preserve revisions and all published pages.
- No activation of staging menu #3493; live menu #1436, live Front Page #2, active Theme v1.3.96 and WooCommerce products must remain unchanged.
- Candidate is reversible by reverting CSS commit and restoring the Page draft via WP revision. No production publish, PR merge, release or deployment without separate approval.

## Acceptance
- L1/L2: scope isolation, responsive and accessible focus/tap styles; no hard-coded Woo/WordPress/product URLs in Theme CSS.
- L3/L4: isolated WordPress native fixture has 3 distinct equal tracks below Hero before services; responsive 3 desktop, 1 tablet/mobile, real links, contrast/accessibility and no overflow; non-opted Pages unchanged.
- On site: Page 43515 remains Draft; readback confirms three paths with real current URLs, once only, before services. Actual authenticated draft screenshot remains UNKNOWN until available.
- Do not infer L5 integration from an isolated fixture.