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
    if ( ! is_string( $source ) ) {
        fwrite( STDERR, "cannot read U0 production path {$relative}\n" );
        exit( 1 );
    }
    foreach ( $forbidden as $needle ) {
        if ( false !== strpos( $source, $needle ) ) {
            fwrite( STDERR, "forbidden ownership token {$needle} in {$relative}\n" );
            exit( 1 );
        }
    }
}

$settings = file_get_contents( $root . '/inc/theme/settings.php' );
if ( false === strpos( $settings, "const THEME_MOD_KEY = 'aznet_theme_settings';" ) ) {
    fwrite( STDERR, "Theme settings storage key is not bounded\n" );
    exit( 1 );
}

$admin = file_get_contents( $root . '/inc/admin/control-center.php' );
if ( false === strpos( $admin, "get_theme_mod( 'custom_logo'" ) || false === strpos( $admin, "has_nav_menu( 'primary' )" ) ) {
    fwrite( STDERR, "WordPress-owned logo/menu status must remain native\n" );
    exit( 1 );
}

if ( false === strpos( $admin, '\\AZnet\\Theme\\Integrations\\WooCommerce\\available()' ) ) {
    fwrite( STDERR, "Woo availability must go through public Theme adapter\n" );
    exit( 1 );
}

echo "PASS: U0 ownership static contract\n";
