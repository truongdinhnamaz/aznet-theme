<?php
/**
 * AZnet Theme 1.3.110: bounded metadata promotion after the merged D-050
 * operator-only presentation authorization changes (PR #588).
 *
 * Technical release candidate only. A source candidate is not a GitHub
 * Release and is not evidence of an effective site-level WordPress lock.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$read = static function (string $path) use ($root): string {
    $data = @file_get_contents($root . '/' . $path);
    if (! is_string($data)) {
        fwrite(STDERR, "FAIL X6 1.3.110: missing {$path}\n");
        exit(1);
    }
    return $data;
};
$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL X6 1.3.110: {$message}\n");
    exit(1);
};
$style = $read('style.css');
$boot = $read('functions.php');
$verify = $read('scripts/verify-v1-core.sh');
$workflow = $read('.github/workflows/x6-cross-surface-release.yml');
$admin = $read('inc/admin/bootstrap.php');
$center = $read('inc/admin/control-center.php');
$danger = $read('inc/admin/danger-zone.php');
$browser = $read('tests/browser/r5-control-center-l4.mjs');

if (! preg_match('/^Version:\s*1\.3\.110\s*$/m', $style)
    || ! str_contains($boot, "define( 'AZNET_THEME_VERSION', '1.3.110' );")) {
    $fail('style.css and functions.php must declare identical 1.3.110 metadata');
}
if (! str_contains($verify, '  1.3.109)')
    || ! str_contains($verify, 'tests/offline/x6-v13109-release-candidate-contract.php')
    || ! str_contains($verify, '  1.3.110)')
    || ! str_contains($verify, 'tests/offline/x6-v13110-release-candidate-contract.php')
    || ! str_contains($verify, 'FAIL: unsupported Theme release contract version')) {
    $fail('dispatcher must retain historical version and fail-closed default');
}
if (! str_contains($admin, 'function register_presentation_operator_guards(): void')
    || ! str_contains($admin, 'register_presentation_operator_guards();')
    || ! str_contains($admin, 'DANGER_ZONE_CAPABILITY,')
    || ! str_contains($admin, "admin_post_aznet_theme_' . \$action")
    || ! str_contains($center, "if ( ! danger_zone_is_authorized() )")
    || ! str_contains($danger, "const DANGER_ZONE_CAPABILITY = 'aznet_theme_manage_danger_zone';")
    || ! str_contains($browser, 'ordinary Administrator unexpectedly reached Control Center')
    || ! str_contains($browser, 'ordinary Administrator bypassed Theme POST guard')) {
    $fail('merged operator-only Theme guards or negative browser checks missing');
}
if (str_contains($admin, 'truongdinhnam') || str_contains($center, 'truongdinhnam')) {
    $fail('never hardcode operator login in Theme code');
}
$start = strrpos($workflow, '            1.3.110)');
$end = false === $start ? false : strpos($workflow, '            *)', $start);
if (false === $start || false === $end || $end <= $start
    || ! str_contains($workflow, '            1.3.109)')
    || ! str_contains($workflow, 'FAIL: unsupported X6 Theme version state')) {
    $fail('version 1.3.110 X6 promotion case or historical fail-closed case missing');
}
$block = substr($workflow, $start, $end - $start);
foreach ([
    'metadata_changes',
    'grep -Fxc -- "$expected"',
    'FAIL: X6 promoted metadata diff exceeds approved 1.3.110 boundary',
    '-Version: 1.3.109',
    '+Version: 1.3.110',
    "-    define( 'AZNET_THEME_VERSION', '1.3.109' );",
    "+    define( 'AZNET_THEME_VERSION', '1.3.110' );",
] as $marker) {
    if (! str_contains($block, $marker)) {
        $fail('strict metadata allowlist missing: ' . $marker);
    }
}
$regexStart = strpos($block, 'grep -Ev');
$regexEnd = false === $regexStart ? false : strpos($block, '|| true)', $regexStart);
if (false === $regexStart || false === $regexEnd) {
    $fail('strict metadata rejection pattern missing');
}
$allowedPattern = str_replace(chr(92), '', substr($block, $regexStart, $regexEnd - $regexStart));
if (! str_contains($allowedPattern, '1.3.109')
    || ! str_contains($allowedPattern, '1.3.110')
    || str_contains($allowedPattern, '1.3.108')) {
    $fail('allowlist must only permit version 1.3.109 to 1.3.110');
}
echo "PASS: AZnet Theme 1.3.110 D-050 operator boundary release candidate\n";
