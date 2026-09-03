<?php
$root = dirname( __DIR__, 2 );
$php = $root . '/inc/admin/control-center.php';
$bootstrap = $root . '/inc/admin/bootstrap.php';
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

$bootstrap_source = file_get_contents( $bootstrap );
if ( false === strpos( $bootstrap_source, "add_action( 'admin_enqueue_scripts', '\\\\AZnet\\\\Theme\\\\Admin\\\\ControlCenter\\\\enqueue_assets' );" ) ) {
    fwrite( STDERR, "missing scoped admin asset hook\n" );
    exit( 1 );
}

$css_source = file_get_contents( $css );
if ( false === strpos( $css_source, '.aznet-theme-control-center' ) ) {
    fwrite( STDERR, "admin CSS lacks root scope\n" );
    exit( 1 );
}

$selectors = preg_split( '/\}/', $css_source );
foreach ( $selectors as $block ) {
    $parts = explode( '{', $block, 2 );
    if ( 2 !== count( $parts ) ) {
        continue;
    }
    $selector = trim( $parts[0] );
    if ( '' !== $selector && false === strpos( $selector, '.aznet-theme-control-center' ) ) {
        fwrite( STDERR, "unscoped admin CSS selector: {$selector}\n" );
        exit( 1 );
    }
}

echo "PASS: U0 admin asset scope\n";
