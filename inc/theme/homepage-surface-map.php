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
 * This wrapper is the single read/key boundary used by the unified Homepage
 * backend. It delegates to the preset-scoped resolver, which already preserves
 * legacy fallback until a scoped source becomes explicit.
 */
function homepage_effective_source_key( string $preset, string $slot ): ?string {
    return homepage_source_key( $preset, $slot );
}

/** Resolve the effective presentation source while preserving proven compatibility paths. */
function homepage_effective_source_value( string $preset, string $slot, ?array $settings = null ): mixed {
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
 * Return the exact Curtain 01 category-showcase cards that can render.
 *
 * @return array<int,array{term:\WP_Term,url:string,image:string}>
 */
function homepage_curtain01_category_showcase_cards(): array {
    if (
        ! function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_category_showcase_terms' )
        || ! function_exists( 'woocommerce_subcategory_thumbnail' )
    ) {
        return [];
    }

    $cards = [];
    $terms = \AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms( 12 );
    foreach ( $terms as $term ) {
        if ( ! $term instanceof \WP_Term ) {
            continue;
        }

        $url = get_term_link( $term );
        if ( is_wp_error( $url ) ) {
            continue;
        }

        ob_start();
        woocommerce_subcategory_thumbnail( $term );
        $image = trim( (string) ob_get_clean() );

        if (
            '' === $image
            || ! str_contains( $image, '<img' )
            || str_contains( $image, 'woocommerce-placeholder' )
        ) {
            continue;
        }

        $cards[] = [
            'term'  => $term,
            'url'   => (string) $url,
            'image' => $image,
        ];
    }

    return $cards;
}

/**
 * Return the filtered public WooCommerce projection used by Curtain 01 catalogue.
 *
 * @return array{categories:array<int,\WP_Term>,products:array<int,object>,shop_url:string}
 */
function homepage_curtain01_catalogue_model(): array {
    $categories = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_categories' )
        ? \AZnet\Theme\Integrations\WooCommerce\homepage_product_categories( 4 )
        : [];
    $products = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_products' )
        ? \AZnet\Theme\Integrations\WooCommerce\homepage_products( 6 )
        : [];
    $shop_url = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\shop_url' )
        ? \AZnet\Theme\Integrations\WooCommerce\shop_url()
        : '';

    $categories = array_values(
        array_filter(
            $categories,
            static function ( $term ): bool {
                if ( ! $term instanceof \WP_Term ) {
                    return false;
                }
                return ! is_wp_error( get_term_link( $term ) );
            }
        )
    );

    $products = array_values(
        array_filter(
            $products,
            static function ( $product ): bool {
                if ( ! is_object( $product ) || ! method_exists( $product, 'get_name' ) || ! method_exists( $product, 'get_permalink' ) ) {
                    return false;
                }
                return '' !== trim( (string) $product->get_name() ) && '' !== (string) $product->get_permalink();
            }
        )
    );

    return [
        'categories' => $categories,
        'products'   => $products,
        'shop_url'   => $shop_url,
    ];
}

/** @return array<int,array<string,mixed>> */
function homepage_curtain01_effective_surface_map( ?array $settings = null ): array {
    $settings = null === $settings ? settings() : $settings;
    $surfaces = [];

    $hero_id = (int) homepage_effective_source_value( 'curtain-01', 'hero', $settings );
    $hero = homepage_block_reference( $hero_id );
    $hero_html = '';
    if ( $hero instanceof \WP_Post ) {
        $raw = trim( (string) $hero->post_content );
        $hero_html = '' !== $raw ? trim( (string) do_blocks( $raw ) ) : '';
    }

    if ( '' !== $hero_html ) {
        $surfaces[] = homepage_surface_entry(
            'hero',
            __( 'Hero', 'aznet-theme' ),
            'hero',
            'before',
            'aznet-homepage-curtain-hero',
            'wp_block',
            (int) $hero->ID,
            [
                'title'   => homepage_surface_first_heading( (string) $hero->post_content ),
                'summary' => homepage_surface_text_summary( $hero_html ),
                'source'  => get_the_title( $hero ),
            ]
        );
    } else {
        $front_id = (int) get_option( 'page_on_front', 0 );
        $front_page = homepage_page_reference( $front_id );
        $title = trim( (string) get_bloginfo( 'name' ) );
        if ( '' === $title && $front_page instanceof \WP_Post ) {
            $title = trim( (string) get_the_title( $front_page ) );
        }
        $lede = $front_page instanceof \WP_Post ? trim( (string) get_the_excerpt( $front_page ) ) : '';
        if ( '' === $lede ) {
            $lede = trim( (string) get_bloginfo( 'description' ) );
        }
        $has_image = $front_page instanceof \WP_Post && has_post_thumbnail( $front_page );

        if ( '' !== $title || '' !== $lede || $has_image ) {
            $surfaces[] = homepage_surface_entry(
                'hero',
                __( 'Hero', 'aznet-theme' ),
                'hero',
                'before',
                'aznet-homepage-curtain-hero',
                'fallback',
                $front_page instanceof \WP_Post ? (int) $front_page->ID : 0,
                [
                    'title'   => $title,
                    'summary' => $lede,
                    'source'  => __( 'Dữ liệu WordPress dự phòng', 'aznet-theme' ),
                ]
            );
        }
    }

    $proof_id = (int) homepage_effective_source_value( 'curtain-01', 'proof', $settings );
    $proof = homepage_block_reference( $proof_id );
    if ( $proof instanceof \WP_Post ) {
        $raw = trim( (string) $proof->post_content );
        $proof_html = '' !== $raw ? trim( (string) do_blocks( $raw ) ) : '';
        if ( '' !== $proof_html ) {
            $surfaces[] = homepage_surface_entry(
                'proof',
                __( 'Bằng chứng nhanh', 'aznet-theme' ),
                'proof-strip',
                'before',
                'aznet-homepage-curtain-proof',
                'wp_block',
                (int) $proof->ID,
                [
                    'title'   => get_the_title( $proof ),
                    'summary' => homepage_surface_text_summary( $proof_html ),
                    'source'  => get_the_title( $proof ),
                ]
            );
        }
    }

    $front_id = (int) get_option( 'page_on_front', 0 );
    $front_page = homepage_page_reference( $front_id );
    if ( $front_page instanceof \WP_Post ) {
        $front_content = trim( (string) $front_page->post_content );
        $front_summary = homepage_surface_text_summary( (string) get_the_excerpt( $front_page ) );
        if ( '' === $front_summary && '' !== $front_content ) {
            $front_summary = homepage_surface_text_summary( (string) do_blocks( $front_content ) );
        }
        $surfaces[] = homepage_surface_entry(
            'front-page-content',
            __( 'Nội dung trang chủ', 'aznet-theme' ),
            '',
            'native',
            'post-' . (string) $front_page->ID,
            'front_page',
            (int) $front_page->ID,
            [
                'title'   => get_the_title( $front_page ),
                'summary' => $front_summary,
                'source'  => __( 'Page được chọn làm Trang chủ trong WordPress', 'aznet-theme' ),
            ]
        );
    }

    $about_id = (int) homepage_effective_source_value( 'curtain-01', 'about', $settings );
    $about = homepage_page_reference( $about_id );
    if ( $about instanceof \WP_Post ) {
        $heading = trim( (string) setting( 'homepage_about_heading', '' ) );
        $surfaces[] = homepage_surface_entry(
            'about',
            __( 'Giới thiệu', 'aznet-theme' ),
            'about',
            'after',
            'aznet-homepage-curtain-about',
            'page',
            (int) $about->ID,
            [
                'title'   => '' !== $heading ? $heading : get_the_title( $about ),
                'summary' => homepage_surface_text_summary( (string) get_the_excerpt( $about ) ),
                'source'  => get_the_title( $about ),
            ]
        );
    }

    $showcase_cards = homepage_curtain01_category_showcase_cards();
    if ( [] !== $showcase_cards ) {
        $showcase_terms = array_values(
            array_filter(
                array_map(
                    static fn ( array $card ) => $card['term'] ?? null,
                    $showcase_cards
                ),
                static fn ( $term ): bool => $term instanceof \WP_Term
            )
        );
        $surfaces[] = homepage_surface_entry(
            'category-showcase',
            __( 'Bộ sưu tập dòng rèm', 'aznet-theme' ),
            'category-showcase',
            'after',
            'aznet-homepage-curtain-category-showcase',
            'woocommerce',
            0,
            [
                'title'  => __( 'Khám phá theo dòng rèm', 'aznet-theme' ),
                'items'  => $showcase_terms,
                'source' => __( 'Danh mục sản phẩm công khai từ WooCommerce', 'aznet-theme' ),
            ]
        );
    }

    $catalogue = homepage_curtain01_catalogue_model();
    if ( [] !== $catalogue['categories'] || [] !== $catalogue['products'] ) {
        $surfaces[] = homepage_surface_entry(
            'catalogue',
            __( 'Sản phẩm & giải pháp rèm', 'aznet-theme' ),
            'catalogue',
            'after',
            'aznet-homepage-curtain-catalogue',
            'woocommerce',
            0,
            [
                'title'  => __( 'Sản phẩm & giải pháp rèm', 'aznet-theme' ),
                'items'  => $catalogue['categories'],
                'source' => __( 'Catalog công khai từ WooCommerce', 'aznet-theme' ),
            ]
        );
    }

    $process_id = (int) homepage_effective_source_value( 'curtain-01', 'process', $settings );
    $process = homepage_page_reference( $process_id );
    if ( $process instanceof \WP_Post ) {
        $raw = trim( (string) $process->post_content );
        $content = '' !== $raw ? trim( (string) apply_filters( 'the_content', $raw ) ) : '';
        if ( '' !== $content ) {
            $surfaces[] = homepage_surface_entry(
                'process',
                __( 'Quy trình', 'aznet-theme' ),
                'process',
                'after',
                'aznet-homepage-curtain-process',
                'page',
                (int) $process->ID,
                [
                    'title'   => get_the_title( $process ),
                    'summary' => homepage_surface_text_summary( $content ),
                    'source'  => get_the_title( $process ),
                ]
            );
        }
    }

    $exclude_ids = [];
    $project_term_id = (int) homepage_effective_source_value( 'curtain-01', 'projects', $settings );
    $project_term = homepage_category_reference( $project_term_id );
    $project_posts = $project_term instanceof \WP_Term
        ? homepage_latest_posts( [ $project_term_id ], 3, $exclude_ids )
        : [];
    if ( $project_term instanceof \WP_Term && [] !== $project_posts ) {
        $exclude_ids = array_values(
            array_unique(
                array_merge(
                    $exclude_ids,
                    array_map( static fn ( \WP_Post $post ): int => (int) $post->ID, $project_posts )
                )
            )
        );
        $surfaces[] = homepage_surface_entry(
            'projects',
            __( 'Công trình', 'aznet-theme' ),
            'projects',
            'after',
            'aznet-homepage-curtain-projects',
            'category',
            (int) $project_term->term_id,
            [
                'title'  => __( 'Câu chuyện từ công trình', 'aznet-theme' ),
                'items'  => $project_posts,
                'source' => $project_term->name,
            ]
        );
    }

    $knowledge_ids = (array) homepage_effective_source_value( 'curtain-01', 'knowledge', $settings );
    $knowledge_terms = homepage_category_references( $knowledge_ids );
    $knowledge_posts = homepage_latest_posts( $knowledge_ids, 3, $exclude_ids );
    if ( [] !== $knowledge_posts ) {
        $surfaces[] = homepage_surface_entry(
            'knowledge',
            __( 'Kiến thức', 'aznet-theme' ),
            'knowledge',
            'after',
            'aznet-homepage-curtain-knowledge',
            'categories',
            0,
            [
                'title'  => __( 'Gợi ý để chọn rèm phù hợp', 'aznet-theme' ),
                'items'  => $knowledge_posts,
                'source' => [] !== $knowledge_terms
                    ? implode( ', ', array_map( static fn ( \WP_Term $term ): string => $term->name, $knowledge_terms ) )
                    : __( 'Bài viết công khai', 'aznet-theme' ),
            ]
        );
    }

    $contact_id = (int) homepage_effective_source_value( 'curtain-01', 'contact', $settings );
    $contact = homepage_page_reference( $contact_id );
    $phone = '';
    if ( function_exists( __NAMESPACE__ . '\\contact_surface_model' ) ) {
        $contact_model = contact_surface_model();
        if ( is_array( $contact_model ) ) {
            foreach ( (array) ( $contact_model['contact']['points'] ?? [] ) as $point ) {
                if ( is_array( $point ) && 'phone' === ( $point['kind'] ?? '' ) ) {
                    $candidate = trim( (string) ( $point['value'] ?? '' ) );
                    if ( '' !== $candidate ) {
                        $phone = $candidate;
                        break;
                    }
                }
            }
        }
    }
    if ( $contact instanceof \WP_Post || '' !== $phone ) {
        $surfaces[] = homepage_surface_entry(
            'final-cta',
            __( 'Liên hệ', 'aznet-theme' ),
            'final-cta',
            'after',
            'aznet-homepage-curtain-contact',
            $contact instanceof \WP_Post ? 'page' : 'provider',
            $contact instanceof \WP_Post ? (int) $contact->ID : 0,
            [
                'title'   => __( 'Trao đổi để tìm giải pháp rèm phù hợp', 'aznet-theme' ),
                'summary' => $contact instanceof \WP_Post ? homepage_surface_text_summary( (string) get_the_excerpt( $contact ) ) : $phone,
                'phone'   => $phone,
                'source'  => $contact instanceof \WP_Post ? get_the_title( $contact ) : __( 'RootProfile public contact provider', 'aznet-theme' ),
            ]
        );
    }

    return $surfaces;
}

/** @return array<int,array<string,mixed>> */
function homepage_industrial01_effective_surface_map( ?array $settings = null ): array {
    $settings = null === $settings ? settings() : $settings;
    $surfaces = [];

    $site_name = trim( (string) get_bloginfo( 'name' ) );
    $tagline = trim( (string) get_bloginfo( 'description' ) );
    $hero_title = trim( (string) ( $settings['homepage_industrial01_hero_title'] ?? '' ) );
    $hero_lede = trim( (string) ( $settings['homepage_industrial01_hero_lede'] ?? '' ) );
    $hero_title = '' !== $hero_title ? $hero_title : $site_name;
    $hero_lede = '' !== $hero_lede ? $hero_lede : $tagline;

    $surfaces[] = homepage_surface_entry(
        'hero',
        __( 'Hero', 'aznet-theme' ),
        'hero',
        'before',
        'aznet-homepage-industrial-hero',
        'presentation',
        0,
        [
            'title'   => $hero_title,
            'summary' => $hero_lede,
            'source'  => __( 'Thiết lập trình bày Industrial 01', 'aznet-theme' ),
        ]
    );

    $categories = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_product_category_showcase_terms' )
        ? \AZnet\Theme\Integrations\WooCommerce\homepage_product_category_showcase_terms( 8 )
        : [];
    if ( [] !== $categories ) {
        $surfaces[] = homepage_surface_entry(
            'categories',
            __( 'Danh mục', 'aznet-theme' ),
            'categories',
            'before',
            'aznet-homepage-industrial-categories',
            'woocommerce',
            0,
            [
                'items'  => $categories,
                'source' => __( 'Danh mục sản phẩm công khai từ WooCommerce', 'aznet-theme' ),
            ]
        );
    }

    $products = function_exists( 'AZnet\\Theme\\Integrations\\WooCommerce\\homepage_products' )
        ? \AZnet\Theme\Integrations\WooCommerce\homepage_products( 8 )
        : [];
    if ( is_array( $products ) && [] !== $products ) {
        $surfaces[] = homepage_surface_entry(
            'products',
            __( 'Sản phẩm', 'aznet-theme' ),
            'products',
            'before',
            'aznet-homepage-industrial-products',
            'woocommerce',
            0,
            [
                'items'  => $products,
                'source' => __( 'Sản phẩm công khai từ WooCommerce', 'aznet-theme' ),
            ]
        );
    }

    $front_id = (int) get_option( 'page_on_front', 0 );
    $front_page = homepage_page_reference( $front_id );
    if ( $front_page instanceof \WP_Post ) {
        $front_content = trim( (string) $front_page->post_content );
        $front_summary = homepage_surface_text_summary( (string) get_the_excerpt( $front_page ) );
        if ( '' === $front_summary && '' !== $front_content ) {
            $front_summary = homepage_surface_text_summary( (string) do_blocks( $front_content ) );
        }
        $surfaces[] = homepage_surface_entry(
            'front-page-content',
            __( 'Nội dung trang chủ', 'aznet-theme' ),
            '',
            'native',
            'post-' . (string) $front_page->ID,
            'front_page',
            (int) $front_page->ID,
            [
                'title'   => get_the_title( $front_page ),
                'summary' => $front_summary,
                'source'  => __( 'Page được chọn làm Trang chủ trong WordPress', 'aznet-theme' ),
            ]
        );
    }

    $surfaces[] = homepage_surface_entry(
        'cta',
        __( 'Liên hệ', 'aznet-theme' ),
        'cta',
        'after',
        'aznet-industrial01-quote',
        'presentation',
        0,
        [
            'title'  => preset_term( 'primary_cta', __( 'Yêu cầu báo giá', 'aznet-theme' ), 'industrial-01' ),
            'source' => __( 'Trình bày Theme; dữ liệu liên hệ vẫn thuộc nguồn sở hữu', 'aznet-theme' ),
        ]
    );

    return $surfaces;
}

/**
 * Resolve the effective Homepage surfaces that can actually render now.
 *
 * The returned model is request-local and never persisted. Law 01 and
 * Curtain 01 both consume this shared model for frontend composition and the
 * primary Homepage Map.
 *
 * @return array<int,array<string,mixed>>
 */
function homepage_effective_surface_map( ?string $preset = null ): array {
    static $cache = [];

    $preset = null === $preset ? homepage_preset() : $preset;
    if ( array_key_exists( $preset, $cache ) ) {
        return $cache[ $preset ];
    }
    if ( 'curtain-01' === $preset ) {
        $cache[ $preset ] = homepage_curtain01_effective_surface_map();
        return $cache[ $preset ];
    }
    if ( 'industrial-01' === $preset ) {
        $cache[ $preset ] = homepage_industrial01_effective_surface_map();
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
        ? homepage_renderable_child_pages( (int) $services_page->ID, 6 )
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
                'items' => $team instanceof \WP_Post ? team_directory_public_members( 4 ) : [],
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

    $front_id = (int) get_option( 'page_on_front', 0 );
    $front_page = homepage_page_reference( $front_id );
    if ( $front_page instanceof \WP_Post ) {
        $front_content = trim( (string) $front_page->post_content );
        $front_summary = homepage_surface_text_summary( (string) get_the_excerpt( $front_page ) );
        if ( '' === $front_summary && '' !== $front_content ) {
            $front_summary = homepage_surface_text_summary( (string) do_blocks( $front_content ) );
        }

        $surfaces[] = homepage_surface_entry(
            'front-page-content',
            __( 'Nội dung trang chủ', 'aznet-theme' ),
            '',
            'native',
            'post-' . (string) $front_page->ID,
            'front_page',
            (int) $front_page->ID,
            [
                'title'   => get_the_title( $front_page ),
                'summary' => $front_summary,
                'source'  => __( 'Page được chọn làm Trang chủ trong WordPress', 'aznet-theme' ),
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
            $posts = homepage_latest_posts( (array) homepage_effective_source_value( 'law-01', 'knowledge', $settings ), 3, $exclude_ids );
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
            $slot = 'analysis' === $surface_key ? 'case_analysis' : 'legal_news';
            $term_id = (int) homepage_effective_source_value( 'law-01', $slot, $settings );
            $term = homepage_category_reference( $term_id );
            $limit = 'analysis' === $surface_key ? 3 : 5;
            $posts = $term instanceof \WP_Term ? homepage_latest_posts( [ $term_id ], $limit, 'news' === $surface_key ? [] : $exclude_ids ) : [];
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
