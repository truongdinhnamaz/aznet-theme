<?php
/** Digital 01 fourth production template: manifest, ownership, and native Page contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
$root = dirname(__DIR__, 2);
$manifestPath = $root . '/inc/theme/templates/digital-01/manifest.php';
$patternPath = $root . '/patterns/homepage-digital-01.php';
$visualPath = $root . '/assets/css/presets/digital-01.css';
$homepagePath = $root . '/assets/css/components/homepage-digital-01.css';
foreach ([$manifestPath, $patternPath, $visualPath, $homepagePath] as $path) {
    if (! is_file($path)) {
        fwrite(STDERR, 'FAIL: Digital 01 production file missing: ' . basename($path) . "\n");
        exit(1);
    }
}
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';
require_once $root . '/inc/theme/assets.php';
$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$result = \AZnet\Theme\load_local_template_manifests();
if ([] !== $result['rejected'] || ! in_array('digital-01', $result['loaded'], true)) {
    fwrite(STDERR, "FAIL: Digital 01 cannot be loaded through the v1 manifest contract.\n");
    exit(1);
}
$manifest = \AZnet\Theme\template_manifest('digital-01');
if (! is_array($manifest)
    || 1 !== $manifest['contract_version']
    || 'Digital 01' !== $manifest['name']
    || 'digital-01' !== ($manifest['presentation']['visual_preset'] ?? null)
    || 'digital-01' !== ($manifest['presentation']['homepage_preset'] ?? null)
    || ! in_array('homepage', $manifest['capabilities'], true)
    || ! in_array('woocommerce', $manifest['capabilities'], true)) {
    fwrite(STDERR, "FAIL: Digital 01 contract or presentation IDs are invalid.\n");
    exit(1);
}
if ('native' !== ($manifest['homepage']['composition']['mode'] ?? null)
    || isset($manifest['assets']['homepage']['composition'])) {
    fwrite(STDERR, "FAIL: Digital 01 must declare native mode under Homepage, not assets.\\n");
    exit(1);
}
if (['content'] !== ($manifest['homepage']['authoring']['sections'] ?? null)
    || true === ($manifest['homepage']['hero']['enabled'] ?? false)
    || [] !== ($manifest['provisioning'] ?? [])) {
    fwrite(STDERR, "FAIL: Digital 01 must use WordPress-native authored Homepage without auto-provisioning or phantom Hero controls.\n");
    exit(1);
}
$assets = \AZnet\Theme\template_homepage_asset_descriptors('digital-01');
if (1 !== count($assets)
    || 'aznet-theme-homepage-digital-01' !== ($assets[0]['handle'] ?? null)
    || '/assets/css/components/homepage-digital-01.css' !== ($assets[0]['path'] ?? null)) {
    fwrite(STDERR, "FAIL: Digital 01 must load its own single Homepage asset through the manifest.\n");
    exit(1);
}
$pattern = (string) file_get_contents($patternPath);
if (! str_contains($pattern, 'Slug: aznet-theme/homepage-digital-01')
    || ! str_contains($pattern, 'wp:query')
    || ! str_contains($pattern, 'aznet-digital01')
    || str_contains($pattern, 'woocommerce/product-collection')) {
    fwrite(STDERR, "FAIL: Digital 01 native-pattern/news query or optional Woo separation invalid.\n");
    exit(1);
}
foreach ([$visualPath, $homepagePath] as $path) {
    $css = (string) file_get_contents($path);
    if (! str_contains($css, 'digital-01') && ! str_contains($css, 'aznet-digital01')) {
        fwrite(STDERR, "FAIL: Digital 01 CSS is not independently scoped.\n");
        exit(1);
    }
}
$homepageCss = (string) file_get_contents($homepagePath);
if (preg_match('/\.aznet-digital01__hero\\s+\\.wp-block-heading\\s*\\{[^}]*max-width:\\s*[0-9]+ch/i', $homepageCss)) {
    fwrite(STDERR, "FAIL: Digital 01 Hero heading hard-width cap violates D-036 desktop natural wrapping.\\n");
    exit(1);
}
foreach (['law-01', 'curtain-01', 'industrial-01'] as $retained) {
    if (! is_array(\AZnet\Theme\template_manifest($retained))) {
        fwrite(STDERR, "FAIL: previously accepted template missing: {$retained}.\n");
        exit(1);
    }
}
echo "PASS: Digital 01 manifest, WordPress-native pattern, scoped assets, retained templates\n";
