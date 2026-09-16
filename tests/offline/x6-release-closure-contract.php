<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x6_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x6_read(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (! is_file($path)) {
        x6_fail('missing required X6 file: ' . $relative);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        x6_fail('cannot read required X6 file: ' . $relative);
    }
    return $source;
}

$required = [
    'scripts/assert-theme-version.sh',
    'tests/runtime/x6-cross-surface.php',
    'tests/browser/x6-cross-surface-l4.mjs',
    '.github/workflows/x6-cross-surface-release.yml',
    'scripts/build-release-package.py',
    '.github/workflows/v1-release-candidate.yml',
];

foreach ($required as $relative) {
    x6_read($relative);
}

$verify = x6_read('scripts/verify-v1-core.sh');
foreach ([
    'tests/offline/x1-comments-surface-contract.php',
    'tests/offline/x2-search-404-empty-contract.php',
    'tests/offline/x3-media-gallery-embed-contract.php',
    'tests/offline/x4-pagination-navigation-contract.php',
    'tests/offline/x5-native-form-controls-contract.php',
] as $contract) {
    if (! str_contains($verify, $contract)) {
        x6_fail('reusable verifier missing retained X1-X5 contract: ' . $contract);
    }
}

$workflow = x6_read('.github/workflows/x6-cross-surface-release.yml');
foreach ([
    "php-version: '8.1'",
    'wp core download --version=6.9',
    'playwright@1.55.0',
    '@axe-core/playwright@4.10.2',
    'scripts/assert-theme-version.sh',
    'scripts/verify-v1-core.sh',
    'scripts/build-release-package.py',
    'cmp ',
    'diff -ru',
    'wp plugin list --status=active',
    'twentytwentyfive',
    'wp theme activate aznet-theme',
] as $marker) {
    if (! str_contains($workflow, $marker)) {
        x6_fail('X6 workflow missing release-closure marker: ' . $marker);
    }
}

$browserRun = strpos($workflow, 'node tests/browser/x6-cross-surface-l4.mjs');
$cleanup = strpos($workflow, 'rm -rf node_modules');
$packageBuild = strpos($workflow, '- name: Build deterministic current-version package twice');
if (false === $browserRun || false === $cleanup || false === $packageBuild || ! ($browserRun < $cleanup && $cleanup < $packageBuild)) {
    x6_fail('X6 workflow must remove transient node_modules after browser QA and before release packaging');
}

$implementation = x6_read('tests/runtime/x6-cross-surface.php') . "\n"
    . x6_read('tests/browser/x6-cross-surface-l4.mjs') . "\n"
    . $workflow;

foreach ([
    'new WP_Query',
    'query_posts(',
    'pre_get_posts',
    '$wpdb',
    'get_post_meta(',
    'choiceguide_',
    'rootprofile_',
    'ConvertFlow',
    'wp_redirect(',
    'wp_safe_redirect(',
    'XMLHttpRequest',
    'fetch(',
] as $forbidden) {
    if (stripos($implementation, $forbidden) !== false) {
        x6_fail('forbidden ownership/provider/application marker found: ' . $forbidden);
    }
}

echo "PASS: X6 cross-surface release-closure infrastructure contract\n";
