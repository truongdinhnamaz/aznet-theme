<?php
/** Technical-only v1.3.97 draft candidate: fail-closed 1.3.96 -> 1.3.97 metadata promotion. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$php = (string) file_get_contents($root . '/functions.php');
$wf = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
$fail = static function (string $why): never {
    fwrite(STDERR, "FAIL X6 v1.3.97 candidate: {$why}\n");
    exit(1);
};
if (!str_contains($style, 'Version: 1.3.97')
    || !str_contains($php, "define( 'AZNET_THEME_VERSION', '1.3.97' );")) {
    $fail('style/PHP version metadata mismatch');
}
$needle = '            1.3.97)';
$start = strrpos($wf, $needle);
$end = false === $start ? false : strpos($wf, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('v1.3.97 X6 fail-closed version gate missing');
}
$block = substr($wf, $start, $end - $start);
foreach ([
    '-Version: 1.3.96',
    '+Version: 1.3.97',
    "-    define( 'AZNET_THEME_VERSION', '1.3.96' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.97' );",
    'metadata_changes',
    'grep -Fxc -- "$expected"',
] as $marker) {
    if (!str_contains($block, $marker)) $fail('v1.3.97 promotion boundary missing ' . $marker);
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('X6 v1.3.97 regex allowlist missing');
}
$regexNormalized = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (!str_contains($regexNormalized, '1.3.96') ||
    !str_contains($regexNormalized, '1.3.97') ||
    str_contains($regexNormalized, '1.3.95')) {
    $fail('X6 regex allowlist still authorizes obsolete 1.3.95-to-1.3.96 metadata');
}

echo "PASS: X6 v1.3.97 bounded technical-only draft candidate\n";
