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
        'schema_version'                => 2,
        'visual_preset'                 => 'default',
        'header_preset'                 => 'standard',
        'header_sticky'                 => 'sticky',
        'header_search'                 => true,
        'header_utilities'              => true,
        'woo_catalog_preset'            => 'grid',
        'woo_product_card_density'      => 'balanced',
        'woo_product_preset'            => 'classic',
        'homepage_preset'               => 'off',
        'homepage_services_page'        => 0,
        'homepage_about_page'           => 0,
        'homepage_team_page'            => 0,
        'homepage_knowledge_terms'      => [],
        'homepage_case_analysis_term'   => 0,
        'homepage_legal_news_term'      => 0,
        'homepage_process_page'         => 0,
        'homepage_faq_page'             => 0,
        'homepage_contact_page'         => 0,
    ];
}

/**
 * Normalize raw Theme presentation/reference settings through a strict allow-list.
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

    $homepage_preset = isset( $raw['homepage_preset'] ) && in_array( $raw['homepage_preset'], [ 'off', 'law-01' ], true )
        ? (string) $raw['homepage_preset']
        : 'off';

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
        if ( is_string( $value ) && in_array( $value, [ '0', '1' ], true ) ) {
            return '1' === $value;
        }
        return $default;
    };

    $normalize_id = static function ( mixed $value ): int {
        if ( ! is_numeric( $value ) ) {
            return 0;
        }
        $id = (int) $value;
        return $id > 0 ? $id : 0;
    };

    $normalize_ids = static function ( mixed $value ) use ( $normalize_id ): array {
        if ( ! is_array( $value ) ) {
            return [];
        }
        $ids = [];
        foreach ( $value as $candidate ) {
            $id = $normalize_id( $candidate );
            if ( $id > 0 && ! in_array( $id, $ids, true ) ) {
                $ids[] = $id;
            }
        }
        return $ids;
    };

    return [
        'schema_version'                => 2,
        'visual_preset'                 => $preset,
        'header_preset'                 => $header_preset,
        'header_sticky'                 => $header_sticky,
        'header_search'                 => $normalize_boolean( 'header_search', true ),
        'header_utilities'              => $normalize_boolean( 'header_utilities', true ),
        'woo_catalog_preset'            => $woo_catalog_preset,
        'woo_product_card_density'      => $woo_product_card_density,
        'woo_product_preset'            => $woo_product_preset,
        'homepage_preset'               => $homepage_preset,
        'homepage_services_page'        => $normalize_id( $raw['homepage_services_page'] ?? 0 ),
        'homepage_about_page'           => $normalize_id( $raw['homepage_about_page'] ?? 0 ),
        'homepage_team_page'            => $normalize_id( $raw['homepage_team_page'] ?? 0 ),
        'homepage_knowledge_terms'      => $normalize_ids( $raw['homepage_knowledge_terms'] ?? [] ),
        'homepage_case_analysis_term'   => $normalize_id( $raw['homepage_case_analysis_term'] ?? 0 ),
        'homepage_legal_news_term'      => $normalize_id( $raw['homepage_legal_news_term'] ?? 0 ),
        'homepage_process_page'         => $normalize_id( $raw['homepage_process_page'] ?? 0 ),
        'homepage_faq_page'             => $normalize_id( $raw['homepage_faq_page'] ?? 0 ),
        'homepage_contact_page'         => $normalize_id( $raw['homepage_contact_page'] ?? 0 ),
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
 * @param string $key Setting key.
 * @param mixed $fallback Fallback when key is not present.
 * @return mixed
 */
function setting( string $key, mixed $fallback = null ): mixed {
    $all = settings();
    return array_key_exists( $key, $all ) ? $all[ $key ] : $fallback;
}
