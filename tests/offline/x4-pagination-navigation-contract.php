<?php
$root = dirname( __DIR__, 2 );

$read = static function ( string $path ) use ( $root ): string {
    $full = $root . '/' . $path;
    if ( ! is_file( $full ) ) {
        throw new RuntimeException( 'Missing required X4 file: ' . $path );
    }
    $source = file_get_contents( $full );
    if ( false === $source ) {
        throw new RuntimeException( 'Unable to read X4 file: ' . $path );
    }
    return $source;
};

$archive  = $read( 'archive.php' );
$search   = $read( 'search.php' );
$single   = $read( 'template-parts/content/content-single.php' );
$comments = $read( 'comments.php' );
$assets   = $read( 'inc/theme/assets.php' );
$css      = $read( 'assets/css/components/navigation.css' );
$style    = $read( 'style.css' );
$funcs    = $read( 'functions.php' );

$required = [
    'archive.php' => [ 'have_posts()', 'the_post()', 'the_posts_pagination(', "'aria_label'", "esc_attr__( 'Archive pagination', 'aznet-theme' )" ],
    'search.php' => [ 'have_posts()', 'the_post()', 'the_posts_pagination(', "'aria_label'", "esc_attr__( 'Search results pagination', 'aznet-theme' )" ],
    'template-parts/content/content-single.php' => [ 'the_content()', 'wp_link_pages(', 'aznet-theme-article__page-links', 'aznet-theme-navigation__summary', 'the_post_navigation(', "esc_attr__( 'Post navigation', 'aznet-theme' )" ],
    'comments.php' => [ 'wp_list_comments(', 'the_comments_pagination(', "esc_attr__( 'Comments pagination', 'aznet-theme' )" ],
    'inc/theme/assets.php' => [ 'function should_enqueue_navigation_assets(): bool', 'function enqueue_navigation_assets( ?string $version = null ): void', "'aznet-theme-navigation'", "'/assets/css/components/navigation.css'", 'enqueue_navigation_assets( $version );' ],
];

$sources = [
    'archive.php' => $archive,
    'search.php' => $search,
    'template-parts/content/content-single.php' => $single,
    'comments.php' => $comments,
    'inc/theme/assets.php' => $assets,
];

foreach ( $required as $path => $needles ) {
    foreach ( $needles as $needle ) {
        if ( false === strpos( $sources[ $path ], $needle ) ) {
            throw new RuntimeException( $path . ' missing X4 token: ' . $needle );
        }
    }
}

foreach ( [
    '.aznet-theme-listing__pagination .page-numbers',
    '.aznet-theme-article__page-links .post-page-numbers',
    '.aznet-theme-comments .comments-pagination .page-numbers',
    '.aznet-theme-navigation__summary',
    ':focus-visible',
    '.current',
    '@media (max-width: 47.999rem)',
] as $needle ) {
    if ( false === strpos( $css, $needle ) ) {
        throw new RuntimeException( 'navigation.css missing X4 selector/token: ' . $needle );
    }
}

$production = implode( "\n", [ $archive, $search, $single, $comments, $assets ] );
foreach ( [
    'new WP_Query',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    'wp_redirect(',
    'wp_safe_redirect(',
    '$_GET',
    '$_SERVER[\'REQUEST_URI\']',
    '$wpdb',
    'choiceguide_',
    'rootprofile_',
] as $needle ) {
    if ( false !== strpos( $production, $needle ) ) {
        throw new RuntimeException( 'X4 ownership violation: ' . $needle );
    }
}

if ( false === strpos( $style, 'Version: 1.1.0' ) ) {
    throw new RuntimeException( 'X4 must not promote style.css metadata before X6.' );
}
if ( false === strpos( $funcs, "define( 'AZNET_THEME_VERSION', '1.1.0' );" ) ) {
    throw new RuntimeException( 'X4 must not promote AZNET_THEME_VERSION before X6.' );
}

echo "PASS: X4 pagination/navigation ownership and presentation contract\n";
