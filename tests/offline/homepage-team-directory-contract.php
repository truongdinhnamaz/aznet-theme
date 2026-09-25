<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$assets = file_get_contents($root . '/inc/theme/assets.php');

foreach ([
    'team_directory_members( 4 )',
    "'template-parts/team/card'",
    "esc_html_e( 'Xem tất cả'",
] as $needle) {
    if (! str_contains((string) $profile, $needle)) {
        fwrite(STDERR, "FAIL: Homepage Team missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'law01_reference_art_urls',
    'team-illustration--generated',
    'team_illustration_url',
] as $forbidden) {
    if (str_contains((string) $profile, $forbidden)) {
        fwrite(STDERR, "FAIL: Homepage Team still exposes fake-person fallback {$forbidden}\n");
        exit(1);
    }
}

if (! str_contains((string) $assets, "'aznet-theme-team-card'")) {
    fwrite(STDERR, "FAIL: Homepage Team does not enqueue shared Team card CSS\n");
    exit(1);
}

echo "PASS: Homepage Team uses only real WordPress child Pages\n";
