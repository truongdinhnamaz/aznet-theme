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

## Correction 4 — Each bounded U0 commit must remain runtime-loadable

The original plan made Task 2 `inc/admin/bootstrap.php` require `inc/admin/settings.php` and register admin-post callbacks before Task 3 created that handler file. It also registered the admin asset callback before Task 4 created `enqueue_assets()`. That would make intermediate bounded commits unsafe in real admin requests.

Use this sequencing instead.

### Task 2 admin bootstrap

Task 2 creates `inc/admin/bootstrap.php` with only the Control Center module and menu hook:

```php
<?php
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/control-center.php';

function bootstrap(): void {
    add_action( 'admin_menu', '\\AZnet\\Theme\\Admin\\ControlCenter\\register_menu' );
}
```

Task 2 must not yet require `inc/admin/settings.php`, register `admin_post_*` callbacks, or register `admin_enqueue_scripts`.

### Task 3 extends admin bootstrap

After `inc/admin/settings.php` exists, Task 3 modifies `inc/admin/bootstrap.php` to:

```php
require_once __DIR__ . '/control-center.php';
require_once __DIR__ . '/settings.php';

function bootstrap(): void {
    add_action( 'admin_menu', '\\AZnet\\Theme\\Admin\\ControlCenter\\register_menu' );
    add_action( 'admin_post_aznet_theme_save_u0_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_save' );
    add_action( 'admin_post_aznet_theme_reset_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_reset' );
}
```

### Task 4 extends admin bootstrap again

Only after `AZnet\Theme\Admin\ControlCenter\enqueue_assets(string $hook_suffix): void` exists does Task 4 add:

```php
add_action( 'admin_enqueue_scripts', '\\AZnet\\Theme\\Admin\\ControlCenter\\enqueue_assets' );
```

This ordering preserves the bounded-commit rule: every Task 1–4 commit is independently loadable and rollback-safe.

## Unchanged constraints

- Theme presentation/settings only.
- No page builder.
- No private WooCommerce/RootProfile/ConvertFlow storage or classes.
- No authoritative Page/Menu/Product/Profile/Journey creation.
- Admin CSS/JS remains scoped to AZnet Theme admin screens.
- L1/L2/L3/L4/L5/L6 remain independent evidence layers.
