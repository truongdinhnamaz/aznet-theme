<?php
/**
 * WooCommerce public capability and surface adapter.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Integrations\WooCommerce;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Whether WooCommerce public runtime capability is available.
 */
function available(): bool {
    return function_exists( 'WC' );
}

/**
 * Return the normalized WooCommerce presentation surface for this request.
 *
 * @return 'product'|'archive'|'cart'|'checkout'|'account'|null
 */
function current_surface(): ?string {
    if ( ! available() ) {
        return null;
    }

    if ( function_exists( 'is_product' ) && \is_product() ) {
        return 'product';
    }

    if (
        ( function_exists( 'is_shop' ) && \is_shop() )
        || ( function_exists( 'is_product_taxonomy' ) && \is_product_taxonomy() )
    ) {
        return 'archive';
    }

    if ( function_exists( 'is_cart' ) && \is_cart() ) {
        return 'cart';
    }

    if ( function_exists( 'is_checkout' ) && \is_checkout() ) {
        return 'checkout';
    }

    if ( function_exists( 'is_account_page' ) && \is_account_page() ) {
        return 'account';
    }

    return null;
}
