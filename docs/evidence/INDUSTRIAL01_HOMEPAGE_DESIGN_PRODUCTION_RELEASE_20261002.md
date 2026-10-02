# Industrial 01 Homepage Design production release checkpoint — 02/10/2026

## Scope

This checkpoint records the approved publication of the current AZnet Theme draft to production on `minhnguyen.vn`, followed by fresh public production verification.

## Approval and safety gate

- User explicitly instructed: `publish minhnguyen.vn`.
- User then confirmed a fresh backup had been created before publication.
- WPVibe publish additionally created the theme-directory rollback copy `aznet-theme-wpvibe-backup`.
- No database mutation was introduced by the Theme publish step beyond normal WordPress/theme runtime behavior.

## Canonical source

- Canonical repository: `truongdinhnamaz/aznet-theme`.
- PR #342 merged to `main@db7ec0621e3bc61d15d9ab74d25196b8bb3d7934`.
- The production publish used the verified Minh Nguyên draft containing the same bounded Industrial 01 Homepage Design parity slice.

## Production state — minhnguyen.vn

- WordPress: 7.1.2.
- PHP: 8.2.33.
- Active theme: AZnet Theme 1.3.65.
- Production theme directory: `aznet-theme`.
- Rollback theme directory: `aznet-theme-wpvibe-backup`.
- Rank Math sitemap cache was purged and object cache flushed automatically after theme publication.

## Fresh production verification

Public production HTML at `https://minhnguyen.vn/` rendered successfully after publish.

Observed presentation/runtime markers include:

- `aznet-theme-preset--industrial-01` on the page body.
- Industrial 01 Hero rendered with the configured kicker, title, CTA links and Hero image.
- Existing navigation, WooCommerce-facing presentation, category grid and product grid rendered after publish.

Fresh mobile Lighthouse on the production URL:

- Accessibility: **100/100**.
- Best Practices: **100/100**.
- No issues flagged in either audited category.

## QA disposition

- L1-L2 canonical repository evidence: PASS through PR #342 and its completed CI gate.
- Public production L4 accessibility/best-practices: PASS for the audited mobile categories above.
- Production front-end render after publish: PASS at the observed page-render scope.
- Authenticated wp-admin interaction remains UNKNOWN because the available rendered-page session is not authenticated into WordPress admin.
- This checkpoint does not infer authenticated admin L3/L4 PASS.
- Rèm 01 remains separately blocked by the previously recorded WPVibe HTTP 503 state.

## Rollback

If production regression is discovered, restore from the user's pre-publish backup or the WPVibe theme backup `aznet-theme-wpvibe-backup`, then re-verify the affected QA layer.
