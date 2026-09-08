<?php
/** Setup-vs-launch readiness for Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

function provisioning_readiness_page_ok( int $id ): bool {
    if ( $id <= 0 ) { return false; }
    $post = get_post( $id );
    return $post instanceof \WP_Post && 'page' === $post->post_type && 'publish' === $post->post_status;
}

/** @return array<int,string> */
function provisioning_launch_warnings(): array {
    $warnings = [];
    if ( function_exists( 'has_custom_logo' ) && ! has_custom_logo() ) { $warnings[] = 'Bổ sung hoặc xác nhận logo thực tế.'; }
    $s = settings();
    $contact_id = (int) ( $s['homepage_contact_page'] ?? 0 );
    if ( $contact_id > 0 && function_exists( 'get_post_meta' ) && 'law01-v1' === (string) get_post_meta( $contact_id, PROVISIONING_META_BLUEPRINT, true ) ) {
        $warnings[] = 'Xác nhận thông tin liên hệ thực tế trước khi phát hành.';
    }
    $starter_found = false;
    foreach ( [ 'homepage_services_page','homepage_about_page','homepage_team_page','homepage_process_page','homepage_faq_page','homepage_contact_page' ] as $key ) {
        $id = (int) ( $s[ $key ] ?? 0 );
        if ( $id > 0 && function_exists( 'get_post_meta' ) && 'law01-v1' === (string) get_post_meta( $id, PROVISIONING_META_BLUEPRINT, true ) ) { $starter_found = true; break; }
    }
    if ( $starter_found ) { $warnings[] = 'Rà soát và thay starter copy bằng nội dung thực tế của tổ chức.'; }
    $front_id = (int) get_option( 'page_on_front', 0 );
    if ( $front_id > 0 && function_exists( 'has_post_thumbnail' ) && ! has_post_thumbnail( $front_id ) ) { $warnings[] = 'Bổ sung hình ảnh thực tế phù hợp cho Trang chủ nếu cần.'; }
    return array_values( array_unique( $warnings ) );
}

/** @return array<string,mixed> */
function provisioning_readiness(): array {
    $s = settings();
    $missing = [];
    if ( 'page' !== (string) get_option( 'show_on_front', 'posts' ) || ! provisioning_readiness_page_ok( (int) get_option( 'page_on_front', 0 ) ) ) { $missing[] = 'front_page'; }
    if ( 'law-01' !== (string) ( $s['homepage_preset'] ?? 'off' ) ) { $missing[] = 'law01_preset'; }
    foreach ( [ 'services' => 'homepage_services_page', 'about' => 'homepage_about_page', 'contact' => 'homepage_contact_page' ] as $role => $key ) {
        if ( ! provisioning_readiness_page_ok( (int) ( $s[ $key ] ?? 0 ) ) ) { $missing[] = $role; }
    }
    $locations = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : [];
    if ( (int) ( $locations['primary'] ?? 0 ) <= 0 ) { $missing[] = 'primary_menu'; }
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' ) ) { $missing[] = 'homepage_composer'; }

    $optional = [];
    foreach ( [ 'team' => 'homepage_team_page', 'process' => 'homepage_process_page', 'faq' => 'homepage_faq_page' ] as $role => $key ) {
        if ( (int) ( $s[ $key ] ?? 0 ) <= 0 ) { $optional[] = $role; }
    }
    if ( [] === (array) ( $s['homepage_knowledge_terms'] ?? [] ) ) { $optional[] = 'knowledge'; }
    if ( (int) ( $s['homepage_case_analysis_term'] ?? 0 ) <= 0 ) { $optional[] = 'case_analysis'; }
    if ( (int) ( $s['homepage_legal_news_term'] ?? 0 ) <= 0 ) { $optional[] = 'legal_news'; }
    $launch = provisioning_launch_warnings();
    $setup_ready = [] === $missing;
    $status = ! $setup_ready ? 'INCOMPLETE' : ( [] !== $optional || [] !== $launch ? 'READY_WITH_WARNINGS' : 'READY' );
    return [ 'status' => $status, 'setup_ready' => $setup_ready, 'missing_required' => $missing, 'optional_warnings' => $optional, 'launch_warnings' => $launch ];
}
