<?php
/**
 * Y1 Page Experience WordPress runtime fixture.
 *
 * @package AZnetTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Create one published Page fixture and fail closed on errors. */
function y1_create_page( array $args ): int {
    $defaults = [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_content' => '<p>Y1 native WordPress Page content.</p>',
    ];
    $post_id = wp_insert_post( array_merge( $defaults, $args ), true );
    if ( is_wp_error( $post_id ) || 0 >= (int) $post_id ) {
        WP_CLI::error( 'Unable to create Y1 Page fixture.' );
    }
    return (int) $post_id;
}

$parent_id = y1_create_page(
    [
        'post_title'   => 'Y1 Parent Page',
        'post_excerpt' => 'Authored parent introduction for Y1.',
        'post_name'    => 'y1-parent-page',
    ]
);

$child_id = y1_create_page(
    [
        'post_title'   => 'Y1 Child Page',
        'post_excerpt' => 'Authored child introduction for Y1.',
        'post_parent'  => $parent_id,
        'post_name'    => 'y1-child-page',
        'post_content' => '<p id="y1-child-content">Child Page content owned by WordPress.</p>',
    ]
);

$wide_id = y1_create_page(
    [
        'post_title'   => 'Y1 Wide Page',
        'post_excerpt' => 'Wide native Page presentation.',
        'post_name'    => 'y1-wide-page',
        'post_content' => '<p id="y1-wide-content">Wide Page content owned by WordPress.</p>',
    ]
);
update_post_meta( $wide_id, '_wp_page_template', 'page-templates/wide.php' );

$landing_id = y1_create_page(
    [
        'post_title'   => 'Y1 Landing Page',
        'post_excerpt' => 'Landing native Page presentation.',
        'post_name'    => 'y1-landing-page',
        'post_content' => '<p id="y1-landing-content">Landing Page content owned by WordPress.</p>',
    ]
);
update_post_meta( $landing_id, '_wp_page_template', 'page-templates/landing.php' );

$services_id = $parent_id;

$service_detail_id = y1_create_page(
    [
        'post_title'   => 'Y1 Business Service',
        'post_excerpt' => 'Mapped child service presentation.',
        'post_parent'  => $services_id,
        'post_name'    => 'y1-business-service',
        'post_content' => '<h2>Phạm vi hỗ trợ</h2><p id="y1-service-content">Service Page content owned by WordPress.</p><h2>Thông tin nên chuẩn bị</h2><p>Prepare relevant documents.</p>',
    ]
);

$service_sibling_id = y1_create_page(
    [
        'post_title'   => 'Y1 Civil Service',
        'post_excerpt' => 'Sibling mapped service Page.',
        'post_parent'  => $parent_id,
        'post_name'    => 'y1-civil-service',
    ]
);

$contact_id = y1_create_page(
    [
        'post_title'   => 'Y1 Contact',
        'post_excerpt' => 'Trao đổi trực tiếp với văn phòng qua trang Liên hệ.',
        'post_name'    => 'y1-contact',
        'post_content' => '<p id="y1-contact-content">Contact Page content owned by WordPress.</p><p><label>Họ tên <input type="text" name="y1_name"></label></p><p><label>Nội dung <textarea name="y1_message"></textarea></label></p>',
    ]
);

$settings = get_theme_mod( 'aznet_theme_settings', [] );
$settings = is_array( $settings ) ? $settings : [];
$settings['homepage_services_page'] = $services_id;
$settings['homepage_contact_page']  = $contact_id;
$settings['homepage_preset']        = 'law-01';
$settings['homepage_law01_variant'] = 'burgundy-gold';
set_theme_mod( 'aznet_theme_settings', $settings );

$commerce_looking_id = y1_create_page(
    [
        'post_title'   => 'Y1 Commerce Looking Page',
        'post_name'    => 'shop',
        'post_content' => '<p id="y1-commerce-looking-content">A normal native Page with a commerce-looking slug.</p>',
    ]
);

// Attach a tiny local PNG without relying on an external media service.
$png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wlq8QAAAABJRU5ErkJggg==', true );
if ( false === $png ) {
    WP_CLI::error( 'Unable to decode Y1 featured-image fixture.' );
}
$upload = wp_upload_bits( 'y1-featured.png', null, $png );
if ( ! empty( $upload['error'] ) ) {
    WP_CLI::error( 'Unable to create Y1 featured-image upload: ' . $upload['error'] );
}
$attachment_id = wp_insert_attachment(
    [
        'post_mime_type' => 'image/png',
        'post_title'     => 'Y1 Featured Image',
        'post_status'    => 'inherit',
    ],
    $upload['file'],
    $child_id,
    true
);
if ( is_wp_error( $attachment_id ) || 0 >= (int) $attachment_id ) {
    WP_CLI::error( 'Unable to create Y1 featured-image attachment.' );
}
require_once ABSPATH . 'wp-admin/includes/image.php';
$metadata = wp_generate_attachment_metadata( (int) $attachment_id, $upload['file'] );
if ( is_array( $metadata ) ) {
    wp_update_attachment_metadata( (int) $attachment_id, $metadata );
}
set_post_thumbnail( $child_id, (int) $attachment_id );

$urls = [];
foreach (
    [
        'parent_url'           => $parent_id,
        'child_url'            => $child_id,
        'wide_url'             => $wide_id,
        'landing_url'          => $landing_id,
        'commerce_looking_url' => $commerce_looking_id,
        'service_detail_url'    => $service_detail_id,
        'contact_url'           => $contact_id,
    ] as $key => $post_id
) {
    $url = get_permalink( $post_id );
    if ( ! is_string( $url ) || '' === $url ) {
        WP_CLI::error( 'Unable to resolve Y1 fixture URL: ' . $key );
    }
    $urls[ $key ] = $url;
}

$result = array_merge(
    $urls,
    [
        'parent_id'           => $parent_id,
        'child_id'            => $child_id,
        'wide_id'             => $wide_id,
        'landing_id'          => $landing_id,
        'commerce_looking_id' => $commerce_looking_id,
        'services_id'          => $services_id,
        'service_detail_id'    => $service_detail_id,
        'service_sibling_id'   => $service_sibling_id,
        'contact_id'           => $contact_id,
        'attachment_id'        => (int) $attachment_id,
    ]
);

echo wp_json_encode( $result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
