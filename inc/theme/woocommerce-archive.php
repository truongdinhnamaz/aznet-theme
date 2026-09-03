<?php
/**
 * WooCommerce archive presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether Theme-owned Woo archive presentation assets should load.
 */
function should_enqueue_woocommerce_archive_assets(): bool {
    return 'archive' === current_surface();
}
