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

if ( ! function_exists( __NAMESPACE__ . '\\template_presentation_ids' ) ) {
    require_once __DIR__ . '/template-registry.php';
}
if ( ! function_exists( __NAMESPACE__ . '\\load_local_template_manifests' ) ) {
    require_once __DIR__ . '/template-loader.php';
}
if ( [] === template_manifest_ids() ) {
    load_local_template_manifests();
}

/**
 * Return valid Theme-owned visual preset ids.
 *
 * @return array<int,string>
 */
function visual_preset_ids(): array {
    $ids = [ 'default', 'editorial', 'commerce' ];
    if ( function_exists( __NAMESPACE__ . '\\template_presentation_ids' ) ) {
        $ids = array_merge( $ids, template_presentation_ids( 'visual_preset' ) );
    }
    return array_values( array_unique( $ids ) );
}

/**
 * Return valid Theme-owned Homepage preset ids.
 *
 * @return array<int,string>
 */
function homepage_preset_ids(): array {
    $ids = [ 'off' ];
    if ( function_exists( __NAMESPACE__ . '\\template_presentation_ids' ) ) {
        $ids = array_merge( $ids, template_presentation_ids( 'homepage_preset' ) );
    }
    return array_values( array_unique( $ids ) );
}

/**
 * Return manifest-driven visual preset labels for generic Theme admin controls.
 *
 * Core defaults remain available without templates. Registered templates own
 * their display metadata; the first registered template wins when multiple
 * manifests intentionally share one visual preset id.
 *
 * @return array<string,string>
 */
function visual_preset_choices(): array {
    $choices = [
        'default'   => 'Default',
        'editorial' => 'Editorial',
        'commerce'  => 'Commerce',
    ];

    if ( ! function_exists( __NAMESPACE__ . '\\template_manifests' ) ) {
        return $choices;
    }

    foreach ( template_manifests() as $manifest ) {
        $preset = $manifest['presentation']['visual_preset'] ?? null;
        $name = $manifest['name'] ?? null;
        if ( ! is_string( $preset ) || '' === $preset || ! is_string( $name ) || '' === trim( $name ) ) {
            continue;
        }
        if ( ! array_key_exists( $preset, $choices ) ) {
            $choices[ $preset ] = trim( $name );
        }
    }

    return $choices;
}

/**
 * Return normalized defaults for Theme-owned presentation settings.
 *
 * @return array<string, mixed>
 */
function settings_defaults(): array {
    return [
        'schema_version'                => 3,
        'visual_preset'                 => 'default',
        'page_breadcrumbs'              => true,
        'header_preset'                 => 'standard',
        'header_sticky'                 => 'sticky',
        'header_search'                 => true,
        'header_utilities'              => true,
        'footer_preset'                 => 'standard',
        'footer_content_block'          => 0,
        'footer_minimal_primary_heading'      => 'Liên kết nhanh',
        'footer_minimal_contact_heading'      => 'Thông tin liên hệ',
        'footer_minimal_social_heading'       => 'Kết nối với chúng tôi',
        'footer_minimal_policy_heading'       => 'Chính sách',
        'footer_classic_primary_heading'      => 'Liên kết nhanh',
        'footer_classic_contact_heading'      => 'Thông tin liên hệ',
        'footer_classic_social_heading'       => 'Kết nối với chúng tôi',
        'footer_classic_policy_heading'       => 'Chính sách',
        'footer_professional_primary_heading' => 'Liên kết nhanh',
        'footer_professional_contact_heading' => 'Thông tin liên hệ',
        'footer_professional_social_heading'  => 'Kết nối với chúng tôi',
        'footer_professional_policy_heading'  => 'Chính sách',
        'footer_split_primary_heading'        => 'Liên kết nhanh',
        'footer_split_contact_heading'        => 'Thông tin liên hệ',
        'footer_split_social_heading'         => 'Kết nối với chúng tôi',
        'footer_split_policy_heading'         => 'Chính sách',
        'footer_centered_primary_heading'     => 'Liên kết nhanh',
        'footer_centered_contact_heading'     => 'Thông tin liên hệ',
        'footer_centered_social_heading'      => 'Kết nối với chúng tôi',
        'footer_centered_policy_heading'      => 'Chính sách',
        'footer_compact_primary_heading'      => 'Liên kết nhanh',
        'footer_compact_contact_heading'      => 'Thông tin liên hệ',
        'footer_compact_social_heading'       => 'Kết nối với chúng tôi',
        'footer_compact_policy_heading'       => 'Chính sách',
        'woo_catalog_preset'            => 'grid',
        'woo_product_card_density'      => 'balanced',
        'woo_product_preset'            => 'classic',
        'homepage_preset'               => 'off',
        'homepage_industrial01_hero_kicker' => '',
        'homepage_industrial01_hero_title' => '',
        'homepage_industrial01_hero_lede' => '',
        'homepage_industrial01_hero_primary_label' => '',
        'homepage_industrial01_hero_primary_url' => '',
        'homepage_industrial01_hero_secondary_label' => '',
        'homepage_industrial01_hero_secondary_url' => '',
        'homepage_industrial01_hero_image' => 0,
        'homepage_industrial01_hero_background' => 0,
        'homepage_law01_variant'        => 'navy-gold',
        'homepage_law01_sources_initialized' => false,
        'homepage_curtain01_sources_initialized' => false,
        'homepage_law01_hero_variant'   => '',
        'homepage_law01_hero_block'     => 0,
        'homepage_law01_hero_page'      => 0,
        'homepage_law01_services_page'  => 0,
        'homepage_law01_about_page'     => 0,
        'homepage_law01_team_page'      => 0,
        'homepage_law01_knowledge_terms' => [],
        'homepage_law01_case_analysis_term' => 0,
        'homepage_law01_legal_news_term' => 0,
        'homepage_law01_process_page'   => 0,
        'homepage_law01_faq_page'       => 0,
        'homepage_law01_contact_page'   => 0,
        'homepage_curtain01_hero_block' => 0,
        'homepage_curtain01_proof_block' => 0,
        'homepage_curtain01_about_page' => 0,
        'homepage_curtain01_about_image' => 0,
        'homepage_curtain01_knowledge_terms' => [],
        'homepage_curtain01_contact_page' => 0,
        'homepage_hero_variant'          => 'split',
        'homepage_hero_block'            => 0,
        'homepage_proof_block'           => 0,
        'homepage_hero_page'            => 0,
        'homepage_services_page'        => 0,
        'homepage_about_page'           => 0,
        'homepage_about_image'          => 0,
        'homepage_about_kicker'         => '',
        'homepage_about_heading'        => '',
        'homepage_about_quote'          => '',
        'homepage_curtain01_process_page' => 0,
        'homepage_curtain01_projects_term' => 0,
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
    $preset = isset( $raw['visual_preset'] ) && in_array( $raw['visual_preset'], visual_preset_ids(), true )
        ? (string) $raw['visual_preset']
        : 'default';

    $header_preset = isset( $raw['header_preset'] ) && in_array( $raw['header_preset'], [ 'standard', 'compact', 'commerce', 'overlay' ], true )
        ? (string) $raw['header_preset']
        : 'standard';

    $header_sticky = isset( $raw['header_sticky'] ) && in_array( $raw['header_sticky'], [ 'off', 'sticky', 'sticky-compact' ], true )
        ? (string) $raw['header_sticky']
        : 'sticky';

    $footer_preset = isset( $raw['footer_preset'] ) && in_array( $raw['footer_preset'], [ 'standard', 'minimal', 'classic', 'professional', 'split', 'centered', 'compact', 'law-01' ], true )
        ? (string) $raw['footer_preset']
        : 'standard';

    $woo_catalog_preset = isset( $raw['woo_catalog_preset'] ) && in_array( $raw['woo_catalog_preset'], [ 'grid', 'compact-grid', 'editorial' ], true )
        ? (string) $raw['woo_catalog_preset']
        : 'grid';

    $woo_product_card_density = isset( $raw['woo_product_card_density'] ) && in_array( $raw['woo_product_card_density'], [ 'comfortable', 'balanced', 'compact' ], true )
        ? (string) $raw['woo_product_card_density']
        : 'balanced';

    $woo_product_preset = isset( $raw['woo_product_preset'] ) && in_array( $raw['woo_product_preset'], [ 'classic', 'focus', 'story' ], true )
        ? (string) $raw['woo_product_preset']
        : 'classic';

    $homepage_preset = isset( $raw['homepage_preset'] ) && in_array( $raw['homepage_preset'], homepage_preset_ids(), true )
        ? (string) $raw['homepage_preset']
        : 'off';

    $homepage_law01_variant = isset( $raw['homepage_law01_variant'] ) && in_array( $raw['homepage_law01_variant'], [ 'navy-gold', 'burgundy-gold' ], true )
        ? (string) $raw['homepage_law01_variant']
        : 'navy-gold';

    $homepage_hero_variant = isset( $raw['homepage_hero_variant'] ) && in_array( $raw['homepage_hero_variant'], [ 'split', 'centered', 'inverse', 'media-left' ], true )
        ? (string) $raw['homepage_hero_variant']
        : 'split';

    $homepage_law01_hero_variant = isset( $raw['homepage_law01_hero_variant'] ) && in_array( $raw['homepage_law01_hero_variant'], [ 'split', 'centered', 'inverse', 'media-left' ], true )
        ? (string) $raw['homepage_law01_hero_variant']
        : '';

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

    $normalize_text = static function ( mixed $value ): string {
        return is_string( $value ) ? trim( $value ) : '';
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
        'schema_version'                => 3,
        'visual_preset'                 => $preset,
        'page_breadcrumbs'              => $normalize_boolean( 'page_breadcrumbs', true ),
        'header_preset'                 => $header_preset,
        'header_sticky'                 => $header_sticky,
        'header_search'                 => $normalize_boolean( 'header_search', true ),
        'header_utilities'              => $normalize_boolean( 'header_utilities', true ),
        'footer_preset'                 => $footer_preset,
        'footer_content_block'          => $normalize_id( $raw['footer_content_block'] ?? 0 ),
        'footer_minimal_primary_heading'      => $normalize_text( $raw['footer_minimal_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_minimal_contact_heading'      => $normalize_text( $raw['footer_minimal_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_minimal_social_heading'       => $normalize_text( $raw['footer_minimal_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_minimal_policy_heading'       => $normalize_text( $raw['footer_minimal_policy_heading'] ?? 'Chính sách' ),
        'footer_classic_primary_heading'      => $normalize_text( $raw['footer_classic_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_classic_contact_heading'      => $normalize_text( $raw['footer_classic_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_classic_social_heading'       => $normalize_text( $raw['footer_classic_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_classic_policy_heading'       => $normalize_text( $raw['footer_classic_policy_heading'] ?? 'Chính sách' ),
        'footer_professional_primary_heading' => $normalize_text( $raw['footer_professional_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_professional_contact_heading' => $normalize_text( $raw['footer_professional_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_professional_social_heading'  => $normalize_text( $raw['footer_professional_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_professional_policy_heading'  => $normalize_text( $raw['footer_professional_policy_heading'] ?? 'Chính sách' ),
        'footer_split_primary_heading'        => $normalize_text( $raw['footer_split_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_split_contact_heading'        => $normalize_text( $raw['footer_split_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_split_social_heading'         => $normalize_text( $raw['footer_split_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_split_policy_heading'         => $normalize_text( $raw['footer_split_policy_heading'] ?? 'Chính sách' ),
        'footer_centered_primary_heading'     => $normalize_text( $raw['footer_centered_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_centered_contact_heading'     => $normalize_text( $raw['footer_centered_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_centered_social_heading'      => $normalize_text( $raw['footer_centered_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_centered_policy_heading'      => $normalize_text( $raw['footer_centered_policy_heading'] ?? 'Chính sách' ),
        'footer_compact_primary_heading'      => $normalize_text( $raw['footer_compact_primary_heading'] ?? 'Liên kết nhanh' ),
        'footer_compact_contact_heading'      => $normalize_text( $raw['footer_compact_contact_heading'] ?? 'Thông tin liên hệ' ),
        'footer_compact_social_heading'       => $normalize_text( $raw['footer_compact_social_heading'] ?? 'Kết nối với chúng tôi' ),
        'footer_compact_policy_heading'       => $normalize_text( $raw['footer_compact_policy_heading'] ?? 'Chính sách' ),
        'woo_catalog_preset'            => $woo_catalog_preset,
        'woo_product_card_density'      => $woo_product_card_density,
        'woo_product_preset'            => $woo_product_preset,
        'homepage_preset'               => $homepage_preset,
        'homepage_industrial01_hero_kicker' => $normalize_text( $raw['homepage_industrial01_hero_kicker'] ?? '' ),
        'homepage_industrial01_hero_title' => $normalize_text( $raw['homepage_industrial01_hero_title'] ?? '' ),
        'homepage_industrial01_hero_lede' => $normalize_text( $raw['homepage_industrial01_hero_lede'] ?? '' ),
        'homepage_industrial01_hero_primary_label' => $normalize_text( $raw['homepage_industrial01_hero_primary_label'] ?? '' ),
        'homepage_industrial01_hero_primary_url' => $normalize_text( $raw['homepage_industrial01_hero_primary_url'] ?? '' ),
        'homepage_industrial01_hero_secondary_label' => $normalize_text( $raw['homepage_industrial01_hero_secondary_label'] ?? '' ),
        'homepage_industrial01_hero_secondary_url' => $normalize_text( $raw['homepage_industrial01_hero_secondary_url'] ?? '' ),
        'homepage_industrial01_hero_image' => $normalize_id( $raw['homepage_industrial01_hero_image'] ?? 0 ),
        'homepage_industrial01_hero_background' => $normalize_id( $raw['homepage_industrial01_hero_background'] ?? 0 ),
        'homepage_law01_variant'        => $homepage_law01_variant,
        'homepage_law01_sources_initialized' => $normalize_boolean( 'homepage_law01_sources_initialized', false ),
        'homepage_curtain01_sources_initialized' => $normalize_boolean( 'homepage_curtain01_sources_initialized', false ),
        'homepage_law01_hero_variant'   => $homepage_law01_hero_variant,
        'homepage_law01_hero_block'     => $normalize_id( $raw['homepage_law01_hero_block'] ?? 0 ),
        'homepage_law01_hero_page'      => $normalize_id( $raw['homepage_law01_hero_page'] ?? 0 ),
        'homepage_law01_services_page'  => $normalize_id( $raw['homepage_law01_services_page'] ?? 0 ),
        'homepage_law01_about_page'     => $normalize_id( $raw['homepage_law01_about_page'] ?? 0 ),
        'homepage_law01_team_page'      => $normalize_id( $raw['homepage_law01_team_page'] ?? 0 ),
        'homepage_law01_knowledge_terms' => $normalize_ids( $raw['homepage_law01_knowledge_terms'] ?? [] ),
        'homepage_law01_case_analysis_term' => $normalize_id( $raw['homepage_law01_case_analysis_term'] ?? 0 ),
        'homepage_law01_legal_news_term' => $normalize_id( $raw['homepage_law01_legal_news_term'] ?? 0 ),
        'homepage_law01_process_page'   => $normalize_id( $raw['homepage_law01_process_page'] ?? 0 ),
        'homepage_law01_faq_page'       => $normalize_id( $raw['homepage_law01_faq_page'] ?? 0 ),
        'homepage_law01_contact_page'   => $normalize_id( $raw['homepage_law01_contact_page'] ?? 0 ),
        'homepage_curtain01_hero_block' => $normalize_id( $raw['homepage_curtain01_hero_block'] ?? 0 ),
        'homepage_curtain01_proof_block' => $normalize_id( $raw['homepage_curtain01_proof_block'] ?? 0 ),
        'homepage_curtain01_about_page' => $normalize_id( $raw['homepage_curtain01_about_page'] ?? 0 ),
        'homepage_curtain01_about_image' => $normalize_id( $raw['homepage_curtain01_about_image'] ?? 0 ),
        'homepage_curtain01_knowledge_terms' => $normalize_ids( $raw['homepage_curtain01_knowledge_terms'] ?? [] ),
        'homepage_curtain01_contact_page' => $normalize_id( $raw['homepage_curtain01_contact_page'] ?? 0 ),
        'homepage_hero_variant'          => $homepage_hero_variant,
        'homepage_hero_block'            => $normalize_id( $raw['homepage_hero_block'] ?? 0 ),
        'homepage_proof_block'           => $normalize_id( $raw['homepage_proof_block'] ?? 0 ),
        'homepage_hero_page'            => $normalize_id( $raw['homepage_hero_page'] ?? 0 ),
        'homepage_services_page'        => $normalize_id( $raw['homepage_services_page'] ?? 0 ),
        'homepage_about_page'           => $normalize_id( $raw['homepage_about_page'] ?? 0 ),
        'homepage_about_image'          => $normalize_id( $raw['homepage_about_image'] ?? 0 ),
        'homepage_about_kicker'         => $normalize_text( $raw['homepage_about_kicker'] ?? '' ),
        'homepage_about_heading'        => $normalize_text( $raw['homepage_about_heading'] ?? '' ),
        'homepage_about_quote'          => $normalize_text( $raw['homepage_about_quote'] ?? '' ),
        'homepage_curtain01_process_page' => $normalize_id( $raw['homepage_curtain01_process_page'] ?? 0 ),
        'homepage_curtain01_projects_term' => $normalize_id( $raw['homepage_curtain01_projects_term'] ?? 0 ),
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
