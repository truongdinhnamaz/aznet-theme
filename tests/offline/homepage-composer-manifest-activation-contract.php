<?php
/** D-043 C3 Homepage Composer activation contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';

if (! function_exists('AZnet\\Theme\\template_manifest_for_homepage_preset')) {
    fwrite(STDERR, "FAIL: missing manifest lookup by Homepage preset.\n");
    exit(1);
}

$store =& \AZnet\Theme\template_manifest_store();
$store = [];

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-compose',
    'name' => 'Fixture Compose',
    'version' => '1.0.0',
    'capabilities' => ['homepage'],
    'presentation' => [
        'homepage_preset' => 'fixture-compose-home',
    ],
];
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: valid Homepage composition fixture rejected.\n");
    exit(1);
}

$resolved = \AZnet\Theme\template_manifest_for_homepage_preset('fixture-compose-home');
if (! is_array($resolved) || 'fixture-compose' !== ($resolved['id'] ?? null)) {
    fwrite(STDERR, "FAIL: Homepage preset did not resolve through Template Manifest Registry.\n");
    exit(1);
}
if (null !== \AZnet\Theme\template_manifest_for_homepage_preset('missing-home')) {
    fwrite(STDERR, "FAIL: unknown Homepage preset must fail soft.\n");
    exit(1);
}

$composer = (string) file_get_contents($root . '/inc/theme/homepage-composer.php');
if (! str_contains($composer, 'template_manifest_for_homepage_preset( homepage_preset() )')) {
    fwrite(STDERR, "FAIL: Homepage Composer activation does not consume Template Manifest Registry.\n");
    exit(1);
}
if (str_contains($composer, "in_array( homepage_preset(), [ 'law-01', 'curtain-01', 'industrial-01' ], true )")) {
    fwrite(STDERR, "FAIL: Homepage Composer activation still hard-codes bundled template ids.\n");
    exit(1);
}

echo "PASS: D-043 C3 Homepage Composer activation\n";
