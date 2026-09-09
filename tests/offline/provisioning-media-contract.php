<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', dirname( __DIR__, 2 ) . '/' ); }
$root = dirname( __DIR__, 2 );
$module = $root . '/inc/theme/provisioning-media.php';
assert( is_file( $module ), 'provisioning-media.php must exist' );
require_once $root . '/inc/theme/provisioning-blueprints.php';
require_once $root . '/inc/theme/provisioning-provenance.php';
require_once $module;

assert( function_exists( 'AZnet\\Theme\\provisioning_media_definitions' ) );
assert( function_exists( 'AZnet\\Theme\\provisioning_import_media' ) );
$defs = \AZnet\Theme\provisioning_media_definitions();
assert( array_keys( $defs ) === [ 'hero', 'editorial-1', 'editorial-2', 'editorial-3', 'editorial-4' ] );
foreach ( $defs as $role => $def ) {
    assert( isset( $def['path'], $def['alt'] ) );
    assert( str_starts_with( (string) $def['path'], 'assets/starter/law01/' ) );
    assert( str_ends_with( (string) $def['path'], '.webp' ) );
    assert( '' !== trim( (string) $def['alt'] ) );
    $path = $root . '/' . $def['path'];
    assert( is_file( $path ), 'starter media missing: ' . $path );
    $head = file_get_contents( $path, false, null, 0, 12 );
    assert( is_string( $head ) && strlen( $head ) >= 12 );
    assert( 'RIFF' === substr( $head, 0, 4 ) && 'WEBP' === substr( $head, 8, 4 ), 'starter media must be WebP: ' . $role );
}

$templates = '';
foreach ( glob( $root . '/template-parts/homepage/law-01/*.php' ) ?: [] as $file ) { $templates .= (string) file_get_contents( $file ); }
assert( ! str_contains( $templates, 'assets/starter/law01/' ), 'Law 01 runtime templates must not hard-code packaged starter media URLs' );

$prov = file_get_contents( $root . '/inc/theme/provisioning-provenance.php' );
assert( is_string( $prov ) && str_contains( $prov, "'attachment'" ), 'provenance lookup must support attachments' );

$runner = file_get_contents( $root . '/inc/theme/provisioning-runner.php' );
foreach ( [ "'import_media'", "'create_post'", "'assign_featured_media'" ] as $needle ) {
    assert( is_string( $runner ) && str_contains( $runner, $needle ), 'runner missing v1.1 operation: ' . $needle );
}

echo "PASS: Law v1.1 starter media definitions, package assets and runner boundary\n";
