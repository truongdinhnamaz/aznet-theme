# Curtain 01 — History-first Homepage live checkpoint — 2026-09-22

## Scope

Bounded follow-up to the Curtain 01 Homepage candidate after PR #243.

- Rework the Homepage About block into a history-first editorial presentation.
- Use WordPress-owned media/content references; Theme owns presentation only.
- Expose `curtain-01` as the independent **Rèm 01** Homepage preset in the admin selector.
- Keep Law 01 isolated; its variant control renders only while `law-01` is active.
- Preserve the already-approved Curtain 01 category showcase.

## Owner approval

Owner approved the history section and subsequently approved the merge gate on 2026-09-22.

## Production evidence

Fresh live verification after draft publication confirmed:

- `homepage_preset = curtain-01`
- About image resolves from WordPress attachment mapping to `anh-nen-xua.png`
- History heading: `Hai thế hệ, một nếp nghề`
- Category showcase remains rendered after the About section
- Desktop Accessibility: 100/100
- Desktop Best Practices: 100/100
- WPVibe rollback theme exists as `aznet-theme-wpvibe-backup`

## Ownership / boundary

The Theme stores only presentation/reference settings. Site media and Page content remain WordPress-owned. No product/domain store was introduced and no WooCommerce authoritative data was copied.

## Provenance

- PR #243 merged to `main` first.
- This checkpoint belongs to PR #245, branch `feature/curtain01-history-about`.
- PR #245 must be verified on its exact post-checkpoint head before merge.
