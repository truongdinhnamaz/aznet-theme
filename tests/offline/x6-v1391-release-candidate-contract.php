<?php
/**
 * Technical-only v1.3.91 release candidate: fail-closed metadata + X6 contract.
 *
 * No tag, publication workflow, owner release authorization or production
 * installation is implied by a successful result.
 *
 * @package AZnetTheme
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = file_get_contents($root . '/style.css');
$boot = file_get_contents($root . '/functions.php');
$workflow = file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
if (!is_string($style) || !is_string($boot) || !is_string($workflow)) {
    fwrite(STDERR, "FAIL: unable to read v1.3.91 release candidate inputs\n");
    exit(1);
}
$required = [
    [$style, 'Version: 1.3.91', 'Theme version header'],
    [$boot, "define( 'AZNET_THEME_VERSION', '1.3.91' );", 'Theme PHP version'],
    [$workflow, '            1.3.91)', 'strict X6 candidate case'],
];
foreach ($required as [$source, $needle, $label]) {
    if (!str_contains($source, $needle)) {
        fwrite(STDERR, "FAIL: v1.3.91 technical candidate missing {$label}\n");
        exit(1);
    }
}

$start = strrpos($workflow, '            1.3.91)');
$end = strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    fwrite(STDERR, "FAIL: v1.3.91 candidate gate missing fail-closed boundary\n");
    exit(1);
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    '-Version: 1.3.90',
    '+Version: 1.3.91',
    "-    define( 'AZNET_THEME_VERSION', '1.3.90' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.91' );",
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'echo',
] as $needle) {
    if (!str_contains($block, $needle)) {
        fwrite(STDERR, "FAIL: v1.3.91 candidate X6 promotion check missing {$needle}\n");
        exit(1);
    }
}
if (str_contains($workflow, 'gh release create v1.3.91')) {
    fwrite(STDERR, "FAIL: technical candidate cannot self-publish a GitHub Release\n");
    exit(1);
}

echo "PASS: strict v1.3.91 technical candidate, without release or deployment\n";
