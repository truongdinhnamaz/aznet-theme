<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$core = $root . '/inc/admin/homepage-design.php';
assert(file_exists($core), 'Missing Core Homepage Design module.');
$source = (string) file_get_contents($core);
$bootstrap = (string) file_get_contents($root . '/inc/admin/bootstrap.php');
$homepage = (string) file_get_contents($root . '/inc/admin/homepage.php');

foreach ([
    'function homepage_design_surface_config',
    'function homepage_hero_design_available',
    'function homepage_hero_design_renderer',
    'function render_homepage_design_entrypoint',
    'function render_homepage_design_hero_screen',
    "template_manifest(",
    "'homepage'",
    "'hero'",
] as $needle) {
    assert(str_contains($source, $needle), "Missing Core Homepage Design contract: {$needle}");
}

foreach (['law-01', 'curtain-01', 'industrial-01'] as $forbidden) {
    assert(! str_contains($source, $forbidden), "Core Homepage Design must not branch on template id: {$forbidden}");
}

assert(str_contains($bootstrap, "require_once __DIR__ . '/homepage-design.php';"), 'Admin bootstrap must load Core Homepage Design.');
assert(str_contains($homepage, 'render_homepage_design_hero_screen('), 'Hero design screen must route through Core.');

foreach ([
    'inc/theme/templates/law-01/manifest.php',
    'inc/theme/templates/curtain-01/manifest.php',
    'inc/theme/templates/industrial-01/manifest.php',
] as $manifestPath) {
    $manifest = (string) file_get_contents($root . '/' . $manifestPath);
    assert(str_contains($manifest, "'hero'"), "Template must declare its Hero design adapter: {$manifestPath}");
    assert(str_contains($manifest, "'admin_renderer'"), "Template-specific Hero renderer must live in manifest: {$manifestPath}");
}

echo "PASS: Core Homepage Design owns the Hero backend shell; templates own Hero-specific adapters.\n";
