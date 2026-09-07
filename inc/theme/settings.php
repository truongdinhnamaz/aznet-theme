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
        'schema_version' => 1,
        'visual_preset'  => 'default',
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

    return [
        'schema_version' => 1,
        'visual_preset'  => $preset,
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
