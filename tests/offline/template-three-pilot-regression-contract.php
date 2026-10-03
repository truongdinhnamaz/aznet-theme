<?php
/** D-043 C7 three-pilot manifest-path regression closure. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';
require_once $root . '/inc/theme/provisioning-professional-services.php';
require_once $root . '/inc/theme/provisioning-blueprints.php';
require_once $root . '/inc/theme/assets.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();
if ([] !== ($summary['rejected'] ?? [])) {
    fwrite(STDERR, "FAIL: bundled pilot manifests did not load cleanly.\n");
    exit(1);
}

$pilots = [
    'law-01' => [
        'homepage_preset' => 'law-01',
        'visual_preset' => null,
        'authoring' => ['hero','services','about','team','knowledge','case_analysis','legal_news','process','faq','contact'],
        'assets' => ['aznet-theme-team-card','aznet-theme-homepage-law-01','aznet-theme-homepage-law-01-variants'],
        'provisioning' => 'law01-v1-2',
    ],
    'curtain-01' => [
        'homepage_preset' => 'curtain-01',
        'visual_preset' => 'curtain-01',
        'authoring' => ['hero','proof','about','process','projects','knowledge','contact'],
        'assets' => ['aznet-theme-homepage-curtain-01','aznet-theme-homepage-curtain-01-motion'],
        'provisioning' => null,
    ],
    'industrial-01' => [
        'homepage_preset' => 'industrial-01',
        'visual_preset' => 'industrial-01',
        'authoring' => ['hero','about','solutions','process','knowledge','contact'],
        'assets' => ['aznet-theme-homepage-industrial-01'],
        'provisioning' => null,
    ],
];

foreach ($pilots as $id => $expected) {
    $manifest = \AZnet\Theme\template_manifest($id);
    if (! is_array($manifest)) {
        fwrite(STDERR, "FAIL: pilot manifest missing: {$id}.\n");
        exit(1);
    }

    $homepagePreset = $manifest['presentation']['homepage_preset'] ?? null;
    if ($expected['homepage_preset'] !== $homepagePreset) {
        fwrite(STDERR, "FAIL: Homepage preset regression for {$id}.\n");
        exit(1);
    }

    $visualPreset = $manifest['presentation']['visual_preset'] ?? null;
    if ($expected['visual_preset'] !== $visualPreset) {
        fwrite(STDERR, "FAIL: visual preset regression for {$id}.\n");
        exit(1);
    }

    $resolved = \AZnet\Theme\template_manifest_for_homepage_preset((string) $homepagePreset);
    if (! is_array($resolved) || $id !== ($resolved['id'] ?? null)) {
        fwrite(STDERR, "FAIL: registry Homepage resolution regression for {$id}.\n");
        exit(1);
    }

    $authoring = $manifest['homepage']['authoring']['sections'] ?? [];
    if ($expected['authoring'] !== $authoring) {
        fwrite(STDERR, "FAIL: authoring descriptor regression for {$id}.\n");
        exit(1);
    }

    $assetHandles = array_values(array_map(
        static fn(array $asset): string => (string) ($asset['handle'] ?? ''),
        \AZnet\Theme\template_homepage_asset_descriptors((string) $homepagePreset)
    ));
    if ($expected['assets'] !== $assetHandles) {
        fwrite(STDERR, "FAIL: asset descriptor regression for {$id}.\n");
        exit(1);
    }

    $blueprint = \AZnet\Theme\template_provisioning_blueprint_key((string) $homepagePreset);
    if ($expected['provisioning'] !== $blueprint) {
        fwrite(STDERR, "FAIL: provisioning bridge regression for {$id}.\n");
        exit(1);
    }
}

foreach ([
    'inc/theme/template-registry.php',
    'inc/theme/settings.php',
    'inc/admin/control-center.php',
] as $genericFile) {
    $source = (string) file_get_contents($root . '/' . $genericFile);
    if (str_contains($source, "'law-01' =>") || str_contains($source, "'curtain-01' =>") || str_contains($source, "'industrial-01' =>")) {
        fwrite(STDERR, "FAIL: generic registry/settings path regained template catalog branching in {$genericFile}.\n");
        exit(1);
    }
}

$verify = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');
if (! str_contains($verify, 'tests/offline/template-three-pilot-regression-contract.php')) {
    fwrite(STDERR, "FAIL: C7 three-pilot regression is not retained in the V1 core verification chain.\n");
    exit(1);
}

echo "PASS: D-043 C7 three-pilot manifest-path regression\n";
