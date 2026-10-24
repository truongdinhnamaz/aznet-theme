# D-063 — AZnet Header: stale WP Rocket minified Digital 01 CSS (2026-10-22)

## Scope / status
**BLOCKED on WP Rocket minified asset invalidation; not a Theme source bug.** Read-only live-browser diagnostic and Theme CSS rollback completed. No new Theme runtime changes, WordPress native menu/content mutations, plugin-source edits, code deployment, or main merge.

### Symptoms reproduced
Owner screenshot `/gioi-thieu`: menu and logo cramped/overlapping. Authenticated WordPress HTML contains both native Primary (3 items, one long title) and header-utility (8 items) navigation plus search/account/cart. Both are source-owned by WordPress; Theme owns responsive display.

### Source baseline
- Active site: AZnet Theme **1.3.96** on `aznet.vn`.
- Canonical `main` after approved PR #501: `f9bdffed17d358a3a0667d4c92ce02955d955f2f`.
- Verified live Theme source `assets/css/presets/digital-01.css`, 54 lines, already contains D-057 Header guards: `@media (min-width:1501px)` to remove duplicate primary rail, `@media (min-width:981px) and (max-width:1500px)` to switch to mobile disclosure.
- Live PHP/HTML enqueue points to the hashed original stylesheet `/wp-content/themes/aznet-theme/assets/css/presets/digital-01.css?ver=1.3.96-8dd099066fb0`.

### Real-browser RED, public logged-out CSS
- Exact-head CI run [39750398836](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39750398836) exposed a candidate regression: unnecessarily hiding inner-page Search and Account links, while 1321–1500px still lacked the accessible mobile trigger because the public cached preset is stale. The original D-063 CSS workaround must not ship.
- Actual CSS URL returned to Chromium on 22 Oct:
  `https://aznet.vn/wp-content/cache/min/1/wp-content/themes/aznet-theme/assets/css/presets/digital-01.css?ver=1792621629`.
- HTTP 200, **1,289 chars**, but **missing both D-057 breakpoint rules** in this optimized response.
- At 1366px, baseline Header overflow reported; applying fresh candidate D-057 styles from the Git source in an earlier browser run [39748364482](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39748364482) removed Header overflow and exposed correct mobile disclosure.

### Attempted recovery and outcome
- Connected WordPress `wp cache purge` succeeded: WPVibe reports **WP Rocket**, Rank Math sitemap cache, and object cache purged.
- Retest **after purge** at [39750921929](https://github.com/truongdinhnamaz/aznet-theme/actions/runs/39750921929) still returned **the same 1,289-char minified CSS URL and missing rules**, with header overflow at 1321, 1320, 1280, 1024 and 1366px.
- Official WP Rocket documentation distinguishes `rocket_clean_domain()` / page cache from `rocket_clean_minify()` / `wp-content/cache/min/` and says normal domain cache purge alone does **not** necessarily purge `/cache/min/`. References:
  - https://docs.wp-rocket.me/article/1135-when-does-wp-rocket-delete-which-files
  - https://docs.wp-rocket.me/article/1801-how-to-programmatically-clear-the-cache-and-optimizations
- The connected WPVibe WP-CLI emulator exposes `cache purge` only, not `rocket_clean_minify`. WordPress registered `wp-rocket/v1` REST routes include no safe minified-asset purge endpoint. Do not bypass via private plugin storage, direct PHP eval, SQL, WPCode/plugin installation or unapproved production deployment.

### Source rollback / owner boundaries
- The original unnecessary 72-line workaround was reverted on isolated branch at commit `618ae1a63624d0d160ad9eefadc9de3da90122b8`.
- `assets/css/components/header-utility.css` has **exactly the same Git blob SHA** as canonical main (`c10f79fd5996d5ebfb6807e12ff571d26d0d283f`).
- D-063 branch now carries **diagnostic test + workflow only**, not a runtime fix. No main merge or Theme release requested.

## EXACT NEXT (external operational gate)
Use WP Rocket administrator's **Clear and Preload Cache** command which includes cleanup of `/cache/min/` (or otherwise invoke WP Rocket's officially documented `rocket_clean_minify()` via an owner-authorized maintenance path), then run **D-063 public browser L4** on actual served CSS at 1705/1600/1500/1440/1366/1320/1280/1024/390px and the homepage. Do not declare production PASS before stylesheet `hasRule=true`, no Header overflow/collisions, Search and Account retained on applicable inner-page desktop, keyboard/Escape menu PASS.

If the minified stylesheet remains stale after an authorized minified-cache clear, investigate an upstream CDN/proxy layer or file-optimization regeneration separately. Never substitute a duplicate Theme CSS rule without proof.
