<?php
/**
 * Curtain 01 presentation-only WordPress-native starter recipe.
 * Product/variant/order/project business data remains with its source owner.
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
    'key'                       => 'curtain01-v1',
    'homepage_preset'           => 'curtain-01',
    'existing_site_page_status' => 'draft',
    'required_page_roles'       => [ 'home', 'about', 'contact' ],
    'pages' => [
        'home'       => $page( 'Trang chủ', 'Điểm bắt đầu của website rèm và giải pháp không gian.', 'Hãy thay phần giới thiệu này bằng nội dung về thế mạnh, dòng rèm và giá trị phục vụ thực tế của doanh nghiệp. Ảnh và liên kết sản phẩm do WordPress hoặc hệ thống thương mại tương ứng quản lý.' ),
        'about'      => $page( 'Giới thiệu', 'Giới thiệu thương hiệu, kinh nghiệm và cách làm việc.', 'Hãy bổ sung thông tin xác thực về doanh nghiệp, hành trình phát triển, đội ngũ và những cam kết có nguồn chứng minh.' ),
        'collection' => $page( 'Bộ sưu tập', 'Không gian tham khảo các phong cách và hình ảnh rèm.', 'Hãy sử dụng Thư viện Media WordPress để bổ sung ảnh đã được phép sử dụng, kèm chú thích và nguồn phù hợp. Trang này không tạo hoặc đồng bộ sản phẩm WooCommerce.' ),
        'projects'   => $page( 'Công trình', 'Giới thiệu các công trình đã được doanh nghiệp xác thực.', 'Hãy trình bày công trình có thật với hình ảnh, phạm vi thực hiện và sự cho phép đăng tải thích hợp. Theme không tạo công trình hoặc lời chứng thực giả định.' ),
        'knowledge'  => $page( 'Kiến thức về rèm', 'Nội dung hữu ích về lựa chọn và sử dụng rèm.', 'Hãy giới thiệu các bài hướng dẫn chọn chất liệu, cách đo kích thước và bảo quản rèm. Bài viết và chuyên mục tiếp tục do WordPress quản lý.' ),
        'contact'    => $page( 'Liên hệ', 'Thông tin để khách hàng liên hệ và trao đổi nhu cầu.', 'Hãy cập nhật số điện thoại, địa chỉ, email và phương thức liên hệ đã được doanh nghiệp xác nhận. Không sử dụng thông tin liên hệ mẫu để xuất bản.' ),
    ],
    'categories' => [
        'knowledge' => $category( 'Kiến thức về rèm' ),
        'projects'  => $category( 'Công trình rèm' ),
    ],
    'menu' => [
        'location' => 'primary',
        'label'    => 'AZnet Primary',
        'roles'    => [ 'home', 'about', 'collection', 'projects', 'knowledge', 'contact' ],
        'labels'   => [
            'home'       => 'Trang chủ',
            'about'      => 'Giới thiệu',
            'collection' => 'Bộ sưu tập',
            'projects'   => 'Công trình',
            'knowledge'  => 'Kiến thức',
            'contact'    => 'Liên hệ',
        ],
    ],
    'editorial_examples' => [ 'status' => 'draft', 'items' => [] ],
];
