<?php
/** Bounded provenance for objects created by Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

const PROVISIONING_META_BLUEPRINT = '_aznet_theme_provisioned_by';
const PROVISIONING_META_ROLE = '_aznet_theme_provisioning_role';
const PROVISIONING_META_RUN = '_aznet_theme_provisioning_run';

function provisioning_mark_post( int $post_id, string $blueprint, string $role, string $run_id ): bool {
    if ( $post_id <= 0 || ! in_array( $blueprint, provisioning_blueprint_keys(), true ) || '' === $role || '' === $run_id ) { return false; }
    update_post_meta( $post_id, PROVISIONING_META_BLUEPRINT, $blueprint );
    update_post_meta( $post_id, PROVISIONING_META_ROLE, $role );
    update_post_meta( $post_id, PROVISIONING_META_RUN, $run_id );
    return (string) get_post_meta( $post_id, PROVISIONING_META_BLUEPRINT, true ) === $blueprint
        && (string) get_post_meta( $post_id, PROVISIONING_META_ROLE, true ) === $role
        && (string) get_post_meta( $post_id, PROVISIONING_META_RUN, true ) === $run_id;
}

function provisioning_mark_term( int $term_id, string $blueprint, string $role, string $run_id ): bool {
    if ( $term_id <= 0 || ! in_array( $blueprint, provisioning_blueprint_keys(), true ) || '' === $role || '' === $run_id ) { return false; }
    update_term_meta( $term_id, PROVISIONING_META_BLUEPRINT, $blueprint );
    update_term_meta( $term_id, PROVISIONING_META_ROLE, $role );
    update_term_meta( $term_id, PROVISIONING_META_RUN, $run_id );
    return (string) get_term_meta( $term_id, PROVISIONING_META_BLUEPRINT, true ) === $blueprint
        && (string) get_term_meta( $term_id, PROVISIONING_META_ROLE, true ) === $role
        && (string) get_term_meta( $term_id, PROVISIONING_META_RUN, true ) === $run_id;
}

function provisioning_find_owned_role( string $blueprint, string $object_type, string $role ): int {
    if ( ! in_array( $blueprint, provisioning_blueprint_keys(), true ) || '' === $role ) { return 0; }
    $meta_query = [
        [ 'key' => PROVISIONING_META_BLUEPRINT, 'value' => $blueprint ],
        [ 'key' => PROVISIONING_META_ROLE, 'value' => $role ],
    ];
    if ( in_array( $object_type, [ 'page', 'post', 'attachment' ], true ) ) {
        $posts = get_posts( [
            'post_type' => $object_type,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'no_found_rows' => true,
            'suppress_filters' => true,
            'meta_query' => $meta_query,
        ] );
        if ( is_array( $posts ) && isset( $posts[0] ) ) {
            $candidate = $posts[0];
            if ( $candidate instanceof \WP_Post ) { return (int) $candidate->ID; }
            if ( is_numeric( $candidate ) ) { return (int) $candidate; }
        }
        return 0;
    }
    if ( in_array( $object_type, [ 'category', 'menu' ], true ) ) {
        $terms = get_terms( [ 'taxonomy' => 'category' === $object_type ? 'category' : 'nav_menu', 'hide_empty' => false, 'number' => 1, 'meta_query' => $meta_query ] );
        if ( ! is_wp_error( $terms ) && is_array( $terms ) && isset( $terms[0] ) && $terms[0] instanceof \WP_Term ) { return (int) $terms[0]->term_id; }
    }
    return 0;
}
