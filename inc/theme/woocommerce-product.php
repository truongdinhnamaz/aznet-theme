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
