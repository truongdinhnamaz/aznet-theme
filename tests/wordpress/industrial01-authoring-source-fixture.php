<?php
/**
 * Disposable WordPress-only fixture for Industrial 01 source authoring.
 * Usage: wp eval-file tests/wordpress/industrial01-authoring-source-fixture.php
 * Set INDUSTRIAL01_SOURCE_ACTION=restore to return Theme settings to the original.
 * Never run on production. No site identity or provider private storage.
 */
if ( ! defined( 'ABSPATH' ) ) { throw new RuntimeException( 'WordPress required' ); }
$directory = '/tmp/industrial01-pilot';
$initial_file = $directory . '/authoring-source-original.json';
$fixture_file = $directory . '/authoring-source-ids.json';
$action = getenv( 'INDUSTRIAL01_SOURCE_ACTION' ) ?: 'seed';

if ( 'restore' === $action ) {
    $original = json_decode( (string) file_get_contents( $initial_file ), true );
    $fixture = json_decode( (string) file_get_contents( $fixture_file ), true );
    if ( ! is_array( $original ) || ! is_array( $fixture ) ) {
        throw new RuntimeException( 'Missing baseline Theme settings or fixture' );
    }
    set_theme_mod( 'aznet_theme_settings', $original );
    foreach ( [ 'about', 'process', 'contact' ] as $slot ) {
        $id = (int) ( $fixture[ $slot ] ?? 0 );
        $expected_hash = (string) ( $fixture['hashes'][ $slot ] ?? '' );
        $actual = (string) get_post_field( 'post_content', $id );
        if ( $id <= 0 || '' === $actual || '' === $expected_hash || ! hash_equals( $expected_hash, hash( 'sha256', $actual ) ) ) {
            throw new RuntimeException( 'Native WordPress data mutated by Theme mapping: ' . $slot );
        }
    }
    echo "PASS: original Theme settings restored; WordPress source posts retained unchanged\n";
    return;
}
if ( 'seed' !== $action ) { throw new RuntimeException( 'Unsupported fixture action' ); }

$before = get_theme_mod( 'aznet_theme_settings', [] );
if ( ! is_array( $before ) ) { throw new RuntimeException( 'Expected Theme settings array' ); }
file_put_contents( $initial_file, wp_json_encode( $before, JSON_UNESCAPED_UNICODE ) );
$records = [
    'about' => [
        'title' => 'Giới thiệu nguồn B2B',
        'name' => 'about-source-b2b',
        'content' => '<p>Doanh nghiệp cung ứng vật tư theo nhu cầu và thông tin công bố của mình.</p>',
    ],
    'process' => [
        'title' => 'Quy trình xác nhận yêu cầu',
        'name' => 'process-source-b2b',
        'content' => '<h3>Tiếp nhận thông số</h3><p>Khách hàng cung cấp quy cách cần đối chiếu.</p><h3>Trao đổi phạm vi</h3><p>Đội ngũ xác nhận thông tin công bố trước khi trả lời.</p>',
    ],
    'contact' => [
        'title' => 'Liên hệ nguồn B2B',
        'name' => 'contact-source-b2b',
        'content' => '<p>Đây là trang Liên hệ WordPress do site sở hữu, không phải Theme form.</p>',
    ],
];
$ids = [];
$hashes = [];
foreach ( $records as $slot => $r ) {
    $created = wp_insert_post( [
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $r['title'],
        'post_name' => $r['name'],
        'post_content' => $r['content'],
        'post_excerpt' => $r['content'],
    ], true );
    if ( is_wp_error( $created ) || (int) $created <= 0 ) {
        throw new RuntimeException( 'Could not seed native Page: ' . $slot );
    }
    $ids[ $slot ] = (int) $created;
    $hashes[ $slot ] = hash( 'sha256', $r['content'] );
}
$solutions = get_page_by_path( 'giai-phap' );
if ( ! $solutions instanceof WP_Post ) { throw new RuntimeException( 'Existing Industrial Solutions fixture missing' ); }
$ids['solutions'] = (int) $solutions->ID;

$cat = wp_insert_term( 'Kiến thức thử nghiệm B2B', 'category', [ 'slug' => 'industrial-b2b-qa' ] );
if ( is_wp_error( $cat ) ) { throw new RuntimeException( $cat->get_error_message() ); }
$ids['knowledge'] = (int) $cat['term_id'];
$post = wp_insert_post( [
    'post_type' => 'post',
    'post_status' => 'publish',
    'post_title' => 'Kiến thức kiểm tra vật liệu',
    'post_name' => 'industrial-b2b-qa-knowledge',
    'post_content' => '<p>Bài viết thử nghiệm WordPress: kiểm tra yêu cầu trước khi trao đổi.</p>',
    'post_category' => [ $ids['knowledge'] ],
], true );
if ( is_wp_error( $post ) || (int) $post <= 0 ) { throw new RuntimeException( 'Knowledge post fixture missing' ); }
$ids['post'] = (int) $post;
$ids['contact_url'] = (string) get_permalink( $ids['contact'] );
if ( '' === $ids['contact_url'] ) { throw new RuntimeException( 'WordPress contact Page permalink missing' ); }
$ids['hashes'] = $hashes;
file_put_contents( $fixture_file, wp_json_encode( $ids, JSON_UNESCAPED_UNICODE ) );
echo "PASS: disposable WordPress owner pages/categories/posts seeded for Industrial 01 authoring\n";
