<?php
/** Empty-site post-wizard verification for Law Site Provisioning. */
function aznet_empty_must( bool $ok, string $message ): void {
    if ( ! $ok ) {
        throw new RuntimeException( $message );
    }
}
$pages = get_posts( [
    'post_type'      => 'page',
    'post_status'    => 'any',
    'posts_per_page' => -1,
    'meta_query'     => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ] ],
] );
aznet_empty_must( 13 === count( $pages ), 'expected 13 provisioned Pages, got ' . count( $pages ) );
foreach ( $pages as $page ) {
    aznet_empty_must( 'publish' === $page->post_status, 'starter structural Page not published' );
}
$services = get_posts( [
    'post_type'      => 'page',
    'post_status'    => 'any',
    'posts_per_page' => 1,
    'meta_query'     => [
        [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ],
        [ 'key' => '_aznet_theme_provisioning_role', 'value' => 'services' ],
    ],
] );
aznet_empty_must( isset( $services[0] ), 'services role missing' );
$children = get_posts( [
    'post_type'      => 'page',
    'post_status'    => 'publish',
    'post_parent'    => (int) $services[0]->ID,
    'posts_per_page' => -1,
] );
aznet_empty_must( 6 === count( $children ), 'expected 6 service children, got ' . count( $children ) );
$terms = get_terms( [
    'taxonomy'   => 'category',
    'hide_empty' => false,
    'meta_query' => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ] ],
] );
aznet_empty_must( ! is_wp_error( $terms ) && 9 === count( $terms ), 'expected 9 provisioned Categories' );
$settings = AZnet\Theme\settings();
aznet_empty_must( 'law-01' === $settings['homepage_preset'], 'Law01 preset not active' );
$services_source = (int) AZnet\Theme\homepage_source_value( 'law-01', 'services', $settings );
$about_source = (int) AZnet\Theme\homepage_source_value( 'law-01', 'about', $settings );
$team_source = (int) AZnet\Theme\homepage_source_value( 'law-01', 'team', $settings );
$contact_source = (int) AZnet\Theme\homepage_source_value( 'law-01', 'contact', $settings );
aznet_empty_must( $services_source > 0 && $about_source > 0 && $team_source > 0 && $contact_source > 0, 'required effective Homepage sources missing' );
$team = get_post( $team_source );
aznet_empty_must( $team instanceof WP_Post && 'Đội ngũ của chúng tôi' === $team->post_title, 'expected provisioned Team parent title' );
$team_children = get_posts( [
    'post_type'      => 'page',
    'post_status'    => 'any',
    'post_parent'    => (int) $team->ID,
    'posts_per_page' => -1,
] );
aznet_empty_must( 0 === count( $team_children ), 'provisioning must not create sample Team members' );
aznet_empty_must( 6 === count( (array) AZnet\Theme\homepage_source_value( 'law-01', 'knowledge', $settings ) ), 'knowledge mappings missing' );
aznet_empty_must( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) > 0, 'Front Page not assigned' );
$locations = get_nav_menu_locations();
aznet_empty_must( (int) ( $locations['primary'] ?? 0 ) > 0, 'Primary Menu not assigned' );
echo "PASS: empty-site Law01 provisioning state\n";
