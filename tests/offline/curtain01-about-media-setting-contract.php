<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__);
}

$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';

$defaults = \AZnet\Theme\settings_defaults();
if (! array_key_exists('homepage_about_image', $defaults) || 0 !== $defaults['homepage_about_image']) {
    fwrite(STDERR, "FAIL: homepage_about_image default must exist and be 0.\n");
    exit(1);
}

$normalized = \AZnet\Theme\normalize_settings([
    'homepage_about_image' => '993',
]);

if (993 !== $normalized['homepage_about_image']) {
    fwrite(STDERR, "FAIL: positive attachment ID must normalize as homepage_about_image.\n");
    exit(1);
}

$invalid = \AZnet\Theme\normalize_settings([
    'homepage_about_image' => '-4',
]);

if (0 !== $invalid['homepage_about_image']) {
    fwrite(STDERR, "FAIL: invalid homepage_about_image must normalize to 0.\n");
    exit(1);
}

echo "PASS: Curtain 01 About media presentation setting contract\n";
