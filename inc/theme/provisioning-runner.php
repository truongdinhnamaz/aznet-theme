<?php
/** Apply/rollback orchestration for Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

function provisioning_run_id(): string {
    if ( function_exists( 'wp_generate_uuid4' ) ) { return (string) wp_generate_uuid4(); }
    try { return 'run-' . bin2hex( random_bytes( 8 ) ); } catch ( \Throwable $e ) { return 'run-' . uniqid( '', true ); }
}

function provisioning_test_maybe_fail( string $operation_id ): void {
    if ( defined( 'AZNET_THEME_TEST_PROVISIONING_FAILURE' ) && is_string( AZNET_THEME_TEST_PROVISIONING_FAILURE ) && AZNET_THEME_TEST_PROVISIONING_FAILURE === $operation_id ) {
        throw new \RuntimeException( 'Injected provisioning failure at ' . $operation_id );
    }
}

/** @return array<string,mixed> */
function provisioning_initial_receipt( string $run_id ): array {
    return [
        'run_id' => $run_id,
        'pre' => [
            'homepage_settings' => settings(),
            'show_on_front' => get_option( 'show_on_front', 'posts' ),
            'page_on_front' => (int) get_option( 'page_on_front', 0 ),
            'nav_locations' => get_theme_mod( 'nav_menu_locations', [] ),
            'blog_public' => (int) get_option( 'blog_public', 1 ),
        ],
        'index_visibility_changed' => false,
        'index_visibility_target' => null,
        'created' => [],
        'reused' => [],
        'errors' => [],
    ];
}

/** @return array{ok:bool,errors:array<int,string>} */
function provisioning_rollback_failed_run( array $receipt ): array {
    $errors = [];
    try { set_theme_mod( 'aznet_theme_settings', normalize_settings( (array) ( $receipt['pre']['homepage_settings'] ?? [] ) ) ); } catch ( \Throwable $e ) { $errors[] = 'Theme settings rollback failed: ' . $e->getMessage(); }
    try {
        update_option( 'show_on_front', $receipt['pre']['show_on_front'] ?? 'posts' );
        update_option( 'page_on_front', (int) ( $receipt['pre']['page_on_front'] ?? 0 ) );
    } catch ( \Throwable $e ) { $errors[] = 'Front Page rollback failed: ' . $e->getMessage(); }
    if ( ! empty( $receipt['index_visibility_changed'] ) ) {
        try { update_option( 'blog_public', (int) ( $receipt['pre']['blog_public'] ?? 1 ) ); } catch ( \Throwable $e ) { $errors[] = 'Search visibility rollback failed: ' . $e->getMessage(); }
    }
    try { set_theme_mod( 'nav_menu_locations', (array) ( $receipt['pre']['nav_locations'] ?? [] ) ); } catch ( \Throwable $e ) { $errors[] = 'Menu-location rollback failed: ' . $e->getMessage(); }

    $created = array_reverse( (array) ( $receipt['created'] ?? [] ) );
    foreach ( $created as $item ) {
        $type = (string) ( $item['type'] ?? '' );
        $id = (int) ( $item['id'] ?? 0 );
        if ( $id <= 0 ) { continue; }
        try {
            if ( in_array( $type, [ 'page', 'post' ], true ) ) {
                if ( false === wp_delete_post( $id, true ) ) { $errors[] = 'Failed to delete current-run ' . $type . ' #' . $id; }
            } elseif ( 'attachment' === $type ) {
                $deleted = function_exists( 'wp_delete_attachment' ) ? wp_delete_attachment( $id, true ) : wp_delete_post( $id, true );
                if ( false === $deleted ) { $errors[] = 'Failed to delete current-run attachment #' . $id; }
            } elseif ( 'category' === $type ) {
                $deleted = wp_delete_term( $id, 'category' );
                if ( false === $deleted || is_wp_error( $deleted ) ) { $errors[] = 'Failed to delete current-run Category #' . $id; }
            } elseif ( 'menu' === $type ) {
                $deleted = wp_delete_nav_menu( $id );
                if ( false === $deleted || is_wp_error( $deleted ) ) { $errors[] = 'Failed to delete current-run Menu #' . $id; }
            }
        } catch ( \Throwable $e ) { $errors[] = 'Rollback delete failed for ' . $type . ' #' . $id . ': ' . $e->getMessage(); }
    }
    return [ 'ok' => [] === $errors, 'errors' => $errors ];
}

function provisioning_resolve_page_reuse( int $id ): int {
    $post = get_post( $id );
    if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) { throw new \RuntimeException( 'Selected Page is unavailable: #' . $id ); }
    return $id;
}

function provisioning_resolve_term_reuse( int $id ): int {
    if ( function_exists( 'get_term' ) ) {
        $term = get_term( $id, 'category' );
        if ( is_wp_error( $term ) || ! $term instanceof \WP_Term || 'category' !== $term->taxonomy ) { throw new \RuntimeException( 'Selected Category is unavailable: #' . $id ); }
    }
    return $id;
}

/** @param array<string,int> $page_ids */
function provisioning_populate_created_menu( int $menu_id, array $page_ids, array $blueprint ): void {
    $existing = function_exists( 'wp_get_nav_menu_items' ) ? wp_get_nav_menu_items( $menu_id ) : [];
    $existing_object_ids = [];
    foreach ( is_array( $existing ) ? $existing : [] as $item ) { $existing_object_ids[] = (int) ( $item->object_id ?? 0 ); }
    foreach ( (array) ( $blueprint['menu']['roles'] ?? [] ) as $role ) {
        $page_id = (int) ( $page_ids[ $role ] ?? 0 );
        if ( $page_id <= 0 || in_array( $page_id, $existing_object_ids, true ) ) { continue; }
        $label = (string) ( $blueprint['menu']['labels'][ $role ] ?? '' );
        $args = [ 'menu-item-object-id' => $page_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ];
        if ( '' !== $label ) { $args['menu-item-title'] = $label; }
        $item_id = wp_update_nav_menu_item( $menu_id, 0, $args );
        if ( is_wp_error( $item_id ) || (int) $item_id <= 0 ) { throw new \RuntimeException( 'Unable to create starter menu item for Page #' . $page_id ); }
    }
}

function provisioning_existing_page_mapping_is_valid( int $id ): bool {
    if ( $id <= 0 ) { return false; }
    $post = get_post( $id );
    return $post instanceof \WP_Post && 'page' === $post->post_type && 'publish' === $post->post_status;
}

function provisioning_existing_term_mapping_is_valid( int $id ): bool {
    if ( $id <= 0 || ! function_exists( 'get_term' ) ) { return false; }
    $term = get_term( $id, 'category' );
    return ! is_wp_error( $term ) && $term instanceof \WP_Term && 'category' === $term->taxonomy;
}

/** @param array<string,int> $page_ids @param array<string,int> $term_ids @param array<string,mixed> $operation */
function provisioning_apply_homepage_mapping( array $page_ids, array $term_ids, array $operation = [] ): void {
    $s = settings();
    $page_sources = (array) ( $operation['page_sources'] ?? [] );
    $category_sources = (array) ( $operation['category_sources'] ?? [] );
    $page_map = [
        'services' => 'homepage_services_page', 'about' => 'homepage_about_page', 'team' => 'homepage_team_page',
        'process' => 'homepage_process_page', 'faq' => 'homepage_faq_page', 'contact' => 'homepage_contact_page',
    ];
    foreach ( $page_map as $role => $key ) {
        if ( ! isset( $page_ids[ $role ] ) ) { continue; }
        $resolved = (int) $page_ids[ $role ];
        $current = (int) ( $s[ $key ] ?? 0 );
        $action = (string) ( $page_sources[ $role ]['action'] ?? '' );
        if ( 'create' === $action && $current > 0 && $current !== $resolved && provisioning_existing_page_mapping_is_valid( $current ) ) { continue; }
        $s[ $key ] = $resolved;
    }
    $knowledge = [];
    $knowledge_actions = [];
    foreach ( [ 'knowledge_business','knowledge_civil','knowledge_criminal','knowledge_real_estate','knowledge_family','knowledge_labor' ] as $role ) {
        if ( isset( $term_ids[ $role ] ) ) { $knowledge[] = (int) $term_ids[ $role ]; $knowledge_actions[] = (string) ( $category_sources[ $role ]['action'] ?? '' ); }
    }
    if ( [] !== $knowledge ) {
        $current_knowledge = array_values( array_filter( array_map( 'intval', (array) ( $s['homepage_knowledge_terms'] ?? [] ) ) ) );
        $preserve_current = [] !== $current_knowledge && ! in_array( 'reuse', $knowledge_actions, true );
        if ( $preserve_current ) {
            foreach ( $current_knowledge as $current_id ) { if ( ! provisioning_existing_term_mapping_is_valid( $current_id ) ) { $preserve_current = false; break; } }
        }
        if ( ! $preserve_current ) { $s['homepage_knowledge_terms'] = $knowledge; }
    }
    foreach ( [ 'case_analysis' => 'homepage_case_analysis_term', 'legal_news' => 'homepage_legal_news_term' ] as $role => $key ) {
        if ( ! isset( $term_ids[ $role ] ) ) { continue; }
        $resolved = (int) $term_ids[ $role ];
        $current = (int) ( $s[ $key ] ?? 0 );
        $action = (string) ( $category_sources[ $role ]['action'] ?? '' );
        if ( 'create' === $action && $current > 0 && $current !== $resolved && provisioning_existing_term_mapping_is_valid( $current ) ) { continue; }
        $s[ $key ] = $resolved;
    }
    set_theme_mod( 'aznet_theme_settings', normalize_settings( $s ) );
}

function provisioning_target_has_thumbnail( int $post_id ): bool {
    if ( $post_id <= 0 ) { return false; }
    if ( function_exists( 'has_post_thumbnail' ) ) { return has_post_thumbnail( $post_id ); }
    return function_exists( 'get_post_thumbnail_id' ) && (int) get_post_thumbnail_id( $post_id ) > 0;
}

/** @return array<string,mixed> */
function provisioning_apply_plan( array $plan ): array {
    $validation = provisioning_validate_plan( $plan );
    if ( ! $validation['ok'] ) { return [ 'ok' => false, 'run_id' => '', 'created' => [], 'reused' => [], 'errors' => $validation['errors'] ]; }
    $current_fingerprint = provisioning_state_fingerprint( provisioning_discovery() );
    if ( ! hash_equals( (string) $plan['state_fingerprint'], $current_fingerprint ) ) { return [ 'ok' => false, 'run_id' => '', 'created' => [], 'reused' => [], 'errors' => [ 'The provisioning plan is stale. Run preflight again.' ] ]; }

    $blueprint = provisioning_blueprint( (string) $plan['blueprint'] );
    if ( null === $blueprint ) { return [ 'ok' => false, 'run_id' => '', 'created' => [], 'reused' => [], 'errors' => [ 'Blueprint unavailable.' ] ]; }
    $receipt = provisioning_initial_receipt( provisioning_run_id() );
    $page_ids = [];
    $term_ids = [];
    $post_ids = [];
    $created_starter_post_ids = [];
    $media_ids = [];
    $menu_id = 0;

    try {
        foreach ( (array) $plan['operations'] as $op ) {
            $type = (string) $op['type']; $role = (string) $op['role']; $op_id = (string) $op['id'];
            if ( 'reuse_page' === $type ) {
                $id = provisioning_resolve_page_reuse( (int) $op['object_id'] ); $page_ids[ $role ] = $id; $receipt['reused'][] = [ 'type' => 'page', 'id' => $id, 'role' => $role ];
            } elseif ( 'create_page' === $type ) {
                $owned = provisioning_find_owned_role( (string) $plan['blueprint'], 'page', $role );
                if ( $owned > 0 && get_post( $owned ) instanceof \WP_Post ) {
                    $page_ids[ $role ] = $owned; $receipt['reused'][] = [ 'type' => 'page', 'id' => $owned, 'role' => $role ];
                } else {
                    $definition = $blueprint['pages'][ $role ] ?? null; if ( ! is_array( $definition ) ) { throw new \RuntimeException( 'Unknown Page role: ' . $role ); }
                    $parent_id = isset( $op['parent_role'] ) && $op['parent_role'] ? (int) ( $page_ids[ (string) $op['parent_role'] ] ?? 0 ) : 0;
                    $id = wp_insert_post( [ 'post_type' => 'page', 'post_status' => (string) $definition['status'], 'post_title' => (string) $definition['title'], 'post_excerpt' => (string) $definition['excerpt'], 'post_content' => (string) $definition['content'], 'post_parent' => $parent_id ], true );
                    if ( is_wp_error( $id ) || (int) $id <= 0 ) { throw new \RuntimeException( is_wp_error( $id ) ? $id->get_error_message() : 'Page creation failed.' ); }
                    $id = (int) $id; if ( ! provisioning_mark_post( $id, (string) $plan['blueprint'], $role, (string) $receipt['run_id'] ) ) { throw new \RuntimeException( 'Page provenance failed for #' . $id ); }
                    $page_ids[ $role ] = $id; $receipt['created'][] = [ 'type' => 'page', 'id' => $id, 'role' => $role ];
                }
            } elseif ( 'reuse_term' === $type ) {
                $id = provisioning_resolve_term_reuse( (int) $op['object_id'] ); $term_ids[ $role ] = $id; $receipt['reused'][] = [ 'type' => 'category', 'id' => $id, 'role' => $role ];
            } elseif ( 'create_term' === $type ) {
                $owned = provisioning_find_owned_role( (string) $plan['blueprint'], 'category', $role );
                if ( $owned > 0 ) { $term_ids[ $role ] = $owned; $receipt['reused'][] = [ 'type' => 'category', 'id' => $owned, 'role' => $role ]; }
                else {
                    $definition = $blueprint['categories'][ $role ] ?? null; if ( ! is_array( $definition ) ) { throw new \RuntimeException( 'Unknown Category role: ' . $role ); }
                    $parent_id = isset( $op['parent_role'] ) && $op['parent_role'] ? (int) ( $term_ids[ (string) $op['parent_role'] ] ?? 0 ) : 0;
                    $created = wp_insert_term( (string) $definition['name'], 'category', [ 'parent' => $parent_id ] );
                    if ( is_wp_error( $created ) || ! is_array( $created ) || (int) ( $created['term_id'] ?? 0 ) <= 0 ) { throw new \RuntimeException( is_wp_error( $created ) ? $created->get_error_message() : 'Category creation failed.' ); }
                    $id = (int) $created['term_id']; if ( ! provisioning_mark_term( $id, (string) $plan['blueprint'], $role, (string) $receipt['run_id'] ) ) { throw new \RuntimeException( 'Category provenance failed for #' . $id ); }
                    $term_ids[ $role ] = $id; $receipt['created'][] = [ 'type' => 'category', 'id' => $id, 'role' => $role ];
                }
            } elseif ( 'reuse_menu' === $type ) {
                $menu_id = (int) $op['object_id']; if ( $menu_id <= 0 ) { throw new \RuntimeException( 'Selected menu is unavailable.' ); }
                $receipt['reused'][] = [ 'type' => 'menu', 'id' => $menu_id, 'role' => 'primary_menu' ];
                $locations = get_theme_mod( 'nav_menu_locations', [] ); $locations = is_array( $locations ) ? $locations : []; $locations['primary'] = $menu_id; set_theme_mod( 'nav_menu_locations', $locations );
            } elseif ( 'create_menu' === $type ) {
                $owned = provisioning_find_owned_role( (string) $plan['blueprint'], 'menu', 'primary_menu' );
                if ( $owned > 0 ) { $menu_id = $owned; $receipt['reused'][] = [ 'type' => 'menu', 'id' => $owned, 'role' => 'primary_menu' ]; }
                else {
                    $created = wp_create_nav_menu( (string) $blueprint['menu']['label'] ); if ( is_wp_error( $created ) || (int) $created <= 0 ) { throw new \RuntimeException( is_wp_error( $created ) ? $created->get_error_message() : 'Menu creation failed.' ); }
                    $menu_id = (int) $created; if ( ! provisioning_mark_term( $menu_id, (string) $plan['blueprint'], 'primary_menu', (string) $receipt['run_id'] ) ) { throw new \RuntimeException( 'Menu provenance failed.' ); }
                    $receipt['created'][] = [ 'type' => 'menu', 'id' => $menu_id, 'role' => 'primary_menu' ];
                    provisioning_populate_created_menu( $menu_id, $page_ids, $blueprint );
                }
                $locations = get_theme_mod( 'nav_menu_locations', [] ); $locations = is_array( $locations ) ? $locations : []; $locations['primary'] = $menu_id; set_theme_mod( 'nav_menu_locations', $locations );
            } elseif ( 'set_front_page' === $type ) {
                $front_id = (int) ( $page_ids[ $role ] ?? 0 ); if ( $front_id <= 0 ) { throw new \RuntimeException( 'Front Page role is unresolved.' ); }
                update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $front_id );
            } elseif ( 'map_homepage_sources' === $type ) {
                provisioning_apply_homepage_mapping( $page_ids, $term_ids, $op );
            } elseif ( 'set_homepage_preset' === $type ) {
                $s = settings(); $s['homepage_preset'] = (string) ( $op['preset'] ?? 'off' ); set_theme_mod( 'aznet_theme_settings', normalize_settings( $s ) );
            } elseif ( 'set_search_visibility' === $type ) {
                $target = (int) ( $op['target'] ?? 0 );
                $current = (int) get_option( 'blog_public', 1 );
                if ( $current !== $target ) {
                    update_option( 'blog_public', $target );
                    if ( $target !== (int) get_option( 'blog_public', 1 ) ) { throw new \RuntimeException( 'Search visibility update failed.' ); }
                    $receipt['index_visibility_changed'] = true;
                    $receipt['index_visibility_target'] = $target;
                }
            } elseif ( 'set_search_visibility' === $type ) {
                $target = (int) ( $op['target'] ?? 0 );
                $current = (int) get_option( 'blog_public', 1 );
                if ( $current !== $target ) {
                    update_option( 'blog_public', $target );
                    if ( $target !== (int) get_option( 'blog_public', 1 ) ) { throw new \RuntimeException( 'Search visibility update failed.' ); }
                    $receipt['index_visibility_changed'] = true;
                    $receipt['index_visibility_target'] = $target;
                }
            } elseif ( 'set_search_visibility' === $type ) {
                $target = (int) ( $op['target'] ?? 0 );
                $current = (int) get_option( 'blog_public', 1 );
                if ( $current !== $target ) {
                    update_option( 'blog_public', $target );
                    if ( $target !== (int) get_option( 'blog_public', 1 ) ) { throw new \RuntimeException( 'Search visibility update failed.' ); }
                    $receipt['index_visibility_changed'] = true;
                    $receipt['index_visibility_target'] = $target;
                }
            } elseif ( 'set_search_visibility' === $type ) {
                $target = (int) ( $op['target'] ?? 0 );
                $current = (int) get_option( 'blog_public', 1 );
                if ( $current !== $target ) {
                    update_option( 'blog_public', $target );
                    if ( $target !== (int) get_option( 'blog_public', 1 ) ) { throw new \RuntimeException( 'Search visibility update failed.' ); }
                    $receipt['index_visibility_changed'] = true;
                    $receipt['index_visibility_target'] = $target;
                }
            } elseif ( 'import_media' === $type ) {
                $media_role = (string) ( $op['media_role'] ?? $role );
                $provenance_role = function_exists( __NAMESPACE__ . '\\provisioning_media_role_provenance' ) ? provisioning_media_role_provenance( $media_role ) : 'starter_media:' . $media_role;
                $before = provisioning_find_owned_role( (string) $plan['blueprint'], 'attachment', $provenance_role );
                $id = provisioning_import_media( $media_role, (string) $plan['blueprint'], (string) $receipt['run_id'] );
                $media_ids[ $media_role ] = $id;
                if ( $before > 0 ) { $receipt['reused'][] = [ 'type' => 'attachment', 'id' => $id, 'role' => $provenance_role ]; }
                else { $receipt['created'][] = [ 'type' => 'attachment', 'id' => $id, 'role' => $provenance_role ]; }
            } elseif ( 'create_post' === $type ) {
                $editorial_role = (string) ( $op['editorial_role'] ?? '' );
                $owned = provisioning_find_owned_role( (string) $plan['blueprint'], 'post', $role );
                if ( $owned > 0 && get_post( $owned ) instanceof \WP_Post ) {
                    $post_ids[ $role ] = $owned; $receipt['reused'][] = [ 'type' => 'post', 'id' => $owned, 'role' => $role ];
                } else {
                    $definition = $blueprint['editorial_examples']['items'][ $editorial_role ] ?? null;
                    if ( ! is_array( $definition ) ) { throw new \RuntimeException( 'Unknown starter Post role: ' . $editorial_role ); }
                    $category_role = (string) ( $op['category_role'] ?? '' );
                    $category_id = (int) ( $term_ids[ $category_role ] ?? 0 );
                    if ( $category_id <= 0 ) { throw new \RuntimeException( 'Starter Post Category is unresolved: ' . $category_role ); }
                    $id = wp_insert_post( [
                        'post_type' => 'post',
                        'post_status' => (string) ( $op['post_status'] ?? 'draft' ),
                        'post_title' => (string) $definition['title'],
                        'post_excerpt' => (string) $definition['excerpt'],
                        'post_content' => (string) $definition['content'],
                    ], true );
                    if ( is_wp_error( $id ) || (int) $id <= 0 ) { throw new \RuntimeException( is_wp_error( $id ) ? $id->get_error_message() : 'Starter Post creation failed.' ); }
                    $id = (int) $id;
                    if ( function_exists( 'wp_set_post_categories' ) ) {
                        $assigned = wp_set_post_categories( $id, [ $category_id ], false );
                        if ( is_wp_error( $assigned ) ) { throw new \RuntimeException( $assigned->get_error_message() ); }
                    }
                    if ( ! provisioning_mark_post( $id, (string) $plan['blueprint'], $role, (string) $receipt['run_id'] ) ) { throw new \RuntimeException( 'Starter Post provenance failed for #' . $id ); }
                    $post_ids[ $role ] = $id; $created_starter_post_ids[] = $id; $receipt['created'][] = [ 'type' => 'post', 'id' => $id, 'role' => $role ];
                }
            } elseif ( 'assign_featured_media' === $type ) {
                $media_role = (string) ( $op['media_role'] ?? '' );
                $media_id = (int) ( $media_ids[ $media_role ] ?? 0 );
                $target_role = (string) ( $op['target_role'] ?? '' );
                $target_type = (string) ( $op['target_type'] ?? '' );
                $target_id = 'page_role' === $target_type ? (int) ( $page_ids[ $target_role ] ?? 0 ) : (int) ( $post_ids[ $target_role ] ?? 0 );
                if ( $target_id <= 0 || $media_id <= 0 ) { throw new \RuntimeException( 'Featured-media assignment target is unresolved.' ); }
                if ( ! provisioning_target_has_thumbnail( $target_id ) && function_exists( 'set_post_thumbnail' ) ) {
                    if ( false === set_post_thumbnail( $target_id, $media_id ) ) { throw new \RuntimeException( 'Featured-media assignment failed for #' . $target_id ); }
                }
            }
            provisioning_test_maybe_fail( $op_id );
        }
        foreach ( array_values( array_unique( $created_starter_post_ids ) ) as $starter_post_id ) {
            if ( ! provisioning_store_starter_fingerprint( (int) $starter_post_id ) ) { throw new \RuntimeException( 'Starter Post fingerprint failed for #' . (int) $starter_post_id ); }
        }
        if ( ! empty( $receipt['index_visibility_changed'] ) ) {
            provisioning_store_index_receipt( [
                'blueprint' => (string) $plan['blueprint'],
                'run_id' => (string) $receipt['run_id'],
                'changed' => true,
                'pre_blog_public' => (int) ( $receipt['pre']['blog_public'] ?? 1 ),
                'target_blog_public' => (int) ( $receipt['index_visibility_target'] ?? 0 ),
                'restored' => false,
            ] );
        }
        return [
            'ok' => true, 'run_id' => $receipt['run_id'], 'created' => $receipt['created'], 'reused' => $receipt['reused'], 'errors' => [],
            'page_ids' => $page_ids, 'term_ids' => $term_ids, 'post_ids' => $post_ids, 'media_ids' => $media_ids, 'menu_id' => $menu_id,
        ];
    } catch ( \Throwable $e ) {
        $receipt['errors'][] = $e->getMessage();
        $rollback = provisioning_rollback_failed_run( $receipt );
        $errors = array_merge( $receipt['errors'], $rollback['errors'] );
        return [ 'ok' => false, 'run_id' => $receipt['run_id'], 'created' => $receipt['created'], 'reused' => $receipt['reused'], 'errors' => $errors, 'rollback_ok' => $rollback['ok'] ];
    }
}
