<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = file_get_contents($root . '/inc/admin/homepage.php');
$authoring = file_get_contents($root . '/inc/admin/homepage-authoring.php');
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
$directory = file_get_contents($root . '/inc/theme/team-directory.php');

foreach ([
    'team_directory_managed_members()',
    'aznet_theme_delete_team_member',
    'Xóa',
    'data-team-member-delete',
] as $needle) {
    if (! str_contains((string) $homepage, $needle)) {
        fwrite(STDERR, "FAIL: Team management UI missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function handle_homepage_team_member_delete',
    "check_admin_referer( 'aznet_theme_delete_team_member_",
    'team_directory_member_is_child',
    'wp_trash_post(',
    'team_member_deleted',
] as $needle) {
    if (! str_contains((string) $authoring, $needle)) {
        fwrite(STDERR, "FAIL: Team delete action missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains((string) $bootstrap, 'admin_post_aznet_theme_delete_team_member')) {
    fwrite(STDERR, "FAIL: Team delete action not registered\n");
    exit(1);
}

foreach ([
    'function team_directory_managed_members',
    "'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ]",
    "'post_parent'    => (int) $parent->ID",
] as $needle) {
    if (! str_contains((string) $directory, $needle)) {
        fwrite(STDERR, "FAIL: Team managed member resolver missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Homepage Team management contract\n";
