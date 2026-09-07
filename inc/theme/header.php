<?php
/**
 * Theme-owned Header presentation resolver and context.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the normalized requested Header preset.
 */
function header_preset(): string {
    $preset = setting( 'header_preset', 'standard' );

    return in_array( $preset, [ 'standard', 'compact', 'commerce', 'overlay' ], true )
        ? (string) $preset
        : 'standard';
}

/**
 * Return the Header preset that is safe for the current WordPress surface.
 */
function effective_header_preset(): string {
    $requested = header_preset();

    if ( 'overlay' === $requested && ! ( function_exists( 'is_front_page' ) && \is_front_page() ) ) {
        return 'standard';
    }

    return $requested;
}

/**
 * Return the normalized Header sticky presentation mode.
 */
function header_sticky_mode(): string {
    $mode = setting( 'header_sticky', 'sticky' );

    return in_array( $mode, [ 'off', 'sticky', 'sticky-compact' ], true )
        ? (string) $mode
        : 'sticky';
}

/**
 * Whether the Header has useful content for the mobile panel.
 */
function header_mobile_panel_enabled(): bool {
    if ( true === setting( 'header_search', true ) ) {
        return true;
    }

    if ( function_exists( 'has_nav_menu' ) && has_nav_menu( 'primary' ) ) {
        return true;
    }

    if ( true !== setting( 'header_utilities', true ) ) {
        return false;
    }

    if ( function_exists( 'wc_get_page_permalink' ) ) {
        $account_url = wc_get_page_permalink( 'myaccount' );
        if ( is_string( $account_url ) && '' !== trim( $account_url ) ) {
            return true;
        }
    }

    if ( function_exists( 'wc_get_cart_url' ) ) {
        $cart_url = wc_get_cart_url();
        if ( is_string( $cart_url ) && '' !== trim( $cart_url ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Build presentation context from WordPress-native state and public Woo URLs.
 *
 * @return array<string, mixed>
 */
function header_context(): array {
    $site_title = trim( (string) get_bloginfo( 'name' ) );
    $home_url   = (string) home_url( '/' );
    $logo_id    = (int) get_theme_mod( 'custom_logo', 0 );
    $logo_html  = '';

    if ( $logo_id > 0 && function_exists( 'wp_get_attachment_image' ) ) {
        $logo_html = (string) wp_get_attachment_image(
            $logo_id,
            'full',
            false,
            [
                'class' => 'aznet-theme-site-header__logo',
                'alt'   => '',
            ]
        );
    }

    $menu_html = static function ( string $menu_id ): string {
        if ( ! function_exists( 'wp_nav_menu' ) ) {
            return '';
        }

        $html = wp_nav_menu(
            [
                'theme_location' => 'primary',
                'container'      => false,
                'fallback_cb'    => false,
                'echo'           => false,
                'menu_class'     => 'aznet-theme-site-header__menu',
                'menu_id'        => $menu_id,
                'depth'          => 2,
            ]
        );

        return is_string( $html ) ? trim( $html ) : '';
    };

    $account_url = '';
    if ( function_exists( 'wc_get_page_permalink' ) ) {
        $candidate   = wc_get_page_permalink( 'myaccount' );
        $account_url = is_string( $candidate ) ? trim( $candidate ) : '';
    }

    $cart_url = '';
    if ( function_exists( 'wc_get_cart_url' ) ) {
        $candidate = wc_get_cart_url();
        $cart_url  = is_string( $candidate ) ? trim( $candidate ) : '';
    }

    return [
        'site_title'        => $site_title,
        'home_url'          => $home_url,
        'logo_id'           => $logo_id,
        'logo_html'         => $logo_html,
        'primary_menu'      => $menu_html( 'aznet-theme-primary-menu' ),
        'mobile_menu'       => $menu_html( 'aznet-theme-mobile-menu' ),
        'account_url'       => $account_url,
        'cart_url'          => $cart_url,
        'search_enabled'    => true === setting( 'header_search', true ),
        'utilities_enabled' => true === setting( 'header_utilities', true ),
        'requested_preset'  => header_preset(),
        'effective_preset'  => effective_header_preset(),
        'sticky_mode'       => header_sticky_mode(),
    ];
}
