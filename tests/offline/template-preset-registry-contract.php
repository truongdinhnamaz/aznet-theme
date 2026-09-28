<?php
/** Core C1 registry-driven preset resolution contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-manifests.php';
require_once $root . '/inc/theme/settings.php';

$visual = \AZnet\Theme\registered_visual_preset_ids();
$homepage = \AZnet\Theme\registered_homepage_preset_ids();

foreach (['default', 'editorial', 'commerce', 'curtain-01'] as $preset) {
    if (! in_array($preset, $visual, true)) {
        fwrite(STDERR, "FAIL: visual preset registry missing {$preset}.\n");
        exit(1);
    }
}
foreach (['off', 'law-01', 'curtain-01'] as $preset) {
    if (! in_array($preset, $homepage, true)) {
        fwrite(STDERR, "FAIL: Homepage preset registry missing {$preset}.\n");
        exit(1);
    }
}

if (! \AZnet\Theme\register_template_manifest([
    'contract_version' => 1,
    'id' => 'fixture-c1',
    'name' => 'Fixture C1',
    'version' => '1.0.0',
    'category' => 'test',
    'description' => '',
    'capabilities' => ['homepage'],
    'presentation' => [
        'visual_preset' => 'fixture-c1',
        'homepage_preset' => 'fixture-c1',
    ],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
])) {
    fwrite(STDERR, "FAIL: C1 fixture manifest registration failed.\n");
    exit(1);
}

if (! in_array('fixture-c1', \AZnet\Theme\registered_visual_preset_ids(), true)) {
    fwrite(STDERR, "FAIL: registered visual preset is not dynamically accepted.\n");
    exit(1);
}
if (! in_array('fixture-c1', \AZnet\Theme\registered_homepage_preset_ids(), true)) {
    fwrite(STDERR, "FAIL: registered Homepage preset is not dynamically accepted.\n");
    exit(1);
}

$normalized = \AZnet\Theme\normalize_settings([
    'visual_preset' => 'fixture-c1',
    'homepage_preset' => 'fixture-c1',
]);
if ('fixture-c1' !== $normalized['visual_preset'] || 'fixture-c1' !== $normalized['homepage_preset']) {
    fwrite(STDERR, "FAIL: settings normalization still rejects registry-provided presets.\n");
    exit(1);
}

$source = (string) file_get_contents($root . '/inc/theme/settings.php');
if (str_contains($source, "[ 'off', 'law-01', 'curtain-01' ]") || str_contains($source, "[ 'default', 'editorial', 'commerce', 'curtain-01' ]")) {
    fwrite(STDERR, "FAIL: generic settings Core still hard-codes template preset allow-lists.\n");
    exit(1);
}

echo "PASS: Core C1 registry-driven preset resolution\n";
