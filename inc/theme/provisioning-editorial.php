<?php
/** Starter editorial definitions and coverage recommendations for Law provisioning. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

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
        'post_type' => 'post',
        'post_status' => 'publish',
        'cat' => $term_id,
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
    ] );
    return is_array( $posts ) && [] !== $posts ? 1 : 0;
}

/**
 * Recommend starter Posts only where a selected Law editorial source has no public Post.
 *
 * @return array<string,array<string,mixed>>
 */
function provisioning_editorial_coverage_recommendations( array $recommendations, array $discovery ): array {
    unset( $discovery ); // Reserved for future bounded observable state; no semantic inference is performed here.
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
            $out[ $role ] = [
                'state' => 'SKIP_HAS_PUBLIC_CONTENT',
                'action' => 'skip',
                'category_role' => $category_role,
                'category_id' => $term_id,
                'reason' => 'public_post_exists',
            ];
            continue;
        }

        if ( 'create' === $action || ( 'reuse' === $action && $term_id > 0 ) ) {
            $out[ $role ] = [
                'state' => 'SUGGESTED_CREATE',
                'action' => 'create',
                'category_role' => $category_role,
                'category_id' => $term_id,
                'reason' => 'selected_source_has_no_public_post',
            ];
            continue;
        }

        $out[ $role ] = [
            'state' => 'OPTIONAL_SKIP',
            'action' => 'skip',
            'category_role' => $category_role,
            'category_id' => 0,
            'reason' => 'source_not_selected',
        ];
    }

    return $out;
}
