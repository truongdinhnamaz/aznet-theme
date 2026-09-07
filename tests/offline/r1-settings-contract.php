<?php
/**
 * R1 Theme settings contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$GLOBALS['r1_theme_mod'] = [];

function get_theme_mod(string $name, mixed $default = false): mixed {
    if ('aznet_theme_settings' !== $name) {
        return $default;
    }

    return $GLOBALS['r1_theme_mod'] ?? $default;
}

$settings_file = dirname(__DIR__, 2) . '/inc/theme/settings.php';

if (! file_exists($settings_file)) {
    fwrite(STDERR, "FAIL: inc/theme/settings.php does not exist\n");
    exit(1);
}

require_once $settings_file;

$required = [
    'AZnet\\Theme\\settings_defaults',
    'AZnet\\Theme\\normalize_settings',
    'AZnet\\Theme\\settings',
    'AZnet\\Theme\\setting',
];

foreach ($required as $function) {
    if (! function_exists($function)) {
        fwrite(STDERR, "FAIL: missing function {$function}\n");
        exit(1);
    }
}

$defaults = \AZnet\Theme\settings_defaults();
assert(1 === $defaults['schema_version']);
assert('default' === $defaults['visual_preset']);

$GLOBALS['r1_theme_mod'] = [
    'schema_version' => 1,
    'visual_preset'  => 'commerce',
    'unknown_key'    => 'must-drop',
];
$normalized = \AZnet\Theme\settings();
assert('commerce' === $normalized['visual_preset']);
assert(! array_key_exists('unknown_key', $normalized));

$GLOBALS['r1_theme_mod'] = [
    'schema_version' => 999,
    'visual_preset'  => 'invalid',
];
$invalid = \AZnet\Theme\settings();
assert(1 === $invalid['schema_version']);
assert('default' === $invalid['visual_preset']);
assert('default' === \AZnet\Theme\setting('visual_preset'));
assert('fallback' === \AZnet\Theme\setting('missing_key', 'fallback'));

echo "PASS: R1 Theme settings contract\n";
