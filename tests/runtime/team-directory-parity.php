<?php

if ( ! defined( 'ABSPATH' ) ) { exit( 1 ); }

$parent = \AZnet\Theme\team_directory_parent();
if ( ! $parent instanceof \WP_Post ) {
    fwrite( STDERR, "FAIL: mapped Team parent missing\n" );
    exit( 1 );
}

$raw = \AZnet\Theme\team_directory_members();
$homepage = \AZnet\Theme\team_directory_public_members( 4 );
$all = \AZnet\Theme\team_directory_public_members();

if ( 7 !== count( $raw ) ) {
    fwrite( STDERR, 'FAIL: raw Team source expected 7 published members including the untitled source Page, got ' . count( $raw ) . "\n" );
    exit( 1 );
}
if ( 4 !== count( $homepage ) ) {
    fwrite( STDERR, 'FAIL: Homepage Team expected 4 public members, got ' . count( $homepage ) . "\n" );
    exit( 1 );
}
if ( 6 !== count( $all ) ) {
    fwrite( STDERR, 'FAIL: Team directory expected 6 named public members, got ' . count( $all ) . "\n" );
    exit( 1 );
}

$homepage_orders = array_map( static fn( \WP_Post $post ): int => (int) $post->menu_order, $homepage );
if ( [ 10, 20, 30, 40 ] !== $homepage_orders ) {
    fwrite( STDERR, 'FAIL: Homepage Team order mismatch: ' . wp_json_encode( $homepage_orders ) . "\n" );
    exit( 1 );
}

$all_titles = array_map( static fn( \WP_Post $post ): string => (string) $post->post_title, $all );
if ( in_array( 'Draft Team Member', $all_titles, true ) ) {
    fwrite( STDERR, "FAIL: draft Team member leaked into public resolver\n" );
    exit( 1 );
}
if ( in_array( '', array_map( 'trim', $all_titles ), true ) ) {
    fwrite( STDERR, "FAIL: untitled Team member leaked into public resolver\n" );
    exit( 1 );
}

$second = $homepage[1] ?? null;
$third = $homepage[2] ?? null;
if ( ! $second instanceof \WP_Post || has_post_thumbnail( $second ) ) {
    fwrite( STDERR, "FAIL: missing-portrait member fixture not preserved\n" );
    exit( 1 );
}
if ( ! $third instanceof \WP_Post || '' !== trim( (string) $third->post_excerpt ) ) {
    fwrite( STDERR, "FAIL: missing-role member fixture not preserved\n" );
    exit( 1 );
}

echo "PASS: Team raw/public parity keeps 7 WordPress source Pages while rendering 4-of-6 named public members.\n";
