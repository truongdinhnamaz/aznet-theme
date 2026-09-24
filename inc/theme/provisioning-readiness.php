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
    return in_array( $blueprint, [ 'law01-v1', 'law01-v1-1', 'law01-v1-2' ], true );
}

/** @return array<int,string> */
function provisioning_launch_warnings(): array {
    $warnings = [];
    if ( function_exists( 'has_custom_logo' ) && ! has_custom_logo() ) { $warnings[] = 'Bổ sung hoặc xác nhận logo thực tế.'; }
    $s = settings();
    $contact_id = (int) homepage_source_value( 'law-01', 'contact', $s );
    if ( $contact_id > 0 && function_exists( 'get_post_meta' ) && provisioning_is_law_starter_blueprint( (string) get_post_meta( $contact_id, PROVISIONING_META_BLUEPRINT, true ) ) ) {
        $warnings[] = 'Xác nhận thông tin liên hệ thực tế trước khi phát hành.';
    }

    $starter_found = false;
    foreach ( [ 'services', 'about', 'team', 'process', 'faq', 'contact' ] as $slot ) {
        $id = (int) homepage_source_value( 'law-01', $slot, $s );
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
            && provisioning_is_law_starter_blueprint( (string) get_post_meta( $thumb_id, PROVISIONING_META_BLUEPRINT, true ) )
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
    foreach ( [ 'services', 'about', 'contact' ] as $role ) {
        if ( ! provisioning_readiness_page_ok( (int) homepage_source_value( 'law-01', $role, $s ) ) ) { $missing[] = $role; }
    }
    $locations = function_exists( 'get_nav_menu_locations' ) ? get_nav_menu_locations() : [];
    if ( (int) ( $locations['primary'] ?? 0 ) <= 0 ) { $missing[] = 'primary_menu'; }
    if ( ! function_exists( __NAMESPACE__ . '\\homepage_composer_active' ) ) { $missing[] = 'homepage_composer'; }

    $optional = [];
    foreach ( [ 'team', 'process', 'faq' ] as $role ) {
        if ( (int) homepage_source_value( 'law-01', $role, $s ) <= 0 ) { $optional[] = $role; }
    }
    if ( [] === (array) homepage_source_value( 'law-01', 'knowledge', $s ) ) { $optional[] = 'knowledge'; }
    if ( (int) homepage_source_value( 'law-01', 'case_analysis', $s ) <= 0 ) { $optional[] = 'case_analysis'; }
    if ( (int) homepage_source_value( 'law-01', 'legal_news', $s ) <= 0 ) { $optional[] = 'legal_news'; }
    $launch = provisioning_launch_warnings();
    $setup_ready = [] === $missing;
    $status = ! $setup_ready ? 'INCOMPLETE' : ( [] !== $optional || [] !== $launch ? 'READY_WITH_WARNINGS' : 'READY' );
    return [ 'status' => $status, 'setup_ready' => $setup_ready, 'missing_required' => $missing, 'optional_warnings' => $optional, 'launch_warnings' => $launch ];
}
