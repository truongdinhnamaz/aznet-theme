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

function enqueue_assets(): void {
    $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;

    wp_enqueue_style(
        'aznet-theme-tokens',
        get_theme_file_uri( '/assets/css/tokens.css' ),
        [],
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

    wp_enqueue_style(
        'aznet-theme-site-footer',
        get_theme_file_uri( '/assets/css/components/site-footer.css' ),
        [ 'aznet-theme-tokens' ],
        $version
    );

    if ( should_enqueue_generic_content_assets() ) {
        wp_enqueue_style(
            'aznet-theme-generic-content',
            get_theme_file_uri( '/assets/css/components/generic-content.css' ),
            [ 'aznet-theme-tokens' ],
            $version
        );
    }
}
