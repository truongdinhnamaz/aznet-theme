<?php
/**
 * Theme-owned Design System helpers.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the active normalized visual preset.
 */
function visual_preset(): string {
    $preset = setting( 'visual_preset', 'default' );

    return in_array( $preset, [ 'default', 'editorial', 'commerce' ], true )
        ? (string) $preset
        : 'default';
}

/**
 * Return the WordPress-native editor styles for the active visual preset.
 *
 * @return array<int, string>
 */
function editor_stylesheets(): array {
    $stylesheets = [ 'assets/css/tokens.css' ];
    $preset      = visual_preset();

    if ( 'default' !== $preset ) {
        $stylesheets[] = 'assets/css/presets/' . $preset . '.css';
    }

    return $stylesheets;
}

/**
 * Add the active visual preset as a Theme-owned presentation body class.
 *
 * @param array<int, string> $classes Existing body classes.
 * @return array<int, string>
 */
function visual_preset_body_classes( array $classes ): array {
    $classes[] = 'aznet-theme-preset--' . visual_preset();

    return array_values( array_unique( $classes ) );
}
