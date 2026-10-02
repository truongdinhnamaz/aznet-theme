<?php
/**
 * Typed WordPress source resolution for Homepage Composer.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Resolve one public native Page by exact ID.
 */
function homepage_page_reference( int $id ): ?\WP_Post {
    if ( $id <= 0 ) {
        return null;
    }

    $post = get_post( $id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
        return null;
    }

    return $post;
}

/**
 * Resolve one public WordPress synced-pattern block by exact ID.
 */
function homepage_block_reference( int $id ): ?\WP_Post {
    if ( $id <= 0 ) {
        return null;
    }

    $post = get_post( $id );
    if ( ! $post instanceof \WP_Post || 'wp_block' !== $post->post_type || 'publish' !== $post->post_status ) {
        return null;
    }

    return $post;
}

/**
 * Resolve one native Category by exact term ID.
 */
function homepage_category_reference( int $id ): ?\WP_Term {
    if ( $id <= 0 ) {
        return null;
    }

    $term = get_term( $id );
    if ( ! $term instanceof \WP_Term || 'category' !== $term->taxonomy ) {
        return null;
    }

    return $term;
}

/**
 * Resolve an ordered list of exact Category IDs, dropping invalid entries.
 *
 * @param array<int, mixed> $ids Category IDs.
 * @return array<int, \WP_Term>
 */
function homepage_category_references( array $ids ): array {
    $terms = [];
    foreach ( $ids as $candidate ) {
        $id = is_numeric( $candidate ) ? (int) $candidate : 0;
        $term = homepage_category_reference( $id );
        if ( null !== $term ) {
            $terms[] = $term;
        }
    }

    return $terms;
}

/**
 * Resolve direct published child Pages for one mapped parent.
 *
 * @return array<int, \WP_Post>
 */
function homepage_direct_published_children( int $parent_id, int $limit ): array {
    if ( null === homepage_page_reference( $parent_id ) ) {
        return [];
    }

    $limit = max( 1, min( 24, $limit ) );
    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => $parent_id,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => $limit,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts ) ? $posts : [];
}

/**
 * Resolve public child Pages that can render a meaningful card.
 *
 * WordPress remains the source owner. Missing titles are not inferred from
 * slugs, URLs, IDs, authors, custom fields, or provider-private state.
 *
 * @return array<int, \WP_Post>
 */
function homepage_renderable_child_pages( int $parent_id, int $limit ): array {
    $limit = max( 1, min( 24, $limit ) );
    $renderable = [];

    foreach ( homepage_direct_published_children( $parent_id, 24 ) as $post ) {
        if ( ! $post instanceof \WP_Post || '' === trim( (string) get_the_title( $post ) ) ) {
            continue;
        }

        $url = get_permalink( $post );
        if ( ! is_string( $url ) || '' === trim( $url ) ) {
            continue;
        }

        $renderable[] = $post;
        if ( count( $renderable ) >= $limit ) {
            break;
        }
    }

    return $renderable;
}

/**
 * Resolve newest native Posts from mapped Categories.
 *
 * @param array<int, mixed> $category_ids Mapped Category IDs.
 * @param int               $limit        Maximum number of Posts.
 * @param array<int, mixed> $exclude_ids  Request-local display exclusions.
 * @return array<int, \WP_Post>
 */
function homepage_latest_posts( array $category_ids, int $limit, array $exclude_ids = [] ): array {
    $terms = homepage_category_references( $category_ids );
    if ( [] === $terms ) {
        return [];
    }

    $valid_category_ids = array_map(
        static fn ( \WP_Term $term ): int => (int) $term->term_id,
        $terms
    );

    $valid_exclude_ids = [];
    foreach ( $exclude_ids as $candidate ) {
        $id = is_numeric( $candidate ) ? (int) $candidate : 0;
        if ( $id > 0 && ! in_array( $id, $valid_exclude_ids, true ) ) {
            $valid_exclude_ids[] = $id;
        }
    }

    $limit = max( 1, min( 20, $limit ) );
    $posts = get_posts(
        [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'category__in'        => $valid_category_ids,
            'post__not_in'        => $valid_exclude_ids,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]
    );

    return is_array( $posts ) ? $posts : [];
}

/**
 * Resolve newest published Posts that contain meaningful editorial content.
 *
 * Empty placeholder Posts are skipped while preserving the native date-desc
 * order from WordPress. This is a presentation projection only; WordPress
 * remains the owner of Post and Category data.
 *
 * @param array<int, mixed> $category_ids Mapped Category IDs.
 * @param int               $limit        Maximum number of meaningful Posts.
 * @param array<int, mixed> $exclude_ids  Optional request-local exclusions.
 * @return array<int, \WP_Post>
 */
function homepage_latest_content_posts( array $category_ids, int $limit, array $exclude_ids = [] ): array {
    $limit = max( 1, min( 20, $limit ) );
    $posts = homepage_latest_posts( $category_ids, 20, $exclude_ids );
    $meaningful = [];

    foreach ( $posts as $post ) {
        if ( ! $post instanceof \WP_Post ) {
            continue;
        }

        if ( '' === trim( (string) $post->post_content ) && '' === trim( (string) $post->post_excerpt ) ) {
            continue;
        }

        $meaningful[] = $post;
        if ( count( $meaningful ) >= $limit ) {
            break;
        }
    }

    return $meaningful;
}
