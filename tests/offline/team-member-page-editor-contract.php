<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$bootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');
$editor = (string) @file_get_contents($root . '/inc/admin/team-member-editor.php');
foreach ([
    "require_once __DIR__ . '/team-member-editor.php';",
    "'add_meta_boxes_page'",
    "'save_post_page'",
    "team_directory_member_is_child",
    "wp_nonce_field",
    "wp_verify_nonce",
    "current_user_can( 'edit_post'",
    "save_team_member_contact",
    "team_member_phone(",
    "team_member_zalo(",
] as $needle) {
    if (! str_contains($bootstrap . $editor, $needle)) {
        fwrite(STDERR, "FAIL: Page editor Team contact missing {$needle}\n");
        exit(1);
    }
}
echo "PASS: WordPress Team Page editor contact fields\n";
