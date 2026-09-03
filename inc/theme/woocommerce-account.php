<?php
/**
 * WooCommerce My Account presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned My Account presentation assets should load.
 */
function should_enqueue_woocommerce_account_assets(): bool {
    return 'account' === current_surface();
}
