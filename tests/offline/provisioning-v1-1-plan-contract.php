<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
$root = dirname( __DIR__, 2 );
require_once $root . '/inc/theme/provisioning-blueprints.php';
require_once $root . '/inc/theme/provisioning-recommendations.php';
require_once $root . '/inc/theme/provisioning-editorial.php';
require_once $root . '/inc/theme/provisioning-plan.php';

function aznet_v11_discovery( bool $active = false ): array {
    return [
        'show_on_front' => 'posts',
        'front_page_id' => 0,
        'page_count' => $active ? 4 : 0,
        'category_count' => 0,
        'post_count' => $active ? 1 : 0,
        'published_post_count' => $active ? 1 : 0,
        'prior_blueprints' => [],
        'homepage_preset' => 'off',
        'homepage_slots' => [
            'services' => 0, 'about' => 0, 'team' => 0, 'knowledge' => [],
            'case_analysis' => 0, 'legal_news' => 0, 'process' => 0, 'faq' => 0, 'contact' => 0,
        ],
        'primary_menu_id' => 0,
        'pages' => [],
        'categories' => [],
    ];
}

function aznet_v11_selections( bool $discourage = true ): array {
    $bp = \AZnet\Theme\provisioning_blueprint( 'law01-v1-1' );
    $pages = [];
    foreach ( array_keys( $bp['pages'] ) as $role ) { $pages[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $categories = [];
    foreach ( array_keys( $bp['categories'] ) as $role ) { $categories[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $starter = [];
    foreach ( array_keys( $bp['editorial_examples']['items'] ) as $role ) { $starter[ $role ] = [ 'action' => 'create' ]; }
    return [
        'pages' => $pages,
        'categories' => $categories,
        'menu' => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
        'starter_posts' => $starter,
        'starter_media' => [ 'hero' => true, 'editorial-1' => true, 'editorial-2' => true, 'editorial-3' => true, 'editorial-4' => true ],
        'starter_publication' => [ 'publish' => true, 'discourage_indexing' => $discourage ],
    ];
}

$new_plan = \AZnet\Theme\provisioning_build_plan( 'law01-v1-1', aznet_v11_selections( true ), aznet_v11_discovery( false ) );
$types = array_column( $new_plan['operations'], 'type' );
foreach ( [ 'create_post', 'import_media', 'assign_featured_media', 'set_search_visibility' ] as $type ) {
    assert( in_array( $type, $types, true ), 'Missing v1.1 operation type: ' . $type );
}
$visibility = array_values( array_filter( $new_plan['operations'], static fn( array $op ): bool => 'set_search_visibility' === ( $op['type'] ?? '' ) ) );
assert( 1 === count( $visibility ) );
assert( 0 === (int) ( $visibility[0]['target'] ?? -1 ) );
assert( 'temporary_new_site_starter_publication' === ( $visibility[0]['reason'] ?? '' ) );

$posts = array_values( array_filter( $new_plan['operations'], static fn( array $op ): bool => 'create_post' === ( $op['type'] ?? '' ) ) );
assert( 8 === count( $posts ), 'New site should plan eight starter Posts when all are selected' );
foreach ( $posts as $op ) {
    assert( str_starts_with( (string) ( $op['role'] ?? '' ), 'starter_post:' ) );
    assert( '' !== (string) ( $op['category_role'] ?? '' ) );
    assert( '' !== (string) ( $op['media_role'] ?? '' ) );
    assert( 'publish' === ( $op['post_status'] ?? '' ), 'New site + confirmed discourage indexing may publish starter Posts' );
}
$valid = \AZnet\Theme\provisioning_validate_plan( $new_plan );
assert( true === $valid['ok'], implode( '; ', $valid['errors'] ) );

$declined = \AZnet\Theme\provisioning_build_plan( 'law01-v1-1', aznet_v11_selections( false ), aznet_v11_discovery( false ) );
assert( ! in_array( 'set_search_visibility', array_column( $declined['operations'], 'type' ), true ) );
foreach ( $declined['operations'] as $op ) {
    if ( 'create_post' === ( $op['type'] ?? '' ) ) { assert( 'draft' === ( $op['post_status'] ?? '' ) ); }
}

$active = \AZnet\Theme\provisioning_build_plan( 'law01-v1-1', aznet_v11_selections( true ), aznet_v11_discovery( true ) );
assert( ! in_array( 'set_search_visibility', array_column( $active['operations'], 'type' ), true ), 'Active site must never receive whole-site noindex for starter content' );
foreach ( $active['operations'] as $op ) {
    if ( 'create_post' === ( $op['type'] ?? '' ) ) { assert( 'draft' === ( $op['post_status'] ?? '' ), 'Active site starter Posts must fail safe to Draft' ); }
}

echo "PASS: Law v1.1 immutable plan operations and index-safe publication defaults\n";
