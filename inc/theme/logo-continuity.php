<?php
/**
 * Preserve an explicitly configured native WordPress Custom Logo on theme switch.
 *
 * Never infer a site's logo from attachment filenames, favicon, URLs, or
 * third-party theme/plugin storage. A missing logo remains a manual site choice.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Transfer only an explicitly selected WordPress custom_logo from the prior
 * theme, when AZnet Theme has no configured logo.
 *
 * @param string         $old_theme_name Previous theme display name.
 * @param \WP_Theme|null $old_theme      Native previous theme instance.
 */
function preserve_native_custom_logo( $old_theme_name, $old_theme ): void {
    if ( ! $old_theme instanceof \WP_Theme || ! function_exists( 'get_theme_mod' )
        || ! function_exists( 'set_theme_mod' ) || ! function_exists( 'get_option' )
        || ! function_exists( 'wp_attachment_is_image' ) || ! function_exists( 'wp_get_attachment_image' ) ) {
        return;
    }

    // An explicitly configured current logo always wins, even if it is invalid.
    if ( (int) get_theme_mod( 'custom_logo', 0 ) > 0 ) {
        return;
    }

    $stylesheet = (string) $old_theme->get_stylesheet();
    if ( '' === $stylesheet || ! preg_match( '/^[a-zA-Z0-9._-]+$/', $stylesheet ) ) {
        return;
    }

    // theme_mods_* is WordPress-native theme configuration, not a plugin store.
    $previous_mods = get_option( 'theme_mods_' . $stylesheet, [] );
    if ( ! is_array( $previous_mods ) || ! isset( $previous_mods['custom_logo'] )
        || ! is_numeric( $previous_mods['custom_logo'] ) ) {
        return;
    }

    $logo_id = (int) $previous_mods['custom_logo'];
    if ( $logo_id <= 0 || ! wp_attachment_is_image( $logo_id )
        || '' === trim( (string) wp_get_attachment_image( $logo_id, 'full' ) ) ) {
        return;
    }

    set_theme_mod( 'custom_logo', $logo_id );
}

/**
 * Theme-control-center guidance only; never guess or mutate identity assets.
 */
function logo_configuration_notice(): void {
    if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'edit_theme_options' )
        || ! function_exists( 'get_current_screen' ) ) {
        return;
    }

    $screen = get_current_screen();
    if ( ! is_object( $screen ) || ! in_array( (string) ( $screen->id ?? '' ),
        [ 'toplevel_page_aznet-theme', 'appearance_page_aznet-theme' ], true ) ) {
        return;
    }

    $logo_id = (int) get_theme_mod( 'custom_logo', 0 );
    if ( $logo_id > 0 && function_exists( 'wp_attachment_is_image' )
        && wp_attachment_is_image( $logo_id )
        && function_exists( 'wp_get_attachment_image' )
        && '' !== trim( (string) wp_get_attachment_image( $logo_id, 'full' ) ) ) {
        return;
    }

    $url = admin_url( 'customize.php?autofocus[control]=custom_logo' );
    echo '<div class="notice notice-warning"><p>';
    echo esc_html__( 'No valid site logo is configured. The header and footer will display the site name until you select the official logo.', 'aznet-theme' );
    echo ' <a href="' . esc_url( $url ) . '">';
    echo esc_html__( 'Choose the WordPress Custom Logo', 'aznet-theme' );
    echo '</a></p></div>';
}

add_action( 'after_switch_theme', __NAMESPACE__ . '\\preserve_native_custom_logo', 10, 2 );
add_action( 'admin_notices', __NAMESPACE__ . '\\logo_configuration_notice' );
