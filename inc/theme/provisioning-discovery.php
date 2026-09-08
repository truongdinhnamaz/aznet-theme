<?php
/** Read-only WordPress inventory for Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<int,array{id:int,title:string,status:string}> */
function provisioning_page_candidates(): array {
    $pages = get_pages( [
        'post_status' => [ 'publish', 'draft', 'private', 'pending' ],
        'sort_column' => 'post_title',
    ] );
    $out = [];
    foreach ( is_array( $pages ) ? $pages : [] as $page ) {
        if ( ! $page instanceof \WP_Post ) { continue; }
        $out[] = [ 'id' => (int) $page->ID, 'title' => (string) $page->post_title, 'status' => (string) $page->post_status ];
    }
    return $out;
}

/** @return array<int,array{id:int,name:string}> */
function provisioning_category_candidates(): array {
    $terms = get_categories( [ 'hide_empty' => false ] );
    $out = [];
    foreach ( is_array( $terms ) ? $terms : [] as $term ) {
        if ( ! $term instanceof \WP_Term || 'category' !== ( $term->taxonomy ?? 'category' ) ) { continue; }
        $out[] = [ 'id' => (int) $term->term_id, 'name' => (string) $term->name ];
    }
    return $out;
}

function provisioning_post_count(): int {
    $counts = wp_count_posts( 'post' );
    $total = 0;
    foreach ( [ 'publish', 'draft', 'private', 'pending', 'future' ] as $status ) {
        $total += isset( $counts->{$status} ) ? (int) $counts->{$status} : 0;
    }
    return $total;
}

/** @return array<string,mixed> */
function provisioning_discovery(): array {
    $s = settings();
    $locations = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : [];
    $slot_keys = [
        'services' => 'homepage_services_page',
        'about' => 'homepage_about_page',
        'team' => 'homepage_team_page',
        'knowledge' => 'homepage_knowledge_terms',
        'case_analysis' => 'homepage_case_analysis_term',
        'legal_news' => 'homepage_legal_news_term',
        'process' => 'homepage_process_page',
        'faq' => 'homepage_faq_page',
        'contact' => 'homepage_contact_page',
    ];
    $slots = [];
    foreach ( $slot_keys as $role => $key ) { $slots[ $role ] = $s[ $key ] ?? ( 'knowledge' === $role ? [] : 0 ); }
    $pages = provisioning_page_candidates();
    $categories = provisioning_category_candidates();
    return [
        'show_on_front' => (string) get_option( 'show_on_front', 'posts' ),
        'front_page_id' => (int) get_option( 'page_on_front', 0 ),
        'page_count' => count( $pages ),
        'category_count' => count( $categories ),
        'post_count' => provisioning_post_count(),
        'homepage_preset' => (string) ( $s['homepage_preset'] ?? 'off' ),
        'homepage_slots' => $slots,
        'primary_menu_id' => isset( $locations['primary'] ) ? (int) $locations['primary'] : 0,
        'pages' => $pages,
        'categories' => $categories,
    ];
}
