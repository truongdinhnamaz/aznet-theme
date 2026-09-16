<?php
/**
 * Seed deterministic WordPress-native fixtures for X6 cross-surface QA.
 * Executed with wp eval-file.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$image_one_id = (int) getenv( 'X6_MEDIA_ONE_ID' );
$image_two_id = (int) getenv( 'X6_MEDIA_TWO_ID' );
if ( $image_one_id <= 0 || $image_two_id <= 0 ) {
    WP_CLI::error( 'X6 media attachment IDs are required.' );
}

$image_one_url = wp_get_attachment_image_url( $image_one_id, 'full' );
$image_two_url = wp_get_attachment_image_url( $image_two_id, 'full' );
if ( ! is_string( $image_one_url ) || '' === $image_one_url || ! is_string( $image_two_url ) || '' === $image_two_url ) {
    WP_CLI::error( 'X6 media attachment URLs could not be resolved.' );
}

update_option( 'posts_per_page', 3 );
update_option( 'page_comments', 1 );
update_option( 'comments_per_page', 2 );
update_option( 'default_comments_page', 'oldest' );

$term = wp_insert_term( 'X6 Cross Surface Archive', 'category', [ 'slug' => 'x6-cross-surface-archive' ] );
if ( is_wp_error( $term ) ) {
    WP_CLI::error( $term->get_error_message() );
}
$category_id = (int) $term['term_id'];

$archive_post_ids = [];
for ( $index = 1; $index <= 7; $index++ ) {
    $post_id = wp_insert_post(
        [
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_title'    => 'X6 Archive Fixture ' . $index,
            'post_name'     => 'x6-archive-fixture-' . $index,
            'post_content'  => '<p>X6 archive pagination content ' . $index . '.</p>',
            'post_category' => [ $category_id ],
            'post_date'     => sprintf( '2026-09-%02d 08:00:00', $index ),
            'post_date_gmt' => sprintf( '2026-09-%02d 01:00:00', $index ),
        ],
        true
    );
    if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
        WP_CLI::error( 'Unable to create X6 archive fixture.' );
    }
    $archive_post_ids[] = (int) $post_id;
}

$comment_post_id = wp_insert_post(
    [
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'post_title'     => 'X6 Comments Surface',
        'post_name'      => 'x6-comments-surface',
        'post_content'   => '<p id="x6-comments-content">X6 comments integration surface.</p>',
        'comment_status' => 'open',
    ],
    true
);
if ( is_wp_error( $comment_post_id ) ) {
    WP_CLI::error( $comment_post_id->get_error_message() );
}
for ( $index = 1; $index <= 3; $index++ ) {
    $comment_id = wp_insert_comment(
        [
            'comment_post_ID'      => (int) $comment_post_id,
            'comment_author'       => 'X6 Commenter ' . $index,
            'comment_author_email' => 'x6-comment-' . $index . '@example.test',
            'comment_content'      => 'X6 approved comment ' . $index,
            'comment_approved'     => 1,
            'comment_date'         => sprintf( '2026-09-12 10:%02d:00', $index ),
            'comment_date_gmt'     => sprintf( '2026-09-12 03:%02d:00', $index ),
        ]
    );
    if ( ! $comment_id ) {
        WP_CLI::error( 'Unable to create X6 comment fixture.' );
    }
}

$multipage_id = wp_insert_post(
    [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Multipage Article',
        'post_name'    => 'x6-multipage-article',
        'post_content' => '<p id="x6-page-one">X6 article page one.</p><!--nextpage--><p id="x6-page-two">X6 article page two.</p>',
    ],
    true
);
if ( is_wp_error( $multipage_id ) ) {
    WP_CLI::error( $multipage_id->get_error_message() );
}

$embed_target_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Embed Target',
        'post_name'    => 'x6-embed-target',
        'post_content' => '<p>X6 deterministic same-origin embed target.</p>',
    ],
    true
);
if ( is_wp_error( $embed_target_id ) ) {
    WP_CLI::error( $embed_target_id->get_error_message() );
}

$media_content = sprintf(
    '<p id="x6-media-sentinel">X6 media content sentinel.</p>\n' .
    '<!-- wp:image {"id":%1$d,"sizeSlug":"full"} --><figure class="wp-block-image size-full"><img src="%2$s" alt="X6 core image" class="wp-image-%1$d"/><figcaption class="wp-element-caption">X6 responsive figure caption.</figcaption></figure><!-- /wp:image -->\n' .
    '<!-- wp:gallery --><figure class="wp-block-gallery has-nested-images columns-default is-cropped"><!-- wp:image {"id":%1$d,"sizeSlug":"full"} --><figure class="wp-block-image size-full"><img src="%2$s" alt="X6 gallery one" class="wp-image-%1$d"/></figure><!-- /wp:image --><!-- wp:image {"id":%3$d,"sizeSlug":"full"} --><figure class="wp-block-image size-full"><img src="%4$s" alt="X6 gallery two" class="wp-image-%3$d"/></figure><!-- /wp:image --></figure><!-- /wp:gallery -->\n' .
    '<!-- wp:video --><figure class="wp-block-video" id="x6-video"><video controls preload="none"></video><figcaption>X6 video controls.</figcaption></figure><!-- /wp:video -->\n' .
    '<!-- wp:audio --><figure class="wp-block-audio" id="x6-audio"><audio controls preload="none"></audio><figcaption>X6 audio controls.</figcaption></figure><!-- /wp:audio -->\n' .
    '<!-- wp:embed --><figure class="wp-block-embed" id="x6-embed"><div class="wp-block-embed__wrapper"><iframe title="X6 deterministic embed" src="%5$s" width="1200" height="675"></iframe></div><figcaption>X6 same-origin embed.</figcaption></figure><!-- /wp:embed -->',
    $image_one_id,
    esc_url( $image_one_url ),
    $image_two_id,
    esc_url( $image_two_url ),
    esc_url( get_permalink( (int) $embed_target_id ) )
);

$media_id = wp_insert_post(
    [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Media Surface',
        'post_name'    => 'x6-media-surface',
        'post_content' => $media_content,
    ],
    true
);
if ( is_wp_error( $media_id ) ) {
    WP_CLI::error( $media_id->get_error_message() );
}

$controls_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Native Controls',
        'post_name'    => 'x6-native-controls',
        'post_content' => '<p id="x6-controls-sentinel">X6 native controls surface.</p><!-- wp:search {"label":"X6 Search"} /--><!-- wp:categories {"displayAsDropdown":true} /-->',
    ],
    true
);
if ( is_wp_error( $controls_id ) ) {
    WP_CLI::error( $controls_id->get_error_message() );
}

$password_id = wp_insert_post(
    [
        'post_type'     => 'page',
        'post_status'   => 'publish',
        'post_title'    => 'X6 Protected Page',
        'post_name'     => 'x6-protected-page',
        'post_password' => 'x6-password',
        'post_content'  => '<p>X6 protected content sentinel.</p>',
    ],
    true
);
if ( is_wp_error( $password_id ) ) {
    WP_CLI::error( $password_id->get_error_message() );
}

$search_target_id = wp_insert_post(
    [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Cross Surface Target',
        'post_name'    => 'x6-cross-surface-target',
        'post_content' => '<p>X6 Cross Surface Target unique search sentinel.</p>',
    ],
    true
);
if ( is_wp_error( $search_target_id ) ) {
    WP_CLI::error( $search_target_id->get_error_message() );
}

$sentinel_post_id = wp_insert_post(
    [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'X6 Lifecycle Sentinel',
        'post_name'    => 'x6-lifecycle-sentinel',
        'post_content' => '<p>X6 WordPress-owned lifecycle sentinel content.</p>',
    ],
    true
);
if ( is_wp_error( $sentinel_post_id ) ) {
    WP_CLI::error( $sentinel_post_id->get_error_message() );
}

$settings = function_exists( 'AZnet\\Theme\\normalize_settings' )
    ? AZnet\Theme\normalize_settings( [ 'visual_preset' => 'editorial' ] )
    : [ 'schema_version' => 1, 'visual_preset' => 'editorial' ];
set_theme_mod( 'aznet_theme_settings', $settings );

$archive_url = get_category_link( $category_id );
if ( is_wp_error( $archive_url ) ) {
    WP_CLI::error( $archive_url->get_error_message() );
}

$result = [
    'comment_url'        => get_permalink( (int) $comment_post_id ),
    'archive_page_1_url' => $archive_url,
    'archive_page_2_url' => add_query_arg( 'paged', '2', $archive_url ),
    'search_result_url'  => get_search_link( 'X6 Cross Surface Target' ),
    'search_empty_url'   => get_search_link( 'X6 Definitely Missing Query' ),
    'multipage_url'      => get_permalink( (int) $multipage_id ),
    'media_url'          => get_permalink( (int) $media_id ),
    'controls_url'       => get_permalink( (int) $controls_id ),
    'password_url'       => get_permalink( (int) $password_id ),
    'missing_url'        => home_url( '/x6-definitely-missing/' ),
    'home_url'           => home_url( '/' ),
    'sentinel_post_id'   => (int) $sentinel_post_id,
    'category_id'        => $category_id,
    'image_one_id'       => $image_one_id,
    'image_two_id'       => $image_two_id,
];

echo wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
