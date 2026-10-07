<?php

$root = dirname( __DIR__, 2 );
$home = $root . '/home.php';

if ( ! is_file( $home ) ) {
    fwrite( STDERR, "FAIL: home.php missing for posts page
" );
    exit( 1 );
}

$source = file_get_contents( $home );
if ( false === $source ) {
    fwrite( STDERR, "FAIL: unable to read home.php
" );
    exit( 1 );
}

$required = [
    'aznet-theme-main--posts-page',
    'single_post_title',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination',
    'aznet-theme-posts-page-title',
];

foreach ( $required as $needle ) {
    if ( false === strpos( $source, $needle ) ) {
        fwrite( STDERR, "FAIL: posts page contract missing {$needle}
" );
        exit( 1 );
    }
}

if ( false !== strpos( $source, 'the_content();' ) ) {
    fwrite( STDERR, "FAIL: posts page must not dump full post content
" );
    exit( 1 );
}

echo "PASS: posts page uses the reusable listing surface and avoids full-content dumping.
";
