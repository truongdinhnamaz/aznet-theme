<?php
/** Pure change-plan model for Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<int,string> */
function provisioning_required_page_roles(): array { return [ 'home', 'about', 'services', 'contact' ]; }

/** @return array<string,mixed> */
function provisioning_state_payload( array $discovery ): array {
    $pages = [];
    foreach ( (array) ( $discovery['pages'] ?? [] ) as $page ) {
        $pages[] = [ 'id' => (int) ( $page['id'] ?? 0 ), 'status' => (string) ( $page['status'] ?? '' ) ];
    }
    usort( $pages, static fn( array $a, array $b ): int => $a['id'] <=> $b['id'] );
    $categories = [];
    foreach ( (array) ( $discovery['categories'] ?? [] ) as $term ) { $categories[] = [ 'id' => (int) ( $term['id'] ?? 0 ) ]; }
    usort( $categories, static fn( array $a, array $b ): int => $a['id'] <=> $b['id'] );
    $prior = array_values( array_unique( array_map( 'strval', (array) ( $discovery['prior_blueprints'] ?? [] ) ) ) );
    sort( $prior, SORT_STRING );
    return [
        'show_on_front' => (string) ( $discovery['show_on_front'] ?? 'posts' ),
        'front_page_id' => (int) ( $discovery['front_page_id'] ?? 0 ),
        'homepage_preset' => (string) ( $discovery['homepage_preset'] ?? 'off' ),
        'homepage_slots' => (array) ( $discovery['homepage_slots'] ?? [] ),
        'primary_menu_id' => (int) ( $discovery['primary_menu_id'] ?? 0 ),
        'published_post_count' => (int) ( $discovery['published_post_count'] ?? 0 ),
        'prior_blueprints' => $prior,
        'pages' => $pages,
        'categories' => $categories,
    ];
}

function provisioning_state_fingerprint( array $discovery ): string {
    return 'sha256:' . hash( 'sha256', json_encode( provisioning_state_payload( $discovery ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ?: '' );
}

/** @return array<string,mixed> */
function provisioning_build_plan( string $blueprint_key, array $selections, array $discovery ): array {
    $blueprint = provisioning_blueprint( $blueprint_key );
    if ( null === $blueprint ) {
        return [ 'schema_version' => 1, 'blueprint' => $blueprint_key, 'state_fingerprint' => provisioning_state_fingerprint( $discovery ), 'operations' => [] ];
    }
    $ops = [];
    $page_sources = [];
    $category_sources = [];
    $allowed_actions = [ 'reuse', 'create', 'skip' ];

    foreach ( $blueprint['pages'] as $role => $definition ) {
        $choice = (array) ( $selections['pages'][ $role ] ?? [] );
        $action = in_array( $choice['action'] ?? '', $allowed_actions, true ) ? (string) $choice['action'] : 'skip';
        $object_id = max( 0, (int) ( $choice['object_id'] ?? 0 ) );
        if ( 'skip' === $action ) { continue; }
        if ( 'reuse' === $action ) {
            $ops[] = [ 'id' => 'page:' . $role . ':reuse', 'type' => 'reuse_page', 'role' => $role, 'object_id' => $object_id, 'effect' => sprintf( 'REUSE Page #%d → %s', $object_id, $role ) ];
        } else {
            $ops[] = [ 'id' => 'page:' . $role . ':create', 'type' => 'create_page', 'role' => $role, 'object_id' => 0, 'parent_role' => $definition['parent_role'], 'effect' => sprintf( 'CREATE Page “%s” → %s', $definition['title'], $role ) ];
        }
        $page_sources[ $role ] = [ 'action' => $action, 'object_id' => $object_id ];
    }

    foreach ( $blueprint['categories'] as $role => $definition ) {
        $choice = (array) ( $selections['categories'][ $role ] ?? [] );
        $action = in_array( $choice['action'] ?? '', $allowed_actions, true ) ? (string) $choice['action'] : 'skip';
        $object_id = max( 0, (int) ( $choice['object_id'] ?? 0 ) );
        if ( 'skip' === $action ) { continue; }
        if ( 'reuse' === $action ) {
            $ops[] = [ 'id' => 'term:' . $role . ':reuse', 'type' => 'reuse_term', 'role' => $role, 'object_id' => $object_id, 'effect' => sprintf( 'REUSE Category #%d → %s', $object_id, $role ) ];
        } else {
            $ops[] = [ 'id' => 'term:' . $role . ':create', 'type' => 'create_term', 'role' => $role, 'object_id' => 0, 'parent_role' => $definition['parent_role'], 'effect' => sprintf( 'CREATE Category “%s” → %s', $definition['name'], $role ) ];
        }
        $category_sources[ $role ] = [ 'action' => $action, 'object_id' => $object_id ];
    }

    $menu = (array) ( $selections['menu'] ?? [] );
    $menu_action = in_array( $menu['action'] ?? '', [ 'reuse', 'create', 'skip' ], true ) ? (string) $menu['action'] : 'skip';
    if ( 'create' === $menu_action ) {
        $ops[] = [ 'id' => 'menu:primary:create', 'type' => 'create_menu', 'role' => 'primary_menu', 'object_id' => 0, 'effect' => 'CREATE Primary Menu “AZnet Primary”' ];
    } elseif ( 'reuse' === $menu_action ) {
        $menu_id = max( 0, (int) ( $menu['menu_id'] ?? 0 ) );
        $ops[] = [ 'id' => 'menu:primary:reuse', 'type' => 'reuse_menu', 'role' => 'primary_menu', 'object_id' => $menu_id, 'effect' => sprintf( 'REUSE Primary Menu #%d', $menu_id ) ];
    }

    $front = (array) ( $selections['front_page'] ?? [] );
    if ( 'set_to_role' === ( $front['action'] ?? '' ) && isset( $page_sources[ (string) ( $front['role'] ?? '' ) ] ) ) {
        $role = (string) $front['role'];
        $ops[] = [ 'id' => 'front-page:set:' . $role, 'type' => 'set_front_page', 'role' => $role, 'object_id' => 0, 'effect' => sprintf( 'SET Front Page → %s', $role ) ];
    }

    $ops[] = [ 'id' => 'homepage:map', 'type' => 'map_homepage_sources', 'role' => 'homepage', 'object_id' => 0, 'page_sources' => $page_sources, 'category_sources' => $category_sources, 'effect' => 'MAP Homepage Content Map to confirmed WordPress sources' ];
    $ops[] = [ 'id' => 'homepage:preset:law-01', 'type' => 'set_homepage_preset', 'role' => 'homepage', 'object_id' => 0, 'preset' => 'law-01', 'effect' => 'SET Homepage preset → law-01' ];

    if ( 'law01-v1-1' === $blueprint_key ) {
        $mode = function_exists( __NAMESPACE__ . '\\provisioning_site_mode' ) ? provisioning_site_mode( $discovery ) : 'EXISTING_ACTIVE';
        $publication = (array) ( $selections['starter_publication'] ?? [] );
        $publish_requested = ! empty( $publication['publish'] );
        $discourage_requested = ! empty( $publication['discourage_indexing'] );
        $may_publish = 'NEW_OR_MOSTLY_EMPTY' === $mode && $publish_requested && $discourage_requested;
        $post_status = $may_publish ? 'publish' : 'draft';

        // Search visibility must be applied before any public starter Post is created.
        if ( $may_publish ) {
            $ops[] = [
                'id' => 'search-visibility:discourage',
                'type' => 'set_search_visibility',
                'role' => 'search_visibility',
                'object_id' => 0,
                'target' => 0,
                'reason' => 'temporary_new_site_starter_publication',
                'effect' => 'SET Search visibility → discourage indexing during starter-content review',
            ];
        }

        $media_selected = [];
        foreach ( (array) ( $selections['starter_media'] ?? [] ) as $media_role => $enabled ) {
            $media_role = (string) $media_role;
            if ( ! $enabled || ! in_array( $media_role, [ 'hero', 'editorial-1', 'editorial-2', 'editorial-3', 'editorial-4' ], true ) ) { continue; }
            $media_selected[ $media_role ] = true;
            $ops[] = [
                'id' => 'media:' . $media_role . ':import',
                'type' => 'import_media',
                'role' => $media_role,
                'object_id' => 0,
                'media_role' => $media_role,
                'effect' => sprintf( 'IMPORT starter media → %s', $media_role ),
            ];
        }

        if ( isset( $media_selected['hero'] ) && isset( $page_sources['home'] ) ) {
            $ops[] = [
                'id' => 'media:hero:assign:home',
                'type' => 'assign_featured_media',
                'role' => 'featured_media:home',
                'object_id' => 0,
                'target_type' => 'page_role',
                'target_role' => 'home',
                'media_role' => 'hero',
                'effect' => 'SET starter Hero as Front Page featured image only when no featured image exists',
            ];
        }

        foreach ( (array) ( $blueprint['editorial_examples']['items'] ?? [] ) as $editorial_role => $definition ) {
            $choice = (array) ( $selections['starter_posts'][ $editorial_role ] ?? [] );
            if ( 'create' !== (string) ( $choice['action'] ?? 'skip' ) ) { continue; }
            $category_role = (string) ( $definition['category_role'] ?? $editorial_role );
            if ( ! isset( $category_sources[ $category_role ] ) ) { continue; }
            $media_role = (string) ( $definition['media_role'] ?? '' );
            $post_role = 'starter_post:' . $editorial_role;
            $ops[] = [
                'id' => 'post:' . $editorial_role . ':create',
                'type' => 'create_post',
                'role' => $post_role,
                'object_id' => 0,
                'editorial_role' => (string) $editorial_role,
                'category_role' => $category_role,
                'media_role' => $media_role,
                'post_status' => $post_status,
                'effect' => sprintf( 'CREATE starter Post “%s” → %s (%s)', (string) $definition['title'], $category_role, $post_status ),
            ];
            if ( '' !== $media_role && isset( $media_selected[ $media_role ] ) ) {
                $ops[] = [
                    'id' => 'media:' . $media_role . ':assign:' . $editorial_role,
                    'type' => 'assign_featured_media',
                    'role' => 'featured_media:' . $post_role,
                    'object_id' => 0,
                    'target_type' => 'post_role',
                    'target_role' => $post_role,
                    'media_role' => $media_role,
                    'effect' => sprintf( 'SET starter media %s → %s', $media_role, $post_role ),
                ];
            }
        }
    }

    return [
        'schema_version' => 1,
        'blueprint' => $blueprint_key,
        'generated_at' => 0,
        'state_fingerprint' => provisioning_state_fingerprint( $discovery ),
        'operations' => $ops,
    ];
}

function provisioning_plan_fingerprint( array $plan ): string {
    $payload = [
        'schema_version' => $plan['schema_version'] ?? 0,
        'blueprint' => $plan['blueprint'] ?? '',
        'state_fingerprint' => $plan['state_fingerprint'] ?? '',
        'operations' => $plan['operations'] ?? [],
    ];
    return 'sha256:' . hash( 'sha256', json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) ?: '' );
}

/** @return array{ok:bool,errors:array<int,string>} */
function provisioning_validate_plan( array $plan ): array {
    $errors = [];
    if ( 1 !== (int) ( $plan['schema_version'] ?? 0 ) ) { $errors[] = 'Unsupported plan schema.'; }
    if ( ! in_array( (string) ( $plan['blueprint'] ?? '' ), provisioning_blueprint_keys(), true ) ) { $errors[] = 'Unsupported blueprint.'; }
    if ( ! str_starts_with( (string) ( $plan['state_fingerprint'] ?? '' ), 'sha256:' ) ) { $errors[] = 'Missing state fingerprint.'; }
    $allowed_types = [
        'reuse_page','create_page','reuse_term','create_term','reuse_menu','create_menu','set_front_page','map_homepage_sources','set_homepage_preset',
        'create_post','import_media','assign_featured_media','set_search_visibility',
    ];
    $seen = [];
    foreach ( (array) ( $plan['operations'] ?? [] ) as $op ) {
        if ( ! is_array( $op ) || ! isset( $op['id'], $op['type'], $op['role'], $op['effect'] ) ) { $errors[] = 'Malformed operation.'; continue; }
        $type = (string) $op['type'];
        if ( ! in_array( $type, $allowed_types, true ) ) { $errors[] = 'Forbidden operation type: ' . $type; }
        if ( isset( $seen[ $op['id'] ] ) ) { $errors[] = 'Duplicate operation id.'; }
        $seen[ $op['id'] ] = true;
        if ( str_starts_with( $type, 'reuse_' ) && (int) ( $op['object_id'] ?? 0 ) <= 0 ) { $errors[] = 'Reuse operation requires object ID.'; }
        if ( 'create_post' === $type ) {
            if ( ! str_starts_with( (string) ( $op['role'] ?? '' ), 'starter_post:' ) ) { $errors[] = 'Starter Post role is invalid.'; }
            if ( '' === (string) ( $op['category_role'] ?? '' ) ) { $errors[] = 'Starter Post category role is required.'; }
            if ( '' === (string) ( $op['media_role'] ?? '' ) ) { $errors[] = 'Starter Post media role is required.'; }
            if ( ! in_array( (string) ( $op['post_status'] ?? '' ), [ 'draft', 'publish' ], true ) ) { $errors[] = 'Starter Post status is invalid.'; }
        } elseif ( 'import_media' === $type ) {
            if ( '' === (string) ( $op['media_role'] ?? '' ) ) { $errors[] = 'Media import role is required.'; }
        } elseif ( 'assign_featured_media' === $type ) {
            if ( ! in_array( (string) ( $op['target_type'] ?? '' ), [ 'page_role', 'post_role' ], true ) ) { $errors[] = 'Featured-media target type is invalid.'; }
            if ( '' === (string) ( $op['target_role'] ?? '' ) || '' === (string) ( $op['media_role'] ?? '' ) ) { $errors[] = 'Featured-media target/media role is required.'; }
        } elseif ( 'set_search_visibility' === $type ) {
            if ( 0 !== (int) ( $op['target'] ?? -1 ) || 'temporary_new_site_starter_publication' !== (string) ( $op['reason'] ?? '' ) ) {
                $errors[] = 'Search-visibility operation is not an approved starter-publication guard.';
            }
        }
    }
    foreach ( provisioning_required_page_roles() as $required ) {
        $matched = false;
        foreach ( (array) ( $plan['operations'] ?? [] ) as $op ) {
            if ( in_array( $op['type'] ?? '', [ 'reuse_page','create_page' ], true ) && ( $op['role'] ?? '' ) === $required ) { $matched = true; break; }
        }
        if ( ! $matched ) { $errors[] = 'Missing required Page role: ' . $required; }
    }
    return [ 'ok' => [] === $errors, 'errors' => $errors ];
}
