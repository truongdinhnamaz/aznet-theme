# Optional Core vertical catalog / Industrial 01 pilot — 2026-10-19

## Scope and decision
- User requested a Flatsome-like catalog menu option in the **AZnet Theme Core** and a paired **Minh Nguyên** Industrial 01 Homepage Hero presentation.
- The selected architecture is WordPress-native-first: a new **Product Catalog Menu** registered menu location, backed only by the menu already authored in WordPress. WooCommerce owns \`product_cat\`, product facts and catalogue semantics; Theme only renders \`wp_nav_menu\` HTML and owns layout/interaction. No Woo dependency, term replication, heuristic menu inference, or external plugin private storage.
- Control Center Header option \`header_catalog_mode\`: \`off\` (default), \`dropdown\`, \`homepage-rail\`. Last mode positions the menu beside Hero only for active Industrial 01 Homepage composition; fallback is the independent header disclosure for other surfaces.
- Desktop disclosure and mobile catalog accordion use native HTML \`details/summary\`, keyboard accessible without JavaScript. Desktop Industrial 01 Hero uses a side rail; on <=1100px the rail is hidden and the catalog remains accessible through the existing mobile Header panel.
- CSS is conditionally enqueued only when the option is enabled and a WordPress catalog menu is assigned. Existing sites remain default OFF.

## Real pilot source inventory (read-only)
- Site: \`https://minhnguyen.vn\`; active AZnet Theme **1.3.79**, \`visual_preset=industrial-01\`, \`homepage_preset=industrial-01\`, WooCommerce enabled (WPVibe site_info).
- Existing WordPress **Main menu** term 55 is bound to \`primary\`; existing **Menu danh mục** term **143** has **19 editor-owned menu entries** referring to real WooCommerce \`product_cat\` terms and is **not assigned to a Theme location** at inspection.
- Existing Hero image ID 1593, background ID 0, so the currently accepted fallback image becomes the background; no replacement image/new domain data required to prove core functionality.
- No WordPress option/menu changes applied at inspection.

## Rollback / explicit gates
- Revert bounded work branch commit or keep \`header_catalog_mode=off\` to preserve existing Header/Hero.
- Require fresh L1 lint + L2 contract tests + L3 real WordPress + L4 desktop/tablet/mobile & keyboard/focus/long-menu tests before production publication.
- Main merge, release and updating the real Minh Nguyên active Theme or live setting/menu location are separate owner approval gates. **Do not infer live deployment from passing CI**.
