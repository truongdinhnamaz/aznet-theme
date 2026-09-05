<?php
$root = dirname( __DIR__, 2 );
$path = $root . '/inc/theme/setup.php';
$source = file_get_contents( $path );
if ( false === strpos( $source, "add_theme_support( 'custom-logo'" ) ) {
    fwrite( STDERR, "missing native custom-logo Theme support\n" );
    exit( 1 );
}
echo "PASS: U0 native custom-logo support contract\n";
