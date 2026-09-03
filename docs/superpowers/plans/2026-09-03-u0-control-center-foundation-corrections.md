# U0 Control Center Foundation — Plan Corrections

Status: Accepted implementation-plan correction against the owner-approved Control Center spec.
Date: 2026-09-03
Applies to: `docs/superpowers/plans/2026-09-03-u0-control-center-foundation.md`

These corrections do not change the approved architecture. Where this file conflicts with the original U0 plan, this file prevails.

## Correction 1 — Control Center is a top-level admin menu

The approved spec requires one top-level WordPress admin entry named `AZnet Theme`.

Replace the Task 2 `add_theme_page()` requirement with `add_menu_page()`.

Required implementation contract:

```php
const MENU_SLUG = 'aznet-theme';

function required_capability(): string {
    return 'edit_theme_options';
}

function register_menu(): void {
    add_menu_page(
        __( 'AZnet Theme', 'aznet-theme' ),
        __( 'AZnet Theme', 'aznet-theme' ),
        required_capability(),
        MENU_SLUG,
        __NAMESPACE__ . '\\render_overview',
        'dashicons-admin-appearance',
        58
    );
}

function is_control_center_screen( ?string $hook_suffix ): bool {
    return 'toplevel_page_' . MENU_SLUG === $hook_suffix;
}
```

Task 2 offline contract must require `add_menu_page(` and must not require `add_theme_page(`.

Control Center redirects must target:

```php
admin_url( 'admin.php?page=aznet-theme' )
```

rather than `themes.php?page=aznet-theme`.

## Correction 2 — Theme settings save API returns void

WordPress `set_theme_mod()` is a mutation API and does not provide a stable boolean return contract. The Theme settings API must therefore be:

```php
AZnet\Theme\Settings\save(array $candidate): void
```

Required implementation:

```php
function save( array $candidate ): void {
    set_theme_mod( THEME_MOD_KEY, normalize( $candidate ) );
}
```

Update Task 1 contract/self-review interface references from `save(...): bool` to `save(...): void`.

No U0 caller may branch on a return value from `save()`.

## Correction 3 — Admin wiring is verified in a real admin request

Do not use this WP-CLI assertion from Task 6:

```php
has_action( 'admin_menu', 'AZnet\\Theme\\Admin\\ControlCenter\\register_menu' )
```

WP-CLI is not an authenticated WordPress admin request and Theme admin bootstrap is intentionally gated by `is_admin()`.

Runtime verification is split as follows:

- WP-CLI proves global Theme settings schema/read/write/reset and Woo capability on/off.
- An authenticated request to `wp-admin/admin.php?page=aznet-theme` proves admin bootstrap, menu/page registration and Overview rendering.

The authenticated admin response must return HTTP 200 and contain the visible strings:

```text
AZnet Theme
Logo
Primary Menu
WooCommerce
```

This is the authoritative runtime proof for Task 2 admin wiring.

## Unchanged constraints

- Theme presentation/settings only.
- No page builder.
- No private WooCommerce/RootProfile/ConvertFlow storage or classes.
- No authoritative Page/Menu/Product/Profile/Journey creation.
- Admin CSS/JS remains scoped to AZnet Theme admin screens.
- L1/L2/L3/L4/L5/L6 remain independent evidence layers.
