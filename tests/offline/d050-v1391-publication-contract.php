<?php
/**
 * D-050 one-shot v1.3.91 publication contract.
 * Fail closed: approved Theme ZIP must be exact-byte identical to the
 * retained Y5 candidate, and publication never mutates production.
 *
 * @package AZnetTheme
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$workflowPath = $root . '/.github/workflows/d050-v1391-approved-release.yml';
if (!is_file($workflowPath)) {
    fwrite(STDERR, "FAIL: owner-approved v1.3.91 publication workflow absent\n");
    exit(1);
}
$workflow = file_get_contents($workflowPath);
if (!is_string($workflow)) {
    fwrite(STDERR, "FAIL: publication workflow unreadable\n");
    exit(1);
}
$required = [
    'name: D-050 Owner-Approved v1.3.91 Publication',
    'branches: [main]',
    'permissions:',
    'contents: write',
    'if: startsWith(github.event.head_commit.message,',
    'D050 Release v1.3.91:',
    'refs/heads/main',
    'git rev-parse HEAD',
    'AZNET_THEME_VERSION',
    '1.3.91',
    'bash scripts/verify-v1-core.sh',
    'python3 scripts/build-release-package.py',
    'cmp dist/aznet-theme-1.3.91.zip',
    'bd77316792a31abdc1ddf7f7e8966ee1e08b15ab55c3c998792016a3c5eecc7c',
    'sha256sum dist/aznet-theme-1.3.91.zip',
    'PHP_COUNT',
    'wp core download --version=6.9',
    'wp theme activate aznet-theme',
    'gh release create v1.3.91',
    '--target "$GITHUB_SHA"',
    'git ls-remote --exit-code --tags origin refs/tags/v1.3.91',
    'test "$(git rev-list -n 1 v1.3.91)" = "$GITHUB_SHA"',
    'Site Guard',
    'does NOT deploy',
];
foreach ($required as $marker) {
    if (!str_contains($workflow, $marker)) {
        fwrite(STDERR, "FAIL: v1.3.91 publication missing {$marker}\n");
        exit(1);
    }
}
$forbidden = [
    'minhnguyen.vn/wp-admin',
    'wp theme install --url=https://minhnguyen.vn',
    'wp theme update --url=https://minhnguyen.vn',
    'gh release create v1.3.90',
];
foreach ($forbidden as $marker) {
    if (str_contains($workflow, $marker)) {
        fwrite(STDERR, "FAIL: forbidden release/deploy token {$marker}\n");
        exit(1);
    }
}
echo "PASS: D-050 one-shot v1.3.91 publication scope, SHA and no-deploy guard\n";
