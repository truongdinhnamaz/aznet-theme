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

/**
 * Return public WooCommerce product categories for Homepage presentation.
 *
 * @return array<int, \WP_Term>
 */
function homepage_product_categories( int $limit = 4 ): array {
    if ( ! available() || ! function_exists( 'taxonomy_exists' ) || ! taxonomy_exists( 'product_cat' ) ) {
        return [];
    }

    $limit = max( 1, min( 12, $limit ) );
    $terms = get_terms(
        [
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'number'     => $limit,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]
    );

    if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
        return [];
    }

    return array_values(
        array_filter(
            $terms,
            static fn ( $term ): bool => $term instanceof \WP_Term
        )
    );
}

/**
 * Return public WooCommerce products for Homepage presentation.
 *
 * @return array<int, object>
 */
function homepage_products( int $limit = 6 ): array {
    if ( ! available() || ! function_exists( 'wc_get_products' ) ) {
        return [];
    }

    $limit = max( 1, min( 12, $limit ) );
    $products = wc_get_products(
        [
            'status'     => 'publish',
            'limit'      => $limit,
            'orderby'    => 'date',
            'order'      => 'DESC',
            'visibility' => 'catalog',
            'return'     => 'objects',
        ]
    );

    return is_array( $products ) ? array_values( $products ) : [];
}

/**
 * Return the public WooCommerce shop URL when available.
 */
function shop_url(): string {
    if ( ! available() || ! function_exists( 'wc_get_page_permalink' ) ) {
        return '';
    }

    $url = wc_get_page_permalink( 'shop' );
    return is_string( $url ) ? $url : '';
}

