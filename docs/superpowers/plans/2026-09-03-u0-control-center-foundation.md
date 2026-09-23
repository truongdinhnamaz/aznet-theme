# U0 Control Center Foundation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add the first customer-visible AZnet Theme Control Center foundation: one scoped admin entry, a useful Overview, a versioned Theme-owned settings model, safe save/reset plumbing, and admin assets that load only on AZnet Theme screens.

**Architecture:** Keep Theme-owned settings and frontend-safe normalization in `inc/theme/settings.php`; keep WordPress admin wiring/rendering under `inc/admin/`; wire the admin layer from the existing Theme bootstrap without touching WooCommerce/RootProfile domain ownership. U0 stores only Theme presentation configuration in one structured Theme Mod named `aznet_theme_settings`; WordPress-native logo/menu/site data stays native and provider-owned data stays outside Theme storage.

**Tech Stack:** WordPress 6.9+ public APIs, PHP 8.1+, hybrid PHP theme + `theme.json`, vanilla CSS, no React, no bundler, no proprietary framework.

**Spec:** `docs/superpowers/specs/2026-09-03-aznet-theme-control-center-design.md`

## Global Constraints

- Canonical repo: `truongdinhnamaz/aznet-theme`.
- Production baseline before this work: `main@540669b440e7d36c1f2a6f33bef2fec80cc60fff`.
- Scope is AZnet Theme only.
- `SOURCE OWNS DATA. THEME OWNS PRESENTATION. INTEGRATION CONTRACTS CONNECT THEM.`
- WordPress minimum: 6.9+.
- PHP minimum: 8.1+.
- Naming family: namespace `AZnet\\Theme`, procedural `aznet_theme_`, constants `AZNET_THEME_`, CSS `aznet-theme-`, custom properties `--aznet-theme-*`.
- No private WooCommerce/RootProfile/ConvertFlow classes, options, meta, CPTs, tables, or storage reads.
- No custom page builder, no React/bundler, no copied Flatsome/UX Builder code/assets/layouts/UI.
- Admin CSS loads only on AZnet Theme admin screens.
- U0 must not create Pages, Menus, Products, Profiles, Journeys, or any other authoritative/domain records.
- One-time source-governance reconciliation for the accepted Control Center decision is required before treating the architecture change as canonical; U0 progress itself must stay in GitHub/evidence rather than causing repeated AZT source-document churn.
- QA layers remain separate: L1 static != L2 contract != L3 runtime != L4 browser/a11y != L5 integration != L6 delivery.

---

## File Structure Locked for U0

### Production files

- Create `inc/theme/settings.php`
  - Theme-owned settings schema version, defaults, normalization, read/write/reset helpers.
- Create `inc/admin/bootstrap.php`
  - Admin-only hook registration and module wiring.
- Create `inc/admin/control-center.php`
  - Top-level admin menu, Overview screen model, Overview renderer, screen identity helper.
- Create `inc/admin/settings.php`
  - Nonce/capability-protected POST handlers for saving the small U0 settings envelope and resetting Theme-owned settings.
- Create `assets/css/admin/control-center.css`
  - Scoped WordPress-native styling for the Overview and notices.
- Modify `inc/theme/bootstrap.php`
  - Require Theme settings for all requests; require/admin-wire Control Center only in admin context.

### Contract tests

- Create `tests/offline/u0-settings-contract.php`
  - Defaults, schema version, normalization, unknown-key dropping, Theme Mod API usage, no direct option access.
- Create `tests/offline/u0-admin-control-center-contract.php`
  - Menu slug/capability, Overview status model boundaries, WordPress-native logo/menu checks, Woo availability via existing adapter.
- Create `tests/offline/u0-admin-write-security-contract.php`
  - Nonce/capability checks, sanctioned Theme Mod writes only, reset boundary.
- Create `tests/offline/u0-admin-asset-scope-contract.php`
  - Admin stylesheet handle/path plus screen-scoped enqueue guard.
- Create `tests/offline/u0-ownership-static-contract.php`
  - Reject forbidden plugin/private storage/domain patterns in new U0 production files.

### Runtime/browser verification infrastructure

Do not merge test infrastructure into production `main` merely to prove U0. Reuse the established isolated GitHub Actions WordPress/Woo pattern on a test-only verification branch stacked on the U0 candidate.

---

### Task 1: Theme-Owned Settings Schema and Normalization

**Files:**
- Create: `inc/theme/settings.php`
- Test: `tests/offline/u0-settings-contract.php`

**Interfaces:**
- Produces: `AZnet\\Theme\\Settings\\SCHEMA_VERSION` integer constant.
- Produces: `AZnet\\Theme\\Settings\\THEME_MOD_KEY` string constant with exact value `aznet_theme_settings`.
- Produces: `AZnet\\Theme\\Settings\\defaults(): array`.
- Produces: `AZnet\\Theme\\Settings\\normalize(mixed $candidate): array`.
- Produces: `AZnet\\Theme\\Settings\\get(): array`.
- Produces: `AZnet\\Theme\\Settings\\save(array $candidate): bool`.
- Produces: `AZnet\\Theme\\Settings\\reset(): void`.
- Consumers: later U0 admin tasks; U1 Design/Presets plan.

- [ ] **Step 1: Write the failing settings contract**

Create `tests/offline/u0-settings-contract.php` with assertions that the source file exists and includes the exact public contract below:

```php
<?php
$root = dirname( __DIR__, 2 );
$path = $root . '/inc/theme/settings.php';

if ( ! is_file( $path ) ) {
    fwrite( STDERR, "missing U0 settings module\n" );
    exit( 1 );
}

$source = file_get_contents( $path );
$required = [
    "const SCHEMA_VERSION = 1;",
    "const THEME_MOD_KEY = 'aznet_theme_settings';",
    'function defaults(): array',
    'function normalize( mixed $candidate ): array',
    'function get(): array',
    'function save( array $candidate ): bool',
    'function reset(): void',
    "get_theme_mod( THEME_MOD_KEY",
    'set_theme_mod( THEME_MOD_KEY',
    'remove_theme_mod( THEME_MOD_KEY',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "missing settings contract: {$needle}\n" );
        exit( 1 );
    }
}

$forbidden = [
    "get_option( 'theme_mods_",
    "update_option( 'theme_mods_",
    "delete_option( 'theme_mods_",
    '$_POST',
    'WC()->',
    'get_post_meta(',
    'get_user_meta(',
];

foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $source, $needle ) ) {
        fwrite( STDERR, "forbidden settings ownership: {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: U0 settings source contract\n";
```

- [ ] **Step 2: Run the contract and verify RED**

Run:

```bash
php tests/offline/u0-settings-contract.php
```

Expected: exit `1` with `missing U0 settings module`.

- [ ] **Step 3: Implement the minimal settings module**

Create `inc/theme/settings.php` with this structure and exact effective schema:

```php
<?php
namespace AZnet\Theme\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const SCHEMA_VERSION = 1;
const THEME_MOD_KEY = 'aznet_theme_settings';

function defaults(): array {
    return [
        'schema_version' => SCHEMA_VERSION,
        'preset'         => '',
        'design'         => [],
        'header'         => [],
        'footer'         => [],
        'content'        => [],
        'woocommerce'    => [],
    ];
}

function normalize( mixed $candidate ): array {
    $defaults = defaults();
    if ( ! is_array( $candidate ) ) {
        return $defaults;
    }

    $normalized = $defaults;
    $normalized['schema_version'] = SCHEMA_VERSION;

    if ( isset( $candidate['preset'] ) && is_string( $candidate['preset'] ) ) {
        $normalized['preset'] = sanitize_key( $candidate['preset'] );
    }

    foreach ( [ 'design', 'header', 'footer', 'content', 'woocommerce' ] as $section ) {
        if ( isset( $candidate[ $section ] ) && is_array( $candidate[ $section ] ) ) {
            $normalized[ $section ] = $candidate[ $section ];
        }
    }

    return $normalized;
}

function get(): array {
    return normalize( get_theme_mod( THEME_MOD_KEY, [] ) );
}

function save( array $candidate ): bool {
    return set_theme_mod( THEME_MOD_KEY, normalize( $candidate ) );
}

function reset(): void {
    remove_theme_mod( THEME_MOD_KEY );
}
```

For U0, nested section arrays are carried but not yet interpreted. U1/U2/U3 must add explicit field-level allow-lists before those sections accept customer values. U0 admin writes must not expose arbitrary nested payload input.

- [ ] **Step 4: Run the contract and PHP syntax**

Run:

```bash
php tests/offline/u0-settings-contract.php
php -l inc/theme/settings.php
```

Expected: both exit `0`; first output includes `PASS: U0 settings source contract`.

- [ ] **Step 5: Commit Task 1**

```bash
git add inc/theme/settings.php tests/offline/u0-settings-contract.php
git commit -m "feat: add Theme settings foundation"
```

---

### Task 2: Admin Bootstrap and Top-Level Control Center Menu

**Files:**
- Create: `inc/admin/bootstrap.php`
- Create: `inc/admin/control-center.php`
- Modify: `inc/theme/bootstrap.php`
- Test: `tests/offline/u0-admin-control-center-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Settings\\get()`.
- Consumes: `AZnet\\Theme\\Integrations\\WooCommerce\\available()`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\MENU_SLUG` = `aznet-theme`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\required_capability(): string` returning `edit_theme_options`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\register_menu(): void`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\is_control_center_screen(?string $hook_suffix): bool`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\overview_status(): array`.
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\render_overview(): void`.

- [ ] **Step 1: Write the failing admin Control Center contract**

Create `tests/offline/u0-admin-control-center-contract.php` requiring:

```php
<?php
$root = dirname( __DIR__, 2 );
$bootstrap = $root . '/inc/admin/bootstrap.php';
$control = $root . '/inc/admin/control-center.php';
$theme_bootstrap = $root . '/inc/theme/bootstrap.php';

foreach ( [ $bootstrap, $control ] as $path ) {
    if ( ! is_file( $path ) ) {
        fwrite( STDERR, "missing U0 admin path: {$path}\n" );
        exit( 1 );
    }
}

$control_source = file_get_contents( $control );
$required = [
    "const MENU_SLUG = 'aznet-theme';",
    'function required_capability(): string',
    "return 'edit_theme_options';",
    'function register_menu(): void',
    'add_theme_page(',
    'function is_control_center_screen( ?string $hook_suffix ): bool',
    'function overview_status(): array',
    'function render_overview(): void',
    "get_theme_mod( 'custom_logo'",
    "has_nav_menu( 'primary' )",
    '\\AZnet\\Theme\\Integrations\\WooCommerce\\available()',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $control_source, $needle ) ) {
        fwrite( STDERR, "missing Control Center contract: {$needle}\n" );
        exit( 1 );
    }
}

$forbidden = [
    'get_option(',
    'get_post_meta(',
    'get_user_meta(',
    'WC()->',
    'RootProfile',
    'ConvertFlow',
];

foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $control_source, $needle ) ) {
        fwrite( STDERR, "forbidden Control Center ownership: {$needle}\n" );
        exit( 1 );
    }
}

$theme_source = file_get_contents( $theme_bootstrap );
foreach ( [ "require_once __DIR__ . '/settings.php';", "require_once __DIR__ . '/../admin/bootstrap.php';" ] as $needle ) {
    if ( false === strpos( $theme_source, $needle ) ) {
        fwrite( STDERR, "missing Theme bootstrap wiring: {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: U0 admin Control Center contract\n";
```

- [ ] **Step 2: Run the contract and verify RED**

```bash
php tests/offline/u0-admin-control-center-contract.php
```

Expected: exit `1` because the admin files do not exist.

- [ ] **Step 3: Implement admin module wiring**

Create `inc/admin/bootstrap.php`:

```php
<?php
namespace AZnet\Theme\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/control-center.php';
require_once __DIR__ . '/settings.php';

function bootstrap(): void {
    add_action( 'admin_menu', '\\AZnet\\Theme\\Admin\\ControlCenter\\register_menu' );
    add_action( 'admin_enqueue_scripts', '\\AZnet\\Theme\\Admin\\ControlCenter\\enqueue_assets' );
    add_action( 'admin_post_aznet_theme_save_u0_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_save' );
    add_action( 'admin_post_aznet_theme_reset_settings', '\\AZnet\\Theme\\Admin\\Settings\\handle_reset' );
}
```

Create `inc/admin/control-center.php` with:

```php
<?php
namespace AZnet\Theme\Admin\ControlCenter;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const MENU_SLUG = 'aznet-theme';

function required_capability(): string {
    return 'edit_theme_options';
}

function register_menu(): void {
    add_theme_page(
        __( 'AZnet Theme', 'aznet-theme' ),
        __( 'AZnet Theme', 'aznet-theme' ),
        required_capability(),
        MENU_SLUG,
        __NAMESPACE__ . '\\render_overview'
    );
}

function is_control_center_screen( ?string $hook_suffix ): bool {
    return 'appearance_page_' . MENU_SLUG === $hook_suffix;
}

function overview_status(): array {
    $settings = \AZnet\Theme\Settings\get();

    return [
        'logo' => [
            'configured' => (int) get_theme_mod( 'custom_logo', 0 ) > 0,
        ],
        'primary_menu' => [
            'configured' => has_nav_menu( 'primary' ),
        ],
        'preset' => [
            'configured' => '' !== (string) $settings['preset'],
            'value'      => (string) $settings['preset'],
        ],
        'woocommerce' => [
            'available' => \AZnet\Theme\Integrations\WooCommerce\available(),
        ],
    ];
}
```

`render_overview()` must render only escaped data from `overview_status()`, show current `AZNET_THEME_VERSION`, provide links using public WordPress admin URLs to Custom Logo/Customizer-equivalent native logo path where available and Menus, and include the U0 save/reset forms from Task 3. Do not create or mutate logo/menu assignments in this renderer.

Modify `inc/theme/bootstrap.php` so `settings.php` is required for all requests, while the admin bootstrap is loaded/wired only when `is_admin()` is true:

```php
require_once __DIR__ . '/settings.php';

if ( is_admin() ) {
    require_once __DIR__ . '/../admin/bootstrap.php';
    \AZnet\Theme\Admin\bootstrap();
}
```

- [ ] **Step 4: Run the contract and syntax checks**

```bash
php tests/offline/u0-admin-control-center-contract.php
php -l inc/admin/bootstrap.php
php -l inc/admin/control-center.php
php -l inc/theme/bootstrap.php
```

Expected: all exit `0`.

- [ ] **Step 5: Commit Task 2**

```bash
git add inc/admin/bootstrap.php inc/admin/control-center.php inc/theme/bootstrap.php tests/offline/u0-admin-control-center-contract.php
git commit -m "feat: add AZnet Theme Control Center overview"
```

---

### Task 3: Secure U0 Save and Reset Actions

**Files:**
- Create: `inc/admin/settings.php`
- Modify: `inc/admin/control-center.php`
- Test: `tests/offline/u0-admin-write-security-contract.php`

**Interfaces:**
- Consumes: `AZnet\\Theme\\Settings\\get()`, `save()`, `reset()`.
- Produces: `AZnet\\Theme\\Admin\\Settings\\handle_save(): void`.
- Produces: `AZnet\\Theme\\Admin\\Settings\\handle_reset(): void`.
- U0 exposed writable field: `preset` only as sanitized informational metadata, but accepted values are limited to empty string because U1 owns actual preset registry/application. This prevents U0 from pretending preset functionality exists before U1.

- [ ] **Step 1: Write the failing security contract**

Create `tests/offline/u0-admin-write-security-contract.php` requiring:

```php
<?php
$root = dirname( __DIR__, 2 );
$path = $root . '/inc/admin/settings.php';

if ( ! is_file( $path ) ) {
    fwrite( STDERR, "missing U0 admin settings handler\n" );
    exit( 1 );
}

$source = file_get_contents( $path );
$required = [
    'function handle_save(): void',
    'function handle_reset(): void',
    "current_user_can( 'edit_theme_options' )",
    "check_admin_referer( 'aznet_theme_save_u0_settings' )",
    "check_admin_referer( 'aznet_theme_reset_settings' )",
    '\\AZnet\\Theme\\Settings\\save(',
    '\\AZnet\\Theme\\Settings\\reset()',
    'wp_safe_redirect(',
    'exit;',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "missing write security contract: {$needle}\n" );
        exit( 1 );
    }
}

$forbidden = [
    'update_option(',
    'delete_option(',
    'update_post_meta(',
    'update_user_meta(',
    'WC()->',
];

foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $source, $needle ) ) {
        fwrite( STDERR, "forbidden U0 write path: {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: U0 admin write security contract\n";
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/u0-admin-write-security-contract.php
```

Expected: exit `1` with `missing U0 admin settings handler`.

- [ ] **Step 3: Implement capability/nonce-protected handlers**

Create `inc/admin/settings.php`:

```php
<?php
namespace AZnet\Theme\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function redirect_url( string $notice ): string {
    return add_query_arg(
        [
            'page'             => 'aznet-theme',
            'aznet_theme_notice' => $notice,
        ],
        admin_url( 'themes.php' )
    );
}

function assert_allowed(): void {
    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_die( esc_html__( 'Bạn không có quyền thay đổi AZnet Theme.', 'aznet-theme' ) );
    }
}

function handle_save(): void {
    assert_allowed();
    check_admin_referer( 'aznet_theme_save_u0_settings' );

    $current = \AZnet\Theme\Settings\get();
    $current['preset'] = '';
    \AZnet\Theme\Settings\save( $current );

    wp_safe_redirect( redirect_url( 'saved' ) );
    exit;
}

function handle_reset(): void {
    assert_allowed();
    check_admin_referer( 'aznet_theme_reset_settings' );
    \AZnet\Theme\Settings\reset();

    wp_safe_redirect( redirect_url( 'reset' ) );
    exit;
}
```

In `render_overview()`, add two explicit forms:

```php
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="aznet_theme_save_u0_settings">
    <?php wp_nonce_field( 'aznet_theme_save_u0_settings' ); ?>
    <?php submit_button( __( 'Lưu thiết lập nền', 'aznet-theme' ) ); ?>
</form>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <input type="hidden" name="action" value="aznet_theme_reset_settings">
    <?php wp_nonce_field( 'aznet_theme_reset_settings' ); ?>
    <?php submit_button( __( 'Đặt lại thiết lập AZnet Theme', 'aznet-theme' ), 'delete' ); ?>
</form>
```

Do not expose arbitrary JSON/array POST input in U0.

- [ ] **Step 4: Run contract + syntax**

```bash
php tests/offline/u0-admin-write-security-contract.php
php -l inc/admin/settings.php
php -l inc/admin/control-center.php
```

Expected: all exit `0`.

- [ ] **Step 5: Commit Task 3**

```bash
git add inc/admin/settings.php inc/admin/control-center.php tests/offline/u0-admin-write-security-contract.php
git commit -m "feat: secure Control Center settings actions"
```

---

### Task 4: Scoped Admin Presentation

**Files:**
- Create: `assets/css/admin/control-center.css`
- Modify: `inc/admin/control-center.php`
- Test: `tests/offline/u0-admin-asset-scope-contract.php`

**Interfaces:**
- Produces: `AZnet\\Theme\\Admin\\ControlCenter\\enqueue_assets(string $hook_suffix): void`.
- Asset handle: `aznet-theme-control-center`.
- Asset path: `/assets/css/admin/control-center.css`.

- [ ] **Step 1: Write failing asset-scope contract**

Create `tests/offline/u0-admin-asset-scope-contract.php`:

```php
<?php
$root = dirname( __DIR__, 2 );
$php = $root . '/inc/admin/control-center.php';
$css = $root . '/assets/css/admin/control-center.css';

if ( ! is_file( $css ) ) {
    fwrite( STDERR, "missing U0 Control Center CSS\n" );
    exit( 1 );
}

$source = file_get_contents( $php );
$required = [
    'function enqueue_assets( string $hook_suffix ): void',
    'is_control_center_screen( $hook_suffix )',
    "'aznet-theme-control-center'",
    "'/assets/css/admin/control-center.css'",
    'AZNET_THEME_VERSION',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "missing admin asset scope: {$needle}\n" );
        exit( 1 );
    }
}

if ( false === strpos( file_get_contents( $css ), '.aznet-theme-control-center' ) ) {
    fwrite( STDERR, "admin CSS lacks root scope\n" );
    exit( 1 );
}

echo "PASS: U0 admin asset scope\n";
```

- [ ] **Step 2: Run RED**

```bash
php tests/offline/u0-admin-asset-scope-contract.php
```

Expected: exit `1` because the stylesheet does not exist.

- [ ] **Step 3: Implement scoped enqueue and CSS**

Add to `inc/admin/control-center.php`:

```php
function enqueue_assets( string $hook_suffix ): void {
    if ( ! is_control_center_screen( $hook_suffix ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-control-center',
        get_template_directory_uri() . '/assets/css/admin/control-center.css',
        [],
        AZNET_THEME_VERSION
    );
}
```

Create `assets/css/admin/control-center.css` using only `.aznet-theme-control-center` descendants for custom styling. Use native WordPress colors/spacing conventions; do not invent a separate application chrome. Required presentation blocks:

```css
.aznet-theme-control-center {
    max-width: 1180px;
}

.aznet-theme-control-center__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.aznet-theme-control-center__card {
    box-sizing: border-box;
    padding: 20px;
    border: 1px solid #c3c4c7;
    background: #fff;
}

.aznet-theme-control-center__status {
    font-weight: 600;
}

.aznet-theme-control-center__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
```

The Overview wrapper must use class `aznet-theme-control-center`.

- [ ] **Step 4: Run contract + syntax**

```bash
php tests/offline/u0-admin-asset-scope-contract.php
php -l inc/admin/control-center.php
```

Expected: both exit `0`.

- [ ] **Step 5: Commit Task 4**

```bash
git add assets/css/admin/control-center.css inc/admin/control-center.php tests/offline/u0-admin-asset-scope-contract.php
git commit -m "feat: style Control Center admin surface"
```

---

### Task 5: Ownership Regression and Full L1-L2 Closure

**Files:**
- Create: `tests/offline/u0-ownership-static-contract.php`
- No production changes unless a discovered violation requires the smallest root-cause fix.

**Interfaces:**
- Verifies all U0 production files and existing integration boundaries.

- [ ] **Step 1: Write ownership/static regression contract**

Create `tests/offline/u0-ownership-static-contract.php` scanning these production paths:

```php
<?php
$root = dirname( __DIR__, 2 );
$paths = [
    'inc/theme/settings.php',
    'inc/admin/bootstrap.php',
    'inc/admin/control-center.php',
    'inc/admin/settings.php',
];

$forbidden = [
    'choiceguide_',
    '_choiceguide_',
    'get_option(',
    'update_option(',
    'delete_option(',
    'get_post_meta(',
    'update_post_meta(',
    'get_user_meta(',
    'update_user_meta(',
    'WC()->',
    '$wpdb',
    'RootProfile\\',
    'ConvertFlow\\',
    'wp_insert_post(',
    'wp_create_nav_menu(',
];

foreach ( $paths as $relative ) {
    $source = file_get_contents( $root . '/' . $relative );
    foreach ( $forbidden as $needle ) {
        if ( false !== strpos( $source, $needle ) ) {
            fwrite( STDERR, "forbidden ownership token {$needle} in {$relative}\n" );
            exit( 1 );
        }
    }
}

echo "PASS: U0 ownership static contract\n";
```

- [ ] **Step 2: Run all U0 contracts**

```bash
php tests/offline/u0-settings-contract.php
php tests/offline/u0-admin-control-center-contract.php
php tests/offline/u0-admin-write-security-contract.php
php tests/offline/u0-admin-asset-scope-contract.php
php tests/offline/u0-ownership-static-contract.php
```

Expected: five PASS lines, exit `0`.

- [ ] **Step 3: Run changed-production PHP lint**

```bash
php -l inc/theme/settings.php
php -l inc/admin/bootstrap.php
php -l inc/admin/control-center.php
php -l inc/admin/settings.php
php -l inc/theme/bootstrap.php
```

Expected: `No syntax errors detected` for all five files.

- [ ] **Step 4: Run existing invalidated/focused regressions**

Because `inc/theme/bootstrap.php` changes, rerun contracts that prove existing Woo asset/surface behavior and current-surface no-takeover wiring still compile against the changed bootstrap:

```bash
php tests/offline/w1-woocommerce-absent-contract.php
php tests/offline/w1-woocommerce-surface-contract.php
php tests/offline/w1-woocommerce-asset-scope-contract.php
php tests/offline/e5-no-takeover-static-contract.php
```

Expected: all exit `0`.

- [ ] **Step 5: Commit Task 5 tests/evidence-ready state**

```bash
git add tests/offline/u0-ownership-static-contract.php
git commit -m "test: close U0 ownership regressions"
```

---

### Task 6: U0 Runtime Verification on Real WordPress

**Files:**
- Test-only verification branch stacked on the U0 candidate.
- Reuse/adapt established isolated runtime fixture pattern; do not merge runtime workflow into production unless separately approved as permanent CI architecture.

**Interfaces:**
- Runtime must prove Theme activation, Control Center registration, settings save/reset, Woo absent/present Overview state, and no frontend fatal.

- [ ] **Step 1: Create isolated verification branch from exact U0 candidate head**

```bash
git switch -c test/u0-control-center-runtime <U0_CANDIDATE_SHA>
```

The branch must contain no additional production Theme behavior.

- [ ] **Step 2: Add runtime smoke script**

Use WP-CLI inside an isolated WordPress + MySQL GitHub-hosted runtime to assert:

```bash
wp theme activate aznet-theme --path="$WP_ROOT"
wp eval 'if (!has_action("admin_menu", "AZnet\\Theme\\Admin\\ControlCenter\\register_menu")) { exit(1); }' --path="$WP_ROOT"
wp eval '$s=AZnet\\Theme\\Settings\\get(); if (($s["schema_version"] ?? 0)!==1) { exit(1); }' --path="$WP_ROOT"
wp eval 'AZnet\\Theme\\Settings\\save(["schema_version"=>999,"preset"=>"BAD VALUE","unknown"=>"drop"]); $s=AZnet\\Theme\\Settings\\get(); if (($s["schema_version"]??0)!==1 || array_key_exists("unknown",$s)) { exit(1); }' --path="$WP_ROOT"
wp eval 'AZnet\\Theme\\Settings\\reset(); $s=AZnet\\Theme\\Settings\\get(); if (($s["preset"]??null)!=="") { exit(1); }' --path="$WP_ROOT"
```

Then verify Woo capability state once with Woo inactive and once after activating WooCommerce:

```bash
wp plugin deactivate woocommerce --path="$WP_ROOT" || true
wp eval 'if (AZnet\\Theme\\Integrations\\WooCommerce\\available()) { exit(1); }' --path="$WP_ROOT"
wp plugin activate woocommerce --path="$WP_ROOT"
wp eval 'if (!AZnet\\Theme\\Integrations\\WooCommerce\\available()) { exit(1); }' --path="$WP_ROOT"
```

- [ ] **Step 3: Verify the admin page through authenticated HTTP/browser session**

Use a test administrator cookie/session and request `wp-admin/themes.php?page=aznet-theme`. Assert HTTP 200 and these visible strings:

```text
AZnet Theme
Logo
Primary Menu
WooCommerce
```

Do not use a fixture HTML file to claim L3.

- [ ] **Step 4: Save runtime evidence artifact**

Artifact must include:

```text
runtime-report.txt
control-center.html
php-error.log
```

`runtime-report.txt` must record WordPress version, PHP version, WooCommerce version, Theme version, candidate commit SHA, and each U0 runtime assertion.

- [ ] **Step 5: Only after fresh success, write `docs/evidence/U0_CONTROL_CENTER_RUNTIME_VERIFICATION.md`**

Record exact run/job/artifact IDs, candidate SHA, tested versions, PASS scope, and explicit UNKNOWN layers. Evidence commit must add only the Markdown evidence file after the verified candidate; it must not modify production bytes.

---

### Task 7: Browser/A11y Smoke and Alpha.8 Pilot Package

**Files:**
- Test-only browser verification branch based on exact U0 candidate.
- Package artifact only after L3 and L4 U0 verification.
- Production version bump/package requires owner merge/release gate; do not silently change `main` or deploy a customer site.

**Interfaces:**
- Browser target: authenticated Control Center Overview.
- Viewports: desktop `1440x1000`, compact laptop `1024x768`.
- Accessibility gate: zero axe `critical`/`serious` violations on the Control Center page.

- [ ] **Step 1: Add browser smoke for the Control Center page**

Automated assertions:

```text
- page title/heading contains AZnet Theme
- Logo status card visible
- Primary Menu status card visible
- WooCommerce status card visible
- save/reset controls visible to administrator
- no horizontal overflow > 1px
- keyboard Tab reaches first actionable control with visible focus
- axe critical = 0
- axe serious = 0
```

- [ ] **Step 2: Capture and manually review screenshots**

Save:

```text
control-center-1440x1000.png
control-center-1024x768.png
```

Review for clipped cards, overlapping WordPress admin chrome, destructive spacing, hidden buttons, and unreadable notices.

- [ ] **Step 3: Verify upgrade continuity before packaging**

Install the prior `0.1.0-alpha.7` pilot under directory exactly `aznet-theme/`, activate it, set native custom logo/menu assignments where the fixture permits, then replace it with the U0 candidate package using the same `aznet-theme/` directory. Confirm:

```text
- Theme directory remains aznet-theme/
- native menu assignment remains intact
- native custom_logo Theme Mod remains intact
- aznet_theme_settings survives an in-place package replacement when already present
- no provider/domain data is deleted
```

- [ ] **Step 4: Prepare alpha.8 pilot package only after owner-approved merge/version checkpoint**

The ZIP root must be exactly:

```text
aznet-theme/
```

Exclude:

```text
.git/
.github/
docs/
tests/
scripts/
```

Run PHP lint over all packaged PHP files and verify `style.css` + `functions.php` report the same Theme version.

- [ ] **Step 5: Record U0 delivery evidence**

Create `docs/evidence/U0_CONTROL_CENTER_PILOT_DELIVERY.md` containing package filename, SHA-256, source commit SHA, runtime/browser run IDs, tested versions, rollback package identity, and explicit statement that U1-U6 are not implied PASS.

---

## Plan Self-Review

### Spec coverage

U0 spec requirements are covered as follows:

- top-level admin page → Task 2;
- Overview → Task 2;
- versioned Theme settings schema → Task 1;
- capability/nonce/sanitization foundation → Tasks 1 and 3;
- scoped admin assets → Task 4;
- ownership/no-domain persistence → Task 5;
- real WordPress runtime → Task 6;
- browser/a11y usability → Task 7;
- in-place alpha update continuity → Task 7.

Items intentionally deferred because the approved spec assigns them to later workstreams:

- real preset registry/application → U1;
- semantic Design controls/dynamic token overrides → U1;
- Header/Footer Composer → U2;
- Content/Woo presentation controls → U3;
- Gutenberg Pattern library → U4;
- full Integrations screen + portable import/export → U5;
- broad customer-ready integration/release closure → U6.

### Placeholder scan

This plan contains no `TBD`, `TODO`, `implement later`, or undefined implementation step. Runtime commit SHA/run IDs are intentionally runtime-generated evidence values rather than design placeholders.

### Type/signature consistency

Locked U0 public-internal signatures used by later tasks:

```php
AZnet\Theme\Settings\defaults(): array
AZnet\Theme\Settings\normalize(mixed $candidate): array
AZnet\Theme\Settings\get(): array
AZnet\Theme\Settings\save(array $candidate): bool
AZnet\Theme\Settings\reset(): void
AZnet\Theme\Admin\ControlCenter\required_capability(): string
AZnet\Theme\Admin\ControlCenter\register_menu(): void
AZnet\Theme\Admin\ControlCenter\is_control_center_screen(?string $hook_suffix): bool
AZnet\Theme\Admin\ControlCenter\overview_status(): array
AZnet\Theme\Admin\ControlCenter\render_overview(): void
AZnet\Theme\Admin\ControlCenter\enqueue_assets(string $hook_suffix): void
AZnet\Theme\Admin\Settings\handle_save(): void
AZnet\Theme\Admin\Settings\handle_reset(): void
```

Later U1-U5 plans must consume these names rather than invent parallel settings/admin foundations.
