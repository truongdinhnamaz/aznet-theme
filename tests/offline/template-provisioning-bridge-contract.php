<?php
/** D-043 C6 Template Manifest -> Provisioning blueprint bridge contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';
require_once $root . '/inc/theme/provisioning-professional-services.php';
require_once $root . '/inc/theme/provisioning-blueprints.php';

$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$summary = \AZnet\Theme\load_local_template_manifests();
if ([] !== ($summary['rejected'] ?? [])) {
    fwrite(STDERR, "FAIL: bundled manifests did not load cleanly.\n");
    exit(1);
}

if (! function_exists('AZnet\\Theme\\template_provisioning_blueprint_key')) {
    fwrite(STDERR, "FAIL: template provisioning bridge resolver missing.\n");
    exit(1);
}

$law = \AZnet\Theme\template_manifest('law-01');
if (! is_array($law) || 'law01-v1-2' !== ($law['provisioning']['blueprint'] ?? null)) {
    fwrite(STDERR, "FAIL: Law 01 manifest does not own its existing provisioning recipe reference.\n");
    exit(1);
}
if ('law01-v1-2' !== \AZnet\Theme\template_provisioning_blueprint_key('law-01')) {
    fwrite(STDERR, "FAIL: Law 01 provisioning recipe did not resolve through the manifest bridge.\n");
    exit(1);
}

foreach (['curtain-01', 'industrial-01'] as $preset) {
    if (null !== \AZnet\Theme\template_provisioning_blueprint_key($preset)) {
        fwrite(STDERR, "FAIL: {$preset} must fail soft without an accepted provisioning blueprint.\n");
        exit(1);
    }
}

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-provisioning',
    'name' => 'Fixture Provisioning',
    'version' => '1.0.0',
    'capabilities' => ['homepage'],
    'presentation' => ['homepage_preset' => 'fixture-provisioning-home'],
    'provisioning' => ['blueprint' => 'professional-services-v1'],
];
if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: valid fourth-template provisioning manifest rejected.\n");
    exit(1);
}
if ('professional-services-v1' !== \AZnet\Theme\template_provisioning_blueprint_key('fixture-provisioning-home')) {
    fwrite(STDERR, "FAIL: fourth template could not resolve an existing Core blueprint without a Core template-id branch.\n");
    exit(1);
}

$invalid = $fixture;
$invalid['id'] = 'fixture-provisioning-invalid';
$invalid['presentation']['homepage_preset'] = 'fixture-provisioning-invalid-home';
$invalid['provisioning'] = ['blueprint' => 'INVALID BLUEPRINT'];
if (\AZnet\Theme\register_template_manifest($invalid)) {
    fwrite(STDERR, "FAIL: malformed provisioning recipe reference was accepted.\n");
    exit(1);
}

$admin = (string) file_get_contents($root . '/inc/admin/provisioning.php');
if (! str_contains($admin, 'template_provisioning_blueprint_key( homepage_preset() )')) {
    fwrite(STDERR, "FAIL: provisioning wizard does not derive its template recipe through the manifest bridge.\n");
    exit(1);
}

echo "PASS: D-043 C6 manifest provisioning bridge\n";
