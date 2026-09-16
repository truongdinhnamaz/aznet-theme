<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/tamduc-homepage-final.yml';
$browserPath = $root . '/tests/browser/tamduc-homepage-final-l4.mjs';

assert(is_file($workflowPath), 'Tâm Đức final L4 workflow must exist');
assert(is_file($browserPath), 'Tâm Đức final L4 browser harness must exist');

$workflow = file_get_contents($workflowPath);
$browser = file_get_contents($browserPath);
assert(is_string($workflow));
assert(is_string($browser));

foreach ([
    'ops/tamduc-homepage-completion',
    'permissions:',
    'contents: read',
    'tests/browser/tamduc-homepage-final-l4.mjs',
    'upload-artifact',
] as $needle) {
    assert(str_contains($workflow, $needle), "Final workflow missing required marker: {$needle}");
}

foreach ([
    '1440x1000',
    '1024x900',
    '390x844',
    '320x800',
    'main_count',
    'h1_count',
    'horizontal_overflow',
    'primary_navigation',
    'internal_cta_links',
    'console_errors',
    'page_errors',
    'request_failures',
    'blocking_failures',
    'screenshots',
    'data-aznet-theme-nav-trigger',
    'data-aznet-theme-nav-panel',
    'aria-expanded',
    "page.keyboard.press('Enter')",
    "page.keyboard.press('Escape')",
    'viewport.width <= 390',
    'mobile_navigation',
] as $needle) {
    assert(str_contains($browser, $needle), "Final browser harness missing required marker: {$needle}");
}

foreach ([
    'wp-login.php',
    'wp-admin/',
    'PILOT_WP_PASSWORD',
    '.click()',
    '.fill(',
] as $forbidden) {
    assert(!str_contains($browser, $forbidden), "Final L4 harness must remain public/read-only: {$forbidden}");
}

echo "PASS: Tâm Đức final Homepage L4 static contract\n";
