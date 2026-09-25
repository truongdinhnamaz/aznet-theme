<?php
/**
 * Shared effective Homepage surface model.
 *
 * Theme-owned request-local presentation state only. WordPress/provider owners
 * remain authoritative for the underlying content and domain data.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return the Theme setting key that currently owns effective presentation
 * selection for one Homepage slot.
 *
 * A small Law 01 compatibility set intentionally remains on the proven legacy
 * keys from the 1.3.53 recovery. Keeping that exception explicit here prevents
 * admin/frontend drift while avoiding a risky silent source migration.
 */
function homepage_effective_source_key( string $preset, string $slot ): ?string {
    $descriptor = homepage_source_descriptor( $preset, $slot );
    if ( null === $descriptor ) { return null; }

    if ( 'law-01' === $preset && in_array( $slot, [ 'about', 'team', 'case_analysis', 'legal_news' ], true ) ) {
        $legacy = (string) ( $descriptor['legacy'] ?? '' );
        return '' !== $legacy ? $legacy : null;
    }

    $key = (string) ( $descriptor['key'] ?? '' );
    return '' !== $key ? $key : null;
}

/** Resolve the effective presentation source while preserving proven compatibility paths. */
function homepage_effective_source_value( string $preset, string $slot, ?array $settings = null ): mixed {
    $settings = null === $settings ? settings() : $settings;
    $descriptor = homepage_source_descriptor( $preset, $slot );
    if ( null === $descriptor ) { return null; }

    if ( 'law-01' === $preset && in_array( $slot, [ 'about', 'team', 'case_analysis', 'legal_news' ], true ) ) {
        $legacy = (string) ( $descriptor['legacy'] ?? '' );
        $type = (string) ( $descriptor['type'] ?? '' );
        return '' !== $legacy && array_key_exists( $legacy, $settings )
            ? $settings[ $legacy ]
            : homepage_source_neutral_value( $type );
    }

    return homepage_source_value( $preset, $slot, $settings );
}

/** Return compact plain-text copy for an admin presentation summary. */
function homepage_surface_text_summary( string $value, int $words = 24 ): string {
    $value = trim( wp_strip_all_tags( $value ) );
    return '' === $value ? '' : wp_trim_words( $value, $words, '…' );
}

/** Find the first rendered heading-like block copy in a Core-block tree. */
function homepage_surface_first_heading( string $content ): string {
    $queue = parse_blocks( $content );
    while ( [] !== $queue ) {
        $block = array_shift( $queue );
        if ( ! is_array( $block ) ) {
            continue;
        }
        if ( 'core/heading' === ( $block['blockName'] ?? null ) ) {
            $html = (string) ( $block['innerHTML'] ?? '' );
            $text = homepage_surface_text_summary( $html, 20 );
            if ( '' !== $text ) {
                return $text;
            }
        }
        foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $inner ) {
            $queue[] = $inner;
        }
    }
    return '';
}

/**
 * Build one normalized presentation-surface entry.
 *
 * @param array<string,mixed> $model Surface-specific read model.
 * @return array<string,mixed>
 */
function homepage_surface_entry(
    string $key,
    string $label,
    string $template,
    string $boundary,
    string $anchor,
    string $source_type,
    int $source_id,
    array $model = []
): array {
    return [
        'key'         => $key,
        'label'       => $label,
        'template'    => $template,
        'boundary'    => $boundary,
        'anchor'      => $anchor,
        'source_type' => $source_type,
        'source_id'   => $source_id,
        'model'       => $model,
    ];
}

/**
 * Resolve the effective Homepage surfaces that can actually render now.
 *
 * The returned model is request-local and never persisted. Law 01 is the first
 * shared-model implementation. Curtain 01 keeps its existing authoring path
 * until its own parity slice is opened.
 *
 * @return array<int,array<string,mixed>>
 */
function homepage_effective_surface_map( ?string $preset = null ): array {
    static $cache = [];

    $preset = null === $preset ? homepage_preset() : $preset;
    if ( array_key_exists( $preset, $cache ) ) {
        return $cache[ $preset ];
    }
    if ( 'law-01' !== $preset ) {
        $cache[ $preset ] = [];
        return $cache[ $preset ];
    }

    $settings = settings();
    $variant = homepage_law01_variant();
    $surfaces = [];

    $hero_block_id = (int) homepage_effective_source_value( 'law-01', 'hero', $settings );
    $hero_block = homepage_block_reference( $hero_block_id );
    $hero_block_html = '';
    if ( $hero_block instanceof \WP_Post ) {
        $content = trim( (string) $hero_block->post_content );
        $hero_block_html = '' !== $content ? trim( (string) do_blocks( $content ) ) : '';
    }

    if ( '' !== $hero_block_html ) {
        $heading = homepage_surface_first_heading( (string) $hero_block->post_content );
        $surfaces[] = homepage_surface_entry(
            'hero',
            __( 'Hero', 'aznet-theme' ),
            'hero',
            'before',
            'aznet-homepage-hero',
            'wp_block',
            (int) $hero_block->ID,
            [
                'title'   => '' !== $heading ? $heading : get_the_title( $hero_block ),
                'summary' => homepage_surface_text_summary( $hero_block_html ),
                'source'  => get_the_title( $hero_block ),
            ]
        );
    } else {
        $hero_page_id = (int) homepage_effective_source_value( 'law-01', 'hero_page', $settings );
        $hero_page = homepage_page_reference( $hero_page_id );
        if ( $hero_page instanceof \WP_Post ) {
            $surfaces[] = homepage_surface_entry(
                'hero',
                __( 'Hero', 'aznet-theme' ),
                'hero',
                'before',
                'aznet-homepage-hero',
                'page',
                (int) $hero_page->ID,
                [
                    'title'   => get_the_title( $hero_page ),
                    'summary' => homepage_surface_text_summary( (string) get_the_excerpt( $hero_page ) ),
                    'source'  => get_the_title( $hero_page ),
                ]
            );
        } else {
            $front_id = (int) get_option( 'page_on_front', 0 );
            $site_title = trim( (string) get_bloginfo( 'name' ) );
            $front_title = $front_id > 0 ? trim( (string) get_the_title( $front_id ) ) : '';
            $title = '' !== $site_title ? $site_title : $front_title;
            if ( '' !== $title ) {
                $surfaces[] = homepage_surface_entry(
                    'hero',
                    __( 'Hero', 'aznet-theme' ),
                    'hero',
                    'before',
                    'aznet-homepage-hero',
                    'fallback',
                    $front_id,
                    [
                        'title'   => $title,
                        'summary' => $front_id > 0 ? homepage_surface_text_summary( (string) get_the_excerpt( $front_id ) ) : '',
                        'source'  => __( 'Dữ liệu WordPress dự phòng', 'aznet-theme' ),
                    ]
                );
            }
        }
    }

    $services_id = (int) homepage_effective_source_value( 'law-01', 'services', $settings );
    $services_page = homepage_page_reference( $services_id );
    $service_items = $services_page instanceof \WP_Post
        ? homepage_direct_published_children( (int) $services_page->ID, 6 )
        : [];
    if ( $services_page instanceof \WP_Post && [] !== $service_items ) {
        $surfaces[] = homepage_surface_entry(
            'services',
            __( 'Dịch vụ', 'aznet-theme' ),
            'services',
            'before',
            'aznet-homepage-services',
            'page',
            (int) $services_page->ID,
            [
                'title'   => get_the_title( $services_page ),
                'summary' => homepage_surface_text_summary( (string) get_the_excerpt( $services_page ) ),
                'items'   => $service_items,
                'source'  => get_the_title( $services_page ),
            ]
        );
    }

    // Profile currently consumes the proven legacy Page settings path on the frontend.
    $about = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'about', $settings ) );
    $team = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'team', $settings ) );
    if ( $about instanceof \WP_Post || $team instanceof \WP_Post ) {
        $surfaces[] = homepage_surface_entry(
            'profile',
            __( 'Giới thiệu & Đội ngũ', 'aznet-theme' ),
            'profile',
            'before',
            'aznet-homepage-profile',
            'composite',
            0,
            [
                'title' => __( 'Giới thiệu & Đội ngũ', 'aznet-theme' ),
                'about' => $about,
                'team'  => $team,
                'items' => $team instanceof \WP_Post ? team_directory_members( 4 ) : [],
                'source' => implode(
                    ' + ',
                    array_filter(
                        [
                            $about instanceof \WP_Post ? get_the_title( $about ) : '',
                            $team instanceof \WP_Post ? get_the_title( $team ) : '',
                        ]
                    )
                ),
            ]
        );
    }

    $after_order = 'burgundy-gold' === $variant
        ? [ 'latest', 'topics', 'analysis', 'news', 'process', 'faq', 'final-cta' ]
        : [ 'topics', 'latest', 'analysis', 'news', 'process', 'faq', 'final-cta' ];

    $exclude_ids = [];

    foreach ( $after_order as $surface_key ) {
        if ( 'topics' === $surface_key ) {
            $terms = homepage_category_references( (array) homepage_effective_source_value( 'law-01', 'knowledge', $settings ) );
            if ( [] !== $terms ) {
                $surfaces[] = homepage_surface_entry(
                    'topics',
                    __( 'Chủ đề', 'aznet-theme' ),
                    'topics',
                    'after',
                    'aznet-homepage-topics',
                    'categories',
                    0,
                    [
                        'title'  => __( 'Chủ đề', 'aznet-theme' ),
                        'items'  => $terms,
                        'source' => implode( ', ', array_map( static fn ( \WP_Term $term ): string => $term->name, $terms ) ),
                    ]
                );
            }
            continue;
        }

        if ( 'latest' === $surface_key ) {
            $posts = homepage_latest_posts( (array) homepage_source_value( 'law-01', 'knowledge', $settings ), 3, $exclude_ids );
            if ( [] !== $posts ) {
                $exclude_ids = array_values( array_unique( array_merge( $exclude_ids, array_map( static fn ( \WP_Post $post ): int => (int) $post->ID, $posts ) ) ) );
                $posts_page_id = (int) get_option( 'page_for_posts', 0 );
                $posts_page_title = $posts_page_id > 0 && 'publish' === get_post_status( $posts_page_id )
                    ? trim( (string) get_the_title( $posts_page_id ) )
                    : '';
                $surfaces[] = homepage_surface_entry(
                    'latest',
                    __( 'Bài viết', 'aznet-theme' ),
                    'latest',
                    'after',
                    'aznet-homepage-articles',
                    'query',
                    $posts_page_id,
                    [
                        'title'  => '' !== $posts_page_title ? $posts_page_title : __( 'Bài viết', 'aznet-theme' ),
                        'items'  => $posts,
                        'source' => __( '3 bài mới nhất từ các chủ đề đã chọn', 'aznet-theme' ),
                    ]
                );
            }
            continue;
        }

        if ( 'analysis' === $surface_key || 'news' === $surface_key ) {
            $setting_key = 'analysis' === $surface_key ? 'homepage_case_analysis_term' : 'homepage_legal_news_term';
            $slot = 'analysis' === $surface_key ? 'case_analysis' : 'legal_news';
            $term_id = (int) homepage_effective_source_value( 'law-01', $slot, $settings );
            $term = homepage_category_reference( $term_id );
            $limit = 'analysis' === $surface_key ? 3 : 5;
            $posts = $term instanceof \WP_Term ? homepage_latest_posts( [ $term_id ], $limit, $exclude_ids ) : [];
            if ( $term instanceof \WP_Term && [] !== $posts ) {
                $exclude_ids = array_values( array_unique( array_merge( $exclude_ids, array_map( static fn ( \WP_Post $post ): int => (int) $post->ID, $posts ) ) ) );
                $surfaces[] = homepage_surface_entry(
                    $surface_key,
                    'analysis' === $surface_key ? __( 'Phân tích vụ việc', 'aznet-theme' ) : __( 'Tin pháp luật', 'aznet-theme' ),
                    $surface_key,
                    'after',
                    'analysis' === $surface_key ? 'aznet-homepage-analysis' : 'aznet-homepage-news',
                    'category',
                    $term_id,
                    [
                        'title'  => $term->name,
                        'items'  => $posts,
                        'source' => $term->name,
                    ]
                );
            }
            continue;
        }

        if ( 'process' === $surface_key || 'faq' === $surface_key ) {
            $page = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', $surface_key, $settings ) );
            if ( ! $page instanceof \WP_Post ) {
                $legacy_key = 'process' === $surface_key ? 'homepage_process_page' : 'homepage_faq_page';
                $page = homepage_page_reference( (int) setting( $legacy_key, 0 ) );
            }
            if ( $page instanceof \WP_Post ) {
                $surfaces[] = homepage_surface_entry(
                    $surface_key,
                    'process' === $surface_key ? __( 'Quy trình', 'aznet-theme' ) : __( 'Câu hỏi thường gặp', 'aznet-theme' ),
                    $surface_key,
                    'after',
                    'process' === $surface_key ? 'aznet-homepage-process' : 'aznet-homepage-faq',
                    'page',
                    (int) $page->ID,
                    [
                        'title'   => get_the_title( $page ),
                        'summary' => homepage_surface_text_summary( (string) get_the_excerpt( $page ) ),
                        'source'  => get_the_title( $page ),
                    ]
                );
            }
            continue;
        }

        if ( 'final-cta' === $surface_key ) {
            $page = homepage_page_reference( (int) homepage_effective_source_value( 'law-01', 'contact', $settings ) );
            $phone = '';
            if ( function_exists( __NAMESPACE__ . '\\contact_surface_model' ) ) {
                $contact_model = contact_surface_model();
                if ( is_array( $contact_model ) ) {
                    foreach ( (array) ( $contact_model['contact']['points'] ?? [] ) as $point ) {
                        if ( is_array( $point ) && 'phone' === ( $point['kind'] ?? '' ) && '' !== trim( (string) ( $point['value'] ?? '' ) ) ) {
                            $phone = trim( (string) $point['value'] );
                            break;
                        }
                    }
                }
            }
            if ( $page instanceof \WP_Post || '' !== $phone ) {
                $surfaces[] = homepage_surface_entry(
                    'final-cta',
                    __( 'Liên hệ', 'aznet-theme' ),
                    'final-cta',
                    'after',
                    'aznet-homepage-contact',
                    $page instanceof \WP_Post ? 'page' : 'provider',
                    $page instanceof \WP_Post ? (int) $page->ID : 0,
                    [
                        'title'   => $page instanceof \WP_Post ? get_the_title( $page ) : __( 'Liên hệ', 'aznet-theme' ),
                        'summary' => $page instanceof \WP_Post ? homepage_surface_text_summary( (string) get_the_excerpt( $page ) ) : $phone,
                        'phone'   => $phone,
                        'source'  => $page instanceof \WP_Post ? get_the_title( $page ) : __( 'RootProfile public contact provider', 'aznet-theme' ),
                    ]
                );
            }
        }
    }

    $cache[ $preset ] = $surfaces;
    return $cache[ $preset ];
}
