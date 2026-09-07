<?php
/**
 * R3 Header settings contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$defaults = AZnet\Theme\settings_defaults();
$expected_defaults = [
    'header_preset'    => 'standard',
    'header_sticky'    => 'sticky',
    'header_search'    => true,
    'header_utilities' => true,
];

foreach ($expected_defaults as $key => $expected) {
    if (! array_key_exists($key, $defaults)) {
        fwrite(STDERR, "FAIL: missing R3 settings default {$key}\n");
        exit(1);
    }
    if ($defaults[$key] !== $expected) {
        fwrite(STDERR, "FAIL: default {$key} mismatch\n");
        exit(1);
    }
}

$valid = AZnet\Theme\normalize_settings([
    'visual_preset'    => 'editorial',
    'header_preset'    => 'commerce',
    'header_sticky'    => 'sticky-compact',
    'header_search'    => false,
    'header_utilities' => 0,
]);

$valid_expected = [
    'schema_version'   => 1,
    'visual_preset'    => 'editorial',
    'header_preset'    => 'commerce',
    'header_sticky'    => 'sticky-compact',
    'header_search'    => false,
    'header_utilities' => false,
];

if ($valid !== $valid_expected) {
    fwrite(STDERR, "FAIL: valid R3 Header settings were not normalized exactly\n");
    exit(1);
}

$invalid = AZnet\Theme\normalize_settings([
    'visual_preset'    => 'unknown',
    'header_preset'    => 'builder',
    'header_sticky'    => 'always-fixed',
    'header_search'    => 'false',
    'header_utilities' => '1',
    'foreign_state'    => 'must-drop',
]);

if ($invalid['visual_preset'] !== 'default') {
    fwrite(STDERR, "FAIL: invalid visual preset did not fall back\n");
    exit(1);
}
if (($invalid['header_preset'] ?? null) !== 'standard') {
    fwrite(STDERR, "FAIL: invalid header_preset did not fall back to standard\n");
    exit(1);
}
if (($invalid['header_sticky'] ?? null) !== 'sticky') {
    fwrite(STDERR, "FAIL: invalid header_sticky did not fall back to sticky\n");
    exit(1);
}
if (($invalid['header_search'] ?? null) !== true) {
    fwrite(STDERR, "FAIL: string header_search must not be treated as a boolean value\n");
    exit(1);
}
if (($invalid['header_utilities'] ?? null) !== true) {
    fwrite(STDERR, "FAIL: string header_utilities must not be treated as a boolean value\n");
    exit(1);
}
if (array_key_exists('foreign_state', $invalid)) {
    fwrite(STDERR, "FAIL: foreign setting escaped the Theme allow-list\n");
    exit(1);
}

foreach ([
    ['header_search' => 1, 'expected' => true],
    ['header_search' => 0, 'expected' => false],
    ['header_utilities' => true, 'expected' => true],
    ['header_utilities' => false, 'expected' => false],
] as $case) {
    $expected = $case['expected'];
    unset($case['expected']);
    $normalized = AZnet\Theme\normalize_settings($case);
    $key = array_key_first($case);
    if ($normalized[$key] !== $expected) {
        fwrite(STDERR, "FAIL: strict boolean normalization mismatch for {$key}\n");
        exit(1);
    }
}

echo "PASS: R3 Header settings contract\n";
