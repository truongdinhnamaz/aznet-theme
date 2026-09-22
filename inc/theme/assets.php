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
 * Enqueue the site-wide Law 01 Header presentation independently of Front Page assets.
 *
 * @param string|null $version Asset version.
 */
function enqueue_header_law01_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\header_law01_active' ) || ! header_law01_active() ) {
        return;
    }

    if ( 'burgundy-gold' !== homepage_law01_variant() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-header-law-01',
        get_theme_file_uri( '/assets/css/components/header-law-01.css' ),
        [ 'aznet-theme-site-header' ],
        asset_content_version( '/assets/css/components/header-law-01.css', $version )
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

/** Enqueue Curtain 01 only for its active Front Page presentation surface. */
function enqueue_homepage_curtain01_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' ) || ! homepage_composer_active() ) {
        return;
    }
    if ( 'curtain-01' !== homepage_preset() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-homepage-curtain-01',
        get_theme_file_uri( '/assets/css/components/homepage-curtain-01.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-homepage' ],
        asset_content_version( '/assets/css/components/homepage-curtain-01.css', $version )
    );

    wp_enqueue_script(
        'aznet-theme-homepage-curtain-01-motion',
        get_theme_file_uri( '/assets/js/homepage-curtain-01.js' ),
        [],
        asset_content_version( '/assets/js/homepage-curtain-01.js', $version ),
        true
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

/** Determine whether the approved Law 01 category archive presentation can render. */
function should_enqueue_law01_archive_asset(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\law01_category_archive_active' ) ) {
        return false;
    }

    return law01_category_archive_active();
}

/** Enqueue the Law 01 category archive stylesheet only on its native category surface. */
function enqueue_law01_archive_asset( ?string $version = null ): void {
    if ( ! should_enqueue_law01_archive_asset() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-archive-law-01',
        get_theme_file_uri( '/assets/css/components/archive-law-01.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
        asset_content_version( '/assets/css/components/archive-law-01.css', $version )
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



/** Determine whether the mapped premium Law 01 Contact Page presentation can render. */
function should_enqueue_contact_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\contact_page_presentation_active' ) ) {
        return false;
    }

    return contact_page_presentation_active();
}

/** Enqueue scoped premium Contact Page presentation only for the explicit mapped Page. */
function enqueue_contact_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_contact_page_assets() ) {
        return;
    }

    wp_enqueue_style(
        'aznet-theme-contact-surface',
        get_theme_file_uri( '/assets/css/components/contact-surface.css' ),
        [ 'aznet-theme-tokens' ],
        asset_content_version( '/assets/css/components/contact-surface.css', $version )
    );

    wp_enqueue_style(
        'aznet-theme-contact-page',
        get_theme_file_uri( '/assets/css/components/contact-page.css' ),
        [ 'aznet-theme-page', 'aznet-theme-forms', 'aznet-theme-contact-surface' ],
        asset_content_version( '/assets/css/components/contact-page.css', $version )
    );
}

/** Determine whether the mapped premium Law 01 Services Page presentation can render. */
function should_enqueue_services_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\services_page_presentation_active' ) ) {
        return false;
    }

    return services_page_presentation_active();
}

/** Enqueue the shared service-card component only when a Services surface consumes it. */
function enqueue_service_card_asset( ?string $version = null ): void {
    wp_enqueue_style(
        'aznet-theme-service-card',
        get_theme_file_uri( '/assets/css/components/service-card.css' ),
        [ 'aznet-theme-tokens', 'aznet-theme-page' ],
        asset_content_version( '/assets/css/components/service-card.css', $version )
    );
}

/** Enqueue scoped premium Services Page presentation only for the explicit mapped Page. */
function enqueue_services_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_services_page_assets() ) {
        return;
    }

    enqueue_service_card_asset( $version );

    wp_enqueue_style(
        'aznet-theme-services-page',
        get_theme_file_uri( '/assets/css/components/services-page.css' ),
        [ 'aznet-theme-service-card' ],
        asset_content_version( '/assets/css/components/services-page.css', $version )
    );
}

/** Determine whether the mapped Services child landing presentation can render. */
function should_enqueue_service_page_assets(): bool {
    if ( ! function_exists( __NAMESPACE__ . '\\service_page_is_detail' ) ) {
        return false;
    }

    return service_page_is_detail();
}

/** Enqueue scoped service landing presentation only for mapped direct service children. */
function enqueue_service_page_assets( ?string $version = null ): void {
    if ( ! should_enqueue_service_page_assets() ) {
        return;
    }

    enqueue_service_card_asset( $version );

    wp_enqueue_style(
        'aznet-theme-service-page',
        get_theme_file_uri( '/assets/css/components/service-page.css' ),
        [ 'aznet-theme-service-card' ],
        asset_content_version( '/assets/css/components/service-page.css', $version )
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

    enqueue_header_law01_asset( $version );
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
    enqueue_homepage_curtain01_asset( $version );

    if ( should_enqueue_generic_content_assets() ) {
        wp_enqueue_style(
            'aznet-theme-generic-content',
            get_theme_file_uri( '/assets/css/components/generic-content.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }

    enqueue_law01_archive_asset( $version );
    enqueue_page_assets( $version );
    enqueue_contact_page_assets( $version );
    enqueue_services_page_assets( $version );
    enqueue_service_page_assets( $version );
    enqueue_form_assets( $version );
    enqueue_navigation_assets( $version );
    enqueue_media_assets( $version );
    enqueue_recovery_assets( $version );

    if ( function_exists( 'is_singular' ) && is_singular( 'post' ) ) {
        wp_enqueue_style(
            'aznet-theme-article',
            get_theme_file_uri( '/assets/css/components/article.css' ),
            [ 'aznet-theme-tokens', 'aznet-theme-generic-content' ],
            asset_content_version( '/assets/css/components/article.css', $version )
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
