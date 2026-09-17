<?php
/**
 * Generic Professional Services provisioning blueprint definition.
 *
 * @package AZnetTheme
 */

namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Resolve a neutral Page Kit body without inventing organization facts. */
function professional_services_page_content( string $role, string $fallback ): string {
    if ( function_exists( __NAMESPACE__ . '\\professional_page_kit_content' ) ) {
        $content = trim( professional_page_kit_content( $role ) );
        if ( '' !== $content ) {
            return $content;
        }
    }

    return '<p>' . $fallback . '</p>';
}

/** Resolve the portable native professional-services Homepage pattern. */
function professional_services_homepage_content(): string {
    if ( class_exists( 'WP_Block_Patterns_Registry' ) ) {
        $pattern = \WP_Block_Patterns_Registry::get_instance()->get_registered( 'aznet-theme/homepage-professional-services' );
        if ( is_array( $pattern ) && isset( $pattern['content'] ) && '' !== trim( (string) $pattern['content'] ) ) {
            return (string) $pattern['content'];
        }
    }

    return '<p>Hãy thay nội dung này bằng phần giới thiệu ngắn về tổ chức, nhóm dịch vụ và cách khách hàng có thể bắt đầu liên hệ.</p>';
}

/**
 * Return the generic professional-services starter definition.
 *
 * The definition contains presentation-ready starter copy only. WordPress remains
 * authoritative for Page, Menu and publication state after a confirmed plan runs.
 *
 * @return array<string,mixed>
 */
function professional_services_blueprint(): array {
    $page = static fn( string $title, string $excerpt, string $content ): array => [
        'title'       => $title,
        'status'      => 'publish',
        'excerpt'     => $excerpt,
        'content'     => $content,
        'parent_role' => null,
    ];

    return [
        'key'             => 'professional-services-v1',
        'homepage_preset' => 'off',
        'pages'           => [
            'home'     => $page(
                'Giải pháp chuyên nghiệp cho nhu cầu của bạn',
                'Trang giới thiệu tổng quan có thể chỉnh sửa trực tiếp bằng nội dung WordPress.',
                professional_services_homepage_content()
            ),
            'about'    => $page(
                'Giới thiệu',
                'Giới thiệu tổ chức, cách làm việc và những thông tin cần được thay bằng dữ liệu thực tế.',
                professional_services_page_content( 'about', 'Hãy thay nội dung này bằng thông tin thực tế về tổ chức, cách làm việc và các giá trị muốn trình bày với khách hàng.' )
            ),
            'services' => $page(
                'Dịch vụ',
                'Tổng quan các nhóm dịch vụ để khách hàng hiểu nơi nên bắt đầu.',
                professional_services_page_content( 'services', 'Hãy mô tả các nhóm dịch vụ thực tế, phạm vi hỗ trợ và cách khách hàng có thể tìm hiểu thêm.' )
            ),
            'team'     => $page(
                'Đội ngũ',
                'Trang dành cho thông tin đội ngũ hoặc chuyên môn thực tế của tổ chức.',
                professional_services_page_content( 'team', 'Hãy bổ sung hồ sơ đội ngũ hoặc thông tin chuyên môn từ nguồn chính thức của website; Theme không tạo người hay thành tích mẫu.' )
            ),
            'contact'  => $page(
                'Liên hệ',
                'Trang để bổ sung phương thức liên hệ và hướng dẫn tiếp nhận yêu cầu thực tế.',
                professional_services_page_content( 'contact', 'Hãy cập nhật phương thức liên hệ thực tế của tổ chức trước khi đưa website vào vận hành chính thức.' )
            ),
        ],
        'categories'      => [],
        'menu'            => [
            'location' => 'primary',
            'label'    => 'AZnet Primary',
            'roles'    => [ 'home', 'about', 'services', 'team', 'contact' ],
            'labels'   => [
                'home'     => 'Trang chủ',
                'about'    => 'Giới thiệu',
                'services' => 'Dịch vụ',
                'team'     => 'Đội ngũ',
                'contact'  => 'Liên hệ',
            ],
        ],
        'editorial_examples' => [
            'status' => 'draft',
            'items'  => [],
        ],
    ];
}
