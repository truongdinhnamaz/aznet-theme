<?php
/**
 * Theme-owned presentation lexicon for reusable presets.
 *
 * Shared controls remain generic. Preset terms are presentation labels only
 * and never become authoritative domain semantics.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Return presentation labels for a reusable preset.
 *
 * @return array<string, string>
 */
function preset_lexicon( string $preset ): array {
    $shared = [
        'learn_more' => __( 'Tìm hiểu thêm', 'aznet-theme' ),
        'view_all'   => __( 'Xem tất cả', 'aznet-theme' ),
        'contact'    => __( 'Liên hệ', 'aznet-theme' ),
    ];

    $preset_terms = [
        'curtain-01' => [
            'primary_group' => __( 'Dòng rèm', 'aznet-theme' ),
            'catalog'       => __( 'Bộ sưu tập', 'aznet-theme' ),
            'showcase'      => __( 'Công trình', 'aznet-theme' ),
            'knowledge'     => __( 'Kiến thức về rèm', 'aznet-theme' ),
            'primary_cta'   => __( 'Nhận tư vấn', 'aznet-theme' ),
        ],
        'law-01' => [
            'primary_group' => __( 'Lĩnh vực hành nghề', 'aznet-theme' ),
            'people'        => __( 'Luật sư', 'aznet-theme' ),
            'showcase'      => __( 'Vụ việc tiêu biểu', 'aznet-theme' ),
            'knowledge'     => __( 'Góc pháp lý', 'aznet-theme' ),
            'primary_cta'   => __( 'Đặt lịch tư vấn', 'aznet-theme' ),
        ],
        'industrial-01' => [
            'primary_group' => __( 'Danh mục thiết bị', 'aznet-theme' ),
            'catalog'       => __( 'Thiết bị & phụ kiện', 'aznet-theme' ),
            'showcase'      => __( 'Ứng dụng / Dự án', 'aznet-theme' ),
            'knowledge'     => __( 'Kiến thức kỹ thuật', 'aznet-theme' ),
            'people'        => __( 'Đội ngũ kỹ thuật', 'aznet-theme' ),
            'primary_cta'   => __( 'Yêu cầu báo giá', 'aznet-theme' ),
        ],
    ];

    return array_merge( $shared, $preset_terms[ $preset ] ?? [] );
}

/** Return one presentation label with a generic fallback. */
function preset_term( string $key, string $fallback = '', ?string $preset = null ): string {
    $preset = null !== $preset ? $preset : homepage_preset();
    $lexicon = preset_lexicon( $preset );
    return isset( $lexicon[ $key ] ) && is_string( $lexicon[ $key ] ) ? $lexicon[ $key ] : $fallback;
}
