<?php
/**
 * Y5 integrated client-delivery runtime fixture and continuity verifier.
 *
 * Execute with wp eval-file and AZNET_Y5_MODE=setup|continuity|smoke.
 * AZNET_Y5_STATE_DIR controls the evidence/state directory.
 */

declare( strict_types=1 );

function aznet_y5_must( bool $condition, string $message ): void {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
}

function aznet_y5_state_dir(): string {
    $dir = (string) getenv( 'AZNET_Y5_STATE_DIR' );
    return '' !== $dir ? rtrim( $dir, '/' ) : '/tmp/y5-client-delivery';
}

function aznet_y5_state_file(): string {
    return aznet_y5_state_dir() . '/site-state.json';
}

function aznet_y5_reset_site(): void {
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
    update_option( 'page_for_posts', 0 );
    update_option( 'blogname', 'AZnet Y5 Professional Services' );
    update_option( 'blogdescription', 'Nội dung kiểm thử WordPress-native cho Client Delivery System' );
    set_theme_mod( 'nav_menu_locations', [] );
    set_theme_mod( 'aznet_theme_settings', AZnet\Theme\normalize_settings( [] ) );
}

function aznet_y5_selection(): array {
    $blueprint = AZnet\Theme\provisioning_blueprint( 'professional-services-v1' );
    aznet_y5_must( is_array( $blueprint ), 'professional-services-v1 blueprint unavailable' );

    $pages = [];
    foreach ( $blueprint['pages'] as $role => $definition ) {
        $pages[ (string) $role ] = [ 'action' => 'create', 'object_id' => 0 ];
    }

    return [
        'pages'      => $pages,
        'categories' => [],
        'menu'       => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
    ];
}

function aznet_y5_apply_blueprint(): void {
    $plan = AZnet\Theme\provisioning_build_plan(
        'professional-services-v1',
        aznet_y5_selection(),
        AZnet\Theme\provisioning_discovery()
    );
    $validation = AZnet\Theme\provisioning_validate_plan( $plan );
    aznet_y5_must( true === $validation['ok'], implode( '; ', (array) $validation['errors'] ) );
    $result = AZnet\Theme\provisioning_apply_plan( $plan );
    aznet_y5_must( true === $result['ok'], implode( '; ', (array) $result['errors'] ) );
}

function aznet_y5_role_page_id( string $role ): int {
    $ids = get_posts( [
        'post_type'      => 'page',
        'post_status'    => 'any',
        'posts_per_page' => 2,
        'fields'         => 'ids',
        'meta_query'     => [
            [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'professional-services-v1' ],
            [ 'key' => '_aznet_theme_provisioning_role', 'value' => $role ],
        ],
    ] );
    aznet_y5_must( 1 === count( $ids ), 'Expected exactly one provisioned Page for role ' . $role );
    return (int) $ids[0];
}

function aznet_y5_insert_page( string $title, string $content, int $parent = 0, string $template = 'default' ): int {
    $id = wp_insert_post( [
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'post_title'   => $title,
        'post_content' => $content,
        'post_parent'  => $parent,
    ], true );
    if ( is_wp_error( $id ) ) {
        throw new RuntimeException( $id->get_error_message() );
    }
    update_post_meta( (int) $id, '_wp_page_template', $template );
    return (int) $id;
}

function aznet_y5_menu( string $name, array $page_ids ): int {
    $menu_id = wp_create_nav_menu( $name );
    if ( is_wp_error( $menu_id ) ) {
        throw new RuntimeException( $menu_id->get_error_message() );
    }

    foreach ( $page_ids as $page_id ) {
        $item_id = wp_update_nav_menu_item( (int) $menu_id, 0, [
            'menu-item-object-id' => (int) $page_id,
            'menu-item-object'    => 'page',
            'menu-item-type'      => 'post_type',
            'menu-item-status'    => 'publish',
        ] );
        if ( is_wp_error( $item_id ) ) {
            throw new RuntimeException( $item_id->get_error_message() );
        }
    }
    return (int) $menu_id;
}

function aznet_y5_track_post( int $id ): array {
    $post = get_post( $id );
    aznet_y5_must( $post instanceof WP_Post, 'Tracked post missing #' . $id );
    return [
        'id'           => $id,
        'type'         => (string) $post->post_type,
        'status'       => (string) $post->post_status,
        'title'        => (string) $post->post_title,
        'content_sha'  => hash( 'sha256', (string) $post->post_content ),
        'parent'       => (int) $post->post_parent,
        'template'     => (string) get_post_meta( $id, '_wp_page_template', true ),
        'permalink'    => (string) get_permalink( $id ),
    ];
}

function aznet_y5_write_state( array $state ): void {
    $dir = aznet_y5_state_dir();
    if ( ! is_dir( $dir ) && ! mkdir( $dir, 0777, true ) && ! is_dir( $dir ) ) {
        throw new RuntimeException( 'Unable to create Y5 state directory: ' . $dir );
    }
    $encoded = wp_json_encode( $state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    aznet_y5_must( is_string( $encoded ), 'Unable to encode Y5 state' );
    file_put_contents( aznet_y5_state_file(), $encoded . "\n" );
}

function aznet_y5_read_state(): array {
    $file = aznet_y5_state_file();
    aznet_y5_must( is_file( $file ), 'Y5 state file missing: ' . $file );
    $state = json_decode( (string) file_get_contents( $file ), true );
    aznet_y5_must( is_array( $state ), 'Y5 state file is invalid JSON' );
    return $state;
}

function aznet_y5_assert_continuity( array $state ): void {
    aznet_y5_must( 'page' === get_option( 'show_on_front' ), 'Static Front Page mode changed' );
    aznet_y5_must( (int) $state['options']['page_on_front'] === (int) get_option( 'page_on_front', 0 ), 'Front Page ID changed' );
    aznet_y5_must( (int) $state['options']['page_for_posts'] === (int) get_option( 'page_for_posts', 0 ), 'Posts Page ID changed' );

    foreach ( (array) $state['posts'] as $role => $tracked ) {
        $post = get_post( (int) $tracked['id'] );
        aznet_y5_must( $post instanceof WP_Post, 'Tracked WordPress content disappeared: ' . $role );
        aznet_y5_must( (string) $tracked['type'] === (string) $post->post_type, 'Post type changed for ' . $role );
        aznet_y5_must( (string) $tracked['content_sha'] === hash( 'sha256', (string) $post->post_content ), 'post_content bytes changed for ' . $role );
        aznet_y5_must( (int) $tracked['parent'] === (int) $post->post_parent, 'Parent changed for ' . $role );
        if ( 'page' === $post->post_type ) {
            aznet_y5_must( (string) $tracked['template'] === (string) get_post_meta( $post->ID, '_wp_page_template', true ), 'Page template changed for ' . $role );
        }
    }

    foreach ( (array) $state['menus'] as $location => $tracked ) {
        $menu = wp_get_nav_menu_object( (int) $tracked['id'] );
        aznet_y5_must( $menu instanceof WP_Term, 'Tracked WordPress menu disappeared: ' . $location );
        $items = wp_get_nav_menu_items( (int) $tracked['id'] );
        aznet_y5_must( is_array( $items ), 'Unable to read tracked menu items: ' . $location );
        aznet_y5_must( (int) $tracked['item_count'] === count( $items ), 'Menu item count changed: ' . $location );
    }
}

$mode = (string) getenv( 'AZNET_Y5_MODE' );

if ( 'setup' === $mode ) {
    aznet_y5_must( function_exists( 'AZnet\\Theme\\provisioning_blueprint' ), 'AZnet Theme provisioning API unavailable' );
    aznet_y5_must( 'aznet-theme' === wp_get_theme()->get_stylesheet(), 'Y5 setup requires AZnet Theme active' );
    aznet_y5_must( [] === array_values( array_filter( (array) get_option( 'active_plugins', [] ) ) ), 'Y5 Standalone Core fixture requires zero active third-party plugins' );

    aznet_y5_reset_site();
    aznet_y5_apply_blueprint();

    $home     = aznet_y5_role_page_id( 'home' );
    $about    = aznet_y5_role_page_id( 'about' );
    $services = aznet_y5_role_page_id( 'services' );
    $team     = aznet_y5_role_page_id( 'team' );
    $contact  = aznet_y5_role_page_id( 'contact' );

    update_post_meta( $services, '_wp_page_template', 'page-templates/wide.php' );
    foreach ( [ $about, $team, $contact ] as $standard_page ) {
        update_post_meta( $standard_page, '_wp_page_template', 'default' );
    }

    $about_child = aznet_y5_insert_page(
        'Hồ sơ năng lực',
        '<!-- wp:paragraph --><p>Nội dung kiểm thử breadcrumb thuộc WordPress Page và có thể chỉnh sửa.</p><!-- /wp:paragraph -->',
        $about,
        'default'
    );
    $landing = aznet_y5_insert_page(
        'Trang đích tư vấn',
        '<!-- wp:paragraph --><p>Nội dung Landing kiểm thử thuộc WordPress Page.</p><!-- /wp:paragraph -->',
        0,
        'page-templates/landing.php'
    );
    $blog = aznet_y5_insert_page( 'Bài viết', '<!-- wp:paragraph --><p>WordPress Posts Page.</p><!-- /wp:paragraph -->' );
    update_option( 'page_for_posts', $blog );

    $post_id = wp_insert_post( [
        'post_type'    => 'post',
        'post_status'  => 'publish',
        'post_title'   => 'Y5 Integration Article',
        'post_content' => '<!-- wp:paragraph --><p>Y5_SENTINEL representative native WordPress Post content.</p><!-- /wp:paragraph -->',
    ], true );
    if ( is_wp_error( $post_id ) ) {
        throw new RuntimeException( $post_id->get_error_message() );
    }
    $post_id = (int) $post_id;

    $primary = aznet_y5_menu( 'Y5 Primary', [ $home, $about, $services, $team, $contact, $blog ] );
    $footer = aznet_y5_menu( 'Y5 Footer', [ $about, $services, $team ] );
    $footer_contact = aznet_y5_menu( 'Y5 Footer Contact', [ $contact ] );
    $footer_policy = aznet_y5_menu( 'Y5 Footer Policy', [ $landing ] );
    $locations = [
        'primary'        => $primary,
        'footer'         => $footer,
        'footer-contact' => $footer_contact,
        'footer-policy'  => $footer_policy,
    ];
    set_theme_mod( 'nav_menu_locations', $locations );

    $settings = (array) get_theme_mod( 'aznet_theme_settings', [] );
    $settings['footer_preset'] = 'standard';
    $settings['homepage_preset'] = 'off';
    set_theme_mod( 'aznet_theme_settings', AZnet\Theme\normalize_settings( $settings ) );

    aznet_y5_must( 'wide' === AZnet\Theme\page_variant( $services ), 'Services Page must use Wide presentation' );
    aznet_y5_must( 'landing' === AZnet\Theme\page_variant( $landing ), 'Landing fixture must use Landing presentation' );
    aznet_y5_must( 'standard' === AZnet\Theme\page_variant( $about ), 'About Page must use Standard presentation' );
    $crumbs = AZnet\Theme\page_breadcrumb_items( $about_child );
    aznet_y5_must( 1 === count( $crumbs ) && 'Giới thiệu' === (string) $crumbs[0]['title'], 'About child breadcrumb did not resolve through WordPress ancestry' );

    $posts = [
        'home'        => aznet_y5_track_post( $home ),
        'about'       => aznet_y5_track_post( $about ),
        'about_child' => aznet_y5_track_post( $about_child ),
        'services'    => aznet_y5_track_post( $services ),
        'team'        => aznet_y5_track_post( $team ),
        'contact'     => aznet_y5_track_post( $contact ),
        'landing'     => aznet_y5_track_post( $landing ),
        'blog'        => aznet_y5_track_post( $blog ),
        'post'        => aznet_y5_track_post( $post_id ),
    ];

    $menus = [];
    foreach ( $locations as $location => $menu_id ) {
        $items = wp_get_nav_menu_items( $menu_id );
        $menus[ $location ] = [
            'id'         => $menu_id,
            'item_count' => is_array( $items ) ? count( $items ) : 0,
        ];
    }

    $state = [
        'wordpress_version' => get_bloginfo( 'version' ),
        'theme_version'     => defined( 'AZNET_THEME_VERSION' ) ? AZNET_THEME_VERSION : '',
        'posts'             => $posts,
        'menus'             => $menus,
        'options'           => [
            'show_on_front'  => get_option( 'show_on_front' ),
            'page_on_front'  => (int) get_option( 'page_on_front', 0 ),
            'page_for_posts' => (int) get_option( 'page_for_posts', 0 ),
        ],
        'routes'            => [
            'home'        => home_url( '/' ),
            'about'       => (string) get_permalink( $about ),
            'about_child' => (string) get_permalink( $about_child ),
            'services'    => (string) get_permalink( $services ),
            'team'        => (string) get_permalink( $team ),
            'contact'     => (string) get_permalink( $contact ),
            'landing'     => (string) get_permalink( $landing ),
            'blog'        => (string) get_permalink( $blog ),
            'post'        => (string) get_permalink( $post_id ),
            'search'      => home_url( '/?s=Y5_SENTINEL' ),
            '404'         => home_url( '/y5-intentional-missing-route/' ),
        ],
    ];

    aznet_y5_write_state( $state );
    aznet_y5_assert_continuity( $state );
    echo "PASS: Y5 clean WordPress 6.9 client-delivery runtime fixture\n";
    return;
}

if ( 'continuity' === $mode ) {
    $state = aznet_y5_read_state();
    aznet_y5_assert_continuity( $state );
    echo "PASS: Y5 WordPress Page/Post/Menu continuity\n";
    return;
}

if ( 'smoke' === $mode ) {
    $state = aznet_y5_read_state();
    aznet_y5_assert_continuity( $state );
    aznet_y5_must( 'aznet-theme' === wp_get_theme()->get_stylesheet(), 'Y5 smoke requires AZnet Theme active' );
    aznet_y5_must( defined( 'AZNET_THEME_VERSION' ) && '1.2.0' === AZNET_THEME_VERSION, 'Y5 pre-promotion Theme version must remain 1.2.0' );
    aznet_y5_must( 'off' === AZnet\Theme\setting( 'homepage_preset', 'off' ), 'Professional Services Front Page must remain WordPress-native with homepage preset off' );
    aznet_y5_must( in_array( AZnet\Theme\footer_preset(), [ 'standard', 'professional', 'compact' ], true ), 'Footer preset normalization failed' );
    aznet_y5_must( [] === array_values( array_filter( (array) get_option( 'active_plugins', [] ) ) ), 'Y5 smoke must remain zero-plugin Standalone Core' );
    echo "PASS: Y5 AZnet Theme runtime smoke after lifecycle transition\n";
    return;
}

throw new RuntimeException( 'Unknown AZNET_Y5_MODE: ' . $mode );
