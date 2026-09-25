<?php
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function team_directory_parent(): ?\WP_Post {
    $id = (int) homepage_effective_source_value( 'law-01', 'team' );
    return $id > 0 ? homepage_page_reference( $id ) : null;
}

/** @return array<int,\WP_Post> */
function team_directory_members( int $limit = 0 ): array {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return []; }

    $posts = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'post_parent'    => (int) $parent->ID,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
            'posts_per_page' => $limit > 0 ? min( 24, $limit ) : -1,
            'no_found_rows'  => true,
        ]
    );

    return is_array( $posts )
        ? array_values( array_filter( $posts, static fn( $post ): bool => $post instanceof \WP_Post ) )
        : [];
}

function team_directory_member_is_child( int $post_id ): bool {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post || $post_id <= 0 ) { return false; }

    $post = get_post( $post_id );

    return $post instanceof \WP_Post
        && 'page' === $post->post_type
        && (int) $post->post_parent === (int) $parent->ID;
}

function team_directory_next_menu_order(): int {
    $parent = team_directory_parent();
    if ( ! $parent instanceof \WP_Post ) { return 0; }

    $children = get_posts(
        [
            'post_type'      => 'page',
            'post_status'    => 'any',
            'post_parent'    => (int) $parent->ID,
            'orderby'        => 'menu_order',
            'order'          => 'DESC',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        ]
    );

    $last = is_array( $children ) && isset( $children[0] ) && $children[0] instanceof \WP_Post
        ? (int) $children[0]->menu_order
        : -1;

    return $last + 1;
}
