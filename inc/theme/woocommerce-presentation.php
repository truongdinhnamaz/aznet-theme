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

/**
 * Open the Theme-owned main landmark around native WooCommerce content.
 * WooCommerce remains responsible for all product and commerce markup inside.
 */
function woocommerce_content_wrapper_start(): void {
    echo '<main id="main" class="aznet-theme-main aznet-theme-main--woocommerce">';
}

/** Close the Theme-owned WooCommerce main landmark. */
function woocommerce_content_wrapper_end(): void {
    echo '</main>';
}

/**
 * Replace WooCommerce's generic content wrapper with the canonical AZnet shell.
 * Uses only WooCommerce public presentation hooks; no commerce state is touched.
 */
function register_woocommerce_content_wrappers(): void {
    remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
    remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
    add_action( 'woocommerce_before_main_content', __NAMESPACE__ . '\\woocommerce_content_wrapper_start', 10 );
    add_action( 'woocommerce_after_main_content', __NAMESPACE__ . '\\woocommerce_content_wrapper_end', 10 );
}

add_action( 'wp', __NAMESPACE__ . '\\register_woocommerce_content_wrappers', 5 );
