<?php
/**
 * Industrial 01 presentation-only WordPress-native starter recipe.
 * Products, prices, availability and commerce state remain WooCommerce-owned.
 */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

$page = static fn( string $title, string $excerpt, string $content ): array => [
    'title'       => $title,
    'status'      => 'publish',
    'excerpt'     => $excerpt,
    'content'     => '<p>' . $content . '</p>',
    'parent_role' => null,
];
$category = static fn( string $name ): array => [ 'name' => $name, 'parent_role' => null ];

return [
    'key'                       => 'industrial01-v1',
    'homepage_preset'           => 'industrial-01',
    'existing_site_page_status' => 'draft',
    'required_page_roles'       => [ 'home', 'about', 'contact' ],
    'pages' => [
        'home'      => $page( 'Trang chủ', 'Trang giới thiệu tổng quan về doanh nghiệp và nhóm giải pháp.', 'Hãy giới thiệu năng lực thực tế, nhóm thiết bị và cách khách hàng tìm thông tin kỹ thuật. Trang này không tự tạo dữ liệu hàng hóa, giá hay tồn kho.' ),
        'about'     => $page( 'Giới thiệu', 'Giới thiệu doanh nghiệp và phạm vi hoạt động.', 'Hãy cập nhật thông tin doanh nghiệp, năng lực kỹ thuật và các tài liệu có thể kiểm chứng. Không đăng chứng nhận hoặc thành tích chưa được xác thực.' ),
        'solutions' => $page( 'Giải pháp', 'Thông tin về những giải pháp kỹ thuật và ứng dụng.', 'Hãy mô tả vấn đề cần giải quyết, điều kiện ứng dụng và phạm vi hỗ trợ thực tế. Chi tiết sản phẩm, báo giá và trạng thái giao dịch thuộc hệ thống thương mại tương ứng.' ),
        'projects'  => $page( 'Dự án', 'Các ví dụ triển khai thực tế có thể kiểm chứng.', 'Hãy bổ sung dự án thực tế, mục tiêu, phạm vi triển khai và hình ảnh được phép công bố. Không sử dụng dự án hoặc đánh giá khách hàng giả định.' ),
        'knowledge' => $page( 'Kiến thức', 'Hướng dẫn đọc thông số, lựa chọn và sử dụng thiết bị.', 'Hãy biên tập bài kiến thức từ nguồn kỹ thuật tin cậy; nội dung Post và chuyên mục được quản lý bằng WordPress.' ),
        'contact'   => $page( 'Liên hệ', 'Thông tin liên hệ và tiếp nhận yêu cầu thực tế.', 'Hãy bổ sung địa chỉ, số điện thoại, email và phương thức gửi yêu cầu của doanh nghiệp. Không đưa thông tin liên hệ mẫu vào website đang hoạt động.' ),
    ],
    'categories' => [
        'knowledge' => $category( 'Kiến thức' ),
        'projects'  => $category( 'Dự án' ),
    ],
    'menu' => [
        'location' => 'primary',
        'label'    => 'AZnet Primary',
        'roles'    => [ 'home', 'about', 'solutions', 'projects', 'knowledge', 'contact' ],
        'labels'   => [
            'home'      => 'Trang chủ',
            'about'     => 'Giới thiệu',
            'solutions' => 'Giải pháp',
            'projects'  => 'Dự án',
            'knowledge' => 'Kiến thức',
            'contact'   => 'Liên hệ',
        ],
    ],
    'editorial_examples' => [ 'status' => 'draft', 'items' => [] ],
];
