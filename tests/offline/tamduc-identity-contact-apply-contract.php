<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/tamduc-identity-contact-apply.yml';
$browser = $root . '/tests/browser/tamduc-identity-contact-apply.mjs';
$logo = $root . '/ops/tamduc-identity-contact/logo-official.jpg';

assert(is_file($workflow), 'Tâm Đức identity/contact workflow must exist');
assert(is_file($browser), 'Tâm Đức identity/contact browser harness must exist');
assert(is_file($logo), 'Owner-approved Tâm Đức logo payload must exist on the bounded ops branch');

$workflowText = file_get_contents($workflow);
$browserText = file_get_contents($browser);
assert(is_string($workflowText));
assert(is_string($browserText));

foreach ([
    'ops/tamduc-identity-contact',
    'permissions:',
    'contents: read',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    '[apply-tamduc-identity-contact]',
    'tests/browser/tamduc-identity-contact-apply.mjs',
] as $needle) {
    assert(str_contains($workflowText, $needle), "Workflow missing required marker: {$needle}");
}

foreach ([
    '024 3716 4123',
    'tel:+842437164123',
    'logo-official.jpg',
    'custom_logo',
    'Tâm Đức - Liên hệ chính thức',
    'rollback',
    'before_state',
    'after_state',
] as $needle) {
    assert(str_contains($browserText, $needle), "Harness missing required marker: {$needle}");
}

// WordPress can render the menu save input in a hidden header toolbar. The browser
// harness must activate that native control without Playwright actionability checks.
foreach ([
    'nativeClick',
    "evaluate((element) => element.click())",
    "'#save_menu_header'",
] as $needle) {
    assert(str_contains($browserText, $needle), "Harness missing hidden-control regression marker: {$needle}");
}

foreach ([
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'rootprofile',
    'convertflow',
    'plugin-install.php',
    'theme-install.php',
    'update-core.php',
] as $forbidden) {
    assert(!str_contains(strtolower($browserText), strtolower($forbidden)), "Harness crosses forbidden boundary: {$forbidden}");
}

echo "PASS: Tâm Đức identity/contact apply static contract\n";
