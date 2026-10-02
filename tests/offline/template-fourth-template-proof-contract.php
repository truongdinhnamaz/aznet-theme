<?php
/** D-043 C8 synthetic fourth-template end-to-end extension proof. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$fixturePath = $root . '/tests/fixtures/template-manifests/fixture-04.php';
if (! is_file($fixturePath)) {
    fwrite(STDERR, "FAIL: synthetic fourth-template fixture manifest missing.\n");
    exit(1);
}

require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/settings.php';
require_once $root . '/inc/theme/provisioning-professional-services.php';
require_once $root . '/inc/theme/provisioning-blueprints.php';
require_once $root . '/inc/theme/assets.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];

$fixture = require $fixturePath;
if (! is_array($fixture) || ! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: synthetic fourth template could not register through the v1 manifest contract.\n");
    exit(1);
}

$id = 'fixture-04';
$homepagePreset = 'fixture-04-home';
$visualPreset = 'fixture-04-visual';

$manifest = \AZnet\Theme\template_manifest($id);
if (! is_array($manifest) || 'Fixture 04' !== ($manifest['name'] ?? null)) {
    fwrite(STDERR, "FAIL: C0 registry participation failed for fourth template.\n");
    exit(1);
}

if (! in_array($homepagePreset, \AZnet\Theme\homepage_preset_ids(), true)
    || ! in_array($visualPreset, \AZnet\Theme\visual_preset_ids(), true)
    || 'Fixture 04' !== (\AZnet\Theme\visual_preset_choices()[$visualPreset] ?? null)) {
    fwrite(STDERR, "FAIL: C1 settings/design projection failed for fourth template.\n");
    exit(1);
}

$resolved = \AZnet\Theme\template_manifest_for_homepage_preset($homepagePreset);
if (! is_array($resolved) || $id !== ($resolved['id'] ?? null)) {
    fwrite(STDERR, "FAIL: C3 Homepage activation resolution failed for fourth template.\n");
    exit(1);
}

$sections = $manifest['homepage']['authoring']['sections'] ?? null;
if (['hero', 'content', 'contact'] !== $sections) {
    fwrite(STDERR, "FAIL: C4 authoring descriptors did not remain intact for fourth template.\n");
    exit(1);
}

$assets = \AZnet\Theme\template_homepage_asset_descriptors($homepagePreset);
$handles = array_values(array_map(static fn(array $asset): string => (string) ($asset['handle'] ?? ''), $assets));
if (['fixture-04-home'] !== $handles) {
    fwrite(STDERR, "FAIL: C5 asset registry participation failed for fourth template.\n");
    exit(1);
}

if ('professional-services-v1' !== \AZnet\Theme\template_provisioning_blueprint_key($homepagePreset)) {
    fwrite(STDERR, "FAIL: C6 provisioning bridge participation failed for fourth template.\n");
    exit(1);
}

$admin = (string) file_get_contents($root . '/inc/admin/homepage.php');
$composer = (string) file_get_contents($root . '/inc/theme/homepage-composer.php');
foreach ([
    [$admin, 'template_manifests()', 'C2 Template Library generic registry consumer missing'],
    [$admin, "['homepage']['authoring']['sections']", 'C4 generic authoring consumer missing'],
    [$composer, 'template_manifest_for_homepage_preset( homepage_preset() )', 'C3 generic composition activation consumer missing'],
] as [$source, $needle, $message]) {
    if (! str_contains($source, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

$genericFiles = [
    'inc/theme/template-registry.php',
    'inc/theme/settings.php',
    'inc/theme/design-system.php',
    'inc/admin/control-center.php',
    'inc/admin/homepage.php',
    'inc/theme/homepage-composer.php',
    'inc/theme/assets.php',
    'inc/admin/provisioning.php',
];
foreach ($genericFiles as $file) {
    $source = (string) file_get_contents($root . '/' . $file);
    if (str_contains($source, "'fixture-04'") || str_contains($source, 'fixture-04-home') || str_contains($source, 'fixture-04-visual')) {
        fwrite(STDERR, "FAIL: fourth-template proof required a fixture-specific Core branch in {$file}.\n");
        exit(1);
    }
}

$verify = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');
if (! str_contains($verify, 'tests/offline/template-fourth-template-proof-contract.php')) {
    fwrite(STDERR, "FAIL: C8 fourth-template proof is not retained in reusable V1 verification.\n");
    exit(1);
}

echo "PASS: D-043 C8 synthetic fourth-template extension proof\n";
