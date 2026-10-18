<?php
/**
 * Public Variation Swatches image-size consumer adapter.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Integrations\WooVariationSwatches;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Select a native WordPress derivative for small image-attribute selectors.
 *
 * Variation Swatches 2.5.0 documents the
 * woo_variation_swatches_global_product_attribute_image_size filter.
 * Its default 50x50 registered derivative is missing from some older media:
 * WordPress then returns the full image for a 96px selector. The pre-existing
 * core thumbnail size avoids that fallback on those legacy attachments.
 *
 * The provider still owns attribute/media lookup, HTML and variation state.
 * Gallery images and their full-size URLs are intentionally unaffected.
 *
 * @param mixed $requested_size The provider's currently selected size.
 * @param mixed $context        Provider-supplied public attribute context.
 */
function image_attribute_thumbnail_size( $requested_size, $context ): string {
    return 'thumbnail';
}

// With provider absent the hook is never invoked; WordPress-free audits
// can include this module without requiring WordPress functions.
if ( function_exists( 'add_filter' ) ) {
    add_filter(
        'woo_variation_swatches_global_product_attribute_image_size',
        __NAMESPACE__ . '\\image_attribute_thumbnail_size',
        10,
        2
    );
}
