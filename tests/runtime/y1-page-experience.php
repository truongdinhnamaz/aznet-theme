<?php
/**
 * Seed real WordPress fixtures for Y1 Page Experience.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$image_id = (int) getenv( 'Y1_IMAGE_ID' );
if ( $image_id <= 0 ) {
    fwrite( STDERR, "FAIL: Y1_IMAGE_ID is required\n" );
    exit( 1 );
}

$create_page = static function ( array $args ): int {
    $defaults = [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_excerpt' => '',
        'post_content' => '<p>Y1 native Page content sentinel.</p>',
    ];

    $id = wp_insert_post( array_merge( $defaults, $args ), true );
    if ( is_wp_error( $id ) ) {
        fwrite( STDERR, 'FAIL: ' . $id->get_error_message() . "\n" );
        exit( 1 );
    }

    return (int) $id;
};

$parent_id = $create_page( [
    'post_title'   => 'Y1 Parent Page',
    'post_name'    => 'y1-parent',
    'post_excerpt' => 'Explicitly authored parent lead.',
    'post_content' => '<p id="y1-parent-content">Parent content sentinel.</p>',
] );
set_post_thumbnail( $parent_id, $image_id );

$child_id = $create_page( [
    'post_title'   => 'Y1 Child Page',
    'post_name'    => 'y1-child',
    'post_parent'  => $parent_id,
    'post_excerpt' => 'Explicitly authored child lead.',
    'post_content' => '<p id="y1-child-content">Child content sentinel.</p>',
] );

$wide_id = $create_page( [
    'post_title'   => 'Y1 Wide Page',
    'post_name'    => 'y1-wide',
    'post_content' => '<p id="y1-wide-content">This body text must not become a generated lead.</p>',
] );
update_post_meta( $wide_id, '_wp_page_template', 'page-templates/wide.php' );

$landing_id = $create_page( [
    'post_title'   => 'Y1 Landing Page',
    'post_name'    => 'y1-landing',
    'post_excerpt' => 'Explicitly authored landing lead.',
    'post_content' => '<p id="y1-landing-content">Landing content sentinel.</p>',
] );
update_post_meta( $landing_id, '_wp_page_template', 'page-templates/landing.php' );

$plain_slug_id = $create_page( [
    'post_title'   => 'Cart',
    'post_name'    => 'cart',
    'post_content' => '<p id="y1-plain-slug-content">Ordinary native Page despite commerce-like slug.</p>',
] );

echo wp_json_encode( [
    'parent_id'      => $parent_id,
    'parent_url'     => get_permalink( $parent_id ),
    'child_id'       => $child_id,
    'child_url'      => get_permalink( $child_id ),
    'wide_id'        => $wide_id,
    'wide_url'       => get_permalink( $wide_id ),
    'landing_id'     => $landing_id,
    'landing_url'    => get_permalink( $landing_id ),
    'plain_slug_id'  => $plain_slug_id,
    'plain_slug_url' => get_permalink( $plain_slug_id ),
    'front_url'      => home_url( '/' ),
], JSON_UNESCAPED_SLASHES );
