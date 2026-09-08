<?php
/**
 * Public Woo Blocks presentation capability detection.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Determine whether the current WordPress content needs Theme Woo Blocks CSS.
 */
function should_enqueue_woocommerce_blocks_assets(): bool {
    if ( ! class_exists( '\\WP_Block_Type_Registry' ) || ! function_exists( 'has_block' ) ) {
        return false;
    }

    $registry = \WP_Block_Type_Registry::get_instance();
    $blocks   = [
        'woocommerce/product-collection',
        'woocommerce/product-categories',
    ];

    foreach ( $blocks as $block_name ) {
        if ( $registry->is_registered( $block_name ) && \has_block( $block_name ) ) {
            return true;
        }
    }

    return false;
}
