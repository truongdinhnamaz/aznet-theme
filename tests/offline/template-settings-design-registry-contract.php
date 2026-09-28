<?php
/** D-043 Core C1 settings/design registry contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';

use function AZnet\Theme\register_template_manifest;
use function AZnet\Theme\template_homepage_preset_ids;
use function AZnet\Theme\template_visual_preset_ids;

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-settings',
    'name' => 'Fixture Settings',
    'version' => '1.0.0',
    'category' => 'test',
    'description' => 'C1 registry projection fixture.',
    'capabilities' => ['homepage'],
    'presentation' => [
        'visual_preset' => 'fixture-visual',
        'homepage_preset' => 'fixture-home',
    ],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
];

if (! register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: C1 fixture manifest registration failed.\n");
    exit(1);
}

if (! in_array('fixture-visual', template_visual_preset_ids(), true)) {
    fwrite(STDERR, "FAIL: visual preset registry does not project manifest presentation values.\n");
    exit(1);
}

if (! in_array('fixture-home', template_homepage_preset_ids(), true)) {
    fwrite(STDERR, "FAIL: Homepage preset registry does not project manifest presentation values.\n");
    exit(1);
}

$settings = file_get_contents($root . '/inc/theme/settings.php');
$design = file_get_contents($root . '/inc/theme/design-system.php');

if (! is_string($settings) || str_contains($settings, "[ 'off', 'law-01', 'curtain-01' ]")) {
    fwrite(STDERR, "FAIL: settings.php still hard-codes template Homepage preset ids.\n");
    exit(1);
}

if (! str_contains((string) $settings, 'template_homepage_preset_ids()')) {
    fwrite(STDERR, "FAIL: settings normalization does not consume the template registry.\n");
    exit(1);
}

if (! is_string($design) || str_contains($design, "[ 'default', 'editorial', 'commerce', 'curtain-01' ]")) {
    fwrite(STDERR, "FAIL: design-system.php still hard-codes template visual preset ids.\n");
    exit(1);
}

if (! str_contains((string) $design, 'template_visual_preset_ids()')) {
    fwrite(STDERR, "FAIL: design-system does not consume the template registry.\n");
    exit(1);
}

echo "PASS: D-043 Core C1 settings/design registry contract\n";
