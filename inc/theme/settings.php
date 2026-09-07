<?php
/**
 * Theme-owned presentation settings.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return normalized defaults for Theme-owned presentation settings.
 *
 * @return array<string, mixed>
 */
function settings_defaults(): array {
    return [
        'schema_version'           => 1,
        'visual_preset'            => 'default',
        'header_preset'            => 'standard',
        'header_sticky'            => 'sticky',
        'header_search'            => true,
        'header_utilities'         => true,
        'woo_catalog_preset'       => 'grid',
        'woo_product_card_density' => 'balanced',
        'woo_product_preset'       => 'classic',
    ];
}

/**
 * Normalize raw Theme presentation settings through a strict allow-list.
 *
 * @param array<string, mixed> $raw Raw settings.
 * @return array<string, mixed>
 */
function normalize_settings( array $raw ): array {
    $preset = isset( $raw['visual_preset'] ) && in_array( $raw['visual_preset'], [ 'default', 'editorial', 'commerce' ], true )
        ? (string) $raw['visual_preset']
        : 'default';

    $header_preset = isset( $raw['header_preset'] ) && in_array( $raw['header_preset'], [ 'standard', 'compact', 'commerce', 'overlay' ], true )
        ? (string) $raw['header_preset']
        : 'standard';

    $header_sticky = isset( $raw['header_sticky'] ) && in_array( $raw['header_sticky'], [ 'off', 'sticky', 'sticky-compact' ], true )
        ? (string) $raw['header_sticky']
        : 'sticky';

    $woo_catalog_preset = isset( $raw['woo_catalog_preset'] ) && in_array( $raw['woo_catalog_preset'], [ 'grid', 'compact-grid', 'editorial' ], true )
        ? (string) $raw['woo_catalog_preset']
        : 'grid';

    $woo_product_card_density = isset( $raw['woo_product_card_density'] ) && in_array( $raw['woo_product_card_density'], [ 'comfortable', 'balanced', 'compact' ], true )
        ? (string) $raw['woo_product_card_density']
        : 'balanced';

    $woo_product_preset = isset( $raw['woo_product_preset'] ) && in_array( $raw['woo_product_preset'], [ 'classic', 'focus', 'story' ], true )
        ? (string) $raw['woo_product_preset']
        : 'classic';

    $normalize_boolean = static function ( string $key, bool $default ) use ( $raw ): bool {
        if ( ! array_key_exists( $key, $raw ) ) {
            return $default;
        }

        $value = $raw[ $key ];

        if ( is_bool( $value ) ) {
            return $value;
        }

        if ( is_int( $value ) && ( 0 === $value || 1 === $value ) ) {
            return 1 === $value;
        }

        return $default;
    };

    return [
        'schema_version'           => 1,
        'visual_preset'            => $preset,
        'header_preset'            => $header_preset,
        'header_sticky'            => $header_sticky,
        'header_search'            => $normalize_boolean( 'header_search', true ),
        'header_utilities'         => $normalize_boolean( 'header_utilities', true ),
        'woo_catalog_preset'       => $woo_catalog_preset,
        'woo_product_card_density' => $woo_product_card_density,
        'woo_product_preset'       => $woo_product_preset,
    ];
}

/**
 * Return normalized Theme-owned presentation settings.
 *
 * @return array<string, mixed>
 */
function settings(): array {
    $raw = get_theme_mod( 'aznet_theme_settings', [] );

    return normalize_settings( is_array( $raw ) ? $raw : [] );
}

/**
 * Read one normalized Theme-owned presentation setting.
 *
 * @param string $key      Setting key.
 * @param mixed  $fallback Fallback when key is not present.
 * @return mixed
 */
function setting( string $key, mixed $fallback = null ): mixed {
    $all = settings();

    return array_key_exists( $key, $all ) ? $all[ $key ] : $fallback;
}
