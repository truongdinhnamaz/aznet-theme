<?php
$root = dirname( __DIR__, 2 );
$path = $root . '/inc/admin/settings.php';
$bootstrap = $root . '/inc/admin/bootstrap.php';
$control = $root . '/inc/admin/control-center.php';

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
    "isset( \$_POST['confirm_reset'] )",
    '\\AZnet\\Theme\\Settings\\save(',
    '\\AZnet\\Theme\\Settings\\reset()',
    "admin_url( 'admin.php?page=aznet-theme' )",
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

$bootstrap_source = file_get_contents( $bootstrap );
foreach ( [
    "require_once __DIR__ . '/settings.php';",
    "add_action( 'admin_post_aznet_theme_save_u0_settings'",
    "add_action( 'admin_post_aznet_theme_reset_settings'",
] as $needle ) {
    if ( false === strpos( $bootstrap_source, $needle ) ) {
        fwrite( STDERR, "missing admin write hook: {$needle}\n" );
        exit( 1 );
    }
}

$control_source = file_get_contents( $control );
foreach ( [
    "name=\"action\" value=\"aznet_theme_save_u0_settings\"",
    "wp_nonce_field( 'aznet_theme_save_u0_settings' )",
    "name=\"action\" value=\"aznet_theme_reset_settings\"",
    "wp_nonce_field( 'aznet_theme_reset_settings' )",
    "name=\"confirm_reset\"",
] as $needle ) {
    if ( false === strpos( $control_source, $needle ) ) {
        fwrite( STDERR, "missing Control Center write form: {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: U0 admin write security contract\n";
