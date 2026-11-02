<?php
/**
 * UPN1 proposal-only RED gate: WordPress-native Theme update discovery.
 *
 * This test intentionally fails on canonical 1.3.98 because Update URI and
 * a host-specific update filter are not yet implemented. It must not be added
 * to mandatory CI until UPN0 is accepted and production GREEN is authorized.
 *
 * Run: php tests/proposals/upn1-native-update-red.php
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$style = file_get_contents($root . '/style.css');
$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');

if (!is_string($style) || !is_string($bootstrap)) {
    fwrite(STDERR, "BLOCKED: source files unreadable\n");
    exit(2);
}

$checks = [
    'Update URI is explicitly declared in style.css' =>
        preg_match('/^Update URI:\\s*https:\/\/[^\\s]+/mi', $style) === 1,
    'WordPress external theme update host filter is registered' =>
        preg_match('/update_themes_[a-z0-9.-]+/', $bootstrap) === 1
        || preg_match('/updater\.php|theme-update\.php/', $bootstrap) === 1,
];

$failed = 0;
foreach ($checks as $label => $pass) {
    echo ($pass ? 'PASS: ' : 'RED: ') . $label . PHP_EOL;
    if (!$pass) {
        ++$failed;
    }
}

if ($failed > 0) {
    fwrite(STDERR, "EXPECTED RED: native update-notification entrypoint is absent. "
        . "No claim is made about runtime, install authenticity, or release readiness.\n");
    exit(1);
}

echo "STATIC ENTRYPOINT ONLY: further contract/runtime tests are required.\n";
