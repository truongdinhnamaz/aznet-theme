<?php

$root = dirname( __DIR__, 2 );
$css = $root . '/assets/css/components/page.css';

if ( ! is_file( $css ) ) {
    fwrite( STDERR, "FAIL: page.css missing\n" );
    exit( 1 );
}

$source = file_get_contents( $css );
if ( false === $source ) {
    fwrite( STDERR, "FAIL: unable to read page.css\n" );
    exit( 1 );
}

$required = [
    '.aznet-theme-latest-posts-cards',
    'grid-template-columns',
    '.wp-block-latest-posts__featured-image',
    'aspect-ratio',
    '.wp-block-latest-posts__post-title',
    '.aznet-theme-latest-posts-cards.wp-block-latest-posts.is-grid',
    'width: auto;',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "FAIL: knowledge/latest-posts presentation missing {$needle}\n" );
        exit( 1 );
    }
}

if ( false !== strpos( $source, '.page-id-43' ) ) {
    fwrite( STDERR, "FAIL: presentation must not depend on site-specific Page ID\n" );
    exit( 1 );
}

echo "PASS: opt-in Latest Posts cards presentation is reusable and page-id independent.\n";
