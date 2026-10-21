<?php
/**
 * AZnet Theme bootstrap entry point.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'AZNET_THEME_VERSION' ) ) {
    define( 'AZNET_THEME_VERSION', '1.3.92' );
}

require_once __DIR__ . '/inc/theme/bootstrap.php';

/**
 * B12 draft-only page-41978 migration preview stylesheet.
 * This function is scoped to the chosen pilot page; no global CSS override.
 */
add_action(
    'wp_enqueue_scripts',
    static function () {
        if ( is_page( 41978 ) ) {
            $pilot_css = get_stylesheet_directory() . '/assets/css/components/legacy-upgrade-pilot.css';
            $pilot_version = AZNET_THEME_VERSION . '-b12-' . (
                file_exists( $pilot_css ) ? (string) filemtime( $pilot_css ) : '0'
            );
            wp_enqueue_style(
                'aznet-b12-pilot-41978',
                get_stylesheet_directory_uri() . '/assets/css/components/legacy-upgrade-pilot.css',
                [],
                $pilot_version
            );
        }
    },
    20
);

/**
 * Keep tokenized pilot previews out of search indexes.
 * Scope: this page and WPVibe's preview query parameter only.
 */
add_filter(
    'rank_math/frontend/robots',
    static function ( $robots ) {
        if ( is_page( 41978 ) && isset( $_GET['wpvibe_preview'] ) ) {
            return [ 'noindex' => 'noindex', 'nofollow' => 'nofollow' ];
        }
        return $robots;
    },
    99
);

