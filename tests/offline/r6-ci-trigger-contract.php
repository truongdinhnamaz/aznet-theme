<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$prPath = $root . '/.github/workflows/v1-core-ci.yml';
$mainPath = $root . '/.github/workflows/v1-main-verification.yml';

function fail_r6_ci(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

foreach ([$prPath, $mainPath] as $path) {
    if (! is_file($path)) {
        fail_r6_ci('missing reusable workflow: ' . basename($path));
    }
}

$pr = (string) file_get_contents($prPath);
$main = (string) file_get_contents($mainPath);

if (! preg_match('/\bpull_request\s*:/', $pr) || ! preg_match('/branches\s*:\s*(?:\[\s*main\s*\]|\n\s*-\s*main\b)/', $pr)) {
    fail_r6_ci('v1-core-ci must run on pull_request to main');
}

if (! preg_match('/\bpush\s*:/', $main) || ! preg_match('/branches\s*:\s*(?:\[\s*main\s*\]|\n\s*-\s*main\b)/', $main)) {
    fail_r6_ci('v1-main-verification must run on push to main');
}

$requiredPaths = [
    "'*.php'",
    "'inc/**'",
    "'template-parts/**'",
    "'assets/**'",
    "'patterns/**'",
    "'theme.json'",
    "'style.css'",
];

foreach (['v1-core-ci' => $pr, 'v1-main-verification' => $main] as $label => $workflow) {
    foreach ($requiredPaths as $required) {
        if (! str_contains($workflow, $required)) {
            fail_r6_ci("{$label} missing production path {$required}");
        }
    }
    if (! str_contains($workflow, 'scripts/verify-v1-core.sh')) {
        fail_r6_ci("{$label} must call reusable core verification script");
    }
    if (! str_contains($workflow, 'contents: read')) {
        fail_r6_ci("{$label} must use read-only contents permission");
    }
}

if (! str_contains($main, 'github.sha')) {
    fail_r6_ci('v1-main-verification must record exact github.sha');
}

foreach (['WordPress 6.9', 'tests/browser/g-core-l4.mjs'] as $marker) {
    if (! str_contains($main, $marker)) {
        fail_r6_ci("v1-main-verification missing exact-main runtime/browser marker: {$marker}");
    }
}

// Regression: temporary browser probes must never remove Woo runtime coverage.
$r6Path = $root . '/.github/workflows/r6-performance-baseline.yml';
if (! is_file($r6Path)) {
    fail_r6_ci('R6 performance workflow missing');
}
$r6Workflow = (string) file_get_contents($r6Path);
$r6Steps = [
    'Capture clean WordPress baseline',
    'Install WooCommerce 10.9.4 and public product fixtures',
    'Capture WooCommerce baseline',
    'Verify runtime boundary and baseline completeness',
];
$previousOffset = -1;
foreach ($r6Steps as $step) {
    $offset = strpos($r6Workflow, '      - name: ' . $step);
    if (false === $offset || $offset <= $previousOffset) {
        fail_r6_ci('R6 workflow missing/out-of-order Woo regression step: ' . $step);
    }
    $previousOffset = $offset;
}

echo "PASS: R6 reusable PR and exact-main CI trigger contract\n";