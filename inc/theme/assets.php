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
 * Enqueue Header utility presentation only when the WordPress menu exists.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_utility_asset( ?string $version = null ): void {
    if ( function_exists( __NAMESPACE__ . '\\setting' ) && true !== setting( 'header_utilities', true ) ) {
        return;
    }
    if ( ! function_exists( 'has_nav_menu' ) || ! has_nav_menu( 'header-utility' ) ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-header-utility',
        get_theme_file_uri( '/assets/css/components/header-utility.css' ),
        [ 'aznet-theme-site-header' ],
        asset_content_version( '/assets/css/components/header-utility.css', $version )
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
        asset_content_version( '/assets/css/tokens.css', $version )
    );

    wp_enqueue_style(
        'aznet-theme-homepage-editor',
        get_theme_file_uri( '/assets/css/components/homepage.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );
}

/**
 * Build a cache key that changes when a scoped asset's bytes change.
 *
 * @param string      $relative_path Theme-relative asset path.
 * @param string|null $fallback      Theme/version fallback.
 */
function asset_content_version( string $relative_path, ?string $fallback = null ): ?string {
    if ( ! function_exists( 'get_theme_file_path' ) ) {
        return $fallback;
    }

    $path = get_theme_file_path( $relative_path );
    if ( ! is_file( $path ) ) {
        return $fallback;
    }

    $hash = hash_file( 'sha256', $path );
    if ( ! is_string( $hash ) || '' === $hash ) {
        return $fallback;
    }

    $fingerprint = substr( $hash, 0, 12 );
    return null !== $fallback && '' !== $fallback ? $fallback . '-' . $fingerprint : $fingerprint;
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
        asset_content_version( '/assets/css/components/homepage-law-01.css', $version )
    );
    wp_enqueue_style(
        'aznet-theme-homepage-law-01-variants',
        get_theme_file_uri( '/assets/css/components/homepage-law-01-variants.css' ),
        [ 'aznet-theme-homepage-law-01' ],
        asset_content_version( '/assets/css/components/homepage-law-01-variants.css', $version )
    );
}

/** Determine whether the native Post comments surface will render. */
function should_enqueue_comments_assets(): bool {
    if ( ! function_exists( 'is_singular' ) || ! is_singular( 'post' ) ) {
        return false;
    }

    if ( function_exists( 'post_password_required' ) && post_password_required() ) {
        return false;
    }

    if ( ! function_exists( 'comments_open' ) || ! function_exists( 'get_comments_number' ) ) {
        return false;
    }

    return comments_open() || 0 < get_comments_number();
}

/** Enqueue native comments presentation and Core threaded-reply enhancement. */
function enqueue_comments_assets( ?string $version = null ): void {
    if ( ! should_enqueue_comments_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-comments',
        get_theme_file_uri( '/assets/css/components/comments.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content', 'aznet-theme-forms' ],
        $version
    );

    if ( comments_open() && function_exists( 'get_option' ) && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}

/** Determine whether the native Search/404/archive recovery presentation can render. */
function should_enqueue_recovery_assets(): bool {
    if ( ! function_exists( 'is_search' ) || ! function_exists( 'is_404' ) || ! function_exists( 'is_archive' ) ) {
        return false;
    }

    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return is_search() || is_404() || is_archive();
}

/** Enqueue Search/404/archive recovery presentation only on its native surfaces. */
function enqueue_recovery_assets( ?string $version = null ): void {
    if ( ! should_enqueue_recovery_assets() ) {
        return;
    }

    $dependencies = [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ];
    if ( should_enqueue_form_assets() ) {
        $dependencies[] = 'aznet-theme-forms';
    }

    wp_enqueue_style(
        'aznet-theme-recovery',
        get_theme_file_uri( '/assets/css/components/recovery.css' ),
        $dependencies,
        asset_content_version( '/assets/css/components/recovery.css', $version )
    );
}

/** Determine whether native authored Post/Page media presentation can render. */
function should_enqueue_media_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_post = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page = function_exists( 'is_page' ) && is_page();

    return $is_post || $is_page;
}

/** Enqueue authored-media presentation only on native Post/Page surfaces. */
function enqueue_media_assets( ?string $version = null ): void {
    if ( ! should_enqueue_media_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-media',
        get_theme_file_uri( '/assets/css/components/media.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/media.css', $version )
    );
}

/** Determine whether the native Page presentation module should render. */
function should_enqueue_page_assets(): bool {
    if ( ! function_exists( 'is_page' ) || ! is_page() ) {
        return false;
    }

    if ( function_exists( 'is_front_page' ) && is_front_page() ) {
        return false;
    }

    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return true;
}

/** Enqueue Y1 native Page presentation only on non-front, non-Woo Page surfaces. */
function enqueue_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_page_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-page',
        get_theme_file_uri( '/assets/css/components/page.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/page.css', $version )
    );
}

/** Determine whether shared native form presentation can render. */
function should_enqueue_form_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_search = function_exists( 'is_search' ) && is_search();
    $is_404    = function_exists( 'is_404' ) && is_404();
    $is_post   = function_exists( 'is_singular' ) && is_singular( 'post' );
    $is_page   = function_exists( 'is_page' ) && is_page();

    return $is_search || $is_404 || $is_post || $is_page;
}

/** Enqueue shared WordPress-native form presentation only on X5 surfaces. */
function enqueue_form_assets( ?string $version = null ): void {
    if ( ! should_enqueue_form_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-forms',
        get_theme_file_uri( '/assets/css/components/forms.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/forms.css', $version )
    );
}

/** Determine whether native pagination/navigation presentation can render. */
function should_enqueue_navigation_assets(): bool {
    if ( function_exists( __NAMESPACE__ . '\\Integrations\\WooCommerce\\current_surface' )
        && null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    $is_archive = function_exists( 'is_archive' ) && is_archive();
    $is_search  = function_exists( 'is_search' ) && is_search();
    $is_post    = function_exists( 'is_singular' ) && is_singular( 'post' );

    return $is_archive || $is_search || $is_post;
}

/** Enqueue shared native pagination/navigation presentation only on X4 surfaces. */
function enqueue_navigation_assets( ?string $version = null ): void {
    if ( ! should_enqueue_navigation_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-navigation',
        get_theme_file_uri( '/assets/css/components/navigation.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/navigation.css', $version )
    );
}

function enqueue_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
        asset_content_version( '/assets/css/tokens.css', $version )
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

    enqueue_header_utility_asset( $version );
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

    enqueue_page_assets( $version );
    enqueue_form_assets( $version );
    enqueue_navigation_assets( $version );
    enqueue_media_assets( $version );
    enqueue_recovery_assets( $version );

    if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
        wp_enqueue_style(
            'aznet-theme-article',
            get_theme_file_uri( '/assets/css/components/article.css' ),
            [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
            $version
        );
    }

    enqueue_comments_assets( $version );

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
