<?php
/**
 * WooCommerce Single Product presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned Single Product presentation assets should load.
 */
function should_enqueue_woocommerce_product_assets(): bool {
    return 'product' === current_surface();
}

/**
 * Request a universally registered WordPress derivative for Woo image swatches.
 *
 * The provider's 50x50 custom size may be absent on legacy WordPress uploads.
 * In that case wp_get_attachment_image_src() can return the full-resolution
 * original, even for a tiny selector. Native 'thumbnail' is present for those
 * existing uploads and keeps their source attachment and gallery untouched.
 *
 * The plugin owns its image lookup and variation state. Theme only chooses the
 * presentation size through the provider's public, documented filter.
 *
 * @param mixed $requested_size Plugin-supplied image size.
 * @param mixed $swatch_data    Provider-supplied public swatch context.
 */
function woocommerce_swatch_image_size( $requested_size, $swatch_data ): string {
    return 'thumbnail';
}

// Standalone source audits can include this module without WordPress loaded.
if ( function_exists( 'add_filter' ) ) {
    add_filter(
        'woo_variation_swatches_global_product_attribute_image_size',
        __NAMESPACE__ . '\\woocommerce_swatch_image_size',
        10,
        2
    );
}
