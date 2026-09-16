<?php

declare(strict_types=1);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$workflowPath = __DIR__ . '/../../.github/workflows/p1-pilot-identity-inspection.yml';
$browserPath  = __DIR__ . '/../browser/p1-pilot-identity-inspection.mjs';

if (!is_file($workflowPath)) {
    $fail('P1 pilot identity inspection workflow is missing');
}
if (!is_file($browserPath)) {
    $fail('P1 pilot identity inspection browser harness is missing');
}

$workflow = (string) file_get_contents($workflowPath);
$browser  = (string) file_get_contents($browserPath);

foreach ([
    'name: P1 Pilot Identity Inspection',
    'workflow_dispatch:',
    'contents: read',
    'PILOT_WP_USER',
    'PILOT_WP_PASSWORD',
    'tests/browser/p1-pilot-identity-inspection.mjs',
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
    'P1_BASE_URL',
    'P1_ADMIN_USER',
    'P1_ADMIN_PASS',
    '/wp-login.php',
    '#user_login',
    '#user_pass',
    '#wp-submit',
    '/wp-admin/themes.php',
    '_wpThemeSettings',
    'data-slug',
    'admin.php?page=aznet-theme&section=system-health',
    'textarea[readonly]',
    'AZnet Theme',
    '1.1.0',
    'INACTIVE_CANDIDATE',
    'AMBIGUOUS',
    'themes-1440.png',
    'blocking_failures',
] as $marker) {
    if (false === strpos($browser, $marker)) {
        $fail("browser harness missing marker {$marker}");
    }
}

foreach ([
    'themes.php?action=delete',
    'themes.php?action=activate',
    'update.php?action=update-theme',
    'delete-theme',
    'activate-theme',
    'update-theme',
    'admin-post.php',
    'setInputFiles(',
] as $forbidden) {
    if (false !== strpos($browser, $forbidden)) {
        $fail("P1 inspection harness must remain read-only; forbidden marker {$forbidden}");
    }
}

if (substr_count($browser, '.click()') > 1) {
    $fail('P1 inspection harness may click only the WordPress login submit control');
}

fwrite(STDOUT, "PASS: P1 pilot identity inspection workflow/harness contract\n");
