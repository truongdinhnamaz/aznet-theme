<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/_ops-tamduc-v1-2-postdeploy-verify.yml';
$browser = $root . '/tests/browser/tamduc-v1-2-postdeploy-verify.mjs';
assert(is_file($workflow), 'Post-deploy verification workflow must exist');
assert(is_file($browser), 'Post-deploy verification harness must exist');
$workflowText = file_get_contents($workflow);
$browserText = file_get_contents($browser);
assert(is_string($workflowText));
assert(is_string($browserText));
foreach ([
    'ops/deploy-v1.2.0-tamduc',
    '[verify-tamduc-v1.2.0]',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'tests/browser/tamduc-v1-2-postdeploy-verify.mjs',
    'tests/browser/p4-public-pilot-l4.mjs',
] as $needle) {
    assert(str_contains($workflowText, $needle), "Verification workflow missing marker: {$needle}");
}
foreach ([
    "const TARGET_VERSION = '1.2.0'",
    "const PHONE_HREF = 'tel:+842437164123'",
    "const UTILITY_LOCATION = 'header-utility'",
    'mutation: false',
] as $needle) {
    assert(str_contains($browserText, $needle), "Verification harness missing marker: {$needle}");
}
foreach ([
    'setInputFiles',
    'selectOption',
    'theme-install.php',
    'update.php',
    'wp-json/wp/v2/media',
    'aznet_theme_save',
    'aznet_theme_apply_provisioning',
] as $forbidden) {
    assert(!str_contains($browserText, $forbidden), "Post-deploy verification must remain read-only: {$forbidden}");
}
echo "PASS: Tam Duc v1.2.0 post-deploy read-only contract\n";
