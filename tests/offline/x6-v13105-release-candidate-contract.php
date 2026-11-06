<?php
/**
 * AZnet Theme 1.3.105: fail-closed technical candidate for D-066 default-visible portability and upgrade continuity.
 *
 * Technical candidate gate, NOT permission to tag/release/deploy.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = (string) file_get_contents($root . '/style.css');
$boot = (string) file_get_contents($root . '/functions.php');
$verify = (string) file_get_contents($root . '/scripts/verify-v1-core.sh');
$workflow = (string) file_get_contents($root . '/.github/workflows/x6-cross-surface-release.yml');

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL X6 1.3.105 release candidate: {$message}\n");
    exit(1);
};

if (!preg_match('/^Version:\s*1\.3\.105\s*$/m', $style)
    || !str_contains($boot, "define( 'AZNET_THEME_VERSION', '1.3.105' );")) {
    $fail('Theme version must be 1.3.105 in style.css and functions.php');
}
if (!str_contains($verify, '  1.3.105)')
    || !str_contains($verify, 'tests/offline/x6-v13105-release-candidate-contract.php')
    || !str_contains($verify, '  1.3.104)')
    || !str_contains($verify, 'tests/offline/x6-v13104-release-candidate-contract.php')
    || !str_contains($verify, 'FAIL: unsupported Theme release contract version')) {
    $fail('strict version dispatcher must add 1.3.105, retain 1.3.104, reject unknown versions');
}

$start = strrpos($workflow, '            1.3.105)');
$end = false === $start ? false : strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start) {
    $fail('1.3.105 X6 workflow case missing or after fail-closed default');
}
if (!str_contains($workflow, '            1.3.104)')
    || !str_contains($workflow, 'FAIL: unsupported X6 Theme version state')) {
    $fail('historical X6 version case or fail-closed default removed');
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.105 boundary',
    '-Version: 1.3.104',
    '+Version: 1.3.105',
    "-    define( 'AZNET_THEME_VERSION', '1.3.104' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.105' );",
] as $marker) {
    if (!str_contains($block, $marker)) {
        $fail('X6 1.3.105 exact-change allowlist missing ' . $marker);
    }
}

$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('X6 version metadata reject-regex missing');
}
$allowedPattern = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (!str_contains($allowedPattern, '1.3.104')
    || !str_contains($allowedPattern, '1.3.105')
    || str_contains($allowedPattern, '1.3.103')) {
    $fail('X6 version allowlist may only promote 1.3.104 to 1.3.105');
}
echo "PASS: exact one-patch 1.3.105 metadata promotion and historical guard retained\n";
