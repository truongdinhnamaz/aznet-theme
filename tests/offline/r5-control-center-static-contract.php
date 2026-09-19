<?php
$root = dirname( __DIR__, 2 );
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $root . '/' );
}

$required = [
    'inc/admin/bootstrap.php',
    'inc/admin/control-center.php',
    'inc/admin/settings-actions.php',
    'inc/admin/settings-portability.php',
    'inc/admin/system-health.php',
    'assets/css/admin/control-center.css',
];
foreach ( $required as $relative ) {
    if ( ! is_file( $root . '/' . $relative ) ) {
        fwrite( STDERR, "FAIL: missing {$relative}\n" );
        exit( 1 );
    }
}

$setup = file_get_contents( $root . '/inc/theme/setup.php' );
$bootstrap = file_get_contents( $root . '/inc/theme/bootstrap.php' );
$control = file_get_contents( $root . '/inc/admin/control-center.php' );
$health = file_get_contents( $root . '/inc/admin/system-health.php' );
$browser_workflow = file_get_contents( $root . '/.github/workflows/r5-control-center-browser.yml' );
$admin = implode( "\n", array_map( static fn( $p ) => file_get_contents( $root . '/' . $p ), array_slice( $required, 0, 5 ) ) );

$must = [
    [ $setup, "add_theme_support( 'custom-logo' );" ],
    [ $bootstrap, "require_once __DIR__ . '/../admin/bootstrap.php';" ],
    [ $admin, 'edit_theme_options' ],
    [ $admin, 'admin_post_aznet_theme_save_settings' ],
    [ $admin, 'admin_post_aznet_theme_reset_settings' ],
    [ $admin, 'admin_post_aznet_theme_export_settings' ],
    [ $admin, 'admin_post_aznet_theme_import_settings' ],
    [ $admin, '65536' ],
    [ $admin, "'convertflow' => 'unknown'" ],
    [ $control, 'function field_checkbox' ],
    [ $control, "field_checkbox( 'header_search'" ],
    [ $control, "field_checkbox( 'header_utilities'" ],
    [ $control, 'function render_quick_setup_form' ],
    [ $control, 'name="confirm_reset"' ],
    [ $control, 'type="checkbox"' ],
    [ $health, 'function support_snapshot' ],
];
foreach ( $must as [ $haystack, $needle ] ) {
    if ( false === strpos( $haystack, $needle ) ) {
        fwrite( STDERR, "FAIL: missing contract {$needle}\n" );
        exit( 1 );
    }
}

foreach ( [
    'PHP (Fatal error|Warning|Parse error)|Uncaught',
    'WP_Query::rewind_posts known core warning',
    'Undefined array key 0 in /tmp/wp/wp-includes/class-wp-query.php on line 3872',
    'r5-known-wp-core-warnings.log',
    'r5-unexpected-php-errors.log',
] as $needle ) {
    if ( false === strpos( $browser_workflow, $needle ) ) {
        fwrite( STDERR, "FAIL: R5 runtime-boundary warning policy missing {$needle}\n" );
        exit( 1 );
    }
}

require_once $root . '/inc/theme/settings.php';
$boolean_strings = \AZnet\Theme\normalize_settings(
    [
        'header_search' => '0',
        'header_utilities' => '1',
    ]
);
if ( false !== $boolean_strings['header_search'] || true !== $boolean_strings['header_utilities'] ) {
    fwrite( STDERR, "FAIL: admin-form boolean strings are not normalized safely\n" );
    exit( 1 );
}

$forbidden = [
    'wp_insert_post',
    'wp_update_post',
    'wp_create_nav_menu',
    'wc_create_order',
    'register_post_type',
    'DB_PASSWORD',
    'AUTH_KEY',
    'SECURE_AUTH',
    '$_SERVER',
    'wpdb->',
];
foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $admin, $needle ) ) {
        fwrite( STDERR, "FAIL: forbidden admin behavior {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: R5 Control Center static contract\n";
