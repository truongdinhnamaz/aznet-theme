<?php
/**
 * Preset-isolated Homepage authoring/source registry.
 *
 * Theme owns typed references and presentation state only. WordPress remains
 * the authoritative owner of Page/Post/Category/Media/wp_block content.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** @return array<string, array<string, array<string, mixed>>> */
function homepage_preset_registry(): array {
    return [
        'law-01' => [
            'hero'          => [ 'type' => 'wp_block', 'key' => 'homepage_law01_hero_block', 'legacy' => 'homepage_hero_block' ],
            'hero_variant'  => [ 'type' => 'variant', 'key' => 'homepage_law01_hero_variant', 'legacy' => 'homepage_hero_variant' ],
            'hero_page'     => [ 'type' => 'page', 'key' => 'homepage_law01_hero_page', 'legacy' => 'homepage_hero_page' ],
            'services'      => [ 'type' => 'page', 'key' => 'homepage_law01_services_page', 'legacy' => 'homepage_services_page', 'children' => true ],
            'about'         => [ 'type' => 'page', 'key' => 'homepage_law01_about_page', 'legacy' => 'homepage_about_page' ],
            'team'          => [ 'type' => 'page', 'key' => 'homepage_law01_team_page', 'legacy' => 'homepage_team_page', 'children' => true ],
            'knowledge'     => [ 'type' => 'categories', 'key' => 'homepage_law01_knowledge_terms', 'legacy' => 'homepage_knowledge_terms' ],
            'case_analysis' => [ 'type' => 'category', 'key' => 'homepage_law01_case_analysis_term', 'legacy' => 'homepage_case_analysis_term' ],
            'legal_news'    => [ 'type' => 'category', 'key' => 'homepage_law01_legal_news_term', 'legacy' => 'homepage_legal_news_term' ],
            'process'       => [ 'type' => 'page', 'key' => 'homepage_law01_process_page', 'legacy' => 'homepage_process_page' ],
            'faq'           => [ 'type' => 'page', 'key' => 'homepage_law01_faq_page', 'legacy' => 'homepage_faq_page' ],
            'contact'       => [ 'type' => 'page', 'key' => 'homepage_law01_contact_page', 'legacy' => 'homepage_contact_page' ],
        ],
        'curtain-01' => [
            'hero'        => [ 'type' => 'wp_block', 'key' => 'homepage_curtain01_hero_block', 'legacy' => 'homepage_hero_block' ],
            'proof'       => [ 'type' => 'wp_block', 'key' => 'homepage_curtain01_proof_block', 'legacy' => 'homepage_proof_block' ],
            'about'       => [ 'type' => 'page', 'key' => 'homepage_curtain01_about_page', 'legacy' => 'homepage_about_page' ],
            'about_image' => [ 'type' => 'attachment', 'key' => 'homepage_curtain01_about_image', 'legacy' => 'homepage_about_image' ],
            'knowledge'   => [ 'type' => 'categories', 'key' => 'homepage_curtain01_knowledge_terms', 'legacy' => 'homepage_knowledge_terms' ],
            'contact'     => [ 'type' => 'page', 'key' => 'homepage_curtain01_contact_page', 'legacy' => 'homepage_contact_page' ],
            'process'     => [ 'type' => 'page', 'key' => 'homepage_curtain01_process_page', 'legacy' => 'homepage_curtain01_process_page' ],
            'projects'    => [ 'type' => 'category', 'key' => 'homepage_curtain01_projects_term', 'legacy' => 'homepage_curtain01_projects_term' ],
        ],
    ];
}

/** @return array<string, mixed>|null */
function homepage_source_descriptor( string $preset, string $slot ): ?array {
    $registry = homepage_preset_registry();
    $descriptor = $registry[ $preset ][ $slot ] ?? null;
    return is_array( $descriptor ) ? $descriptor : null;
}

function homepage_source_key( string $preset, string $slot ): ?string {
    $descriptor = homepage_source_descriptor( $preset, $slot );
    return null === $descriptor ? null : (string) ( $descriptor['key'] ?? '' );
}

function homepage_source_initialization_key( string $preset ): ?string {
    return [
        'law-01'     => 'homepage_law01_sources_initialized',
        'curtain-01' => 'homepage_curtain01_sources_initialized',
    ][ $preset ] ?? null;
}

function homepage_source_value_is_nonempty( mixed $value, string $type ): bool {
    if ( 'categories' === $type ) {
        return is_array( $value ) && [] !== $value;
    }
    if ( 'variant' === $type ) {
        return is_string( $value ) && '' !== $value;
    }
    return is_numeric( $value ) && (int) $value > 0;
}

function homepage_source_neutral_value( string $type ): mixed {
    if ( 'categories' === $type ) {
        return [];
    }
    if ( 'variant' === $type ) {
        return 'split';
    }
    return 0;
}

/** @param array<string, mixed>|null $settings */
function homepage_source_value( string $preset, string $slot, ?array $settings = null ): mixed {
    $settings = null === $settings ? settings() : $settings;
    $descriptor = homepage_source_descriptor( $preset, $slot );
    if ( null === $descriptor ) {
        return null;
    }

    $key = (string) ( $descriptor['key'] ?? '' );
    $legacy = (string) ( $descriptor['legacy'] ?? '' );
    $type = (string) ( $descriptor['type'] ?? '' );
    $marker_key = homepage_source_initialization_key( $preset );
    $initialized = null !== $marker_key && true === (bool) ( $settings[ $marker_key ] ?? false );
    $scoped = '' !== $key && array_key_exists( $key, $settings ) ? $settings[ $key ] : homepage_source_neutral_value( $type );

    if ( $initialized || homepage_source_value_is_nonempty( $scoped, $type ) ) {
        if ( 'variant' === $type && ! homepage_source_value_is_nonempty( $scoped, $type ) ) {
            return 'split';
        }
        return $scoped;
    }

    if ( '' !== $legacy && array_key_exists( $legacy, $settings ) ) {
        return $settings[ $legacy ];
    }

    return homepage_source_neutral_value( $type );
}
