# AZnet.vn 1.3.95 — live Lighthouse node-level diagnostic, 2026-10-22

**Scope:** Read-only measurement of public production `https://aznet.vn/`; no Theme runtime change, WordPress mutation, provider integration, merge, release, or deploy.

- Parent main at probe creation: `0cd22f023a3b63c90a17cb73188e812f59ed7256` (PR #492 D-056 documentation checkpoint).
- Diagnostic workflow commit: `1c3c78397868c744213b4b9e5969f44b8f335f0b`.
- [GitHub Actions run 39695127582](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39695127582): completed SUCCESS (diagnostic execution, **not** accessibility PASS).
- Lighthouse 12.8.2, Chrome 155, mobile emulation, public unauthenticated home page, fetch 2026-10-21 23:42:47 UTC.
- Performance **66**, Accessibility **96**, LCP **7.2 s**, FCP **2.8 s**, TBT **60 ms**, CLS **0**. This is one lab run; no field-data/casual regression inference.
- Exact `color-contrast` audit score 0 with **9 failing DOM entries**:

| # | HTML element / label | Attribution |
|---|---|---|
| 1 | `#aznet-digital01-services` first service `.aznet-digital01__service-index` “01” | Theme presentation CSS candidate; computed style not logged |
| 2 | First service `.aznet-digital01__text-link > a` “Tìm hiểu giải pháp” | Theme presentation CSS candidate |
| 3 | Second service descriptive `p.wp-block-paragraph` | Theme presentation CSS candidate |
| 4 | Third service descriptive `p.wp-block-paragraph` | Theme presentation CSS candidate |
| 5 | Fourth service descriptive `p.wp-block-paragraph` | Theme presentation CSS candidate |
| 6 | Fifth service descriptive `p.wp-block-paragraph` | Theme presentation CSS candidate |
| 7 | Sixth service descriptive `p.wp-block-paragraph` | Theme presentation CSS candidate |
| 8 | Process section `.aznet-digital01__text-link > a` “Xem các dự án thực tế” | Theme presentation CSS candidate |
| 9 | CTA `.aznet-digital01__eyebrow` “BẮT ĐẦU BẰNG MỘT CUỘC TRAO ĐỔI” | Theme presentation CSS candidate |

**Important:** At the same time the canonical stylesheet contains later overriding rules for some of these selectors (`#596873` on service descriptions, `#ffffff` on CTA eyebrow, `#ff9d85` on featured-service index/link). This mismatch is significant: do not attribute the nine runtime failures to source color pairs until actual computed styles, loaded CSS hashes, and cascade precedence are measured. The earlier date-text `#697985` is overridden by a later `#596873` rule; no date-text fix is justified.

**LCP element:** `h1.wp-block-heading` in Digital 01 Hero (“Biến website thành tài sản kinh doanh số.”), bounding box ~376×151 px at mobile audit. Phase breakdown: TTFB **1424 ms / 20%**, load delay **0**, load time **0**, render delay **5775 ms / 80%**. Investigate actual late text paint via computed font, CSS arrival, font loading and render-blocking chain; avoid speculative image fix. External network/cache/CDN timing not yet attributed.

**Gate status:** Evidence acquisition PASS L4 diagnostic only; accessibility issue confirmed; production fix NOT started, no RED→GREEN Theme fix verified. LCP root cause UNKNOWN beyond observed render-delay split. `1.3.95` stays live. `aznet-theme-wpvibe-backup` retains prior 1.3.94.

**EXACT NEXT:** Use a fresh read-only browser computed-style/cascade capture on the nine violating nodes with CSS resource URLs and build hashes; verify live Theme CSS against canonical commit. If a confirmed Theme-owned defect exists, write a minimal failing regression on an isolated branch then implement and verify GREEN. Merge/deploy/new release remain approval-gated.

## Follow-up computed-style witness and cache hypothesis (same date)

[Read-only GitHub Action run 39695377989](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39695377989) completed SUCCESS. Playwright Chrome mobile captured actual computed values:

- Featured index `01`: **rgb(198,61,37) = #c63d25** on navy **#192e3b**, even though canonical Theme CSS defines a later featured override **#ff9d85**.
- Featured first link: **#c63d25** on navy **#192e3b**, despite canonical featured `#ff9d85` override.
- Light service paragraphs: **rgb(106,119,128) = #6a7780** on **#fbfbfa**, despite canonical/live Theme source **#596873**.
- CTA eyebrow: **rgb(255,223,213) = #ffdfd5** on **#c63d25**, despite canonical Theme later override **#ffffff**.
- Process link: browser default **rgb(0,0,238) = #0000ee** on dark **#101f2b** — separate actual presentation gap; no suitable Theme override proven.
- Browser resource list explicitly includes `/wp-content/cache/min/1/wp-content/themes/aznet-theme/assets/css/components/homepage-digital-01.css?ver=1792621680`, showing WP Rocket's optimized CSS layer is active. The active Theme file read directly via WPVibe confirms corrected `#596873` at line 265. Hence optimized-file staleness is a strong causal hypothesis but byte identity/content of the cached optimized file is not yet fetched; do not claim confirmed cache corruption.

A safe cache refresh was then executed via WPVibe's WordPress command `cache purge --url=https://aznet.vn/` (WP Rocket homepage URL; success), followed by `cache purge` (WP Rocket + Rank Math sitemap + object cache, success). No Theme, WordPress content or SEO data was edited. A fresh post-purge public browser comparison is **required** before claiming resolution. If cached CSS remains old, investigate cache/min regeneration and upstream CDN. The process link `#0000ee` requires a separate Theme-owned RED/Green regardless.
