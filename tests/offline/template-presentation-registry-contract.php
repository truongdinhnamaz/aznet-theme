<?php
/** Core C1 Template Presentation Registry contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/settings.php';

assert(['default', 'editorial', 'commerce'] === \AZnet\Theme\visual_preset_ids());
assert(['off'] === \AZnet\Theme\homepage_preset_ids());

$loaderPath = $root . '/inc/theme/template-loader.php';
if (! is_file($loaderPath)) {
    fwrite(STDERR, "FAIL: C1 template loader file is missing.\n");
    exit(1);
}
require_once $loaderPath;

foreach ([
    'AZnet\\Theme\\load_local_template_manifests',
    'AZnet\\Theme\\template_presentation_ids',
    'AZnet\\Theme\\visual_preset_ids',
    'AZnet\\Theme\\homepage_preset_ids',
] as $function) {
    if (! function_exists($function)) {
        fwrite(STDERR, "FAIL: missing C1 function {$function}.\n");
        exit(1);
    }
}

$summary = \AZnet\Theme\load_local_template_manifests();
assert(in_array('law-01', $summary['loaded'], true));
assert(in_array('curtain-01', $summary['loaded'], true));
assert(in_array('industrial-01', $summary['loaded'], true));

assert(in_array('law-01', \AZnet\Theme\homepage_preset_ids(), true));
assert(in_array('curtain-01', \AZnet\Theme\homepage_preset_ids(), true));
assert(in_array('industrial-01', \AZnet\Theme\homepage_preset_ids(), true));

assert(in_array('curtain-01', \AZnet\Theme\visual_preset_ids(), true));
assert(in_array('industrial-01', \AZnet\Theme\visual_preset_ids(), true));
assert(! in_array('law-01', \AZnet\Theme\visual_preset_ids(), true));

$invalid = [
    'contract_version' => 1,
    'id' => 'fixture-invalid-presentation',
    'name' => 'Fixture Invalid Presentation',
    'version' => '1.0.0',
    'capabilities' => ['homepage'],
    'presentation' => ['visual_preset' => 'INVALID ID'],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
];
assert(false === \AZnet\Theme\register_template_manifest($invalid));

$duplicateProjection = [
    'contract_version' => 1,
    'id' => 'fixture-duplicate-presentation',
    'name' => 'Fixture Duplicate Presentation',
    'version' => '1.0.0',
    'capabilities' => ['homepage'],
    'presentation' => [
        'visual_preset' => 'curtain-01',
        'homepage_preset' => 'fixture-home',
    ],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
];
assert(true === \AZnet\Theme\register_template_manifest($duplicateProjection));
assert(1 === count(array_keys(\AZnet\Theme\visual_preset_ids(), 'curtain-01', true)));
assert([] === \AZnet\Theme\template_presentation_ids('unsupported'));

$normalized = \AZnet\Theme\normalize_settings([
    'visual_preset' => 'industrial-01',
    'homepage_preset' => 'industrial-01',
    'header_preset' => 'compact',
]);
assert('industrial-01' === $normalized['visual_preset']);
assert('industrial-01' === $normalized['homepage_preset']);
assert('compact' === $normalized['header_preset']);

$fallback = \AZnet\Theme\normalize_settings([
    'visual_preset' => 'removed-template',
    'homepage_preset' => 'removed-template',
    'header_preset' => 'compact',
]);
assert('default' === $fallback['visual_preset']);
assert('off' === $fallback['homepage_preset']);
assert('compact' === $fallback['header_preset']);

$settingsSource = file_get_contents($root . '/inc/theme/settings.php');
$designSource = file_get_contents($root . '/inc/theme/design-system.php');

if (is_string($settingsSource) && preg_match("/homepage_preset'.*\[\s*'off'.*'law-01'.*'curtain-01'.*'industrial-01'/s", $settingsSource)) {
    fwrite(STDERR, "FAIL: generic settings still enumerate template Homepage IDs.\n");
    exit(1);
}
if (is_string($designSource) && str_contains($designSource, "in_array( $preset, [ 'default', 'editorial', 'commerce', 'curtain-01', 'industrial-01' ]")) {
    fwrite(STDERR, "FAIL: Design System still owns a template-specific visual allow-list.\n");
    exit(1);
}

echo "PASS: Core C1 Template Presentation Registry contract\n";
