<?php
/**
 * Theme-owned asset registration.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue the active non-default visual preset stylesheet.
 *
 * @param string|null $version Asset version.
 */
function enqueue_visual_preset_asset( ?string $version = null ): void {
    $preset = function_exists( __NAMESPACE__ . '\\visual_preset' ) ? visual_preset() : 'default';

    if ( 'default' === $preset ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-preset-' . $preset,
        get_theme_file_uri( '/assets/css/presets/' . $preset . '.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

/**
 * Enqueue mobile Header navigation enhancement only when its panel renders.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_navigation_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_mobile_panel_enabled' ) || ! header_mobile_panel_enabled() ) {
        return;
    }

    wp_enqueue_script(
        'aznet-theme-header-navigation',
        get_theme_file_uri( '/assets/js/header-navigation.js' ),
        [],
        $version,
        true
    );
}

/**
 * Enqueue sticky-compact enhancement only for that Header mode.
 *
 * @param string|null $version Asset version.
 */
function enqueue_sticky_header_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_sticky_mode' ) || 'sticky-compact' !== header_sticky_mode() ) {
        return;
    }

    wp_enqueue_script(
        'aznet-theme-sticky-header',
        get_theme_file_uri( '/assets/js/sticky-header.js' ),
        [],
        $version,
        true
    );
}

/**
 * Enqueue the dedicated Homepage blueprint presentation only on the Front Page.
 *
 * @param string|null $version Asset version.
 */
function enqueue_homepage_blueprint_asset( ?string $version = null ): void {
    if ( ! function_exists( 'is_front_page' ) || ! is_front_page() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-homepage',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

/**
 * Enqueue scoped Homepage blueprint styles in the Block Editor.
 */
function enqueue_homepage_blueprint_editor_asset(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
        $version
    );

    wp_enqueue_style(
        'aznet-theme-homepage-editor',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}


/** Enqueue Law 01 only for its active Front Page presentation surface. */
function enqueue_homepage_law01_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' ) || ! homepage_composer_active() ) {
        return;
    }
    if ( 'law-01' !== homepage_preset() ) {
        return;
    }
    wp_enqueue_style(
        'aznet-theme-homepage-law-01',
        get_theme_file_uri( '/assets/css/components/homepage-law-01.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

function enqueue_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
        $version
    );

    enqueue_visual_preset_asset( $version );

    wp_enqueue_style(
        'aznet-theme-convertflow-contract',
        get_theme_file_uri( '/assets/css/integrations/convertflow.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    wp_enqueue_style(
        'aznet-theme-style',
        get_stylesheet_uri(),
        [ 'aznet-theme-tokens' ],
        $version
    );

    wp_enqueue_style(
        'aznet-theme-site-header',
        get_theme_file_uri( '/assets/css/components/site-header.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    enqueue_header_navigation_asset( $version );
    enqueue_sticky_header_asset( $version );

    wp_enqueue_style(
        'aznet-theme-site-footer',
        get_theme_file_uri( '/assets/css/components/site-footer.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    enqueue_homepage_blueprint_asset( $version );
    enqueue_homepage_law01_asset( $version );

    if ( should_enqueue_generic_content_assets() ) {
        wp_enqueue_style(
            'aznet-theme-generic-content',
            get_theme_file_uri( '/assets/css/components/generic-content.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
        wp_enqueue_style(
            'aznet-theme-article',
            get_theme_file_uri( '/assets/css/components/article.css' ),
            [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_product_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-product',
            get_theme_file_uri( '/assets/css/components/woocommerce-product.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_archive_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-archive',
            get_theme_file_uri( '/assets/css/components/woocommerce-archive.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_cart_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-cart',
            get_theme_file_uri( '/assets/css/components/woocommerce-cart.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_checkout_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-checkout',
            get_theme_file_uri( '/assets/css/components/woocommerce-checkout.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( should_enqueue_woocommerce_account_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-account',
            get_theme_file_uri( '/assets/css/components/woocommerce-account.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    if ( function_exists( __NAMESPACE__ . '\\should_enqueue_woocommerce_blocks_assets' ) && should_enqueue_woocommerce_blocks_assets() ) {
        wp_enqueue_style(
            'aznet-theme-woocommerce-blocks',
            get_theme_file_uri( '/assets/css/components/woocommerce-blocks.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }
}
