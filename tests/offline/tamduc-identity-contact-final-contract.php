<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/tamduc-identity-contact-final.yml';
$browserPath = $root . '/tests/browser/tamduc-identity-contact-final-l4.mjs';

assert(is_file($workflowPath), 'Tâm Đức identity/contact final workflow must exist');
assert(is_file($browserPath), 'Tâm Đức identity/contact final browser harness must exist');

$workflow = file_get_contents($workflowPath);
$browser = file_get_contents($browserPath);
assert(is_string($workflow));
assert(is_string($browser));

foreach ([
    'ops/tamduc-identity-contact',
    'permissions:',
    'contents: read',
    'tests/browser/tamduc-identity-contact-final-l4.mjs',
    'upload-artifact',
] as $needle) {
    assert(str_contains($workflow, $needle), "Final workflow missing required marker: {$needle}");
}

foreach ([
    '1440x1000',
    '390x844',
    'aznet-theme-site-header__logo',
    'tam-duc-ha-noi-logo-official.jpg',
    'logo_count',
    'logo_src',
    'main_count',
    'h1_count',
    'horizontal_overflow',
    'console_errors',
    'page_errors',
    'request_failures',
    'data-aznet-theme-nav-trigger',
    'data-aznet-theme-nav-panel',
    "page.keyboard.press('Enter')",
    "page.keyboard.press('Escape')",
    'blocking_failures',
    'screenshots',
] as $needle) {
    assert(str_contains($browser, $needle), "Final browser harness missing required marker: {$needle}");
}

foreach ([
    'wp-login.php',
    'wp-admin/',
    'PILOT_WP_PASSWORD',
    '.fill(',
] as $forbidden) {
    assert(!str_contains($browser, $forbidden), "Final identity/contact L4 harness must remain public/read-only: {$forbidden}");
}

echo "PASS: Tâm Đức identity/contact final L4 static contract\n";
