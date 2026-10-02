<?php
/** Team backend completeness contract: all direct members + bounded full-profile editing. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$team = (string) file_get_contents($root . '/inc/theme/team-directory.php');
$homepage = (string) file_get_contents($root . '/inc/admin/homepage.php');
$authoring = (string) file_get_contents($root . '/inc/admin/homepage-authoring.php');
$bootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');

foreach ([
    'function team_directory_admin_members',
    "'post_parent'",
    "'post_status'",
] as $needle) {
    if (! str_contains($team, $needle)) {
        fwrite(STDERR, "FAIL: Team admin projection missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'team_directory_admin_members()',
    'function render_homepage_team_member_editor',
    'Tất cả nhân sự',
    'Tiểu sử / giới thiệu',
    'Thứ tự hiển thị',
    'Cập nhật đường dẫn theo tên',
    'Chỉnh nâng cao trong WordPress',
    'Xem hồ sơ',
    'data-team-member-status',
] as $needle) {
    if (! str_contains($homepage, $needle)) {
        fwrite(STDERR, "FAIL: Team manager UI missing {$needle}\n");
        exit(1);
    }
}
if (str_contains($homepage, 'team_directory_members( 4 )')) {
    fwrite(STDERR, "FAIL: Team backend still truncates management UI to four members.\n");
    exit(1);
}

foreach ([
    'function homepage_team_member_update_data',
    'function handle_homepage_team_member_update',
    "'post_content'",
    "'menu_order'",
    "'post_name'",
    'sanitize_title( $name )',
    'team_directory_member_is_child',
    'wp_update_post(',
] as $needle) {
    if (! str_contains($authoring, $needle)) {
        fwrite(STDERR, "FAIL: Team update path missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "admin_post_aznet_theme_update_team_member")) {
    fwrite(STDERR, "FAIL: Team update action not registered.\n");
    exit(1);
}

$createStart = strpos($authoring, 'function homepage_team_member_insert_data');
$createEnd = strpos($authoring, 'function handle_homepage_team_member_create', false === $createStart ? 0 : $createStart);
if (false === $createStart || false === $createEnd || $createEnd <= $createStart) {
    fwrite(STDERR, "FAIL: Team create helper boundary missing.\n");
    exit(1);
}
$createHelper = substr($authoring, $createStart, $createEnd - $createStart);
if (! str_contains($createHelper, "'post_content'")) {
    fwrite(STDERR, "FAIL: Team create helper does not support biography content.\n");
    exit(1);
}

foreach (['register_post_type(', 'update_post_meta(', 'add_post_meta(', 'RootProfile', 'get_page_by_path('] as $forbidden) {
    if (str_contains($team . $homepage . $authoring, $forbidden)) {
        fwrite(STDERR, "FAIL: Team manager crossed ownership boundary: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Team backend complete bounded management UX contract\n";
