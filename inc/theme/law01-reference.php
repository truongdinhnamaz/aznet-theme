<?php
/** Scoped presentation refinements for the approved Law 01 reference. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Enqueue only for the existing Burgundy/Gold Homepage; never for provider surfaces. */
function enqueue_law01_reference_asset( ?string $version = null ): void {
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' )
        || ! homepage_composer_active()
        || 'burgundy-gold' !== homepage_law01_variant() ) {
        return;
    }
    if ( null === $version ) {
        $version = defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : null;
    }
    $path = '/assets/css/components/homepage-law-01-reference.css';
    wp_enqueue_style(
        'aznet-theme-homepage-law-01-reference',
        get_theme_file_uri( $path ),
        [ 'aznet-theme-homepage-law-01-variants' ],
        asset_content_version( $path, $version )
    );
}
