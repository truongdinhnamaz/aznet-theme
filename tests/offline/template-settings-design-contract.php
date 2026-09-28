<?php
/** Core C1 Template Settings/Design registry contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$registryPath = $root . '/inc/theme/template-registry.php';
$manifestsPath = $root . '/inc/theme/template-manifests.php';
$settingsPath = $root . '/inc/theme/settings.php';
$designPath = $root . '/inc/theme/design-system.php';

require_once $registryPath;

if (! is_file($manifestsPath)) {
    fwrite(STDERR, "FAIL: built-in Template Manifest registration module missing.\n");
    exit(1);
}
require_once $manifestsPath;

foreach (['law-01', 'curtain-01'] as $id) {
    if (! is_array(\AZnet\Theme\template_manifest($id))) {
        fwrite(STDERR, "FAIL: built-in manifest missing: {$id}.\n");
        exit(1);
    }
}

if (! function_exists('AZnet\\Theme\\template_supported_homepage_presets')) {
    fwrite(STDERR, "FAIL: registry-backed Homepage preset resolver missing.\n");
    exit(1);
}
if (! function_exists('AZnet\\Theme\\template_supported_visual_presets')) {
    fwrite(STDERR, "FAIL: registry-backed visual preset resolver missing.\n");
    exit(1);
}

$homepage = \AZnet\Theme\template_supported_homepage_presets();
foreach (['off', 'law-01', 'curtain-01'] as $preset) {
    if (! in_array($preset, $homepage, true)) {
        fwrite(STDERR, "FAIL: Homepage preset not resolved from registry: {$preset}.\n");
        exit(1);
    }
}

$visual = \AZnet\Theme\template_supported_visual_presets();
foreach (['default', 'editorial', 'commerce', 'curtain-01'] as $preset) {
    if (! in_array($preset, $visual, true)) {
        fwrite(STDERR, "FAIL: visual preset not resolved from Core/registry: {$preset}.\n");
        exit(1);
    }
}

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-c1',
    'name' => 'Fixture C1',
    'version' => '1.0.0',
    'category' => 'test',
    'description' => 'C1 registry resolution fixture.',
    'capabilities' => ['homepage'],
    'presentation' => [
        'visual_preset' => 'fixture-c1-visual',
        'homepage_preset' => 'fixture-c1-home',
    ],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
];
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: C1 fixture registration failed.\n");
    exit(1);
}
if (! in_array('fixture-c1-home', \AZnet\Theme\template_supported_homepage_presets(), true)) {
    fwrite(STDERR, "FAIL: new manifest Homepage preset requires a Core allow-list edit.\n");
    exit(1);
}
if (! in_array('fixture-c1-visual', \AZnet\Theme\template_supported_visual_presets(), true)) {
    fwrite(STDERR, "FAIL: new manifest visual preset requires a Core allow-list edit.\n");
    exit(1);
}

$settings = (string) file_get_contents($settingsPath);
$design = (string) file_get_contents($designPath);
if (! str_contains($settings, 'template_supported_homepage_presets()')) {
    fwrite(STDERR, "FAIL: settings normalization still owns Homepage template allow-list.\n");
    exit(1);
}
if (! str_contains($settings, 'template_supported_visual_presets()')) {
    fwrite(STDERR, "FAIL: settings normalization still owns visual template allow-list.\n");
    exit(1);
}
if (! str_contains($design, 'template_supported_visual_presets()')) {
    fwrite(STDERR, "FAIL: Design System still owns template visual allow-list.\n");
    exit(1);
}

echo "PASS: Core C1 settings/design resolve template presets through manifest registry\n";
