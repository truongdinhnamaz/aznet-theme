<?php
/**
 * WooCommerce presentation preset helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

use function AZnet\Theme\Integrations\WooCommerce\current_surface;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the normalized catalog presentation preset.
 */
function woo_catalog_preset(): string {
    return (string) setting( 'woo_catalog_preset', 'grid' );
}

/**
 * Return the normalized product-card density.
 */
function woo_product_card_density(): string {
    return (string) setting( 'woo_product_card_density', 'balanced' );
}

/**
 * Return the normalized single-product presentation preset.
 */
function woo_product_preset(): string {
    return (string) setting( 'woo_product_preset', 'classic' );
}

/**
 * Project Woo presentation settings into Theme-owned body classes.
 *
 * @param array<int, string> $classes Existing body classes.
 * @return array<int, string>
 */
function woocommerce_presentation_body_classes( array $classes ): array {
    $surface = current_surface();

    if ( null === $surface ) {
        return $classes;
    }

    if ( 'archive' === $surface ) {
        $classes[] = 'aznet-theme-woo-catalog--' . woo_catalog_preset();
        $classes[] = 'aznet-theme-woo-card--' . woo_product_card_density();
    }

    if ( 'product' === $surface ) {
        $classes[] = 'aznet-theme-woo-product--' . woo_product_preset();
    }

    return array_values( array_unique( $classes ) );
}
