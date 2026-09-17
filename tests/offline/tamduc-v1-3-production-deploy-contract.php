<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/_ops-tamduc-v1-3-production-deploy.yml';
$browser = $root . '/tests/browser/tamduc-v1-3-production-deploy.mjs';

assert(is_file($workflow), 'Deployment workflow must exist');
assert(is_file($browser), 'Deployment harness must exist');

$workflowText = file_get_contents($workflow);
$browserText = file_get_contents($browser);
assert(is_string($workflowText));
assert(is_string($browserText));

foreach ([
    'ops/deploy-v1.3.0-tamduc',
    '[deploy-tamduc-v1.3.0]',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'aznet-theme-1.3.0.zip',
    'aznet-theme-1.2.0.zip',
    '4a30001f04dbbb881b8bfb01099e2caa4c8d01eafbca686ab934c0b2c12f105e',
    'cb9f58f91b5eb8d3204aa7a6fb1d5807303ea66767968bba3b827470934d8798',
    'tests/browser/tamduc-v1-3-production-deploy.mjs',
] as $needle) {
    assert(str_contains($workflowText, $needle), "Workflow missing required marker: {$needle}");
}

foreach ([
    "const TARGET_VERSION = '1.3.0'",
    "const PREVIOUS_VERSION = '1.2.0'",
    "const PHONE_HREF = 'tel:+842437164123'",
    "const UTILITY_LOCATION = 'header-utility'",
    'supportSnapshot',
    'replaceThemeFromZip',
    'resolveApprovedPhoneMenuFromLocation',
    'verifyPublicMatrix',
    'theme_replaced:',
    'theme_restored:',
    'precondition_header_utility_retained',
] as $needle) {
    assert(str_contains($browserText, $needle), "Harness missing safety marker: {$needle}");
}

foreach ([
    'RootProfile',
    'ConvertFlow',
    'WooCommerce',
    'wp_options',
    'postmeta',
    'wpdb',
    'wp-config.php',
] as $forbidden) {
    assert(!str_contains($browserText, $forbidden), "Deployment harness must not cross owner boundary: {$forbidden}");
}

echo "PASS: Tam Duc v1.3.0 production deployment static contract\n";
