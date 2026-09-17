<?php
/**
 * Regression: the shared Page renderer must not consume Woo-owned dynamic
 * content through get_the_excerpt() before the_content() renders the surface.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', $root . '/' );
}

$GLOBALS['y1_woo_surface'] = 'archive';
$GLOBALS['y1_excerpt_calls'] = 0;
$GLOBALS['y1_excerpt_post_id'] = null;

if ( ! function_exists( 'get_the_excerpt' ) ) {
    function get_the_excerpt( mixed $post = null ): string {
        ++$GLOBALS['y1_excerpt_calls'];
        $GLOBALS['y1_excerpt_post_id'] = $post;
        return '  Generic Page lead sentinel.  ';
    }
}

if ( ! function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\current_surface' ) ) {
    eval( 'namespace AZnet\\Theme\\Integrations\\WooCommerce; function current_surface(): ?string { return $GLOBALS["y1_woo_surface"]; }' );
}

require_once $root . '/inc/theme/page-experience.php';

if ( ! function_exists( 'AZnet\\Theme\\page_excerpt' ) ) {
    fwrite( STDERR, "FAIL: page_excerpt() missing; Woo Page content can still be consumed by get_the_excerpt()\n" );
    exit( 1 );
}

$woo_excerpt = \AZnet\Theme\page_excerpt( 17 );
if ( '' !== $woo_excerpt ) {
    fwrite( STDERR, "FAIL: Woo current surface must not expose a generated Page lead\n" );
    exit( 1 );
}
if ( 0 !== $GLOBALS['y1_excerpt_calls'] ) {
    fwrite( STDERR, "FAIL: Woo current surface called get_the_excerpt() before dynamic content rendering\n" );
    exit( 1 );
}

$GLOBALS['y1_woo_surface'] = null;
$generic_excerpt = \AZnet\Theme\page_excerpt( 17 );
if ( 'Generic Page lead sentinel.' !== $generic_excerpt ) {
    fwrite( STDERR, "FAIL: generic Page excerpt semantics changed\n" );
    exit( 1 );
}
if ( 1 !== $GLOBALS['y1_excerpt_calls'] || 17 !== $GLOBALS['y1_excerpt_post_id'] ) {
    fwrite( STDERR, "FAIL: generic Page excerpt must use WordPress get_the_excerpt() exactly once\n" );
    exit( 1 );
}

$shared = file_get_contents( $root . '/template-parts/content/page.php' );
if ( false === $shared ) {
    fwrite( STDERR, "FAIL: shared Page renderer missing\n" );
    exit( 1 );
}
if ( ! str_contains( $shared, '\\AZnet\\Theme\\page_excerpt(' ) ) {
    fwrite( STDERR, "FAIL: shared Page renderer must consume page_excerpt()\n" );
    exit( 1 );
}
if ( str_contains( $shared, 'get_the_excerpt(' ) ) {
    fwrite( STDERR, "FAIL: shared Page renderer must not call get_the_excerpt() directly\n" );
    exit( 1 );
}

echo "PASS: Woo Page excerpt consumption regression contract\n";
