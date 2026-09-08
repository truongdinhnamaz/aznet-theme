<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/v1-release-candidate.yml';
$builderPath = $root . '/scripts/build-release-package.py';

function fail_r6_release(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (! is_file($workflowPath)) {
    fail_r6_release('missing v1-release-candidate.yml');
}
if (! is_file($builderPath)) {
    fail_r6_release('missing deterministic release package builder');
}

$workflow = (string) file_get_contents($workflowPath);
$builder = (string) file_get_contents($builderPath);

$workflowMarkers = [
    'workflow_dispatch:',
    'contents: read',
    'refs/heads/main',
    'AZNET_THEME_VERSION',
    'Version:',
    'scripts/verify-v1-core.sh',
    'scripts/build-release-package.py',
    'cmp ',
    'unzip ',
    'diff -ru',
    'php -l',
    'wp core download --version=6.9',
    'wp theme activate aznet-theme',
    'actions/upload-artifact@v4',
    'sha256sum',
];

foreach ($workflowMarkers as $marker) {
    if (! str_contains($workflow, $marker)) {
        fail_r6_release("release workflow missing marker: {$marker}");
    }
}

if (substr_count($workflow, 'scripts/build-release-package.py') < 2) {
    fail_r6_release('release workflow must build the package twice');
}

if (! preg_match('/aznet-theme-\$\{[^}]*VERSION[^}]*\}\.zip/', $workflow)) {
    fail_r6_release('release ZIP must be named from the parsed Theme version');
}

foreach (['actions/create-release', 'softprops/action-gh-release', 'git tag ', 'gh release create'] as $forbidden) {
    if (str_contains($workflow, $forbidden)) {
        fail_r6_release("candidate workflow must not publish tag/release: {$forbidden}");
    }
}

$builderMarkers = [
    "PACKAGE_ROOT = 'aznet-theme'",
    'ZipInfo',
    'FIXED_ZIP_TIME = (2020, 1, 1, 0, 0, 0)',
    'external_attr',
    'sorted(',
    'style.css',
    'functions.php',
    'theme.json',
    'front-page.php',
    'index.php',
    'AZNET_THEME_VERSION',
];

foreach ($builderMarkers as $marker) {
    if (! str_contains($builder, $marker)) {
        fail_r6_release("package builder missing deterministic/version marker: {$marker}");
    }
}

foreach (['.git', '.github', 'docs', 'scripts', 'tests', 'README.md', 'aznet-preview.png'] as $excluded) {
    if (! str_contains($builder, $excluded)) {
        fail_r6_release("package builder missing exclusion: {$excluded}");
    }
}

if (! str_contains($builder, 'any(part in EXCLUDED_DIRS for part in relative.parts)')) {
    fail_r6_release('package builder must preserve the legacy exclusion rule at any path depth');
}

echo "PASS: R6 deterministic reusable v1.x release-candidate workflow contract\n";