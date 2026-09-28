<?php
/** Core C1 Template Presentation Registry contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
$registry = $root . '/inc/theme/template-registry.php';
$loader = $root . '/inc/theme/template-loader.php';
$settings = $root . '/inc/theme/settings.php';

require_once $registry;

foreach ([
    'AZnet\\Theme\\template_presentation_ids',
    'AZnet\\Theme\\visual_preset_ids',
    'AZnet\\Theme\\homepage_preset_ids',
] as $function) {
    if (! function_exists($function)) {
        fwrite(STDERR, "FAIL: missing C1 function {$function}.\n");
        exit(1);
    }
}

if (! is_file($loader)) {
    fwrite(STDERR, "FAIL: C1 local template manifest loader missing.\n");
    exit(1);
}
require_once $loader;

if (! function_exists('AZnet\\Theme\\load_local_template_manifests')) {
    fwrite(STDERR, "FAIL: load_local_template_manifests() missing.\n");
    exit(1);
}

$store =& \AZnet\Theme\template_manifest_store();
$store = [];

$summary = \AZnet\Theme\load_local_template_manifests();
if (! is_array($summary)) {
    fwrite(STDERR, "FAIL: local manifest loader did not return summary.\n");
    exit(1);
}

$homepage = \AZnet\Theme\homepage_preset_ids();
$visual = \AZnet\Theme\visual_preset_ids();

foreach (['off', 'law-01', 'curtain-01', 'industrial-01'] as $id) {
    if (! in_array($id, $homepage, true)) {
        fwrite(STDERR, "FAIL: Homepage preset {$id} missing from registry projection.\n");
        exit(1);
    }
}
foreach (['default', 'editorial', 'commerce', 'curtain-01', 'industrial-01'] as $id) {
    if (! in_array($id, $visual, true)) {
        fwrite(STDERR, "FAIL: visual preset {$id} missing from registry projection.\n");
        exit(1);
    }
}
if (in_array('law-01', $visual, true)) {
    fwrite(STDERR, "FAIL: Law 01 Homepage registration must not imply a visual preset.\n");
    exit(1);
}

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-visual-a',
    'name' => 'Fixture Visual A',
    'version' => '1.0.0',
    'presentation' => [
        'visual_preset' => 'shared-visual',
        'homepage_preset' => 'fixture-home-a',
    ],
];
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: valid C1 presentation fixture rejected.\n");
    exit(1);
}
$fixture['id'] = 'fixture-visual-b';
$fixture['name'] = 'Fixture Visual B';
$fixture['presentation']['homepage_preset'] = 'fixture-home-b';
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: second valid C1 presentation fixture rejected.\n");
    exit(1);
}

$shared = array_values(array_filter(\AZnet\Theme\template_presentation_ids('visual_preset'), static fn($id) => 'shared-visual' === $id));
if (1 !== count($shared)) {
    fwrite(STDERR, "FAIL: duplicate presentation IDs were not deduplicated deterministically.\n");
    exit(1);
}
if ([] !== \AZnet\Theme\template_presentation_ids('unsupported')) {
    fwrite(STDERR, "FAIL: unsupported presentation slot must return empty list.\n");
    exit(1);
}

$invalid = [
    'contract_version' => 1,
    'id' => 'fixture-invalid-presentation',
    'name' => 'Invalid Presentation',
    'version' => '1.0.0',
    'presentation' => ['visual_preset' => 'INVALID VALUE'],
];
if (\AZnet\Theme\register_template_manifest($invalid)) {
    fwrite(STDERR, "FAIL: invalid nested presentation value was accepted.\n");
    exit(1);
}

$store = [];
if (['default', 'editorial', 'commerce'] !== \AZnet\Theme\visual_preset_ids()) {
    fwrite(STDERR, "FAIL: zero-template Core visual defaults changed.\n");
    exit(1);
}
if (['off'] !== \AZnet\Theme\homepage_preset_ids()) {
    fwrite(STDERR, "FAIL: zero-template Homepage fallback changed.\n");
    exit(1);
}

require_once $settings;
$normalized = \AZnet\Theme\normalize_settings([
    'visual_preset' => 'removed-template',
    'homepage_preset' => 'removed-template',
    'header_search' => false,
]);
if ('default' !== $normalized['visual_preset'] || 'off' !== $normalized['homepage_preset']) {
    fwrite(STDERR, "FAIL: unknown saved template IDs did not fail safe.\n");
    exit(1);
}
if (false !== $normalized['header_search']) {
    fwrite(STDERR, "FAIL: unrelated settings changed during template fallback normalization.\n");
    exit(1);
}

echo "PASS: Core C1 Template Presentation Registry\n";
