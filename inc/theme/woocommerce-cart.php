<?php
/**
 * WooCommerce Cart presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned Woo Cart presentation assets should load.
 */
function should_enqueue_woocommerce_cart_assets(): bool {
    return 'cart' === current_surface();
}
