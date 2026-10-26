<?php
/**
 * Technical-only v1.3.98 version candidate (#532).
 *
 * This validates strict one-step metadata promotion only; it does not
 * authorize merging, tagging, releasing or deploying any WordPress site.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$php = (string) file_get_contents($root . '/functions.php');
$wf = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
$verifier = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');

$fail = static function (string $why): never {
    fwrite(STDERR, "FAIL X6 v1.3.98 technical candidate: {$why}\n");
    exit(1);
};

if (!preg_match('/^Version:\s*1\.3\.98\s*$/m', $style)
    || !str_contains($php, "define( 'AZNET_THEME_VERSION', '1.3.98' );")) {
    $fail('style.css and functions.php must both declare exact next patch 1.3.98');
}

if (!str_contains($verifier, '  1.3.98)')
    || !str_contains($verifier, 'tests/offline/x6-v1398-release-candidate-contract.php')) {
    $fail('strict V1 Core version dispatch must explicitly allow 1.3.98 only through its contract');
}
if (!str_contains($verifier, '  1.3.97)')
    || !str_contains($verifier, 'tests/offline/x6-v1397-release-candidate-contract.php')
    || !str_contains($verifier, 'FAIL: unsupported Theme release contract version')) {
    $fail('historical strict-version cases and unknown-version failure must be retained');
}

$needle = '            1.3.98)';
$start = strrpos($wf, $needle);
$end = false === $start ? false : strpos($wf, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('explicit X6 1.3.98 fail-closed workflow branch absent');
}
$block = substr($wf, $start, $end - $start);
foreach ([
    '-Version: 1.3.97',
    '+Version: 1.3.98',
    "-    define( 'AZNET_THEME_VERSION', '1.3.97' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.98' );",
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.98 boundary',
] as $marker) {
    if (!str_contains($block, $marker)) {
        $fail('version metadata allowlist missing ' . $marker);
    }
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('exact 1.3.98 X6 metadata allowlist regex absent');
}
$allowlist = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (!str_contains($allowlist, '1.3.97')
    || !str_contains($allowlist, '1.3.98')
    || str_contains($allowlist, '1.3.96')) {
    $fail('X6 metadata allowlist includes obsolete or wrong version');
}
if (!str_contains($wf, '            1.3.97)')
    || !str_contains($wf, 'FAIL: unsupported X6 Theme version state')) {
    $fail('X6 historical gate or unknown-version failure weakened');
}

echo "PASS: technical-only 1.3.98 patch metadata promotion boundary\n";
