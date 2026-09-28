<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');

foreach ([
    "homepage_effective_source_value( 'law-01', 'about', \$settings )",
    "homepage_effective_source_value( 'law-01', 'team', \$settings )",
    "homepage_effective_source_value( 'law-01', 'services', \$settings )",
] as $needle) {
    if (! str_contains($profile, $needle)) {
        fwrite(STDERR, "FAIL: Profile primary surface no longer preserves the proven legacy mapping: {$needle}\n");
        exit(1);
    }
}

echo "PASS: Profile primary About/Team/Services surface preserves the effective scoped mapping path.\n";
