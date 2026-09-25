<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$module = file_get_contents($root . '/inc/theme/team-directory.php');
$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
$card = file_get_contents($root . '/template-parts/team/card.php');

if (false === $module || false === $bootstrap || false === $card) {
    fwrite(STDERR, "FAIL: Team directory files missing\n");
    exit(1);
}

foreach ([
    'function team_directory_parent',
    'function team_directory_members',
    'function team_directory_member_is_child',
    'function team_directory_next_menu_order',
    "homepage_source_value( 'law-01', 'team'",
    "'post_parent'",
    "'post_status'    => 'publish'",
    "'orderby'        => 'menu_order title'",
] as $needle) {
    if (! str_contains($module, $needle)) {
        fwrite(STDERR, "FAIL: missing Team directory contract: {$needle}\n");
        exit(1);
    }
}

foreach (['register_post_type(', 'update_post_meta(', 'add_post_meta(', 'get_page_by_path(', 'post_name'] as $forbidden) {
    if (str_contains($module, $forbidden)) {
        fwrite(STDERR, "FAIL: forbidden Team ownership shortcut: {$forbidden}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "require_once __DIR__ . '/team-directory.php';")) {
    fwrite(STDERR, "FAIL: Team directory module not bootstrapped\n");
    exit(1);
}

foreach (['team_member', 'member_image', 'member_role', 'get_permalink'] as $needle) {
    if (! str_contains($card, $needle)) {
        fwrite(STDERR, "FAIL: shared Team card missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Team directory ownership/read-model contract\n";
