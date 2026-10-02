<?php
/** D-043 C5 manifest-driven Homepage asset registry contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';
require_once $root . '/inc/theme/assets.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();
if ([] !== ($summary['rejected'] ?? [])) {
    fwrite(STDERR, "FAIL: bundled manifests did not load cleanly.\n");
    exit(1);
}

if (! function_exists('AZnet\\Theme\\template_homepage_asset_descriptors')) {
    fwrite(STDERR, "FAIL: generic Homepage asset descriptor resolver missing.\n");
    exit(1);
}

$expected = [
    'law-01' => ['aznet-theme-team-card', 'aznet-theme-homepage-law-01', 'aznet-theme-homepage-law-01-variants'],
    'curtain-01' => ['aznet-theme-homepage-curtain-01', 'aznet-theme-homepage-curtain-01-motion'],
    'industrial-01' => ['aznet-theme-homepage-industrial-01'],
];

foreach ($expected as $preset => $handles) {
    $descriptors = \AZnet\Theme\template_homepage_asset_descriptors($preset);
    $actual = array_values(array_map(static fn(array $asset): string => (string) ($asset['handle'] ?? ''), $descriptors));
    if ($handles !== $actual) {
        fwrite(STDERR, "FAIL: Homepage asset manifest projection mismatch for {$preset}.\n");
        exit(1);
    }
}

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-assets',
    'name' => 'Fixture Assets',
    'version' => '1.0.0',
    'capabilities' => ['homepage'],
    'presentation' => ['homepage_preset' => 'fixture-assets-home'],
    'assets' => [
        'homepage' => [
            [
                'type' => 'style',
                'handle' => 'fixture-assets-home',
                'path' => '/assets/css/components/homepage.css',
                'dependencies' => ['aznet-theme-tokens'],
            ],
        ],
    ],
];
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: valid asset fixture manifest rejected.\n");
    exit(1);
}
$fixtureAssets = \AZnet\Theme\template_homepage_asset_descriptors('fixture-assets-home');
if ('fixture-assets-home' !== ($fixtureAssets[0]['handle'] ?? null)) {
    fwrite(STDERR, "FAIL: fourth-template asset descriptor did not participate without Core branch.\n");
    exit(1);
}

$source = (string) file_get_contents($root . '/inc/theme/assets.php');
if (! str_contains($source, 'template_homepage_asset_descriptors( $preset )')) {
    fwrite(STDERR, "FAIL: generic asset orchestration does not consume manifest descriptors.\n");
    exit(1);
}
if (! str_contains($source, 'enqueue_active_template_homepage_assets( $version )')) {
    fwrite(STDERR, "FAIL: global asset path does not use active manifest Homepage assets.\n");
    exit(1);
}
foreach ([
    'enqueue_homepage_law01_asset( $version );',
    'enqueue_homepage_curtain01_asset( $version );',
    'enqueue_homepage_industrial01_asset( $version );',
] as $legacyCall) {
    if (str_contains($source, $legacyCall)) {
        fwrite(STDERR, "FAIL: global asset path still calls template-specific Homepage asset loaders: {$legacyCall}\n");
        exit(1);
    }
}

echo "PASS: D-043 C5 manifest-driven Homepage asset registry\n";
