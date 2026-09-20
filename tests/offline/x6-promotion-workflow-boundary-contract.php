<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/.github/workflows/x6-cross-surface-release.yml';

if (! is_file($path)) {
    fwrite(STDERR, "FAIL: missing X6 workflow\n");
    exit(1);
}

$workflow = file_get_contents($path);
if (false === $workflow) {
    fwrite(STDERR, "FAIL: cannot read X6 workflow\n");
    exit(1);
}

$required = [
    'VERSION=$(sed -n',
    'case "$VERSION" in',
    '1.1.0)',
    '1.2.0)',
    '1.3.0)',
    '1.3.1)',
    '1.3.2)',
    '1.3.3)',
    '1.3.4)',
    '1.3.5)',
    '1.3.6)',
    '1.3.8)',
    '1.3.9)',
    '1.3.10)',
    '1.3.11)',
    '1.3.12)',
    '1.3.13)',
    '1.3.14)',
    '1.3.15)',
    '1.3.16)',
    '1.3.17)',
    'tests/offline/x6-version-promotion-contract.php',
    "echo 'PASS: X6 pre-promotion ownership boundary'",
    "echo 'PASS: X6 promoted 1.2.0 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.0 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.1 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.2 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.3 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.4 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.5 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.6 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.8 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.9 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.10 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.11 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.12 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.13 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.14 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.15 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.16 ownership boundary'",
    "echo 'PASS: X6 promoted 1.3.17 ownership boundary'",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.2.0 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.0 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.1 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.2 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.3 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.4 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.5 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.6 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.8 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.9 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.10 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.11 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.12 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.13 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.14 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.15 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.16 boundary' >&2",
    "echo 'FAIL: X6 promoted metadata diff exceeds approved 1.3.17 boundary' >&2",
    "echo 'FAIL: unsupported X6 Theme version state' >&2",
];

foreach ($required as $marker) {
    if (! str_contains($workflow, $marker)) {
        fwrite(STDERR, "FAIL: X6 promotion-aware workflow missing marker: {$marker}\n");
        exit(1);
    }
}

if (str_contains($workflow, "if git diff origin/main...HEAD -- style.css functions.php | grep -q '^+'; then")) {
    fwrite(STDERR, "FAIL: X6 workflow still applies the obsolete unconditional pre-promotion metadata guard\n");
    exit(1);
}

echo "PASS: X6 workflow separates pre-promotion and promoted ownership boundaries\n";