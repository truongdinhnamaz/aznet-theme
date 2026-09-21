<?php
/**
 * Runtime fixture for Law 01 Category Archive Choice 1.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$category = wp_insert_term( 'Tin tức', 'category', [ 'slug' => 'law01-news' ] );
if ( is_wp_error( $category ) ) {
    WP_CLI::error( 'Unable to create Law 01 archive category.' );
}
$category_id = (int) $category['term_id'];

$other = wp_insert_term( 'Kiến thức pháp luật', 'category', [ 'slug' => 'law01-legal-knowledge' ] );
if ( is_wp_error( $other ) ) {
    WP_CLI::error( 'Unable to create secondary Law 01 category.' );
}
$other_id = (int) $other['term_id'];

$contact_id = wp_insert_post(
    [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => 'Liên hệ',
        'post_name'    => 'law01-contact',
        'post_content' => '<p>Contact fixture.</p>',
    ],
    true
);
if ( is_wp_error( $contact_id ) || (int) $contact_id <= 0 ) {
    WP_CLI::error( 'Unable to create mapped Contact Page.' );
}

$settings = get_theme_mod( 'aznet_theme_settings', [] );
$settings = is_array( $settings ) ? $settings : [];
$settings['homepage_preset']        = 'law-01';
$settings['homepage_law01_variant'] = 'burgundy-gold';
$settings['homepage_contact_page']  = (int) $contact_id;
set_theme_mod( 'aznet_theme_settings', $settings );

update_option( 'posts_per_page', 6 );

$png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wlq8QAAAABJRU5ErkJggg==', true );
if ( false === $png ) {
    WP_CLI::error( 'Unable to decode archive thumbnail fixture.' );
}
$upload = wp_upload_bits( 'law01-archive-thumb.png', null, $png );
if ( ! empty( $upload['error'] ) ) {
    WP_CLI::error( 'Unable to create archive thumbnail upload.' );
}
$attachment_id = wp_insert_attachment(
    [
        'post_mime_type' => 'image/png',
        'post_title'     => 'Law 01 Archive Thumbnail',
        'post_status'    => 'inherit',
    ],
    $upload['file'],
    0,
    true
);
if ( is_wp_error( $attachment_id ) || (int) $attachment_id <= 0 ) {
    WP_CLI::error( 'Unable to create archive thumbnail attachment.' );
}
require_once ABSPATH . 'wp-admin/includes/image.php';
$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $upload['file'] );
if ( is_array( $metadata ) ) {
    wp_update_attachment_metadata( (int) $attachment_id, $metadata );
}

$post_ids = [];
for ( $index = 1; $index <= 11; ++$index ) {
    $post_id = wp_insert_post(
        [
            'post_type'     => 'post',
            'post_status'   => 'publish',
            'post_title'    => sprintf( 'Bài pháp lý mẫu %02d — hướng dẫn và cập nhật thực tiễn', $index ),
            'post_excerpt'  => sprintf( 'Tóm tắt pháp lý mẫu %02d giúp người đọc nhận biết nội dung chính trước khi mở bài viết.', $index ),
            'post_content'  => sprintf( '<p id="law01-archive-post-%1$d">Nội dung pháp lý mẫu %1$d thuộc WordPress native Post.</p>', $index ),
            'post_category' => [ $category_id, 1 === $index ? $other_id : $category_id ],
            'post_date'     => gmdate( 'Y-m-d H:i:s', time() - ( $index * HOUR_IN_SECONDS ) ),
        ],
        true
    );
    if ( is_wp_error( $post_id ) || (int) $post_id <= 0 ) {
        WP_CLI::error( 'Unable to create archive Post fixture.' );
    }
    $post_ids[] = (int) $post_id;
    if ( $index <= 4 ) {
        set_post_thumbnail( (int) $post_id, (int) $attachment_id );
    }
}

$archive_url = get_category_link( $category_id );
$contact_url = get_permalink( (int) $contact_id );
if ( ! is_string( $archive_url ) || '' === $archive_url || ! is_string( $contact_url ) || '' === $contact_url ) {
    WP_CLI::error( 'Unable to resolve Law 01 archive fixture URLs.' );
}

echo wp_json_encode(
    [
        'archive_url'   => $archive_url,
        'contact_url'   => $contact_url,
        'category_id'   => $category_id,
        'post_ids'      => $post_ids,
        'attachment_id' => (int) $attachment_id,
    ],
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);
