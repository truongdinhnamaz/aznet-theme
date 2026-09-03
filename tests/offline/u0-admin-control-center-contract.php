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
    'add_menu_page(',
    "'dashicons-admin-appearance'",
    'function is_control_center_screen( ?string $hook_suffix ): bool',
    "'toplevel_page_' . MENU_SLUG",
    'function overview_status(): array',
    'function render_overview(): void',
    "get_theme_mod( 'custom_logo'",
    "has_nav_menu( 'primary' )",
    '\\AZnet\\Theme\\Integrations\\WooCommerce\\available()',
    'AZNET_THEME_VERSION',
    "admin_url( 'customize.php",
    "admin_url( 'nav-menus.php' )",
];

foreach ( $required as $needle ) {
    if ( false === strpos( $control_source, $needle ) ) {
        fwrite( STDERR, "missing Control Center contract: {$needle}\n" );
        exit( 1 );
    }
}

$forbidden = [
    'add_theme_page(',
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

$admin_source = file_get_contents( $bootstrap );
foreach ( [
    "require_once __DIR__ . '/control-center.php';",
    "add_action( 'admin_menu', '\\\\AZnet\\\\Theme\\\\Admin\\\\ControlCenter\\\\register_menu' );",
] as $needle ) {
    if ( false === strpos( $admin_source, $needle ) ) {
        fwrite( STDERR, "missing admin bootstrap wiring: {$needle}\n" );
        exit( 1 );
    }
}

$theme_source = file_get_contents( $theme_bootstrap );
foreach ( [
    "require_once __DIR__ . '/settings.php';",
    "require_once __DIR__ . '/../admin/bootstrap.php';",
    '\\AZnet\\Theme\\Admin\\bootstrap();',
] as $needle ) {
    if ( false === strpos( $theme_source, $needle ) ) {
        fwrite( STDERR, "missing Theme bootstrap wiring: {$needle}\n" );
        exit( 1 );
    }
}

if ( substr_count( $theme_source, "add_action( 'after_setup_theme', __NAMESPACE__ . '\\\\setup' );" ) !== 1
    || substr_count( $theme_source, "add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\\\enqueue_assets' );" ) !== 1 ) {
    fwrite( STDERR, "existing Theme hooks were not preserved exactly\n" );
    exit( 1 );
}

if ( substr_count( $theme_source, 'add_action(' ) !== 2 ) {
    fwrite( STDERR, "admin hooks must stay outside inc/theme/bootstrap.php\n" );
    exit( 1 );
}

echo "PASS: U0 admin Control Center contract\n";
