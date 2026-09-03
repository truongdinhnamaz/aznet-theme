<?php
/**
 * Generic content shell presentation helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the presentation classes for the generic content shell.
 *
 * @param bool $with_navigation Whether a normalized navigation region exists.
 * @return string[]
 */
function content_shell_classes( bool $with_navigation = false ): array {
    $classes = [ 'aznet-theme-content-shell' ];

    if ( $with_navigation ) {
        $classes[] = 'aznet-theme-content-shell--with-navigation';
    }

    return $classes;
}

/**
 * Whether the current WordPress request uses a generic content surface.
 */
function should_enqueue_generic_content_assets(): bool {
    if ( null !== \AZnet\Theme\Integrations\WooCommerce\current_surface() ) {
        return false;
    }

    return is_page()
        || is_singular( 'post' )
        || is_archive()
        || is_search()
        || is_404();
}
