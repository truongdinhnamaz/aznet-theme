<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/tamduc-homepage-apply.yml';
$browserPath = $root . '/tests/browser/tamduc-homepage-apply.mjs';
$planPath = $root . '/ops/tamduc-homepage/mutation-plan.json';

assert(is_file($workflowPath), 'Tâm Đức apply workflow must exist');
assert(is_file($browserPath), 'Tâm Đức apply browser harness must exist');
assert(is_file($planPath), 'Tâm Đức checked-in mutation plan must exist');

$workflow = file_get_contents($workflowPath);
$browser = file_get_contents($browserPath);
$plan = file_get_contents($planPath);
assert(is_string($workflow));
assert(is_string($browser));
assert(is_string($plan));

foreach ([
    'ops/tamduc-homepage-completion',
    'contents: read',
    '[apply-tamduc]',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'ops/tamduc-homepage/mutation-plan.json',
    'tests/browser/tamduc-homepage-apply.mjs',
    'if: contains(github.event.head_commit.message',
    'upload-artifact',
] as $needle) {
    assert(str_contains($workflow, $needle), "Apply workflow missing safety marker: {$needle}");
}

foreach ([
    'verifyPreconditions',
    'rollbackApplied',
    'before.json',
    'after.json',
    'mutation-plan.json',
    'blogdescription',
    'page_title_142',
    'post-142',
    'clickQuickEdit',
    'button.click()',
] as $needle) {
    assert(str_contains($browser, $needle), "Apply harness missing safety marker: {$needle}");
}

foreach ([
    'Dội ngũ',
    'Đội ngũ',
    'Trọn Tâm với khách – Vẹn Đức với nghề',
] as $needle) {
    assert(str_contains($plan, $needle), "Checked-in mutation plan missing approved value: {$needle}");
}

foreach ([
    'update-core.php',
    'plugin-install.php',
    'theme-install.php',
    'plugins.php?action=delete',
    'themes.php?action=delete',
    'aznet_theme_apply_provisioning',
    'wp-json/rootprofile',
    'wp-json/convertflow',
] as $forbidden) {
    assert(!str_contains($browser, $forbidden), "Apply harness crosses forbidden boundary: {$forbidden}");
}

assert(str_contains($browser, "process.exitCode = 1"), 'Apply harness must fail closed on drift/error');
assert(str_contains($browser, "before !== mutation.before"), 'Apply harness must abort on baseline drift');

echo "PASS: Tâm Đức homepage apply safety contract\n";
