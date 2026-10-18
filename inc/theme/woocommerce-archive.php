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

/**
 * Give a WooCommerce product-detail fallback the semantics of a product CTA.
 *
 * WooCommerce renders "Read more" for certain non-purchasable products.
 * The button still points to the product URL and WooCommerce retains sole
 * ownership of purchasability, price, stock and add-to-cart behavior.
 *
 * @param string $text WooCommerce-supplied loop button text.
 * @param mixed  $product Public WooCommerce product object.
 */
function woocommerce_loop_product_cta_text( string $text, $product ): string {
    if ( ! $product instanceof \WC_Product ) {
        return $text;
    }

    $read_more_labels = [
        __( 'Read more', 'woocommerce' ),
        'Read more',
        'Đọc tiếp',
    ];

    if ( ! in_array( trim( $text ), $read_more_labels, true ) ) {
        return $text;
    }

    return __( 'Xem chi tiết', 'aznet-theme' );
}

/**
 * Align the classic WooCommerce product-link accessible name with its CTA.
 *
 * Do not rewrite Woo's link target, classes, quantity, product ID or cart
 * attributes; only replace the mismatched "Read more about" aria label.
 *
 * @param array<string, mixed> $args WooCommerce loop-button arguments.
 * @param mixed                $product Public WooCommerce product object.
 * @return array<string, mixed>
 */
function woocommerce_loop_product_cta_args( array $args, $product ): array {
    if (
        ! $product instanceof \WC_Product
        || __( 'Xem chi tiết', 'aznet-theme' ) !== $product->add_to_cart_text()
    ) {
        return $args;
    }

    if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
        $args['attributes'] = [];
    }

    $args['attributes']['aria-label'] = sprintf(
        __( 'Xem chi tiết sản phẩm: %s', 'aznet-theme' ),
        $product->get_name()
    );

    return $args;
}

// Offline source audits can include this module before WordPress is loaded.
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'woocommerce_product_add_to_cart_text', __NAMESPACE__ . '\\woocommerce_loop_product_cta_text', 10, 2 );
    add_filter( 'woocommerce_loop_add_to_cart_args', __NAMESPACE__ . '\\woocommerce_loop_product_cta_args', 10, 2 );
}
