<?php
/** Runtime scenarios for Law Site Provisioning v1. Executed with `wp eval-file`. */

function aznet_provision_must( bool $ok, string $message ): void {
    if ( ! $ok ) {
        throw new RuntimeException( $message );
    }
}

function aznet_provision_page( string $title, string $content ): int {
    $id = wp_insert_post(
        [
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_title'   => $title,
            'post_content' => $content,
        ],
        true
    );
    if ( is_wp_error( $id ) ) {
        throw new RuntimeException( $id->get_error_message() );
    }
    return (int) $id;
}

function aznet_provision_full_create_selection(): array {
    $blueprint = AZnet\Theme\provisioning_blueprint( 'law01-v1' );
    $pages = [];
    foreach ( $blueprint['pages'] as $role => $definition ) {
        $pages[ $role ] = [ 'action' => 'create', 'object_id' => 0 ];
    }
    $categories = [];
    foreach ( $blueprint['categories'] as $role => $definition ) {
        $categories[ $role ] = [ 'action' => 'create', 'object_id' => 0 ];
    }
    return [
        'pages'      => $pages,
        'categories' => $categories,
        'menu'       => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];
}

function aznet_provision_apply( array $selection ): array {
    $plan = AZnet\Theme\provisioning_build_plan( 'law01-v1', $selection, AZnet\Theme\provisioning_discovery() );
    return AZnet\Theme\provisioning_apply_plan( $plan );
}

$scenario = (string) getenv( 'AZNET_PROVISION_SCENARIO' );

if ( 'existing' === $scenario ) {
    $home     = aznet_provision_page( 'Existing Home', 'KEEP HOME' );
    $about    = aznet_provision_page( 'Existing About', 'KEEP ABOUT' );
    $services = aznet_provision_page( 'Existing Services', 'KEEP SERVICES' );
    $contact  = aznet_provision_page( 'Existing Contact', 'KEEP CONTACT' );
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home );
    $menu = wp_create_nav_menu( 'Existing Menu' );
    aznet_provision_must( ! is_wp_error( $menu ), 'existing menu fixture failed' );
    $locations = get_theme_mod( 'nav_menu_locations', [] );
    $locations['primary'] = (int) $menu;
    set_theme_mod( 'nav_menu_locations', $locations );
    $before = [
        $home     => get_post( $home )->post_content,
        $about    => get_post( $about )->post_content,
        $services => get_post( $services )->post_content,
        $contact  => get_post( $contact )->post_content,
    ];
    $selection = [
        'pages' => [
            'home'     => [ 'action' => 'reuse', 'object_id' => $home ],
            'about'    => [ 'action' => 'reuse', 'object_id' => $about ],
            'services' => [ 'action' => 'reuse', 'object_id' => $services ],
            'contact'  => [ 'action' => 'reuse', 'object_id' => $contact ],
        ],
        'categories' => [],
        'menu'       => [ 'action' => 'reuse', 'menu_id' => (int) $menu ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];
    $result = aznet_provision_apply( $selection );
    aznet_provision_must( true === $result['ok'], implode( '; ', $result['errors'] ) );
    aznet_provision_must( 0 === count( $result['created'] ), 'existing-site path created content' );
    foreach ( $before as $id => $content ) {
        aznet_provision_must( get_post( $id )->post_content === $content, 'existing content overwritten' );
    }
    aznet_provision_must( AZnet\Theme\settings()['homepage_about_page'] === $about, 'explicit reuse not mapped' );
    echo "PASS: existing-site no-overwrite\n";
    return;
}

if ( 'rerun' === $scenario ) {
    $selection = aznet_provision_full_create_selection();
    $first = aznet_provision_apply( $selection );
    aznet_provision_must( true === $first['ok'], implode( '; ', $first['errors'] ) );
    $second = aznet_provision_apply( $selection );
    aznet_provision_must( true === $second['ok'], implode( '; ', $second['errors'] ) );
    aznet_provision_must( 0 === count( $second['created'] ), 'rerun created duplicate objects' );
    $pages = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ] ],
    ] );
    aznet_provision_must( 13 === count( $pages ), 'provisioned Page count changed' );
    $menus = get_terms( [
        'taxonomy'   => 'nav_menu',
        'hide_empty' => false,
        'meta_query' => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ] ],
    ] );
    aznet_provision_must( ! is_wp_error( $menus ) && 1 === count( $menus ), 'provisioned menu duplicated' );
    echo "PASS: successful rerun idempotent\n";
    return;
}

if ( 'rollback' === $scenario ) {
    $pre = [
        'show'      => get_option( 'show_on_front' ),
        'front'     => (int) get_option( 'page_on_front' ),
        'settings'  => AZnet\Theme\settings(),
        'locations' => get_theme_mod( 'nav_menu_locations', [] ),
    ];
    define( 'AZNET_THEME_TEST_PROVISIONING_FAILURE', 'homepage:map' );
    $result = aznet_provision_apply( aznet_provision_full_create_selection() );
    aznet_provision_must( false === $result['ok'], 'injected failure unexpectedly passed' );
    aznet_provision_must( true === ( $result['rollback_ok'] ?? false ), implode( '; ', $result['errors'] ) );
    aznet_provision_must( get_option( 'show_on_front' ) === $pre['show'] && (int) get_option( 'page_on_front' ) === $pre['front'], 'Front Page rollback failed' );
    aznet_provision_must( AZnet\Theme\settings() === $pre['settings'], 'settings rollback failed' );
    aznet_provision_must( get_theme_mod( 'nav_menu_locations', [] ) === $pre['locations'], 'menu locations rollback failed' );
    $pages = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1' ] ],
    ] );
    aznet_provision_must( 0 === count( $pages ), 'current-run Pages survived rollback' );
    echo "PASS: injected mid-run rollback bounded\n";
    return;
}

if ( 'user-edit' === $scenario ) {
    $selection = aznet_provision_full_create_selection();
    $first = aznet_provision_apply( $selection );
    aznet_provision_must( true === $first['ok'], implode( '; ', $first['errors'] ) );
    $about = (int) AZnet\Theme\settings()['homepage_about_page'];
    wp_update_post( [ 'ID' => $about, 'post_content' => 'USER EDIT MUST SURVIVE' ] );
    $second = aznet_provision_apply( $selection );
    aznet_provision_must( true === $second['ok'], implode( '; ', $second['errors'] ) );
    aznet_provision_must( 'USER EDIT MUST SURVIVE' === get_post( $about )->post_content, 'rerun overwrote user edit' );
    echo "PASS: user edit preserved on rerun\n";
    return;
}

throw new RuntimeException( 'Unknown AZNET_PROVISION_SCENARIO: ' . $scenario );
