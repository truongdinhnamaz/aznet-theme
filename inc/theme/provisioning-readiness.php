<?php
/** Setup-vs-launch readiness for Law Site Provisioning. */
namespace AZnet\Theme;
if ( ! defined( 'ABSPATH' ) ) { exit; }

function provisioning_readiness_page_ok( int $id ): bool {
    if ( $id <= 0 ) { return false; }
    $post = get_post( $id );
    return $post instanceof \WP_Post && 'page' === $post->post_type && 'publish' === $post->post_status;
}

function provisioning_is_law_starter_blueprint( string $blueprint ): bool {
    return in_array( $blueprint, [ 'law01-v1', 'law01-v1-1' ], true );
}

/** @return array<int,string> */
function provisioning_launch_warnings(): array {
    $warnings = [];
    if ( function_exists( 'has_custom_logo' ) && ! has_custom_logo() ) { $warnings[] = 'Bổ sung hoặc xác nhận logo thực tế.'; }
    $s = settings();
    $contact_id = (int) ( $s['homepage_contact_page'] ?? 0 );
    if ( $contact_id > 0 && function_exists( 'get_post_meta' ) && provisioning_is_law_starter_blueprint( (string) get_post_meta( $contact_id, PROVISIONING_META_BLUEPRINT, true ) ) ) {
        $warnings[] = 'Xác nhận thông tin liên hệ thực tế trước khi phát hành.';
    }

    $starter_found = false;
    foreach ( [ 'homepage_services_page','homepage_about_page','homepage_team_page','homepage_process_page','homepage_faq_page','homepage_contact_page' ] as $key ) {
        $id = (int) ( $s[ $key ] ?? 0 );
        if ( $id > 0 && function_exists( 'get_post_meta' ) && provisioning_is_law_starter_blueprint( (string) get_post_meta( $id, PROVISIONING_META_BLUEPRINT, true ) ) ) { $starter_found = true; break; }
    }
    if ( $starter_found ) { $warnings[] = 'Rà soát và thay starter copy bằng nội dung thực tế của tổ chức.'; }

    if ( function_exists( __NAMESPACE__ . '\\provisioning_untouched_starter_post_ids' ) && [] !== provisioning_untouched_starter_post_ids() ) {
        $warnings[] = 'Rà soát các bài viết mẫu chưa được chỉnh sửa trước khi phát hành.';
    }

    $front_id = (int) get_option( 'page_on_front', 0 );
    if ( $front_id > 0 && function_exists( 'has_post_thumbnail' ) && ! has_post_thumbnail( $front_id ) ) {
        $warnings[] = 'Bổ sung hình ảnh thực tế phù hợp cho Trang chủ nếu cần.';
    } elseif ( $front_id > 0 && function_exists( 'get_post_thumbnail_id' ) && function_exists( 'get_post_meta' ) ) {
        $thumb_id = (int) get_post_thumbnail_id( $front_id );
        if ( $thumb_id > 0
            && 'law01-v1-1' === (string) get_post_meta( $thumb_id, PROVISIONING_META_BLUEPRINT, true )
            && 'starter_media:hero' === (string) get_post_meta( $thumb_id, PROVISIONING_META_ROLE, true ) ) {
            $warnings[] = 'Thay ảnh Hero mẫu bằng hình ảnh thực tế của website khi có thể.';
        }
    }

    if ( function_exists( __NAMESPACE__ . '\\provisioning_index_restore_available' ) && provisioning_index_restore_available() ) {
        $warnings[] = 'Website đang tạm ngăn công cụ tìm kiếm lập chỉ mục do Thiết lập nhanh.';
    }

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
