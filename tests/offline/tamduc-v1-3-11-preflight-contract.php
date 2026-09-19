<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/_ops-tamduc-v1-3-11-preflight.yml';
$browser = $root . '/tests/browser/tamduc-v1-3-11-preflight.mjs';

assert(is_file($workflow), 'Preflight workflow must exist');
assert(is_file($browser), 'Preflight browser harness must exist');

$workflowText = file_get_contents($workflow);
$browserText = file_get_contents($browser);
assert(is_string($workflowText));
assert(is_string($browserText));

foreach ([
    'ops/deploy-v1.3.11-tamduc',
    'https://lstamduchn.vn',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'aznet-theme-1.3.11.zip',
    '723f502f81033136410a49ee1196ff098837b20edbdadaf754cadf522efe9fb0',
    'aznet-theme-1.3.10.zip',
    '5ff6c4862839dcdb6dfbbb77a71cb8bd87259b83cfbf308aa6b04279396bd572',
    'tests/browser/tamduc-v1-3-11-preflight.mjs',
    'lstamduchn-v1-3-11-preflight',
] as $needle) {
    assert(str_contains($workflowText, $needle), "Workflow missing required marker: {$needle}");
}

foreach ([
    "CURRENT_VERSION = '1.3.10'",
    "TARGET_VERSION = '1.3.11'",
    "scope: 'read-only-predeploy-preflight'",
    'mutation: false',
    "const MENU_NAME = 'Tâm Đức - Liên hệ chính thức'",
    "const PHONE_HREF = 'tel:+842437164123'",
    "const UTILITY_LOCATION = 'header-utility'",
    'supportSnapshot',
    'approvedPhoneMenu',
    'publicState',
] as $needle) {
    assert(str_contains($browserText, $needle), "Harness missing safety marker: {$needle}");
}

foreach ([
    'replaceThemeFromZip',
    'install-theme-submit',
    'update-from-upload-overwrite',
    'theme delete',
    'wp_options',
    'postmeta',
    'wpdb',
    'wp-config.php',
] as $forbidden) {
    assert(!str_contains($browserText, $forbidden), "Preflight must remain read-only: {$forbidden}");
}

echo "PASS: lstamduchn.vn v1.3.11 deployment preflight static contract\n";
