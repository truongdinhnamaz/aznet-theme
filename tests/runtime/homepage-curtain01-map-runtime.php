<?php
/** Runtime parity assertions for the shared Curtain 01 Homepage surface model. */
if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$surfaces = \AZnet\Theme\homepage_effective_surface_map( 'curtain-01' );
$keys = array_values(
    array_map(
        static fn ( array $surface ): string => (string) ( $surface['key'] ?? '' ),
        $surfaces
    )
);

$expected = [
    'hero',
    'proof',
    'front-page-content',
    'about',
    'category-showcase',
    'catalogue',
    'process',
    'projects',
    'knowledge',
    'final-cta',
];

if ( $keys !== $expected ) {
    fwrite( STDERR, 'Curtain 01 Homepage surface order mismatch: ' . wp_json_encode( $keys ) . PHP_EOL );
    exit( 1 );
}

foreach ( $surfaces as $surface ) {
    $key = (string) ( $surface['key'] ?? '' );
    $anchor = trim( (string) ( $surface['anchor'] ?? '' ) );
    $boundary = (string) ( $surface['boundary'] ?? '' );
    if ( '' === $key || '' === $anchor || ! in_array( $boundary, [ 'before', 'native', 'after' ], true ) ) {
        fwrite( STDERR, 'Curtain 01 effective surface metadata is incomplete for ' . ( '' !== $key ? $key : 'unknown' ) . PHP_EOL );
        exit( 1 );
    }
}

$source_by_key = [];
foreach ( $surfaces as $surface ) {
    $source_by_key[ (string) $surface['key'] ] = [
        'type' => (string) ( $surface['source_type'] ?? '' ),
        'id'   => (int) ( $surface['source_id'] ?? 0 ),
    ];
}

foreach ( [ 'hero' => 'wp_block', 'proof' => 'wp_block', 'about' => 'page', 'process' => 'page', 'projects' => 'category', 'final-cta' => 'page' ] as $key => $type ) {
    if ( ( $source_by_key[ $key ]['type'] ?? '' ) !== $type || (int) ( $source_by_key[ $key ]['id'] ?? 0 ) <= 0 ) {
        fwrite( STDERR, 'Curtain 01 effective source mismatch for ' . $key . PHP_EOL );
        exit( 1 );
    }
}

echo wp_json_encode(
    [
        'keys'          => $keys,
        'surface_count' => count( $surfaces ),
        'sources'       => $source_by_key,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
) . PHP_EOL;
