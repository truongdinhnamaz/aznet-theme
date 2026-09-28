<?php
/** Core C0 Template Extension Contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$module = $root . '/inc/theme/template-registry.php';

if (! is_file($module)) {
    fwrite(STDERR, "FAIL: Core Template Registry module missing.\n");
    exit(1);
}

require_once $module;

$fixture = [
    'contract_version' => 1,
    'id' => 'fixture-04',
    'name' => 'Fixture 04',
    'version' => '1.0.0',
    'category' => 'test',
    'description' => 'Fourth-template contract fixture.',
    'capabilities' => ['homepage'],
    'presentation' => [
        'visual_preset' => 'fixture-04',
        'homepage_preset' => 'fixture-04',
    ],
    'assets' => [],
    'homepage' => [],
    'provisioning' => [],
];

if (! \AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: valid fourth-template manifest was rejected.\n");
    exit(1);
}

$manifest = \AZnet\Theme\template_manifest('fixture-04');
if (! is_array($manifest) || 'Fixture 04' !== ($manifest['name'] ?? null)) {
    fwrite(STDERR, "FAIL: registered fourth-template manifest cannot be resolved.\n");
    exit(1);
}

if (! in_array('fixture-04', \AZnet\Theme\template_manifest_ids(), true)) {
    fwrite(STDERR, "FAIL: fourth-template id missing from registry.\n");
    exit(1);
}

if (\AZnet\Theme\register_template_manifest($fixture)) {
    fwrite(STDERR, "FAIL: duplicate template id must not silently overwrite an existing manifest.\n");
    exit(1);
}

$invalid = $fixture;
$invalid['id'] = 'INVALID ID';
if (\AZnet\Theme\register_template_manifest($invalid)) {
    fwrite(STDERR, "FAIL: invalid template id was accepted.\n");
    exit(1);
}

$domainLeak = $fixture;
$domainLeak['id'] = 'fixture-domain-leak';
$domainLeak['product_data'] = ['price' => 100];
if (\AZnet\Theme\register_template_manifest($domainLeak)) {
    fwrite(STDERR, "FAIL: unknown/domain manifest key was accepted.\n");
    exit(1);
}

echo "PASS: Core C0 Template Extension Contract\n";
