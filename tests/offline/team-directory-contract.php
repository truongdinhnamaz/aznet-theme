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
    'function team_directory_public_members',
    'function team_directory_member_is_child',
    'function team_directory_next_menu_order',
    "homepage_effective_source_value( 'law-01', 'team'",
    "'post_parent'",
    "'post_status'    => 'publish'",
    "'orderby'        => 'menu_order title'",
] as $needle) {
    if (! str_contains($module, $needle)) {
        fwrite(STDERR, "FAIL: missing Team directory contract: {$needle}\n");
        exit(1);
    }
}

foreach (['register_post_type(', 'add_post_meta(', 'get_page_by_path(', 'post_name'] as $forbidden) {
    if (str_contains($module, $forbidden)) {
        fwrite(STDERR, "FAIL: forbidden Team ownership shortcut: {$forbidden}\n");
        exit(1);
    }
}

foreach (['_aznet_theme_team_phone', '_aznet_theme_team_zalo'] as $allowedMeta) {
    if (! str_contains($module, $allowedMeta)) {
        fwrite(STDERR, "FAIL: D-047 Team contact meta missing {$allowedMeta}\n");
        exit(1);
    }
}

preg_match_all("/_aznet_theme_team_[a-z0-9_]+/", $module, $teamMetaKeys);
$uniqueTeamMetaKeys = array_values(array_unique($teamMetaKeys[0] ?? []));
sort($uniqueTeamMetaKeys);
if ($uniqueTeamMetaKeys !== ['_aznet_theme_team_phone', '_aznet_theme_team_zalo']) {
    fwrite(STDERR, 'FAIL: D-047 allows exactly phone/Zalo Team meta, got ' . json_encode($uniqueTeamMetaKeys) . "\n");
    exit(1);
}

if (! str_contains($bootstrap, "require_once __DIR__ . '/team-directory.php';")) {
    fwrite(STDERR, "FAIL: Team directory module not bootstrapped\n");
    exit(1);
}

foreach (['team_member', 'member_image', 'member_role', 'get_permalink', 'aznet-theme-law01-profile__member', 'aznet-theme-law01-team-card__media', 'aznet-theme-law01-profile__member-image', 'aznet-theme-law01-team-card__body'] as $needle) {
    if (! str_contains($card, $needle)) {
        fwrite(STDERR, "FAIL: shared Team card missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Team directory ownership/read-model contract\n";
