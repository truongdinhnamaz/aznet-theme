<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

update_option( 'posts_per_page', 3 );
update_option( 'page_comments', 1 );
update_option( 'comments_per_page', 2 );
update_option( 'default_comments_page', 'oldest' );

$term = wp_insert_term( 'X4 Navigation Archive', 'category', [ 'description' => 'Native X4 pagination fixture.' ] );
if ( is_wp_error( $term ) ) {
    WP_CLI::error( 'Unable to create X4 category fixture: ' . $term->get_error_message() );
}
$category_id = (int) $term['term_id'];

$post_ids = [];
for ( $index = 1; $index <= 8; $index++ ) {
    $content = '<p id="x4-listing-marker-' . $index . '">X4 navigation fixture result ' . $index . '</p>';
    if ( 4 === $index ) {
        $content = '<p id="x4-post-page-one">X4 post page one marker.</p><!--nextpage--><p id="x4-post-page-two">X4 post page two marker.</p>';
    }

    $post_id = wp_insert_post(
        [
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_title'    => 'X4 Navigation Fixture Post ' . $index . ' — A deliberately long adjacent navigation title for responsive wrapping',
            'post_content'  => $content,
            'post_category' => [ $category_id ],
            'post_date'     => sprintf( '2026-09-%02d 09:00:00', $index ),
            'post_date_gmt' => sprintf( '2026-09-%02d 02:00:00', $index ),
        ],
        true
    );
    if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
        WP_CLI::error( 'Unable to create X4 Post fixture ' . $index . '.' );
    }
    $post_ids[] = (int) $post_id;
}

$target_post_id = $post_ids[3];
for ( $index = 1; $index <= 5; $index++ ) {
    $comment_id = wp_insert_comment(
        [
            'comment_post_ID'      => $target_post_id,
            'comment_author'       => 'X4 Commenter ' . $index,
            'comment_author_email' => 'x4-' . $index . '@example.test',
            'comment_content'      => 'X4 paginated comment marker ' . $index,
            'comment_approved'     => 1,
            'comment_date'         => sprintf( '2026-09-10 10:%02d:00', $index ),
            'comment_date_gmt'     => sprintf( '2026-09-10 03:%02d:00', $index ),
        ]
    );
    if ( ! $comment_id ) {
        WP_CLI::error( 'Unable to create X4 comment fixture ' . $index . '.' );
    }
}

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X4 Asset Absence Page',
        'post_content' => '<p id="x4-page-control">X4 Page control route.</p>',
    ],
    true
);
if ( is_wp_error( $page_id ) || 0 >= (int) $page_id ) {
    WP_CLI::error( 'Unable to create X4 Page control fixture.' );
}

$category_url = get_category_link( $category_id );
$post_url     = get_permalink( $target_post_id );
$page_url     = get_permalink( (int) $page_id );
if ( is_wp_error( $category_url ) || ! is_string( $post_url ) || ! is_string( $page_url ) ) {
    WP_CLI::error( 'Unable to resolve X4 fixture URLs.' );
}

$result = [
    'archive_url' => $category_url,
    'search_url'  => add_query_arg( 's', 'X4 Navigation Fixture Post', home_url( '/' ) ),
    'post_url'    => $post_url,
    'page_url'    => $page_url,
    'home_url'    => home_url( '/' ),
    'missing_url' => home_url( '/x4-navigation-definitely-missing/' ),
    'post_id'     => $target_post_id,
    'category_id' => $category_id,
];

echo wp_json_encode( $result );
