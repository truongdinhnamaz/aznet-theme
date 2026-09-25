<?php
/** Runtime parity assertions for the shared Law 01 Homepage surface model. */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$surfaces = \AZnet\Theme\homepage_effective_surface_map( 'law-01' );
$keys = array_values( array_map(
    static fn ( array $surface ): string => (string) ( $surface['key'] ?? '' ),
    $surfaces
) );

$expected = [ 'hero', 'services', 'profile', 'front-page-content', 'latest', 'topics', 'analysis', 'news', 'process', 'faq', 'final-cta' ];
if ( $keys !== $expected ) {
    fwrite( STDERR, 'Homepage surface order mismatch: ' . wp_json_encode( $keys ) . PHP_EOL );
    exit( 1 );
}

$services = null;
foreach ( $surfaces as $surface ) {
    if ( 'services' === ( $surface['key'] ?? '' ) ) {
        $services = $surface;
        break;
    }
}

$service_items = is_array( $services['model']['items'] ?? null ) ? $services['model']['items'] : [];
if ( 6 !== count( $service_items ) ) {
    fwrite( STDERR, 'Homepage Services effective item count mismatch: ' . count( $service_items ) . PHP_EOL );
    exit( 1 );
}

foreach ( $surfaces as $surface ) {
    $anchor = trim( (string) ( $surface['anchor'] ?? '' ) );
    $boundary = (string) ( $surface['boundary'] ?? '' );
    if ( '' === $anchor || ! in_array( $boundary, [ 'before', 'native', 'after' ], true ) ) {
        fwrite( STDERR, 'Homepage effective surface metadata is incomplete for ' . (string) ( $surface['key'] ?? 'unknown' ) . PHP_EOL );
        exit( 1 );
    }
}

echo wp_json_encode(
    [
        'keys' => $keys,
        'services' => count( $service_items ),
        'surface_count' => count( $surfaces ),
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
