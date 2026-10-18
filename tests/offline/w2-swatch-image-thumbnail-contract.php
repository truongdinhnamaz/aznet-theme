<?php
/**
 * A Theme-owned presentation adapter for Woo Variation Swatches must use a
 * WordPress-provided derivative rather than fetch full-resolution media for
 * tiny selector swatches. WP owns image files and their metadata; the plugin
 * owns attributes, selection state, and media identification.
 */
declare(strict_types=1);

define( 'ABSPATH', __DIR__ );
$GLOBALS['aznet_test_swatch_hooks'] = [];

function add_filter( string $hook, $callback, int $priority = 10, int $accepted_args = 1 ): bool {
    $GLOBALS['aznet_test_swatch_hooks'][$hook] = [ $callback, $priority, $accepted_args ];
    return true;
}

require dirname( __DIR__, 2 ) . '/inc/theme/woocommerce-product.php';

$fail = static function ( string $message ): never {
    fwrite( STDERR, "FAIL: W2 image swatch derivative: {$message}\n" );
    exit( 1 );
};

$hook = 'woo_variation_swatches_global_product_attribute_image_size';
$registration = $GLOBALS['aznet_test_swatch_hooks'][$hook] ?? null;
if ( ! is_array( $registration ) || 2 !== $registration[2] ) {
    $fail( 'public plugin size hook not registered for its two arguments' );
}
$callback = $registration[0];
if ( ! is_callable( $callback ) ) {
    $fail( 'public plugin size hook must have a callable Theme adapter' );
}

// The plugin's default custom 50x50 size may be missing in older uploads.
// WordPress's built-in "thumbnail" derivative already exists for the live
// legacy media; using it avoids a fallback to full-size original images.
foreach ( [ 'variation_swatches_image_size', 'full', 'large', 'thumbnail' ] as $requested ) {
    if ( 'thumbnail' !== $callback( $requested, [ 'attribute_name' => 'pa_kieu' ] ) ) {
        $fail( 'size must resolve to WordPress-native thumbnail: ' . $requested );
    }
}

$source = (string) file_get_contents( dirname( __DIR__, 2 ) . '/inc/theme/woocommerce-product.php' );
foreach ( [ 'get_option(', 'get_term_meta(', 'get_post_meta(', '$wpdb', 'wp_get_attachment_url(', 'wp_get_attachment_image_src(', 'choiceguide_', 'convertflow_' ] as $forbidden ) {
    if ( str_contains( $source, $forbidden ) ) {
        $fail( 'private storage/attachment lookup forbidden in Theme: ' . $forbidden );
    }
}
if ( ! str_contains( $source, "function_exists( 'add_filter' )" ) ) {
    $fail( 'provider absent and WordPress-free source audits must fail softly' );
}
echo "PASS: W2 image swatch uses 150x150 native thumbnail derivative without taking over provider data\n";
