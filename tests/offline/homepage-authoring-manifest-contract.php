<?php
/** D-043 C4 manifest-driven Homepage authoring descriptor contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();
if ([] !== ($summary['rejected'] ?? [])) {
    fwrite(STDERR, "FAIL: bundled manifests did not load cleanly.\n");
    exit(1);
}

$expected = [
    'law-01' => ['hero', 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact'],
    'curtain-01' => ['hero', 'proof', 'about', 'process', 'projects', 'knowledge', 'contact'],
];

foreach ($expected as $id => $sections) {
    $manifest = \AZnet\Theme\template_manifest($id);
    if (! is_array($manifest)) {
        fwrite(STDERR, "FAIL: manifest missing for {$id}.\n");
        exit(1);
    }
    if ($sections !== ($manifest['homepage']['authoring']['sections'] ?? null)) {
        fwrite(STDERR, "FAIL: authoring section order is not owned by manifest {$id}.\n");
        exit(1);
    }
}

$admin = (string) file_get_contents($root . '/inc/admin/homepage.php');
if (! str_contains($admin, "['homepage']['authoring']['sections']")) {
    fwrite(STDERR, "FAIL: Homepage authoring does not consume manifest authoring descriptors.\n");
    exit(1);
}
if (str_contains($admin, "if ( 'law-01' === \$preset ) {\n        return [ 'hero', 'services', 'about', 'team'")) {
    fwrite(STDERR, "FAIL: Law 01 authoring order remains hard-coded in generic Core.\n");
    exit(1);
}
if (str_contains($admin, "if ( 'curtain-01' === \$preset ) {\n        return [ 'hero', 'proof', 'about'")) {
    fwrite(STDERR, "FAIL: Curtain 01 authoring order remains hard-coded in generic Core.\n");
    exit(1);
}
if (str_contains($admin, "if ( ! in_array( \$preset, [ 'law-01', 'curtain-01' ], true ) ) { return; }")) {
    fwrite(STDERR, "FAIL: authoring console still uses a template-id allow-list.\n");
    exit(1);
}
if (! str_contains($admin, "template_manifest_for_homepage_preset( \$preset )")) {
    fwrite(STDERR, "FAIL: authoring console does not resolve active template manifest.\n");
    exit(1);
}

echo "PASS: D-043 C4 manifest-driven Homepage authoring descriptors\n";
