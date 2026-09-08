<?php
$root = dirname( __DIR__, 2 );
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
];
foreach ( $must as [ $haystack, $needle ] ) {
    if ( false === strpos( $haystack, $needle ) ) {
        fwrite( STDERR, "FAIL: missing contract {$needle}\n" );
        exit( 1 );
    }
}
$forbidden = [ 'wp_insert_post', 'wp_update_post', 'wp_create_nav_menu', 'wc_create_order', 'register_post_type', 'DB_PASSWORD', 'AUTH_KEY', 'SECURE_AUTH', '$_SERVER', 'wpdb->' ];
foreach ( $forbidden as $needle ) {
    if ( false !== strpos( $admin, $needle ) ) {
        fwrite( STDERR, "FAIL: forbidden admin behavior {$needle}\n" );
        exit( 1 );
    }
}
echo "PASS: R5 Control Center static contract\n";
