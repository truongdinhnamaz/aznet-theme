<?php
/**
 * Theme-owned native block pattern registration.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Theme-owned pattern categories.
 */
function register_pattern_categories(): void {
    $categories = [
        'aznet-theme-hero'     => __( 'AZnet — Hero', 'aznet-theme' ),
        'aznet-theme-trust'    => __( 'AZnet — Trust & CTA', 'aznet-theme' ),
        'aznet-theme-content'  => __( 'AZnet — Content', 'aznet-theme' ),
        'aznet-theme-pages'    => __( 'AZnet — Pages', 'aznet-theme' ),
        'aznet-theme-commerce' => __( 'AZnet — Commerce', 'aznet-theme' ),
        'aznet-theme-utility'  => __( 'AZnet — Utility', 'aznet-theme' ),
    ];

    foreach ( $categories as $slug => $label ) {
        register_block_pattern_category( $slug, [ 'label' => $label ] );
    }
}

/**
 * Register Woo-dependent patterns only when their public block capability exists.
 */
function register_woocommerce_patterns(): void {
    if ( ! class_exists( '\\WP_Block_Type_Registry' ) ) {
        return;
    }

    $registry = \WP_Block_Type_Registry::get_instance();
    $root     = dirname( __DIR__, 2 );
    $patterns = [
        'aznet-theme/commerce-category-grid' => [
            'block' => 'woocommerce/product-categories',
            'title' => __( 'Commerce — Category Grid', 'aznet-theme' ),
            'file'  => $root . '/inc/patterns/woocommerce/category-grid.php',
        ],
        'aznet-theme/commerce-featured-products' => [
            'block' => 'woocommerce/product-collection',
            'title' => __( 'Commerce — Featured Products', 'aznet-theme' ),
            'file'  => $root . '/inc/patterns/woocommerce/featured-products.php',
        ],
    ];

    foreach ( $patterns as $slug => $pattern ) {
        if ( ! $registry->is_registered( $pattern['block'] ) || ! is_file( $pattern['file'] ) ) {
            continue;
        }

        $content = include $pattern['file'];
        if ( ! is_string( $content ) || '' === trim( $content ) ) {
            continue;
        }

        register_block_pattern(
            $slug,
            [
                'title'      => $pattern['title'],
                'categories' => [ 'aznet-theme-commerce' ],
                'content'    => $content,
            ]
        );
    }
}
