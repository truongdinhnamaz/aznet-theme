<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$surface = (string) file_get_contents($root . '/inc/theme/homepage-surface-map.php');

foreach ([
    "homepage_effective_source_value( 'law-01', 'about', \$settings )",
    "homepage_effective_source_value( 'law-01', 'team', \$settings )",
    "homepage_effective_source_value( 'law-01', 'services', \$settings )",
] as $needle) {
    if (! str_contains($profile, $needle)) {
        fwrite(STDERR, "FAIL: Profile primary surface is no longer routed through the effective compatibility resolver: {$needle}\n");
        exit(1);
    }
}

if (! str_contains($surface, 'return homepage_source_value( $preset, $slot, $settings );')) {
    fwrite(STDERR, "FAIL: Effective Homepage resolver no longer delegates to the preset source resolver.\n");
    exit(1);
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}
require_once $root . '/inc/theme/homepage-authoring.php';

$legacy = [
    'homepage_about_page'    => 101,
    'homepage_team_page'     => 102,
    'homepage_services_page' => 103,
];

$cases = [
    'about'    => 101,
    'team'     => 102,
    'services' => 103,
];

foreach ($cases as $slot => $expected) {
    $actual = \AZnet\Theme\homepage_source_value('law-01', $slot, $legacy);
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: Law 01 {$slot} no longer falls back to the proven legacy mapping; expected {$expected}, got " . var_export($actual, true) . "\n");
        exit(1);
    }
}

$scoped = $legacy + [
    'homepage_law01_about_page'       => 201,
    'homepage_law01_team_page'        => 202,
    'homepage_law01_services_page'    => 203,
    'homepage_law01_sources_initialized' => true,
];

foreach (['about' => 201, 'team' => 202, 'services' => 203] as $slot => $expected) {
    $actual = \AZnet\Theme\homepage_source_value('law-01', $slot, $scoped);
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: Law 01 {$slot} scoped mapping does not supersede legacy after explicit initialization.\n");
        exit(1);
    }
}

echo "PASS: Profile preserves 1.3.53 About/Team/Services behavior through the scoped resolver with proven legacy fallback.\n";
