<?php
/** D-051: RED->GREEN Template Manifest v2 + strict v1 compatibility. */
declare(strict_types=1);

if (! defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/template-registry.php';

function v2_expect(bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, 'FAIL D-051: ' . $message . "\n");
        exit(1);
    }
}

$v1 = require $root . '/inc/theme/templates/law-01/manifest.php';
$legacy = \AZnet\Theme\normalize_template_manifest($v1);
v2_expect(is_array($legacy) && 1 === ($legacy['contract_version'] ?? null), 'valid v1 manifest must retain v1 semantics.');
v2_expect(! array_key_exists('composition', $legacy['homepage']), 'v1 must not acquire a new composition mode.');

$v2 = $v1;
$v2['id'] = 'fixture-v2-native';
$v2['contract_version'] = 2;
$v2['homepage']['composition'] = ['mode' => 'native'];
$native = \AZnet\Theme\normalize_template_manifest($v2);
v2_expect(is_array($native) && 2 === ($native['contract_version'] ?? null), 'explicit native v2 manifest was not accepted.');
v2_expect('native' === ($native['homepage']['composition']['mode'] ?? null), 'native v2 mode not preserved.');

$v2['id'] = 'fixture-v2-mapped';
$v2['homepage']['composition']['mode'] = 'mapped';
$mapped = \AZnet\Theme\normalize_template_manifest($v2);
v2_expect(is_array($mapped) && 'mapped' === ($mapped['homepage']['composition']['mode'] ?? null), 'explicit mapped v2 not accepted.');

unset($v2['homepage']['composition']);
$default = \AZnet\Theme\normalize_template_manifest($v2);
v2_expect(is_array($default) && 'mapped' === ($default['homepage']['composition']['mode'] ?? null), 'v2 absent composition must default safely to mapped.');

$v1_illicit = $v1;
$v1_illicit['homepage']['composition'] = ['mode' => 'native'];
v2_expect(null === \AZnet\Theme\normalize_template_manifest($v1_illicit), 'v1 cannot opt into new v2 native behavior.');

foreach (['future', '', 'NATIVE', 17, false, null] as $mode) {
    $bad = $v2;
    $bad['homepage']['composition'] = ['mode' => $mode];
    v2_expect(null === \AZnet\Theme\normalize_template_manifest($bad), 'unsupported v2 composition mode must be rejected.');
}

foreach ([3, 0, -1, '2'] as $version) {
    $bad = $v2;
    $bad['contract_version'] = $version;
    v2_expect(null === \AZnet\Theme\normalize_template_manifest($bad), 'unsupported or non-integer manifest version must be rejected.');
}

$bad = $v2;
$bad['homepage']['composition'] = ['mode' => 'native', 'domain_store' => ['price' => 999]];
v2_expect(null === \AZnet\Theme\normalize_template_manifest($bad), 'unexpected nested composition fields must be rejected.');

$bad = $v2;
$bad['homepage']['composition'] = 'native';
v2_expect(null === \AZnet\Theme\normalize_template_manifest($bad), 'scalar composition descriptor must be rejected.');

$bad = $v2;
$bad['capabilities'] = [];
$bad['homepage']['composition'] = ['mode' => 'native'];
v2_expect(null === \AZnet\Theme\normalize_template_manifest($bad), 'composition requires an explicit homepage capability.');

$digital = require $root . '/inc/theme/templates/digital-01/manifest.php';
$normalized = \AZnet\Theme\normalize_template_manifest($digital);
v2_expect(is_array($normalized) && 2 === ($normalized['contract_version'] ?? null)
    && 'native' === ($normalized['homepage']['composition']['mode'] ?? null),
    'Digital 01 must register as v2 native without a v1 semantic loophole.');

echo "PASS: D-051 Template Manifest v2 plus unchanged v1 compatibility\n";
