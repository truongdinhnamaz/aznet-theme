<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    fwrite( STDERR, "FAIL: WordPress runtime is not loaded.\n" );
    exit( 1 );
}

$created = [];
$failure = null;

try {
    $parent_id = wp_insert_post(
        [
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_title'  => 'Law 01 Services source-gap parent',
            'post_name'   => 'law01-services-source-gap-parent',
        ],
        true
    );
    if ( is_wp_error( $parent_id ) ) {
        throw new RuntimeException( $parent_id->get_error_message() );
    }
    $parent_id = (int) $parent_id;
    $created[] = $parent_id;

    $untitled_id = wp_insert_post(
        [
            'post_type'   => 'page',
            'post_status' => 'publish',
            'post_title'  => '',
            'post_name'   => 'service-source-gap',
            'post_parent' => $parent_id,
            'menu_order'  => 0,
        ],
        true
    );
    if ( is_wp_error( $untitled_id ) ) {
        throw new RuntimeException( $untitled_id->get_error_message() );
    }
    $untitled_id = (int) $untitled_id;
    $created[] = $untitled_id;

    foreach ( [ 10, 20, 30, 40, 50, 60 ] as $index => $menu_order ) {
        $child_id = wp_insert_post(
            [
                'post_type'   => 'page',
                'post_status' => 'publish',
                'post_title'  => 'Dịch vụ kiểm thử ' . (string) ( $index + 1 ),
                'post_name'   => 'dich-vu-kiem-thu-' . (string) ( $index + 1 ),
                'post_parent' => $parent_id,
                'menu_order'  => $menu_order,
            ],
            true
        );
        if ( is_wp_error( $child_id ) ) {
            throw new RuntimeException( $child_id->get_error_message() );
        }
        $created[] = (int) $child_id;
    }

    $raw = \AZnet\Theme\homepage_direct_published_children( $parent_id, 24 );
    if ( 7 !== count( $raw ) ) {
        throw new RuntimeException( 'FAIL: raw WordPress child projection must retain all 7 published source Pages.' );
    }

    $public = \AZnet\Theme\homepage_renderable_child_pages( $parent_id, 6 );
    if ( 6 !== count( $public ) ) {
        throw new RuntimeException( 'FAIL: public Services projection must fill all 6 slots from renderable siblings.' );
    }

    $public_ids = array_map( static fn ( \WP_Post $post ): int => (int) $post->ID, $public );
    if ( in_array( $untitled_id, $public_ids, true ) ) {
        throw new RuntimeException( 'FAIL: untitled WordPress Page leaked into public Services cards.' );
    }

    foreach ( $public as $page ) {
        if ( '' === trim( (string) get_the_title( $page ) ) ) {
            throw new RuntimeException( 'FAIL: public Services projection contains an unnamed Page.' );
        }
        $url = get_permalink( $page );
        if ( ! is_string( $url ) || '' === trim( $url ) ) {
            throw new RuntimeException( 'FAIL: public Services projection contains an unlinkable Page.' );
        }
    }
} catch ( Throwable $error ) {
    $failure = $error;
} finally {
    foreach ( array_reverse( $created ) as $post_id ) {
        wp_delete_post( (int) $post_id, true );
    }
}

if ( $failure instanceof Throwable ) {
    fwrite( STDERR, $failure->getMessage() . "\n" );
    exit( 1 );
}

echo "PASS: Law 01 Services public projection omits untitled source Pages and fills the public limit.\n";
