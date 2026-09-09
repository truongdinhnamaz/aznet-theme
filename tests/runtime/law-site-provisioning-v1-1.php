<?php
/** Runtime scenarios for Law Site Provisioning v1.1. Execute with `wp eval-file`. */

function aznet_v11_must( bool $ok, string $message ): void {
    if ( ! $ok ) { throw new RuntimeException( $message ); }
}

function aznet_v11_reset_site(): void {
    foreach ( get_posts( [ 'post_type' => [ 'post', 'page', 'attachment' ], 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ] ) as $id ) {
        wp_delete_post( (int) $id, true );
    }
    $menus = wp_get_nav_menus();
    foreach ( is_array( $menus ) ? $menus : [] as $menu ) { wp_delete_nav_menu( (int) $menu->term_id ); }
    $terms = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false ] );
    foreach ( is_wp_error( $terms ) ? [] : $terms as $term ) {
        if ( 'uncategorized' !== $term->slug ) { wp_delete_term( (int) $term->term_id, 'category' ); }
    }
    update_option( 'show_on_front', 'posts' );
    update_option( 'page_on_front', 0 );
    update_option( 'blog_public', 1 );
    set_theme_mod( 'nav_menu_locations', [] );
    set_theme_mod( 'aznet_theme_settings', AZnet\Theme\normalize_settings( [] ) );
    delete_option( 'aznet_theme_provisioning_index_receipt' );
}

function aznet_v11_page( string $title, string $content ): int {
    $id = wp_insert_post( [ 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => $content ], true );
    if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
    return (int) $id;
}

function aznet_v11_post( string $title, int $category_id = 0 ): int {
    $id = wp_insert_post( [ 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $title, 'post_content' => 'Existing public content.' ], true );
    if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
    if ( $category_id > 0 ) { wp_set_post_categories( (int) $id, [ $category_id ] ); }
    return (int) $id;
}

function aznet_v11_full_selection( bool $publish = true, bool $discourage = true ): array {
    $bp = AZnet\Theme\provisioning_blueprint( 'law01-v1-1' );
    $pages = [];
    foreach ( $bp['pages'] as $role => $definition ) { $pages[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $categories = [];
    foreach ( $bp['categories'] as $role => $definition ) { $categories[ $role ] = [ 'action' => 'create', 'object_id' => 0 ]; }
    $starter_posts = [];
    foreach ( $bp['editorial_examples']['items'] as $role => $definition ) { $starter_posts[ $role ] = [ 'action' => 'create' ]; }
    return [
        'pages' => $pages,
        'categories' => $categories,
        'menu' => [ 'action' => 'create', 'menu_id' => 0 ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
        'starter_posts' => $starter_posts,
        'starter_media' => [ 'hero' => true, 'editorial-1' => true, 'editorial-2' => true, 'editorial-3' => true, 'editorial-4' => true ],
        'starter_publication' => [ 'publish' => $publish, 'discourage_indexing' => $discourage ],
    ];
}

function aznet_v11_apply( array $selection ): array {
    $plan = AZnet\Theme\provisioning_build_plan( 'law01-v1-1', $selection, AZnet\Theme\provisioning_discovery() );
    $validation = AZnet\Theme\provisioning_validate_plan( $plan );
    aznet_v11_must( true === $validation['ok'], implode( '; ', $validation['errors'] ) );
    return AZnet\Theme\provisioning_apply_plan( $plan );
}

function aznet_v11_owned_posts( string $post_type ): array {
    return get_posts( [
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => -1,
        'meta_query' => [ [ 'key' => '_aznet_theme_provisioned_by', 'value' => 'law01-v1-1' ] ],
    ] );
}

$scenario = (string) getenv( 'AZNET_PROVISION_V11_SCENARIO' );
aznet_v11_reset_site();

if ( 'new' === $scenario ) {
    $state = AZnet\Theme\provisioning_discovery();
    aznet_v11_must( 'NEW_OR_MOSTLY_EMPTY' === AZnet\Theme\provisioning_site_mode( $state ), 'fresh fixture was not classified NEW_OR_MOSTLY_EMPTY' );
    $plan = AZnet\Theme\provisioning_build_plan( 'law01-v1-1', aznet_v11_full_selection(), $state );
    $types = array_column( $plan['operations'], 'type' );
    $search_pos = array_search( 'set_search_visibility', $types, true );
    $post_pos = array_search( 'create_post', $types, true );
    aznet_v11_must( false !== $search_pos && false !== $post_pos && $search_pos < $post_pos, 'search visibility must precede public starter creation' );
    $result = AZnet\Theme\provisioning_apply_plan( $plan );
    aznet_v11_must( true === $result['ok'], implode( '; ', $result['errors'] ) );
    aznet_v11_must( 0 === (int) get_option( 'blog_public', 1 ), 'new-site starter publication did not discourage indexing' );
    $posts = aznet_v11_owned_posts( 'post' );
    aznet_v11_must( 8 === count( $posts ), 'expected exactly 8 starter Posts' );
    foreach ( $posts as $post ) { aznet_v11_must( 'publish' === $post->post_status, 'new-site starter Post was not published' ); }
    $front_id = (int) get_option( 'page_on_front', 0 );
    $thumb_id = (int) get_post_thumbnail_id( $front_id );
    aznet_v11_must( $front_id > 0 && $thumb_id > 0, 'Front Page starter Hero was not assigned' );
    $url = (string) wp_get_attachment_url( $thumb_id );
    aznet_v11_must( false !== strpos( $url, '/uploads/' ) && false === strpos( $url, '/themes/aznet-theme/' ), 'Hero runtime source is not WordPress uploads' );
    $s = AZnet\Theme\settings();
    $editorial_terms = array_merge( (array) $s['homepage_knowledge_terms'], [ (int) $s['homepage_case_analysis_term'], (int) $s['homepage_legal_news_term'] ] );
    foreach ( array_filter( array_map( 'intval', $editorial_terms ) ) as $term_id ) {
        aznet_v11_must( AZnet\Theme\provisioning_category_public_post_count( $term_id ) > 0, 'mapped editorial source has no public item: #' . $term_id );
    }
    aznet_v11_must( AZnet\Theme\provisioning_index_restore_available(), 'successful new-site run did not persist index ownership receipt' );
    echo "PASS: v1.1 new-site starter coverage/media/index safety\n";
    return;
}

if ( 'active' === $scenario ) {
    $home = aznet_v11_page( 'Existing Home', 'KEEP HOME' );
    $about = aznet_v11_page( 'Existing About', 'KEEP ABOUT' );
    $services = aznet_v11_page( 'Existing Services', 'KEEP SERVICES' );
    $contact = aznet_v11_page( 'Existing Contact', 'KEEP CONTACT' );
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home );
    $menu = wp_create_nav_menu( 'Existing Menu' );
    aznet_v11_must( ! is_wp_error( $menu ), 'existing menu fixture failed' );
    set_theme_mod( 'nav_menu_locations', [ 'primary' => (int) $menu ] );
    $civil = wp_insert_term( 'Dân sự', 'category' );
    aznet_v11_must( ! is_wp_error( $civil ), 'civil category fixture failed' );
    $civil_id = (int) $civil['term_id'];
    aznet_v11_post( 'Existing unrelated article' );
    $state = AZnet\Theme\provisioning_discovery();
    aznet_v11_must( 'EXISTING_ACTIVE' === AZnet\Theme\provisioning_site_mode( $state ), 'active fixture misclassified' );
    $recommendations = AZnet\Theme\provisioning_recommendations( 'law01-v1-1', $state );
    aznet_v11_must( 'SUGGESTED_REUSE' === (string) $recommendations['categories']['knowledge_civil']['state'], 'unique exact Dân sự label was not a suggestion' );
    aznet_v11_must( $civil_id === (int) $recommendations['categories']['knowledge_civil']['object_id'], 'suggested Dân sự ID mismatch' );
    aznet_v11_must( 0 === count( aznet_v11_owned_posts( 'post' ) ), 'recommendation mutated site before apply' );

    $selection = [
        'pages' => [
            'home' => [ 'action' => 'reuse', 'object_id' => $home ],
            'about' => [ 'action' => 'reuse', 'object_id' => $about ],
            'services' => [ 'action' => 'reuse', 'object_id' => $services ],
            'contact' => [ 'action' => 'reuse', 'object_id' => $contact ],
        ],
        'categories' => [ 'knowledge_civil' => [ 'action' => 'reuse', 'object_id' => $civil_id ] ],
        'menu' => [ 'action' => 'reuse', 'menu_id' => (int) $menu ],
        'front_page' => [ 'action' => 'set_to_role', 'role' => 'home' ],
        'starter_posts' => [ 'knowledge_civil' => [ 'action' => 'create' ] ],
        'starter_media' => [],
        'starter_publication' => [ 'publish' => true, 'discourage_indexing' => true ],
    ];
    $result = aznet_v11_apply( $selection );
    aznet_v11_must( true === $result['ok'], implode( '; ', $result['errors'] ) );
    aznet_v11_must( 1 === (int) get_option( 'blog_public', 1 ), 'active site was whole-site noindexed' );
    $starter = aznet_v11_owned_posts( 'post' );
    aznet_v11_must( 1 === count( $starter ) && 'draft' === $starter[0]->post_status, 'active-site starter must remain Draft' );
    aznet_v11_must( 'KEEP HOME' === get_post( $home )->post_content, 'existing Home content overwritten' );
    echo "PASS: v1.1 active-site suggestion/no-overwrite/Draft safety\n";
    return;
}

if ( 'rerun' === $scenario ) {
    $selection = aznet_v11_full_selection();
    $first = aznet_v11_apply( $selection );
    aznet_v11_must( true === $first['ok'], implode( '; ', $first['errors'] ) );
    $counts = [
        'pages' => count( aznet_v11_owned_posts( 'page' ) ),
        'posts' => count( aznet_v11_owned_posts( 'post' ) ),
        'attachments' => count( aznet_v11_owned_posts( 'attachment' ) ),
    ];
    $second = aznet_v11_apply( $selection );
    aznet_v11_must( true === $second['ok'], implode( '; ', $second['errors'] ) );
    aznet_v11_must( $counts['pages'] === count( aznet_v11_owned_posts( 'page' ) ), 'rerun duplicated Pages' );
    aznet_v11_must( $counts['posts'] === count( aznet_v11_owned_posts( 'post' ) ), 'rerun duplicated Posts' );
    aznet_v11_must( $counts['attachments'] === count( aznet_v11_owned_posts( 'attachment' ) ), 'rerun duplicated attachments' );
    echo "PASS: v1.1 rerun idempotent\n";
    return;
}

if ( 'rollback' === $scenario ) {
    $pre = [
        'show' => get_option( 'show_on_front' ),
        'front' => (int) get_option( 'page_on_front', 0 ),
        'blog_public' => (int) get_option( 'blog_public', 1 ),
        'settings' => AZnet\Theme\settings(),
        'locations' => get_theme_mod( 'nav_menu_locations', [] ),
    ];
    define( 'AZNET_THEME_TEST_PROVISIONING_FAILURE', 'post:knowledge_business:create' );
    $result = aznet_v11_apply( aznet_v11_full_selection() );
    aznet_v11_must( false === $result['ok'], 'injected failure unexpectedly passed' );
    aznet_v11_must( true === ( $result['rollback_ok'] ?? false ), implode( '; ', $result['errors'] ) );
    aznet_v11_must( $pre['show'] === get_option( 'show_on_front' ) && $pre['front'] === (int) get_option( 'page_on_front', 0 ), 'Front Page rollback failed' );
    aznet_v11_must( $pre['blog_public'] === (int) get_option( 'blog_public', 1 ), 'blog_public rollback failed' );
    aznet_v11_must( $pre['settings'] === AZnet\Theme\settings(), 'Theme settings rollback failed' );
    aznet_v11_must( $pre['locations'] === get_theme_mod( 'nav_menu_locations', [] ), 'menu location rollback failed' );
    aznet_v11_must( 0 === count( aznet_v11_owned_posts( 'page' ) ), 'current-run Pages survived rollback' );
    aznet_v11_must( 0 === count( aznet_v11_owned_posts( 'post' ) ), 'current-run Posts survived rollback' );
    aznet_v11_must( 0 === count( aznet_v11_owned_posts( 'attachment' ) ), 'current-run attachments survived rollback' );
    echo "PASS: v1.1 current-run rollback restores content/config/indexing\n";
    return;
}

if ( 'user-edit' === $scenario ) {
    $selection = aznet_v11_full_selection();
    $first = aznet_v11_apply( $selection );
    aznet_v11_must( true === $first['ok'], implode( '; ', $first['errors'] ) );
    $starter = aznet_v11_owned_posts( 'post' );
    aznet_v11_must( [] !== $starter, 'starter fixture missing' );
    $edited_id = (int) $starter[0]->ID;
    wp_update_post( [ 'ID' => $edited_id, 'post_content' => 'USER EDIT MUST SURVIVE' ] );
    $front_id = (int) get_option( 'page_on_front', 0 );
    $replacement_id = AZnet\Theme\provisioning_find_owned_role( 'law01-v1-1', 'attachment', 'starter_media:editorial-1' );
    aznet_v11_must( $replacement_id > 0 && get_post( $replacement_id ) instanceof WP_Post, 'valid imported replacement attachment fixture missing' );
    aznet_v11_must( false !== set_post_thumbnail( $front_id, $replacement_id ), 'replacement featured image fixture could not be assigned' );
    aznet_v11_must( $replacement_id === (int) get_post_thumbnail_id( $front_id ), 'replacement featured image fixture was not persisted before rerun' );
    $second = aznet_v11_apply( $selection );
    aznet_v11_must( true === $second['ok'], implode( '; ', $second['errors'] ) );
    aznet_v11_must( 'USER EDIT MUST SURVIVE' === get_post( $edited_id )->post_content, 'rerun overwrote edited starter Post' );
    aznet_v11_must( (int) $replacement_id === (int) get_post_thumbnail_id( $front_id ), 'rerun overwrote user-replaced Hero' );
    echo "PASS: v1.1 user edits/media replacement preserved\n";
    return;
}

if ( 'restore-index' === $scenario ) {
    $result = aznet_v11_apply( aznet_v11_full_selection() );
    aznet_v11_must( true === $result['ok'], implode( '; ', $result['errors'] ) );
    aznet_v11_must( AZnet\Theme\provisioning_index_restore_available(), 'restore not available after owned 1→0 change' );
    $restore = AZnet\Theme\provisioning_restore_search_visibility();
    aznet_v11_must( true === $restore['ok'] && true === $restore['changed'], implode( '; ', $restore['errors'] ) );
    aznet_v11_must( 1 === (int) get_option( 'blog_public', 0 ), 'explicit restore did not return blog_public to 1' );
    aznet_v11_must( ! AZnet\Theme\provisioning_index_restore_available(), 'restore remained available after completion' );
    echo "PASS: v1.1 explicit owned index restore\n";
    return;
}

throw new RuntimeException( 'Unknown AZNET_PROVISION_V11_SCENARIO: ' . $scenario );
