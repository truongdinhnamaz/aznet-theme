<?php
/**
 * AZnet Theme 1.3.107: fail-closed release metadata candidate carrying the
 * Theme-owned native Page nested alignwide / embedded form viewport fix.
 *
 * Candidate technical verification only — NOT authorization to tag, publish
 * or deploy to a production WordPress site.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$boot = (string) file_get_contents($root . '/functions.php');
$verify = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');
$workflow = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
$pageCss = (string) file_get_contents($root . '/assets/css/components/page.css');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL X6 1.3.107 release candidate: {$message}\n");
    exit(1);
};

if (! preg_match('/^Version:\\s*1\\.3\\.107\\s*$/m', $style)
    || ! str_contains($boot, "define( 'AZNET_THEME_VERSION', '1.3.107' );")) {
    $fail('Theme metadata must identify 1.3.107 in style.css and functions.php');
}
if (! str_contains($verify, '  1.3.107)')
    || ! str_contains($verify, 'tests/offline/x6-v13107-release-candidate-contract.php')
    || ! str_contains($verify, '  1.3.106)')
    || ! str_contains($verify, 'tests/offline/x6-v13106-release-candidate-contract.php')
    || ! str_contains($verify, 'FAIL: unsupported Theme release contract version')) {
    $fail('strict version dispatcher must add 1.3.107 and retain historical 1.3.106 and fail-closed default');
}
if (! str_contains($verify, 'tests/offline/page-native-alignwide-form-layout-contract.php')
    || ! str_contains($pageCss, '.aznet-theme-page__content-inner .wp-block-group.alignwide > .wp-block-columns.alignwide')
    || ! str_contains($pageCss, 'form input:not([type="submit"])')) {
    $fail('Theme-owned Page viewport fix and its reusable V1 regression must be retained');
}

$start = strrpos($workflow, '            1.3.107)');
$end = false === $start ? false : strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('1.3.107 X6 workflow case missing or after fail-closed default');
}
if (! str_contains($workflow, '            1.3.106)')
    || ! str_contains($workflow, 'FAIL: unsupported X6 Theme version state')) {
    $fail('historical 1.3.106 X6 version case or fail-closed default removed');
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.107 boundary',
    '-Version: 1.3.106',
    '+Version: 1.3.107',
    "-    define( 'AZNET_THEME_VERSION', '1.3.106' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.107' );",
] as $marker) {
    if (! str_contains($block, $marker)) {
        $fail('X6 exact 1.3.107 metadata allowlist missing ' . $marker);
    }
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('X6 version metadata reject-regex missing');
}
$allowedPattern = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (! str_contains($allowedPattern, '1.3.106')
    || ! str_contains($allowedPattern, '1.3.107')
    || str_contains($allowedPattern, '1.3.105')) {
    $fail('X6 candidate allowlist may only promote 1.3.106 to 1.3.107');
}

echo "PASS: v1.3.107 bounded metadata promotion; Page fix and historical guards retained\n";
