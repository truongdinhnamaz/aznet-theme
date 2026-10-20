<?php
/** Technical-only v1.3.92 release candidate: expected single-step version promotion. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$php = (string) file_get_contents($root . '/functions.php');
$wf = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');
$fail = static function (string $why): never {
    fwrite(STDERR, "FAIL X6 v1.3.92 candidate: {$why}\n");
    exit(1);
};
if (!str_contains($style, 'Version: 1.3.92')
    || !str_contains($php, "define( 'AZNET_THEME_VERSION', '1.3.92' );")) {
    $fail('style/PHP version metadata mismatch');
}
$needle = '            1.3.92)';
$start = strrpos($wf, $needle);
$end = false === $start ? false : strpos($wf, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('v1.3.92 X6 fail-closed version gate missing');
}
$block = substr($wf, $start, $end - $start);
foreach ([
    '-Version: 1.3.91',
    '+Version: 1.3.92',
    "-    define( 'AZNET_THEME_VERSION', '1.3.91' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.92' );",
    'metadata_changes',
    'grep -Fxc -- "$expected"',
] as $marker) {
    if (!str_contains($block, $marker)) $fail('v1.3.92 promotion boundary missing ' . $marker);
}
echo "PASS: X6 v1.3.92 single-step reviewed technical release candidate\n";
