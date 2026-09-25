# D-040 / TD1.0 — Template Library admin presentation Design Packet

Date: 2026-09-25
Base: main@5d2d34074c1b1b0006151b399b6b652927a19d49
Branch: work/d040-template-library-td1
Decision: issue #273 / D-040

## Goal
Replace the flat Homepage preset selector with a scalable Template Library presentation model that can support 100+ templates without bundling or implementing the external catalog service in AZnet Theme.

## Ownership
AZnet Theme owns:
- wp-admin Template Library composition and interaction;
- normalized presentation model for template cards;
- local active-template selection UX;
- renderer/primitives already shipped by Theme core.

External AZnet Template Distribution Service owns:
- catalog publishing;
- price/commercial policy;
- license/entitlement;
- authorized downloads;
- package metadata/signing.

WordPress/RootProfile/ConvertFlow/WooCommerce retain existing authoritative domains.

## TD1 local content contract
TD1 uses a Theme-local normalized fixture/catalog model only to prove the admin presentation layer. It is not a commercial catalog and does not claim L5 integration.

Required normalized fields:
- template_id
- name
- category
- description
- availability: bundled | free | premium
- install_state: available | installed | active | update_available
- preview_url or local preview reference
- variant_count (presentation metadata only)

## UI intent
- Search field.
- Category filter.
- Responsive card grid.
- Each card exposes name, category, short description, availability/install state, preview action, and one primary action.
- Active template is visibly distinguished.
- The existing Homepage source mapping and Hero Library remain separate panels and keep current ownership.
- No price/payment form, account UI, or entitlement mutation in TD1.
- Existing template IDs `off`, `law-01`, and `curtain-01` remain compatible.

## Accessibility
- Search has a visible label or programmatic accessible name.
- Filters use native controls.
- Template cards have semantic headings.
- Primary actions are keyboard reachable.
- State is not conveyed by color alone.
- Focus order follows the visual order.

## Performance
TD1 adds no remote request and no global frontend asset. Any new CSS/JS is admin-only and scoped to the AZnet Theme Homepage section.

## Acceptance / RED target
The new offline contract must fail on main because:
1. no `homepage_template_library_items()` normalized catalog helper exists;
2. no Template Library card-grid marker exists;
3. the current preset control is still a flat `field_select()`.

GREEN later requires the smallest Theme-owned implementation that satisfies those conditions while retaining existing preset IDs and settings persistence.

## Non-goals
- No external service implementation.
- No license/payment logic.
- No package downloader/installer.
- No remote PHP or executable package.
- No production deployment or main merge.
- No changes to PR #272.

## Rollback
Revert the TD1 branch commits. Main remains untouched.

## Next
TD1.1 — run the new contract and capture the expected RED before production implementation.
