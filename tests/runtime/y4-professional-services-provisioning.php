<?php
/**
 * Y4 Professional Services provisioning lifecycle runtime scenarios.
 * Execute with wp eval-file and AZNET_Y4_SCENARIO=clean|rerun|rollback|active.
 */

function aznet_y4_must( bool $ok, string $message ): void {
    if ( ! $ok ) {
        throw new RuntimeException( $message );
    }
}

function aznet_y4_reset_site(): void {
    foreach ( get_posts( [
        'post_type'      => [ 'post', 'page', 'attachment' ],
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
    ] ) as $id ) {
        wp_delete_post( (int) $id, true );
    }

    foreach ( (array) wp_get_nav_menus() as $menu ) {
        wp_delete_nav_menu( (int) $menu->term_id );
    }

    update_option( 'show_on_front', 'posts' );
    update_option( 'page_on_front', 0 );
    set_theme_mod( 'nav_menu_locations', [] );
    set_theme_mod( 'aznet_theme_settings', AZnet\Theme\normalize_settings( [] ) );
}

function aznet_y4_selection(): array {
    $blueprint = AZnet\Theme\provisioning_blueprint( 'professional-services-v1' );
    aznet_y4_must( is_array( $blueprint ), 'professional-services-v1 blueprint unavailable' );

    $pages = [];
    foreach ( $blueprint['pages'] as $role => $definition ) {
        $pages[ $role ] = [ 'action' => 'create', 'object_id' => 0 ];
    }

    return [
        'pages'      => $pages,
        'categories' => [],
        'menu'       => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];
}

function aznet_y4_plan( array $selection ): array {
    $plan = AZnet\Theme\provisioning_build_plan(
        'professional-services-v1',
        $selection,
        AZnet\Theme\provisioning_discovery()
    );
    $validation = AZnet\Theme\provisioning_validate_plan( $plan );
    aznet_y4_must( true === $validation['ok'], implode( '; ', $validation['errors'] ) );
    return $plan;
}

function aznet_y4_owned_pages(): array {
    return get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'   => '_aznet_theme_provisioned_by',
                'value' => 'professional-services-v1',
            ],
        ],
    ] );
}

function aznet_y4_seed_page( string $title, string $content ): int {
    $id = wp_insert_post( [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_content' => $content,
    ], true );
    if ( is_wp_error( $id ) ) {
        throw new RuntimeException( $id->get_error_message() );
    }
    return (int) $id;
}

$scenario = (string) getenv( 'AZNET_Y4_SCENARIO' );
aznet_y4_reset_site();

if ( 'clean' === $scenario ) {
    $result = AZnet\Theme\provisioning_apply_plan( aznet_y4_plan( aznet_y4_selection() ) );
    aznet_y4_must( true === $result['ok'], implode( '; ', (array) $result['errors'] ) );

    $pages = aznet_y4_owned_pages();
    aznet_y4_must( 5 === count( $pages ), 'clean run must create exactly five starter Pages' );
    foreach ( $pages as $page ) {
        aznet_y4_must( '' !== trim( (string) $page->post_content ), 'starter Page content must remain editable WordPress post_content' );
        foreach ( (array) get_post_meta( $page->ID ) as $key => $value ) {
            aznet_y4_must( ! str_starts_with( (string) $key, '_rootprofile' ), 'provider-private RootProfile metadata leaked into starter Page' );
            aznet_y4_must( ! str_starts_with( (string) $key, '_convertflow' ), 'provider-private ConvertFlow metadata leaked into starter Page' );
        }
    }

    $front_id = (int) get_option( 'page_on_front', 0 );
    aznet_y4_must( $front_id > 0, 'clean run did not set a static Front Page' );
    aznet_y4_must( 'page' === get_option( 'show_on_front' ), 'clean run did not enable a static Front Page' );
    aznet_y4_must( 'off' === AZnet\Theme\setting( 'homepage_preset', 'off' ), 'generic blueprint must keep homepage_preset off' );
    echo "PASS: Y4 clean-site confirmed provisioning\n";
    return;
}

if ( 'rerun' === $scenario ) {
    $selection = aznet_y4_selection();
    $first = AZnet\Theme\provisioning_apply_plan( aznet_y4_plan( $selection ) );
    aznet_y4_must( true === $first['ok'], implode( '; ', (array) $first['errors'] ) );
    $first_ids = array_map( static fn( WP_Post $page ): int => (int) $page->ID, aznet_y4_owned_pages() );
    sort( $first_ids );

    $second = AZnet\Theme\provisioning_apply_plan( aznet_y4_plan( $selection ) );
    aznet_y4_must( true === $second['ok'], implode( '; ', (array) $second['errors'] ) );
    $second_ids = array_map( static fn( WP_Post $page ): int => (int) $page->ID, aznet_y4_owned_pages() );
    sort( $second_ids );
    aznet_y4_must( $first_ids === $second_ids, 'second confirmed run duplicated role-owned starter Pages' );
    echo "PASS: Y4 provisioning rerun is idempotent\n";
    return;
}

if ( 'rollback' === $scenario ) {
    $existing = aznet_y4_seed_page( 'Existing Keep', 'EXISTING CONTENT MUST SURVIVE' );
    define( 'AZNET_THEME_TEST_PROVISIONING_FAILURE', 'page:services:create' );
    $result = AZnet\Theme\provisioning_apply_plan( aznet_y4_plan( aznet_y4_selection() ) );
    aznet_y4_must( false === $result['ok'], 'injected Y4 failure unexpectedly passed' );
    aznet_y4_must( true === ( $result['rollback_ok'] ?? false ), implode( '; ', (array) $result['errors'] ) );
    aznet_y4_must( 0 === count( aznet_y4_owned_pages() ), 'current-run Y4 Pages survived rollback' );
    aznet_y4_must( 'EXISTING CONTENT MUST SURVIVE' === get_post( $existing )->post_content, 'pre-existing Page was changed by rollback' );
    echo "PASS: Y4 current-run rollback preserves pre-existing content\n";
    return;
}

if ( 'active' === $scenario ) {
    $home = aznet_y4_seed_page( 'Existing Home', 'KEEP ACTIVE HOME' );
    aznet_y4_seed_page( 'Existing About', 'KEEP ACTIVE ABOUT' );
    wp_insert_post( [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'Existing Article',
        'post_content' => 'KEEP ACTIVE ARTICLE',
    ] );
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home );
    $menu_id = wp_create_nav_menu( 'Existing Primary' );
    aznet_y4_must( ! is_wp_error( $menu_id ), 'existing primary menu fixture failed' );
    set_theme_mod( 'nav_menu_locations', [ 'primary' => (int) $menu_id ] );

    $state = AZnet\Theme\provisioning_discovery();
    aznet_y4_must( 'EXISTING_ACTIVE' === AZnet\Theme\provisioning_site_mode( $state ), 'active fixture was not classified EXISTING_ACTIVE' );

    $result = AZnet\Theme\provisioning_apply_plan( aznet_y4_plan( aznet_y4_selection() ) );
    aznet_y4_must( true === $result['ok'], implode( '; ', (array) $result['errors'] ) );
    aznet_y4_must( 'KEEP ACTIVE HOME' === get_post( $home )->post_content, 'existing active Front Page was overwritten' );

    $owned = aznet_y4_owned_pages();
    foreach ( $owned as $page ) {
        aznet_y4_must( 'draft' === $page->post_status, 'existing-active starter Page must not be silently published: #' . $page->ID );
    }
    echo "PASS: Y4 existing-active provisioning is bounded and non-destructive\n";
    return;
}

throw new RuntimeException( 'Unknown AZNET_Y4_SCENARIO: ' . $scenario );
