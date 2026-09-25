<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = file_get_contents($root . '/inc/admin/homepage.php');
$authoring = file_get_contents($root . '/inc/admin/homepage-authoring.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');

foreach ([
    'function render_homepage_team_authoring',
    "team_directory_members( 4 )",
    'Thêm nhân sự',
    'Xem tất cả trên website',
] as $needle) {
    if (! str_contains((string) $homepage, $needle)) {
        fwrite(STDERR, "FAIL: Team admin missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function homepage_team_member_insert_data',
    'function handle_homepage_team_member_create',
    "'post_status'  => 'draft'",
    "'post_parent'",
    'team_directory_next_menu_order()',
    'wp_insert_post(',
] as $needle) {
    if (! str_contains((string) $authoring, $needle)) {
        fwrite(STDERR, "FAIL: Team create action missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains((string) $bootstrap, "admin_post_aznet_theme_create_team_member")) {
    fwrite(STDERR, "FAIL: Team create action not registered\n");
    exit(1);
}

echo "PASS: Homepage Team bounded authoring contract\n";
