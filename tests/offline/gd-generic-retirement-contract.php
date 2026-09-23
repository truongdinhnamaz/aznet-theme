<?php
/**
 * R-D generic retirement static contract.
 *
 * Protects canonical Milestone D destinations and the required WordPress
 * fallback while legacy retirement proceeds fail-closed.
 */

$root = dirname( __DIR__, 2 );

$required = [
    'page.php',
    'single.php',
    'archive.php',
    'search.php',
    '404.php',
    'index.php',
    'inc/theme/content-shell.php',
    'assets/css/components/generic-content.css',
    'template-parts/content/card.php',
];

foreach ( $required as $path ) {
    if ( ! is_file( $root . '/' . $path ) ) {
        fwrite( STDERR, "Missing protected generic destination/fallback: {$path}\n" );
        exit( 1 );
    }
}

$index = file_get_contents( $root . '/index.php' );
if ( false === $index || false === strpos( $index, 'get_header();' ) || false === strpos( $index, 'get_footer();' ) ) {
    fwrite( STDERR, "index.php must remain a functional WordPress fallback template.\n" );
    exit( 1 );
}

$content_shell = file_get_contents( $root . '/inc/theme/content-shell.php' );
if ( false === $content_shell ) {
    fwrite( STDERR, "Unable to read generic content shell.\n" );
    exit( 1 );
}

foreach ( [ 'is_page()', "is_singular( 'post' )", 'is_archive()', 'is_search()', 'is_404()' ] as $needle ) {
    if ( false === strpos( $content_shell, $needle ) ) {
        fwrite( STDERR, "Generic content asset eligibility lost canonical surface: {$needle}\n" );
        exit( 1 );
    }
}

if ( false === strpos( $content_shell, 'WooCommerce\\current_surface()' ) ) {
    fwrite( STDERR, "Generic asset boundary must continue excluding normalized Woo surfaces.\n" );
    exit( 1 );
}

$archive = file_get_contents( $root . '/archive.php' );
$search  = file_get_contents( $root . '/search.php' );
foreach ( [ 'archive.php' => $archive, 'search.php' => $search ] as $path => $source ) {
    if ( false === $source || false === strpos( $source, "get_template_part( 'template-parts/content/card' )" ) ) {
        fwrite( STDERR, "{$path} must retain the canonical generic card primitive.\n" );
        exit( 1 );
    }
}

$forbidden_storage_reads = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    '_choiceguide_',
    '_rootprofile_',
];

foreach ( [ 'page.php', 'single.php', 'archive.php', 'search.php', '404.php', 'inc/theme/content-shell.php' ] as $path ) {
    $source = file_get_contents( $root . '/' . $path );
    if ( false === $source ) {
        fwrite( STDERR, "Unable to inspect {$path}.\n" );
        exit( 1 );
    }

    foreach ( $forbidden_storage_reads as $needle ) {
        if ( false !== strpos( $source, $needle ) ) {
            fwrite( STDERR, "Ownership violation in {$path}: {$needle}\n" );
            exit( 1 );
        }
    }
}

fwrite( STDOUT, "R-D generic retirement contract PASS\n" );
