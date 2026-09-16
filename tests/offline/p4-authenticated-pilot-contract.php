<?php

declare(strict_types=1);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$workflowPath = __DIR__ . '/../../.github/workflows/p4-authenticated-pilot.yml';
$browserPath  = __DIR__ . '/../browser/p4-authenticated-pilot-l4.mjs';

if (!is_file($workflowPath)) {
    $fail('authenticated pilot workflow is missing');
}
if (!is_file($browserPath)) {
    $fail('authenticated pilot browser harness is missing');
}

$workflow = (string) file_get_contents($workflowPath);
$browser  = (string) file_get_contents($browserPath);

foreach ([
    'name: P4 Authenticated Pilot QA',
    'workflow_dispatch:',
    'contents: read',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'tests/browser/p4-authenticated-pilot-l4.mjs',
    'actions/upload-artifact@v4',
] as $marker) {
    if (false === strpos($workflow, $marker)) {
        $fail("workflow missing marker {$marker}");
    }
}

foreach (["\n  push:\n", "\n  pull_request:\n", 'contents: write'] as $forbidden) {
    if (false !== strpos($workflow, $forbidden)) {
        $fail("workflow must remain manual/read-only; forbidden marker {$forbidden}");
    }
}

foreach ([
    'P4_AUTH_BASE_URL',
    'P4_AUTH_ADMIN_USER',
    'P4_AUTH_ADMIN_PASS',
    '/wp-login.php',
    '#user_login',
    '#user_pass',
    '#wp-submit',
    'admin.php?page=aznet-theme',
    "gotoControlCenter(page, 'system-health')",
    '.aznet-theme-control-center',
    'textarea[readonly]',
    'standalone_core',
    'optional_integrations',
    'blocking_failures',
    '@axe-core/playwright',
] as $marker) {
    if (false === strpos($browser, $marker)) {
        $fail("browser harness missing marker {$marker}");
    }
}

foreach ([
    'admin-post.php',
    'aznet_theme_save_settings',
    'aznet_theme_reset_settings',
    'aznet_theme_import_settings',
    'aznet_theme_run_provisioning',
    'setInputFiles(',
    'Lưu thiết lập',
    'Lưu Quick Setup',
    'Đặt lại',
    'Nhập JSON',
] as $forbidden) {
    if (false !== strpos($browser, $forbidden)) {
        $fail("authenticated pilot harness must be read-only; forbidden marker {$forbidden}");
    }
}

if (substr_count($browser, '.click()') > 1) {
    $fail('authenticated pilot harness may click only the WordPress login submit control');
}

fwrite(STDOUT, "PASS: P4 authenticated pilot workflow/harness contract\n");
