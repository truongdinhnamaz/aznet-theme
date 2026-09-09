<?php
/** Starter editorial definitions and coverage recommendations for Law provisioning. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PROVISIONING_META_STARTER_FINGERPRINT = '_aznet_theme_starter_fingerprint';

/** @return array<string,mixed>|null */
function provisioning_editorial_definition( string $role ): ?array {
    $blueprint = provisioning_blueprint( 'law01-v1-1' );
    if ( ! is_array( $blueprint ) ) { return null; }
    $item = $blueprint['editorial_examples']['items'][ $role ] ?? null;
    return is_array( $item ) ? $item : null;
}

/** Return 1 when at least one public native Post exists in the Category, otherwise 0. */
function provisioning_category_public_post_count( int $term_id ): int {
    if ( $term_id <= 0 || ! function_exists( 'get_posts' ) ) { return 0; }
    $posts = get_posts( [
        'post_type' => 'post', 'post_status' => 'publish', 'cat' => $term_id,
        'posts_per_page' => 1, 'fields' => 'ids', 'no_found_rows' => true, 'suppress_filters' => true,
    ] );
    return is_array( $posts ) && [] !== $posts ? 1 : 0;
}

/** @return array<string,array<string,mixed>> */
function provisioning_editorial_coverage_recommendations( array $recommendations, array $discovery ): array {
    unset( $discovery );
    $blueprint = provisioning_blueprint( 'law01-v1-1' );
    if ( ! is_array( $blueprint ) ) { return []; }
    $category_recommendations = (array) ( $recommendations['categories'] ?? [] );
    $out = [];
    foreach ( (array) ( $blueprint['editorial_examples']['items'] ?? [] ) as $role => $definition ) {
        $category_role = (string) ( $definition['category_role'] ?? $role );
        $source = (array) ( $category_recommendations[ $category_role ] ?? [] );
        $action = (string) ( $source['action'] ?? 'skip' );
        $term_id = (int) ( $source['object_id'] ?? 0 );
        if ( 'reuse' === $action && $term_id > 0 && provisioning_category_public_post_count( $term_id ) > 0 ) {
            $out[ $role ] = [ 'state' => 'SKIP_HAS_PUBLIC_CONTENT', 'action' => 'skip', 'category_role' => $category_role, 'category_id' => $term_id, 'reason' => 'public_post_exists' ];
        } elseif ( 'create' === $action || ( 'reuse' === $action && $term_id > 0 ) ) {
            $out[ $role ] = [ 'state' => 'SUGGESTED_CREATE', 'action' => 'create', 'category_role' => $category_role, 'category_id' => $term_id, 'reason' => 'selected_source_has_no_public_post' ];
        } else {
            $out[ $role ] = [ 'state' => 'OPTIONAL_SKIP', 'action' => 'skip', 'category_role' => $category_role, 'category_id' => 0, 'reason' => 'source_not_selected' ];
        }
    }
    return $out;
}

function provisioning_starter_post_fingerprint( int $post_id ): string {
    $post = get_post( $post_id );
    if ( ! $post instanceof \WP_Post || 'post' !== $post->post_type ) { return ''; }
    $categories = function_exists( 'wp_get_post_categories' ) ? array_map( 'intval', (array) wp_get_post_categories( $post_id ) ) : [];
    sort( $categories, SORT_NUMERIC );
    $payload = [
        'title' => (string) $post->post_title,
        'excerpt' => (string) $post->post_excerpt,
        'content' => (string) $post->post_content,
        'categories' => $categories,
        'featured_media' => function_exists( 'get_post_thumbnail_id' ) ? (int) get_post_thumbnail_id( $post_id ) : 0,
    ];
    return 'sha256:' . hash( 'sha256', json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '' );
}

function provisioning_store_starter_fingerprint( int $post_id ): bool {
    $fingerprint = provisioning_starter_post_fingerprint( $post_id );
    if ( '' === $fingerprint ) { return false; }
    update_post_meta( $post_id, PROVISIONING_META_STARTER_FINGERPRINT, $fingerprint );
    return (string) get_post_meta( $post_id, PROVISIONING_META_STARTER_FINGERPRINT, true ) === $fingerprint;
}

/** @return array<int,int> */
function provisioning_untouched_starter_post_ids(): array {
    if ( ! function_exists( 'get_posts' ) || ! function_exists( 'get_post_meta' ) ) { return []; }
    $posts = get_posts( [
        'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1, 'no_found_rows' => true,
        'suppress_filters' => true,
        'meta_query' => [ [ 'key' => PROVISIONING_META_BLUEPRINT, 'value' => 'law01-v1-1' ] ],
    ] );
    $out = [];
    foreach ( is_array( $posts ) ? $posts : [] as $post ) {
        if ( ! $post instanceof \WP_Post ) { continue; }
        $role = (string) get_post_meta( $post->ID, PROVISIONING_META_ROLE, true );
        if ( ! str_starts_with( $role, 'starter_post:' ) ) { continue; }
        $stored = (string) get_post_meta( $post->ID, PROVISIONING_META_STARTER_FINGERPRINT, true );
        if ( '' !== $stored && hash_equals( $stored, provisioning_starter_post_fingerprint( (int) $post->ID ) ) ) { $out[] = (int) $post->ID; }
    }
    return $out;
}
