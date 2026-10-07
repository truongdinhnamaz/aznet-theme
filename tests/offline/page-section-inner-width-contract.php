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

if ( ! preg_match(
    '/\.aznet-theme-page--landing \.aznet-theme-page__content-inner\s*\{([^}]*)\}/s',
    $source,
    $content_inner_match
) ) {
    fwrite( STDERR, "FAIL: landing content-inner rule missing\n" );
    exit( 1 );
}

foreach ( [ 'width: 100%;', 'max-width: none;' ] as $needle ) {
    if ( false === strpos( $content_inner_match[1], $needle ) ) {
        fwrite( STDERR, "FAIL: landing content-inner must remain full-width: {$needle}\n" );
        exit( 1 );
    }
}

if ( ! preg_match(
    '/\.aznet-theme-page--landing \.aznet-theme-page__content-inner > \.wp-block-group,\s*\.aznet-theme-page--landing \.aznet-theme-page__content-inner > \.wp-block-cover\s*\{([^}]*)\}/s',
    $source,
    $section_match
) ) {
    fwrite( STDERR, "FAIL: landing full-width section rule missing\n" );
    exit( 1 );
}

foreach ( [
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-inline: max(',
    'calc((100vw - var(--aznet-theme-container-wide)) / 2)',
] as $needle ) {
    if ( false === strpos( $section_match[1], $needle ) ) {
        fwrite( STDERR, "FAIL: full-width section surface/content split missing {$needle}\n" );
        exit( 1 );
    }
}

if ( false === strpos( $source, 'var(--aznet-theme-gutter-mobile)' ) ) {
    fwrite( STDERR, "FAIL: mobile inner gutter missing\n" );
    exit( 1 );
}

echo "PASS: landing sections stay edge-to-edge while only their inner content is constrained.\n";
