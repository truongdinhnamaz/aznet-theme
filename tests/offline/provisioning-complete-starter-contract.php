<?php
declare(strict_types=1);

// RED/GREEN contract for the owner-approved Complete Starter Site behavior.
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }
$root = dirname( __DIR__, 2 );
require_once $root . '/inc/theme/provisioning-blueprints.php';
require_once $root . '/inc/theme/provisioning-recommendations.php';
require_once $root . '/inc/theme/provisioning-editorial.php';
require_once $root . '/inc/theme/provisioning-plan.php';

$keys = \AZnet\Theme\provisioning_blueprint_keys();
assert( in_array( 'law01-v1', $keys, true ) );
assert( in_array( 'law01-v1-1', $keys, true ) );
assert( in_array( 'law01-v1-2', $keys, true ), 'Complete Starter Site requires an additive blueprint key; registered v1/v1.1 blueprints must not be rewritten.' );

$bp = \AZnet\Theme\provisioning_blueprint( 'law01-v1-2' );
assert( is_array( $bp ), 'law01-v1-2 blueprint must exist.' );
assert( 'law01-v1-2' === ( $bp['key'] ?? '' ) );
assert( 'law-01' === ( $bp['homepage_preset'] ?? '' ) );
assert( 'burgundy-gold' === ( $bp['homepage_variant'] ?? '' ), 'Complete Starter Site should select the approved Burgundy + Gold presentation.' );
assert( 'ABC Lawyer' === ( $bp['site_defaults']['blogname'] ?? '' ), 'Starter site needs a visible example firm name without requiring a Site Title edit.' );
assert( '' !== trim( (string) ( $bp['site_defaults']['blogdescription'] ?? '' ) ), 'Starter site needs a non-empty example slogan/tagline.' );

$page_roles = [ 'hero','home','about','services','service_business','service_civil','service_criminal','service_real_estate','service_family','service_labor','team','process','faq','contact' ];
assert( array_keys( (array) $bp['pages'] ) === $page_roles );
foreach ( $page_roles as $role ) {
    $page = (array) ( $bp['pages'][ $role ] ?? [] );
    assert( '' !== trim( (string) ( $page['title'] ?? '' ) ), "Missing complete starter title: {$role}" );
    assert( '' !== trim( (string) ( $page['excerpt'] ?? '' ) ), "Missing complete starter excerpt: {$role}" );
    assert( '' !== trim( (string) ( $page['content'] ?? '' ) ), "Missing complete starter content: {$role}" );
}

$public_copy = json_encode( $bp['pages'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
foreach ( [
    'Hãy thay nội dung này',
    'Hãy bổ sung thông tin đội ngũ',
    'Hãy cập nhật thông tin liên hệ',
    'Nội dung giới thiệu tổng quan có thể được chỉnh sửa',
    'Quy trình starter',
    'Trang liên hệ để bổ sung',
] as $placeholder ) {
    assert( ! str_contains( (string) $public_copy, $placeholder ), "Complete Starter Site must not expose editor-instruction placeholder copy: {$placeholder}" );
}
foreach ( [ '500+','98%','1000+','tỷ lệ thắng','hàng đầu','luật sư Nguyễn','090','@example','khách hàng nói','giải thưởng' ] as $fabricated ) {
    assert( ! str_contains( (string) $public_copy, $fabricated ), "Complete Starter Site must not fabricate trust/contact/person data: {$fabricated}" );
}
assert( str_contains( (string) $bp['pages']['home']['excerpt'], 'cá nhân' ) );
assert( str_contains( (string) $bp['pages']['home']['excerpt'], 'doanh nghiệp' ) );
assert( str_contains( (string) $bp['pages']['services']['excerpt'], 'dịch vụ pháp lý' ) );
assert( str_contains( (string) $bp['pages']['team']['content'], 'phối hợp' ), 'Team starter content should be complete without inventing named people.' );
assert( str_contains( (string) $bp['pages']['contact']['content'], 'yêu cầu tư vấn' ), 'Contact starter content should be useful without inventing phone/email.' );

function aznet_complete_starter_discovery( bool $active = false ): array {
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
            'hero' => 0, 'services' => 0, 'about' => 0, 'team' => 0, 'knowledge' => [],
            'case_analysis' => 0, 'legal_news' => 0, 'process' => 0, 'faq' => 0, 'contact' => 0,
        ],
        'primary_menu_id' => 0,
        'pages' => [],
        'categories' => [],
    ];
}

function aznet_complete_starter_selections(): array {
    $bp = \AZnet\Theme\provisioning_blueprint( 'law01-v1-2' );
    $pages = [];
    foreach ( array_keys( (array) $bp['pages'] ) as $role ) { $pages[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $categories = [];
    foreach ( array_keys( (array) $bp['categories'] ) as $role ) { $categories[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $starter_posts = [];
    foreach ( array_keys( (array) ( $bp['editorial_examples']['items'] ?? [] ) ) as $role ) { $starter_posts[ $role ] = [ 'action' => 'create' ]; }
    return [
        'pages' => $pages,
        'categories' => $categories,
        'menu' => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
        'starter_posts' => $starter_posts,
        'starter_media' => [ 'hero' => true, 'editorial-1' => true, 'editorial-2' => true, 'editorial-3' => true, 'editorial-4' => true ],
        'starter_publication' => [ 'publish' => true, 'discourage_indexing' => true ],
        'starter_site_defaults' => [ 'apply' => true ],
    ];
}

$new_plan = \AZnet\Theme\provisioning_build_plan( 'law01-v1-2', aznet_complete_starter_selections(), aznet_complete_starter_discovery( false ) );
$types = array_column( $new_plan['operations'], 'type' );
assert( in_array( 'set_starter_site_defaults', $types, true ), 'New/mostly-empty Complete Starter Site should explicitly plan starter Site Title + Tagline.' );
assert( in_array( 'set_homepage_variant', $types, true ), 'Complete Starter Site should explicitly plan the Burgundy + Gold presentation variant.' );
$site_ops = array_values( array_filter( $new_plan['operations'], static fn( array $op ): bool => 'set_starter_site_defaults' === ( $op['type'] ?? '' ) ) );
assert( 1 === count( $site_ops ) );
assert( 'ABC Lawyer' === ( $site_ops[0]['blogname'] ?? '' ) );
assert( '' !== (string) ( $site_ops[0]['blogdescription'] ?? '' ) );
$variant_ops = array_values( array_filter( $new_plan['operations'], static fn( array $op ): bool => 'set_homepage_variant' === ( $op['type'] ?? '' ) ) );
assert( 1 === count( $variant_ops ) );
assert( 'burgundy-gold' === ( $variant_ops[0]['variant'] ?? '' ) );
assert( true === \AZnet\Theme\provisioning_validate_plan( $new_plan )['ok'] );
$hero_media_ops = array_values( array_filter( $new_plan['operations'], static fn( array $op ): bool => 'assign_featured_media' === ( $op['type'] ?? '' ) && 'hero' === ( $op['media_role'] ?? '' ) ) );
assert( 1 === count( $hero_media_ops ) );
assert( 'hero' === ( $hero_media_ops[0]['target_role'] ?? '' ), 'Complete Starter Site Hero media must target the dedicated Hero Page, not the Front Page.' );


$active_plan = \AZnet\Theme\provisioning_build_plan( 'law01-v1-2', aznet_complete_starter_selections(), aznet_complete_starter_discovery( true ) );
assert( ! in_array( 'set_starter_site_defaults', array_column( $active_plan['operations'], 'type' ), true ), 'Existing active sites must not receive starter identity defaults.' );
assert( in_array( 'set_homepage_variant', array_column( $active_plan['operations'], 'type' ), true ), 'Explicitly choosing Complete Starter Site may still apply Theme-owned presentation.' );

$v11 = \AZnet\Theme\provisioning_blueprint( 'law01-v1-1' );
assert( ! isset( $v11['site_defaults'] ), 'Registered v1.1 blueprint must remain unchanged.' );
assert( ! isset( $v11['homepage_variant'] ), 'Registered v1.1 presentation behavior must remain unchanged.' );

$runner = file_get_contents( $root . '/inc/theme/provisioning-runner.php' );
$admin = file_get_contents( $root . '/inc/admin/provisioning.php' );
$readiness = file_get_contents( $root . '/inc/theme/provisioning-readiness.php' );
foreach ( [ "'set_starter_site_defaults'", "'set_homepage_variant'", "'blogname'", "'blogdescription'", "'starter_identity_changed'" ] as $needle ) {
    assert( str_contains( (string) $runner, $needle ), "Runner must support and roll back Complete Starter Site state: {$needle}" );
}
assert( str_contains( (string) $admin, "'law01-v1-2'" ) && str_contains( (string) $admin, "provisioning_blueprint( \$blueprint_key )" ), 'Provisioning wizard must keep Complete Starter Site as the default while supporting explicit blueprint selection.' );
assert( str_contains( (string) $admin, 'apply_starter_site_defaults' ), 'Provisioning wizard must visibly opt in to example Site Title/Tagline on new sites.' );
assert( str_contains( (string) $readiness, "'law01-v1-2'" ), 'Launch warnings must continue to recognize v1.2 starter provenance.' );

echo "PASS: Law 01 Complete Starter Site blueprint + plan + apply boundary contract\n";