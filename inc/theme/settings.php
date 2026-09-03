<?php
/**
 * Theme-owned presentation settings.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme\Settings;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

const SCHEMA_VERSION = 1;
const THEME_MOD_KEY = 'aznet_theme_settings';

/** @return array<string,mixed> */
function defaults(): array {
    return [
        'schema_version' => SCHEMA_VERSION,
        'preset'         => '',
        'design'         => [],
        'header'         => [],
        'footer'         => [],
        'content'        => [],
        'woocommerce'    => [],
    ];
}

/** @return array<string,mixed> */
function normalize( mixed $candidate ): array {
    $defaults = defaults();
    if ( ! is_array( $candidate ) ) {
        return $defaults;
    }

    $normalized = $defaults;
    $normalized['schema_version'] = SCHEMA_VERSION;

    if ( isset( $candidate['preset'] ) && is_string( $candidate['preset'] ) ) {
        $normalized['preset'] = sanitize_key( $candidate['preset'] );
    }

    foreach ( [ 'design', 'header', 'footer', 'content', 'woocommerce' ] as $section ) {
        if ( isset( $candidate[ $section ] ) && is_array( $candidate[ $section ] ) ) {
            $normalized[ $section ] = $candidate[ $section ];
        }
    }

    return $normalized;
}

/** @return array<string,mixed> */
function get(): array {
    return normalize( get_theme_mod( THEME_MOD_KEY, [] ) );
}

/** @param array<string,mixed> $candidate */
function save( array $candidate ): void {
    set_theme_mod( THEME_MOD_KEY, normalize( $candidate ) );
}

function reset(): void {
    remove_theme_mod( THEME_MOD_KEY );
}
