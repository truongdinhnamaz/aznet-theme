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
 * Is the WordPress Page explicitly hosting AZnet's WooCommerce booking catalogue?
 * The marker is authored in Page content; no hard-coded Page ID or slug is needed.
 */
function booking_catalog_active(): bool {
    if ( ! function_exists( 'is_page' ) || ! is_page() || ! function_exists( 'get_queried_object' ) ) {
        return false;
    }

    $page = get_queried_object();
    return $page instanceof \WP_Post
        && is_string( $page->post_content )
        && str_contains( $page->post_content, 'class="aznet-booking-catalog"' );
}

/** Only AZnet's own WooCommerce service products participate in this Page CTA. */
function booking_catalog_service_product( $product ): bool {
    return booking_catalog_active()
        && $product instanceof \WC_Product
        && function_exists( 'has_term' )
        && has_term( 'dich-vu-aznet', 'product_cat', $product->get_id() );
}

/**
 * Preserve infographic text in AZnet service cards on booking, related
 * products, and shop/category loops. The 400x400 Woo thumbnail is cropped
 * before CSS sees it; use the intact WordPress medium_large source and
 * fit it inside a square presentation frame instead.
 *
 * Non-service product images keep their WooCommerce-default size.
 *
 * @param string|array $size WooCommerce loop image size.
 * @return string|array
 */
function booking_catalog_thumbnail_size( $size ) {
    if ( booking_catalog_active() ) {
        return 'medium_large';
    }

    global $product;

    if ( $product instanceof \WC_Product
        && function_exists( 'has_term' )
        && has_term( 'dich-vu-aznet', 'product_cat', $product->get_id() ) ) {
        return 'medium_large';
    }

    return $size;
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

    if ( booking_catalog_service_product( $product ) ) {
        return '' === $product->get_price()
            ? __( 'Xem dịch vụ', 'aznet-theme' )
            : __( 'Chọn gói', 'aznet-theme' );
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
    if ( booking_catalog_service_product( $product ) ) {
        if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
            $args['attributes'] = [];
        }
        $format = '' === $product->get_price()
            ? __( 'Xem dịch vụ: %s', 'aznet-theme' )
            : __( 'Chọn gói: %s', 'aznet-theme' );
        $args['attributes']['aria-label'] = sprintf( $format, $product->get_name() );
        return $args;
    }

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

/** Add a direct quote-form link without creating a WooCommerce zero-price order. */
function booking_catalog_quote_action(): void {
    global $product;

    if ( ! booking_catalog_service_product( $product ) || '' !== $product->get_price() ) {
        return;
    }

    $page_id = function_exists( 'get_queried_object_id' ) ? (int) get_queried_object_id() : 0;
    if ( $page_id <= 0 ) {
        return;
    }

    // Reuse the ASCII service code already authored in the product's quote URL.
    // A product rename must not silently change the CRM/form service identifier.
    $service_code = $product->get_slug();
    $description  = $product->get_description() . ' ' . $product->get_short_description();
    if ( preg_match( '/[?&]service=([A-Za-z0-9_-]+)/', $description, $matches ) ) {
        $service_code = $matches[1];
    }

    $url = add_query_arg( 'service', $service_code, get_permalink( $page_id ) );
    $url .= '#aznet-yeu-cau-bao-gia';

    echo '<a class="aznet-booking-catalog__quote-link" href="' . esc_url( $url ) . '">' .
        esc_html__( 'Nhận báo giá', 'aznet-theme' ) . '</a>';
}

// Offline source audits can include this module before WordPress is loaded.
if ( function_exists( 'add_filter' ) ) {
    add_filter( 'single_product_archive_thumbnail_size', __NAMESPACE__ . '\\booking_catalog_thumbnail_size', 10, 1 );
    add_filter( 'woocommerce_product_add_to_cart_text', __NAMESPACE__ . '\\woocommerce_loop_product_cta_text', 10, 2 );
    add_filter( 'woocommerce_loop_add_to_cart_args', __NAMESPACE__ . '\\woocommerce_loop_product_cta_args', 10, 2 );
    add_action( 'woocommerce_after_shop_loop_item', __NAMESPACE__ . '\\booking_catalog_quote_action', 20 );
}
