<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$parent = \AZnet\Theme\team_directory_parent();
if ( ! $parent instanceof \WP_Post ) {
    fwrite( STDERR, "FAIL: mapped Team parent missing\n" );
    exit( 1 );
}

$members = \AZnet\Theme\team_directory_members();
if ( [] === $members || ! $members[0] instanceof \WP_Post ) {
    fwrite( STDERR, "FAIL: published Team child missing\n" );
    exit( 1 );
}

$descriptor = \AZnet\Theme\homepage_source_descriptor( 'law-01', 'team' );
if ( ! is_array( $descriptor ) ) {
    fwrite( STDERR, "FAIL: Team descriptor missing\n" );
    exit( 1 );
}

$member_id = (int) $members[0]->ID;
if ( ! \AZnet\Theme\Admin\homepage_quick_edit_target_allowed( 'law-01', 'team', $member_id, $descriptor ) ) {
    fwrite( STDERR, "FAIL: mapped Team child rejected by bounded authoring\n" );
    exit( 1 );
}

$unrelated_id = (int) get_option( 'page_on_front', 0 );
if ( $unrelated_id > 0 && \AZnet\Theme\Admin\homepage_quick_edit_target_allowed( 'law-01', 'team', $unrelated_id, $descriptor ) ) {
    fwrite( STDERR, "FAIL: unrelated Page accepted as Team member\n" );
    exit( 1 );
}

$data = \AZnet\Theme\Admin\homepage_team_member_insert_data( $parent, 'Nguyễn An', 'Luật sư' );
assert( 'page' === $data['post_type'] );
assert( 'publish' === $data['post_status'] );
assert( (int) $parent->ID === (int) $data['post_parent'] );
assert( 'Nguyễn An' === $data['post_title'] );
assert( 'Luật sư' === $data['post_excerpt'] );
assert( isset( $data['menu_order'] ) && is_int( $data['menu_order'] ) );

echo "PASS: Team authoring is bounded to mapped direct children and immediate publication.\n";
