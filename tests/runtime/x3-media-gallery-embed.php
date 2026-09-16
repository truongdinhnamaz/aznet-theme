<?php
/**
 * Seed deterministic native/legacy media fixtures for X3 runtime/browser QA.
 *
 * Executed with `wp eval-file`; intentionally no strict_types declaration.
 */

$image_one_id = (int) getenv( 'X3_MEDIA_ONE_ID' );
$image_two_id = (int) getenv( 'X3_MEDIA_TWO_ID' );

if ( $image_one_id <= 0 || $image_two_id <= 0 ) {
    WP_CLI::error( 'X3 media attachment IDs are required.' );
}

$image_one_url = wp_get_attachment_image_url( $image_one_id, 'full' );
$image_two_url = wp_get_attachment_image_url( $image_two_id, 'full' );

if ( ! is_string( $image_one_url ) || '' === $image_one_url || ! is_string( $image_two_url ) || '' === $image_two_url ) {
    WP_CLI::error( 'X3 attachment URLs could not be resolved.' );
}

$embed_target_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X3 Embed Target',
        'post_name'    => 'x3-embed-target',
        'post_content' => '<p id="x3-embed-target">X3 deterministic embed target.</p>',
    ],
    true
);

if ( is_wp_error( $embed_target_id ) ) {
    WP_CLI::error( $embed_target_id->get_error_message() );
}

$embed_url = get_permalink( (int) $embed_target_id );

$long_caption = 'Chú thích X3 rất dài để kiểm tra khả năng xuống dòng an toàn trên màn hình hẹp mà không làm tràn bố cục nội dung WordPress.';
$legacy_caption = 'Legacy caption X3 vẫn do WordPress sở hữu nội dung; Theme chỉ chịu trách nhiệm trình bày.';

$core_image = sprintf(
    '<!-- wp:image {"id":%1$d,"sizeSlug":"full","linkDestination":"custom"} --><figure class="wp-block-image size-full"><a href="%2$s" id="x3-focus-link"><img src="%3$s" alt="X3 Core image" class="wp-image-%1$d" /></a><figcaption class="wp-element-caption">%4$s</figcaption></figure><!-- /wp:image -->',
    $image_one_id,
    esc_url( home_url( '/' ) ),
    esc_url( $image_one_url ),
    esc_html( $long_caption )
);

$core_gallery = sprintf(
    '<!-- wp:gallery {"linkTo":"none"} --><figure class="wp-block-gallery has-nested-images columns-default is-cropped"><!-- wp:image {"id":%1$d,"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="%2$s" alt="X3 gallery image one" class="wp-image-%1$d" /><figcaption class="wp-element-caption">Gallery caption one</figcaption></figure><!-- /wp:image --><!-- wp:image {"id":%3$d,"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="%4$s" alt="X3 gallery image two" class="wp-image-%3$d" /><figcaption class="wp-element-caption">Gallery caption two</figcaption></figure><!-- /wp:image --></figure><!-- /wp:gallery -->',
    $image_one_id,
    esc_url( $image_one_url ),
    $image_two_id,
    esc_url( $image_two_url )
);

$wide_figure = sprintf(
    '<figure class="wp-block-image alignwide" id="x3-alignwide"><img src="%1$s" alt="X3 wide image" /><figcaption>Wide media remains inside the Theme safe geometry.</figcaption></figure>',
    esc_url( $image_one_url )
);

$full_figure = sprintf(
    '<figure class="wp-block-image alignfull" id="x3-alignfull"><img src="%1$s" alt="X3 full image" /><figcaption>Full media means safe Theme-shell width in X3.</figcaption></figure>',
    esc_url( $image_two_url )
);

$legacy_caption_markup = sprintf(
    '<div id="attachment_%1$d" style="width: 1600px" class="wp-caption alignnone"><img src="%2$s" alt="X3 legacy caption image" width="1600" height="900" /><p class="wp-caption-text">%3$s</p></div>',
    $image_one_id,
    esc_url( $image_one_url ),
    esc_html( $legacy_caption )
);

$legacy_gallery_shortcode = sprintf( '[gallery ids="%d,%d" columns="2" size="full"]', $image_one_id, $image_two_id );

$video = '<!-- wp:video --><figure class="wp-block-video" id="x3-video"><video controls preload="none"></video><figcaption>Native video controls remain available.</figcaption></figure><!-- /wp:video -->';
$audio = '<!-- wp:audio --><figure class="wp-block-audio" id="x3-audio"><audio controls preload="none"></audio><figcaption>Native audio controls remain available.</figcaption></figure><!-- /wp:audio -->';
$embed = sprintf(
    '<!-- wp:embed --><figure class="wp-block-embed" id="x3-embed"><div class="wp-block-embed__wrapper"><iframe title="X3 deterministic embed" src="%1$s" width="1200" height="675"></iframe></div><figcaption>Deterministic same-origin embed.</figcaption></figure><!-- /wp:embed -->',
    esc_url( $embed_url )
);

$authored_content = implode(
    "\n\n",
    [
        '<p id="x3-media-sentinel">X3 Media authored content sentinel.</p>',
        $core_image,
        $core_gallery,
        $wide_figure,
        $full_figure,
        $legacy_caption_markup,
        $legacy_gallery_shortcode,
        $video,
        $audio,
        $embed,
    ]
);

$page_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X3 Media Page',
        'post_name'    => 'x3-media-page',
        'post_content' => $authored_content,
    ],
    true
);

$front_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'X3 Media Front Page',
        'post_name'    => 'x3-media-front-page',
        'post_content' => $authored_content,
    ],
    true
);

$post_id = wp_insert_post(
    [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'X3 Media Post',
        'post_name'    => 'x3-media-post',
        'post_content' => $authored_content,
    ],
    true
);

foreach ( [ $page_id, $front_id, $post_id ] as $created_id ) {
    if ( is_wp_error( $created_id ) ) {
        WP_CLI::error( $created_id->get_error_message() );
    }
}

$category = wp_insert_term( 'X3 Media Archive', 'category', [ 'slug' => 'x3-media-archive' ] );
if ( is_wp_error( $category ) ) {
    WP_CLI::error( $category->get_error_message() );
}

wp_set_post_terms( (int) $post_id, [ (int) $category['term_id'] ], 'category', false );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', (int) $front_id );
update_option( 'page_for_posts', 0 );

$state = [
    'post_id'           => (int) $post_id,
    'page_id'           => (int) $page_id,
    'front_id'          => (int) $front_id,
    'embed_target_id'   => (int) $embed_target_id,
    'image_one_id'      => $image_one_id,
    'image_two_id'      => $image_two_id,
    'post_url'          => get_permalink( (int) $post_id ),
    'page_url'          => get_permalink( (int) $page_id ),
    'front_url'         => home_url( '/' ),
    'archive_url'       => get_category_link( (int) $category['term_id'] ),
    'search_url'        => home_url( '/?s=X3+Media' ),
    'not_found_url'     => home_url( '/?p=999999999' ),
    'admin_page_edit'   => admin_url( 'post.php?post=' . (int) $page_id . '&action=edit' ),
];

echo wp_json_encode( $state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
