<?php
/**
 * X5 native form controls runtime fixture.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

update_option( 'comment_registration', 0 );
update_option( 'require_name_email', 1 );
update_option( 'show_comments_cookies_opt_in', 1 );

$category = wp_insert_term( 'X5 Native Controls Category', 'category' );
if ( is_wp_error( $category ) ) {
    WP_CLI::error( 'Unable to create X5 category fixture: ' . $category->get_error_message() );
}
$category_id = (int) $category['term_id'];

$post_id = wp_insert_post(
    [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post_title'     => 'X5 Native Comment Form',
        'post_content'   => '<p id="x5-comment-content">Native Post for the WordPress comment form.</p>',
        'post_category'  => [ $category_id ],
        'comment_status' => 'open',
    ],
    true
);
if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
    WP_CLI::error( 'Unable to create X5 comment Post.' );
}

$native_blocks = <<<'HTML'
<!-- wp:paragraph -->
<p id="x5-block-controls">Native Core form-like block controls.</p>
<!-- /wp:paragraph -->
<!-- wp:search {"label":"Search the site","showLabel":true,"buttonText":"Search"} /-->
<!-- wp:categories {"displayAsDropdown":true} /-->
HTML;

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X5 Native Block Controls',
        'post_content' => $native_blocks,
    ],
    true
);
if ( is_wp_error( $page_id ) || 0 >= (int) $page_id ) {
    WP_CLI::error( 'Unable to create X5 native-block Page.' );
}

$protected_page_id = wp_insert_post(
    [
        'post_type'     => 'page',
        'post_status'   => 'publish',
        'post_title'    => 'X5 Protected Form',
        'post_content'  => '<p>Protected X5 content.</p>',
        'post_password' => 'x5-native-form',
    ],
    true
);
if ( is_wp_error( $protected_page_id ) || 0 >= (int) $protected_page_id ) {
    WP_CLI::error( 'Unable to create X5 protected Page.' );
}

$post_url      = get_permalink( (int) $post_id );
$page_url      = get_permalink( (int) $page_id );
$protected_url = get_permalink( (int) $protected_page_id );
$archive_url   = get_category_link( $category_id );
$search_url    = get_search_link( 'X5 Native' );

if ( ! is_string( $post_url ) || ! is_string( $page_url ) || ! is_string( $protected_url )
    || ! is_string( $search_url ) || is_wp_error( $archive_url ) ) {
    WP_CLI::error( 'Unable to resolve X5 fixture URLs.' );
}

$result = [
    'comment_post_url'   => $post_url,
    'native_blocks_url'  => $page_url,
    'protected_page_url' => $protected_url,
    'search_url'         => $search_url,
    'not_found_url'      => home_url( '/?p=999999999' ),
    'archive_url'        => $archive_url,
    'home_url'           => home_url( '/' ),
    'post_id'            => (int) $post_id,
    'page_id'            => (int) $page_id,
    'protected_page_id'  => (int) $protected_page_id,
    'category_id'        => $category_id,
];

echo wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
