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
    'function save( array $candidate ): void',
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
