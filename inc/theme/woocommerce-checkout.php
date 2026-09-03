<?php
/**
 * WooCommerce Checkout presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned Checkout presentation assets should load.
 */
function should_enqueue_woocommerce_checkout_assets(): bool {
    return 'checkout' === current_surface();
}
