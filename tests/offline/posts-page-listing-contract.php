<?php

$root = dirname( __DIR__, 2 );
$home = $root . '/home.php';
$archive_presentation = $root . '/inc/theme/archive-presentation.php';
$assets = $root . '/inc/theme/assets.php';
$law01_posts = $root . '/template-parts/archive/law01-posts-page.php';

foreach ( [ $home, $archive_presentation, $assets, $law01_posts ] as $file ) {
    if ( ! is_file( $file ) ) {
        fwrite( STDERR, 'FAIL: required posts page presentation file missing: ' . $file . "\n" );
        exit( 1 );
    }
}

$home_source = (string) file_get_contents( $home );
$archive_source = (string) file_get_contents( $archive_presentation );
$asset_source = (string) file_get_contents( $assets );
$law01_source = (string) file_get_contents( $law01_posts );

$home_required = [
    'law01_posts_page_active',
    'aznet-theme-main--law01-posts',
    "get_template_part( 'template-parts/archive/law01-posts-page' )",
    'aznet-theme-main--posts-page',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination',
];

foreach ( $home_required as $needle ) {
    if ( false === strpos( $home_source, $needle ) ) {
        fwrite( STDERR, "FAIL: posts page home.php contract missing {$needle}\n" );
        exit( 1 );
    }
}

if ( false !== strpos( $home_source, 'the_content();' ) ) {
    fwrite( STDERR, "FAIL: posts page must not dump full post content\n" );
    exit( 1 );
}

foreach ( [ 'function law01_posts_page_active', "is_home()", 'header_law01_active' ] as $needle ) {
    if ( false === strpos( $archive_source, $needle ) ) {
        fwrite( STDERR, "FAIL: Law 01 posts-page activation contract missing {$needle}\n" );
        exit( 1 );
    }
}

foreach ( [ 'law01_posts_page_active', 'aznet-theme-archive-law-01' ] as $needle ) {
    if ( false === strpos( $asset_source, $needle ) ) {
        fwrite( STDERR, "FAIL: Law 01 posts-page asset gate missing {$needle}\n" );
        exit( 1 );
    }
}

$law01_required = [
    'single_post_title',
    "template-parts/archive/law01-item",
    "'featured'",
    "'latest'",
    "'standard'",
    'aznet-theme-law01-archive__featured',
    'aznet-theme-law01-archive__latest-grid',
    'aznet-theme-law01-archive__editorial-layout',
    'wp_list_categories',
    'archive_contact_page_url',
    'the_posts_pagination',
];

foreach ( $law01_required as $needle ) {
    if ( false === strpos( $law01_source, $needle ) ) {
        fwrite( STDERR, "FAIL: Law 01 posts page composition missing {$needle}\n" );
        exit( 1 );
    }
}

if ( false !== strpos( $law01_source, 'the_content();' ) ) {
    fwrite( STDERR, "FAIL: Law 01 posts page must not render full post content\n" );
    exit( 1 );
}

echo "PASS: posts page keeps WordPress query ownership while Law 01 receives the approved editorial archive presentation.\n";
