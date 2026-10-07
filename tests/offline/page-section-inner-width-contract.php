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
    '.aznet-theme-page--landing .aznet-theme-page__content-inner',
    'var(--aznet-theme-container-wide)',
    '.aznet-theme-page--landing .aznet-theme-page__content-inner > .wp-block-group',
    'width: 100vw;',
    'margin-inline: calc(50% - 50vw);',
    'calc((100vw - var(--aznet-theme-container-wide)) / 2)',
    'var(--aznet-theme-gutter-mobile)',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "FAIL: landing page section inner-width contract missing {$needle}\n" );
        exit( 1 );
    }
}

echo "PASS: full-width page sections retain a bounded readable inner content width.\n";
