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
        'aznet-theme-commerce' => __( 'AZnet — Commerce', 'aznet-theme' ),
        'aznet-theme-utility'  => __( 'AZnet — Utility', 'aznet-theme' ),
    ];

    foreach ( $categories as $slug => $label ) {
        register_block_pattern_category( $slug, [ 'label' => $label ] );
    }
}
