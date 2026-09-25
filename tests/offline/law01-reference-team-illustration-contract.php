<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');

foreach ([
    'law01_reference_art_urls',
    'team-illustration--generated',
    'team_illustration_url',
    'team_illustration_caption',
] as $forbidden) {
    if (str_contains($profile, $forbidden)) {
        fwrite(STDERR, "FAIL: Law 01 Team still renders illustrative people fallback: {$forbidden}\n");
        exit(1);
    }
}

if (! str_contains($profile, 'team_directory_members( 4 )')) {
    fwrite(STDERR, "FAIL: Law 01 Team does not use the WordPress-native Team directory.\n");
    exit(1);
}

if (! str_contains($profile, "'template-parts/team/card'")) {
    fwrite(STDERR, "FAIL: Law 01 Team does not use the shared real-member card.\n");
    exit(1);
}

echo "PASS: Law 01 Team has no generated/reference person fallback.\n";
