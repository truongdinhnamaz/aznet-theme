<?php
/**
 * Bounded local Theme-template provisioning recipe loader (D-043 C6).
 *
 * Only manifests already validated by Core may reference bundled, local,
 * source-reviewed recipe files. D-040 remote template packages cannot load
 * executable PHP. A missing/mismatched recipe fails soft, never to another
 * industry's native content.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** Validate the approved WordPress-native Page/Category/Menu recipe projection. */
function provisioning_local_recipe_is_valid( array $recipe, string $key, string $preset ): bool {
    if ( $key !== (string) ( $recipe['key'] ?? '' )
        || $preset !== (string) ( $recipe['homepage_preset'] ?? '' )
        || ! is_array( $recipe['pages'] ?? null )
        || [] === $recipe['pages']
        || ! is_array( $recipe['categories'] ?? null )
        || ! is_array( $recipe['menu'] ?? null )
        || ! is_array( $recipe['required_page_roles'] ?? null )
        || ! in_array( (string) ( $recipe['existing_site_page_status'] ?? '' ), [ 'draft', 'publish' ], true ) ) {
        return false;
    }

    $pages = $recipe['pages'];
    $categories = $recipe['categories'];
    $required = $recipe['required_page_roles'];
    if ( [] === $required || ! in_array( 'home', $required, true )
        || ! in_array( 'about', $required, true )
        || ! in_array( 'contact', $required, true )
        || count( $required ) !== count( array_unique( $required ) ) ) {
        return false;
    }

    foreach ( $pages as $role => $page ) {
        if ( ! is_string( $role ) || ! preg_match( '/^[a-z][a-z0-9_]*$/', $role )
            || ! is_array( $page )
            || ! in_array( (string) ( $page['status'] ?? '' ), [ 'draft', 'publish' ], true )
            || '' === trim( (string) ( $page['title'] ?? '' ) )
            || '' === trim( (string) ( $page['content'] ?? '' ) )
            || ! is_string( $page['excerpt'] ?? null )
            || ! array_key_exists( 'parent_role', $page )
            || ( null !== $page['parent_role'] && ( ! is_string( $page['parent_role'] ) || ! array_key_exists( $page['parent_role'], $pages ) ) ) ) {
            return false;
        }
    }
    foreach ( $required as $role ) {
        if ( ! is_string( $role ) || ! array_key_exists( $role, $pages ) ) {
            return false;
        }
    }
    foreach ( $categories as $role => $term ) {
        if ( ! is_string( $role ) || ! preg_match( '/^[a-z][a-z0-9_]*$/', $role )
            || ! is_array( $term )
            || '' === trim( (string) ( $term['name'] ?? '' ) )
            || ! array_key_exists( 'parent_role', $term )
            || ( null !== $term['parent_role'] && ( ! is_string( $term['parent_role'] ) || ! array_key_exists( $term['parent_role'], $categories ) ) ) ) {
            return false;
        }
    }

    $menu = $recipe['menu'];
    if ( 'primary' !== (string) ( $menu['location'] ?? '' )
        || '' === trim( (string) ( $menu['label'] ?? '' ) )
        || ! is_array( $menu['roles'] ?? null )
        || ! is_array( $menu['labels'] ?? null ) ) {
        return false;
    }
    foreach ( $menu['roles'] as $role ) {
        if ( ! is_string( $role ) || ! array_key_exists( $role, $pages ) ) {
            return false;
        }
    }
    return true;
}

/**
 * Resolve only local bundled recipe files paired with a versioned manifest.
 *
 * @return array<string,array<string,mixed>>
 */
function provisioning_local_template_recipes(): array {
    if ( ! function_exists( __NAMESPACE__ . '\\template_manifests' ) ) {
        return [];
    }
    $reserved = [ 'law01-v1', 'law01-v1-1', 'law01-v1-2', 'professional-services-v1' ];
    $recipes = [];
    foreach ( template_manifests() as $manifest ) {
        $key = $manifest['provisioning']['blueprint'] ?? null;
        $id = $manifest['id'] ?? null;
        $preset = $manifest['presentation']['homepage_preset'] ?? null;
        if ( ! is_string( $key ) || '' === $key || in_array( $key, $reserved, true )
            || isset( $recipes[ $key ] )
            || ! is_string( $id ) || ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $id )
            || ! is_string( $preset ) || '' === $preset ) {
            continue;
        }
        $path = __DIR__ . '/templates/' . $id . '/provisioning.php';
        if ( ! is_file( $path ) ) {
            continue;
        }
        // This path is a bundled Theme file, not an imported remote PHP recipe.
        $recipe = require $path;
        if ( ! is_array( $recipe ) || ! provisioning_local_recipe_is_valid( $recipe, $key, $preset ) ) {
            continue;
        }
        $recipes[ $key ] = $recipe;
    }
    return $recipes;
}
