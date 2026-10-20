<?php
/** D-050 three-pilot v1.3.92 owner-approved, one-shot GitHub release contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$php = (string) file_get_contents($root . '/functions.php');
$path = $root . '/.github/workflows/d050-v1392-approved-release.yml';
$workflow = is_file($path) ? (string) file_get_contents($path) : '';

$fail = static function (string $why): never {
    fwrite(STDERR, "FAIL D-050 v1.3.92 release: {$why}\n");
    exit(1);
};
if (!str_contains($style, 'Version: 1.3.92')
    || !str_contains($php, "define( 'AZNET_THEME_VERSION', '1.3.92' );")) {
    $fail('Theme v1.3.92 metadata does not match');
}
if ($workflow === '') {
    $fail('reviewed one-shot v1.3.92 release workflow is missing');
}
$required = [
    'name: D-050 Owner-Approved v1.3.92 Publication',
    "branches: [main]",
    'contents: write',
    'if: startsWith(github.event.head_commit.message',
    'D050 Release v1.3.92:',
    'refs/heads/main',
    'test "$(git rev-parse HEAD)" = "$GITHUB_SHA"',
    "define( 'AZNET_THEME_VERSION', '1.3.92' );",
    'bash scripts/verify-v1-core.sh',
    'python3 scripts/build-release-package.py',
    'cmp dist/aznet-theme-1.3.92.zip',
    'EXPECTED_PACKAGE_SHA=',
    'sha256sum dist/aznet-theme-1.3.92.zip',
    'unzip -tq dist/aznet-theme-1.3.92.zip',
    'PHP_COUNT',
    'wp core download --version=6.9',
    'wp theme activate aznet-theme',
    'gh release create v1.3.92',
    '--target "$GITHUB_SHA"',
    'git ls-remote --exit-code --tags origin refs/tags/v1.3.92',
    'test "$(git rev-list -n 1 v1.3.92)" = "$GITHUB_SHA"',
    'does NOT deploy',
];
foreach ($required as $needle) {
    if (!str_contains($workflow, $needle)) {
        $fail("missing fail-closed release checkpoint: {$needle}");
    }
}
if (!preg_match("/EXPECTED_PACKAGE_SHA='[a-f0-9]{64}'/", $workflow)) {
    $fail('reviewed deterministic candidate ZIP SHA-256 not pinned');
}
foreach (['wp theme install', 'wp theme update', 'minhnguyen.vn/wp-admin', 'remquocanh.vn/wp-admin', 'lstamduchn.vn/wp-admin', 'gh release create v1.3.91'] as $forbidden) {
    if (str_contains($workflow, $forbidden)) {
        $fail("release workflow wrongly performs site operation or overwrites old tag: {$forbidden}");
    }
}
echo "PASS: D-050 v1.3.92 one-shot release, approved checksum, no site deployment\n";
