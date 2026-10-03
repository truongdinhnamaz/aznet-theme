<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$directory = (string) file_get_contents($root . '/inc/theme/team-directory.php');
$homepage = (string) file_get_contents($root . '/inc/admin/homepage.php');
$authoring = (string) file_get_contents($root . '/inc/admin/homepage-authoring.php');
$card = (string) file_get_contents($root . '/template-parts/team/card.php');
$cardCss = (string) file_get_contents($root . '/assets/css/components/team-card.css');

foreach ([
    "function team_member_phone",
    "function team_member_zalo",
    "function team_member_phone_display",
    "function team_member_phone_href",
    "function team_member_zalo_url",
    "function save_team_member_contact",
    "'_aznet_theme_team_phone'",
    "'_aznet_theme_team_zalo'",
    "https://zalo.me/",
] as $needle) {
    if (! str_contains($directory, $needle)) {
        fwrite(STDERR, "FAIL: Team contact model missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    "Số điện thoại",
    "Zalo",
    'name="team_member_phone"',
    'name="team_member_zalo"',
] as $needle) {
    if (! str_contains($homepage, $needle)) {
        fwrite(STDERR, "FAIL: Team admin contact UI missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    "save_team_member_contact",
    "team_member_phone",
    "team_member_zalo",
] as $needle) {
    if (! str_contains($authoring, $needle)) {
        fwrite(STDERR, "FAIL: Team authoring does not persist {$needle}\n");
        exit(1);
    }
}

$createStart = strpos($authoring, 'function handle_homepage_team_member_create');
$createEnd = strpos($authoring, '/** Move one Team member Page to Trash', $createStart ?: 0);
$createHandler = false !== $createStart ? substr($authoring, $createStart, false !== $createEnd ? $createEnd - $createStart : null) : '';
foreach ([
    "\$phone = isset( \$_POST['team_member_phone'] )",
    "\$zalo = isset( \$_POST['team_member_zalo'] )",
    "save_team_member_contact( (int) \$id, \$phone, \$zalo )",
] as $needle) {
    if (! str_contains($createHandler, $needle)) {
        fwrite(STDERR, "FAIL: Team create flow missing contact input/persistence {$needle}\n");
        exit(1);
    }
}

if (str_contains($authoring, "aznet_theme_settings['team_member_phone']")
    || str_contains($authoring, "aznet_theme_settings['team_member_zalo']")) {
    fwrite(STDERR, "FAIL: Team contact facts must not be stored in Theme settings\n");
    exit(1);
}

foreach ([
    "team_member_phone(",
    "team_member_zalo(",
    "team_member_phone_href(",
    "team_member_zalo_url(",
    "aznet-theme-team-card__contacts",
    "aznet-theme-team-card__contact--phone",
    "aznet-theme-team-card__contact--zalo",
] as $needle) {
    if (! str_contains($card, $needle)) {
        fwrite(STDERR, "FAIL: Team card contact presentation missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    ".aznet-theme-team-card__contacts",
    ".aznet-theme-team-card__contact",
    ":focus-visible",
] as $needle) {
    if (! str_contains($cardCss, $needle)) {
        fwrite(STDERR, "FAIL: Team card contact CSS missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Team member phone/Zalo WordPress-native contact contract\n";
