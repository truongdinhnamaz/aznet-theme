<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
$GLOBALS['aznet_editorial_counts'] = [ 18 => 2, 19 => 0 ];
function get_posts( $args = [] ) {
    $term = (int) ( $args['cat'] ?? 0 );
    $count = (int) ( $GLOBALS['aznet_editorial_counts'][ $term ] ?? 0 );
    return $count > 0 ? [ (object) [ 'ID' => 100 + $term ] ] : [];
}

require_once dirname( __DIR__, 2 ) . '/inc/theme/provisioning-blueprints.php';
if ( is_file( dirname( __DIR__, 2 ) . '/inc/theme/provisioning-editorial.php' ) ) {
    require_once dirname( __DIR__, 2 ) . '/inc/theme/provisioning-editorial.php';
}

$bp = \AZnet\Theme\provisioning_blueprint( 'law01-v1-1' );
assert( is_array( $bp ), 'law01-v1-1 blueprint must exist' );
assert( 'law-01' === $bp['homepage_preset'] );
assert( 8 === count( $bp['editorial_examples']['items'] ?? [] ), 'v1.1 must carry exactly eight starter editorial items' );
assert( isset( $bp['editorial_examples']['items']['knowledge_business'] ) );
assert( isset( $bp['editorial_examples']['items']['legal_news'] ) );

$required = [ 'title', 'excerpt', 'content', 'category_role', 'media_role' ];
foreach ( $bp['editorial_examples']['items'] as $role => $item ) {
    foreach ( $required as $key ) { assert( array_key_exists( $key, $item ), "{$role} missing {$key}" ); }
    assert( '' !== trim( (string) $item['title'] ) );
    assert( '' !== trim( (string) $item['excerpt'] ) );
    assert( str_contains( (string) $item['content'], '<h2>' ), "{$role} requires H2 structure" );
    $plain = trim( strip_tags( (string) $item['content'] ) );
    $words = preg_split( '/\s+/u', $plain ) ?: [];
    assert( count( $words ) >= 320, "{$role} starter copy is too thin for a coherent demo" );
    assert( count( $words ) <= 800, "{$role} starter copy is too long for starter content" );
    assert( ! preg_match( '/\bĐiều\s+\d+/u', $plain ), "{$role} must not cite substantive article numbers" );
    assert( ! preg_match( '/\b(top|hàng đầu|số 1|giải thưởng|chứng nhận|tỷ lệ thắng|khách hàng nói)\b/iu', $plain ), "{$role} contains forbidden credential/testimonial language" );
}

assert( function_exists( 'AZnet\\Theme\\provisioning_editorial_definition' ), 'editorial definition helper must exist' );
assert( function_exists( 'AZnet\\Theme\\provisioning_category_public_post_count' ), 'public post count helper must exist' );
assert( function_exists( 'AZnet\\Theme\\provisioning_editorial_coverage_recommendations' ), 'coverage helper must exist' );
assert( 1 === \AZnet\Theme\provisioning_category_public_post_count( 18 ) );
assert( 0 === \AZnet\Theme\provisioning_category_public_post_count( 19 ) );

$recommendations = [
    'categories' => [
        'knowledge_business' => [ 'action' => 'reuse', 'object_id' => 18 ],
        'knowledge_civil' => [ 'action' => 'reuse', 'object_id' => 19 ],
        'knowledge_criminal' => [ 'action' => 'create', 'object_id' => 0 ],
    ],
];
$coverage = \AZnet\Theme\provisioning_editorial_coverage_recommendations( $recommendations, [] );
assert( 'SKIP_HAS_PUBLIC_CONTENT' === $coverage['knowledge_business']['state'] );
assert( 'SUGGESTED_CREATE' === $coverage['knowledge_civil']['state'] );
assert( 'SUGGESTED_CREATE' === $coverage['knowledge_criminal']['state'] );

echo "PASS: Law v1.1 starter editorial pack and coverage rules\n";
