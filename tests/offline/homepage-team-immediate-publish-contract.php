<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$authoring = (string) file_get_contents($root . '/inc/admin/homepage-authoring.php');
$homepage = (string) file_get_contents($root . '/inc/admin/homepage.php');

$start = strpos($authoring, 'function homepage_team_member_insert_data');
$end = strpos($authoring, 'function handle_homepage_team_member_create', $start === false ? 0 : $start);
if (false === $start || false === $end || $end <= $start) {
    fwrite(STDERR, "FAIL: Team member insert-data helper boundaries missing.\n");
    exit(1);
}
$insertHelper = substr($authoring, $start, $end - $start);

if (! str_contains($insertHelper, "'post_status'  => 'publish'")) {
    fwrite(STDERR, "FAIL: Add Member still does not publish immediately.\n");
    exit(1);
}

if (str_contains($insertHelper, "'post_status'  => 'draft'")) {
    fwrite(STDERR, "FAIL: Team Add Member helper still creates a draft Page.\n");
    exit(1);
}

if (! str_contains($homepage, "submit_button( __( 'Thêm nhân sự'")) {
    fwrite(STDERR, "FAIL: Team form still does not use the immediate-publish action label.\n");
    exit(1);
}

if (str_contains($homepage, 'Tạo bản nháp nhân sự')) {
    fwrite(STDERR, "FAIL: draft wording remains in Team add-member UI.\n");
    exit(1);
}

echo "PASS: Team Add Member publishes immediately and UI matches the behavior.\n";
