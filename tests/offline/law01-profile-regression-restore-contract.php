<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');

foreach ([
    "setting( 'homepage_about_page', 0 )",
    "setting( 'homepage_team_page', 0 )",
    "setting( 'homepage_services_page', 0 )",
] as $needle) {
    if (! str_contains($profile, $needle)) {
        fwrite(STDERR, "FAIL: Profile primary surface no longer preserves the proven legacy mapping: {$needle}\n");
        exit(1);
    }
}

echo "PASS: Profile primary About/Team/Services surface preserves the pre-1.3.52 mapping path.\n";
