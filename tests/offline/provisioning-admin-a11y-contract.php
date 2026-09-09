<?php
$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/provisioning.php');
if (false === $admin) { throw new RuntimeException('provisioning admin source unreadable'); }
foreach ([
    '<fieldset class="aznet-theme-provision-row"><legend>',
    'class="screen-reader-text" for="\' . esc_attr( $action_id )',
    'class="screen-reader-text" for="\' . esc_attr( $object_id )',
    'id="\' . esc_attr( $action_id )',
    'id="\' . esc_attr( $object_id )',
    'for="aznet-theme-provision-menu-action"',
    'id="aznet-theme-provision-menu-action"',
    'for="aznet-theme-provision-menu-id"',
    'id="aznet-theme-provision-menu-id"',
    'Dùng các thiết lập được khuyến nghị',
    'Tùy chỉnh nâng cao',
    'Gợi ý — chưa áp dụng',
    'Cần xác nhận',
    'Tạm thời ngăn công cụ tìm kiếm lập chỉ mục website',
    'Sử dụng nội dung hiện có',
    'Sẽ tạo mới',
    'Sẽ import media',
    'Sẽ thay đổi cấu hình',
    'Tôi đã xem các thay đổi trên và đồng ý áp dụng.',
    'name="confirm_plan"',
] as $needle) {
    if (!str_contains($admin, $needle)) { throw new RuntimeException('missing accessible/recommendation provisioning marker: ' . $needle); }
}
echo "PASS: Provisioning wizard exposes accessible recommendation-first confirmation UX\n";
