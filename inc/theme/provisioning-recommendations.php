<?php
/** Proposal-only recommendation model for Law Site Provisioning. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

const PROVISIONING_MODE_PREVIOUS = 'PREVIOUSLY_PROVISIONED';
const PROVISIONING_MODE_NEW = 'NEW_OR_MOSTLY_EMPTY';
const PROVISIONING_MODE_ACTIVE = 'EXISTING_ACTIVE';

/**
 * Classify only from observable configuration/counts/provenance.
 * Classification changes defaults; it never establishes authoritative semantics.
 */
function provisioning_site_mode( array $discovery ): string {
    $prior = array_values( array_intersect(
        [ 'law01-v1', 'law01-v1-1' ],
        array_map( 'strval', (array) ( $discovery['prior_blueprints'] ?? [] ) )
    ) );
    if ( [] !== $prior ) { return PROVISIONING_MODE_PREVIOUS; }

    $is_new = 'page' !== (string) ( $discovery['show_on_front'] ?? 'posts' )
        && (int) ( $discovery['front_page_id'] ?? 0 ) <= 0
        && (int) ( $discovery['primary_menu_id'] ?? 0 ) <= 0
        && (int) ( $discovery['published_post_count'] ?? 0 ) <= 0
        && (int) ( $discovery['page_count'] ?? 0 ) <= 2;

    return $is_new ? PROVISIONING_MODE_NEW : PROVISIONING_MODE_ACTIVE;
}

/** Normalize display labels for exact comparison without destroying lexical content/diacritics. */
function provisioning_normalize_label( string $label ): string {
    $label = trim( $label );
    $label = (string) preg_replace( '/\s+/u', ' ', $label );
    return function_exists( 'mb_strtolower' ) ? mb_strtolower( $label, 'UTF-8' ) : strtolower( $label );
}

/** @return array<int,int> */
function provisioning_exact_page_candidates( string $label, array $pages ): array {
    $needle = provisioning_normalize_label( $label );
    $ids = [];
    foreach ( $pages as $candidate ) {
        if ( ! is_array( $candidate ) ) { continue; }
        if ( $needle === provisioning_normalize_label( (string) ( $candidate['title'] ?? '' ) ) ) {
            $id = (int) ( $candidate['id'] ?? 0 );
            if ( $id > 0 ) { $ids[] = $id; }
        }
    }
    return array_values( array_unique( $ids ) );
}

/** @return array<int,int> */
function provisioning_exact_term_candidates( string $label, array $terms ): array {
    $needle = provisioning_normalize_label( $label );
    $ids = [];
    foreach ( $terms as $candidate ) {
        if ( ! is_array( $candidate ) ) { continue; }
        if ( $needle === provisioning_normalize_label( (string) ( $candidate['name'] ?? '' ) ) ) {
            $id = (int) ( $candidate['id'] ?? 0 );
            if ( $id > 0 ) { $ids[] = $id; }
        }
    }
    return array_values( array_unique( $ids ) );
}

function provisioning_candidate_exists( int $id, array $candidates ): bool {
    if ( $id <= 0 ) { return false; }
    foreach ( $candidates as $candidate ) {
        if ( is_array( $candidate ) && $id === (int) ( $candidate['id'] ?? 0 ) ) { return true; }
    }
    return false;
}

/** @return array<string,mixed> */
function provisioning_recommendation_result( string $state, string $action, int $object_id, array $candidate_ids, string $reason ): array {
    return [
        'state' => $state,
        'action' => $action,
        'object_id' => $object_id,
        'candidate_ids' => array_values( array_map( 'intval', $candidate_ids ) ),
        'reason' => $reason,
    ];
}

function provisioning_owned_role_for_recommendation( string $blueprint, string $object_type, string $role ): int {
    if ( ! function_exists( __NAMESPACE__ . '\\provisioning_find_owned_role' ) ) { return 0; }
    foreach ( array_values( array_unique( [ $blueprint, 'law01-v1-1', 'law01-v1' ] ) ) as $candidate_blueprint ) {
        if ( ! in_array( $candidate_blueprint, provisioning_blueprint_keys(), true ) ) { continue; }
        $id = provisioning_find_owned_role( $candidate_blueprint, $object_type, $role );
        if ( $id > 0 ) { return $id; }
    }
    return 0;
}

/**
 * Build proposal-only defaults. Nothing returned here mutates or authoritatively maps WordPress data.
 *
 * @return array<string,mixed>
 */
function provisioning_recommendations( string $blueprint_key, array $discovery ): array {
    $blueprint = provisioning_blueprint( $blueprint_key );
    // During additive v1 -> v1.1 rollout, structural recommendation can safely reuse the v1 structure.
    if ( null === $blueprint && 'law01-v1-1' === $blueprint_key ) { $blueprint = provisioning_blueprint( 'law01-v1' ); }
    if ( null === $blueprint ) { return [ 'mode' => provisioning_site_mode( $discovery ), 'pages' => [], 'categories' => [] ]; }

    $pages = (array) ( $discovery['pages'] ?? [] );
    $terms = (array) ( $discovery['categories'] ?? [] );
    $slots = (array) ( $discovery['homepage_slots'] ?? [] );
    $page_slot = [
        'services' => 'services', 'about' => 'about', 'team' => 'team',
        'process' => 'process', 'faq' => 'faq', 'contact' => 'contact',
    ];
    $term_slot = [ 'case_analysis' => 'case_analysis', 'legal_news' => 'legal_news' ];

    $out = [ 'mode' => provisioning_site_mode( $discovery ), 'pages' => [], 'categories' => [] ];

    foreach ( (array) ( $blueprint['pages'] ?? [] ) as $role => $definition ) {
        $current = 0;
        if ( 'home' === $role && 'page' === (string) ( $discovery['show_on_front'] ?? 'posts' ) ) {
            $current = (int) ( $discovery['front_page_id'] ?? 0 );
        } elseif ( isset( $page_slot[ $role ] ) ) {
            $current = (int) ( $slots[ $page_slot[ $role ] ] ?? 0 );
        }
        if ( provisioning_candidate_exists( $current, $pages ) ) {
            $out['pages'][ $role ] = provisioning_recommendation_result( 'CURRENT', 'reuse', $current, [ $current ], 'current_mapping' );
            continue;
        }
        $owned = provisioning_owned_role_for_recommendation( $blueprint_key, 'page', (string) $role );
        if ( provisioning_candidate_exists( $owned, $pages ) ) {
            $out['pages'][ $role ] = provisioning_recommendation_result( 'PROVISIONED_REUSE', 'reuse', $owned, [ $owned ], 'provisioning_provenance' );
            continue;
        }
        $matches = provisioning_exact_page_candidates( (string) ( $definition['title'] ?? '' ), $pages );
        if ( 1 === count( $matches ) ) {
            $out['pages'][ $role ] = provisioning_recommendation_result( 'SUGGESTED_REUSE', 'reuse', $matches[0], $matches, 'unique_exact_label' );
        } elseif ( count( $matches ) > 1 ) {
            $out['pages'][ $role ] = provisioning_recommendation_result( 'AMBIGUOUS', 'skip', 0, $matches, 'multiple_exact_labels' );
        } else {
            $out['pages'][ $role ] = provisioning_recommendation_result( 'SUGGESTED_CREATE', 'create', 0, [], 'no_safe_candidate' );
        }
    }

    foreach ( (array) ( $blueprint['categories'] ?? [] ) as $role => $definition ) {
        $current = isset( $term_slot[ $role ] ) ? (int) ( $slots[ $term_slot[ $role ] ] ?? 0 ) : 0;
        if ( provisioning_candidate_exists( $current, $terms ) ) {
            $out['categories'][ $role ] = provisioning_recommendation_result( 'CURRENT', 'reuse', $current, [ $current ], 'current_mapping' );
            continue;
        }
        $owned = provisioning_owned_role_for_recommendation( $blueprint_key, 'category', (string) $role );
        if ( provisioning_candidate_exists( $owned, $terms ) ) {
            $out['categories'][ $role ] = provisioning_recommendation_result( 'PROVISIONED_REUSE', 'reuse', $owned, [ $owned ], 'provisioning_provenance' );
            continue;
        }
        $matches = provisioning_exact_term_candidates( (string) ( $definition['name'] ?? '' ), $terms );
        if ( 1 === count( $matches ) ) {
            $out['categories'][ $role ] = provisioning_recommendation_result( 'SUGGESTED_REUSE', 'reuse', $matches[0], $matches, 'unique_exact_label' );
        } elseif ( count( $matches ) > 1 ) {
            $out['categories'][ $role ] = provisioning_recommendation_result( 'AMBIGUOUS', 'skip', 0, $matches, 'multiple_exact_labels' );
        } else {
            $out['categories'][ $role ] = provisioning_recommendation_result( 'SUGGESTED_CREATE', 'create', 0, [], 'no_safe_candidate' );
        }
    }

    return $out;
}
