<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/.github/workflows/x4-pagination-navigation.yml';

if (! is_file($path)) {
    fwrite(STDERR, "FAIL: missing X4 workflow\n");
    exit(1);
}

$workflow = file_get_contents($path);
if (false === $workflow) {
    fwrite(STDERR, "FAIL: cannot read X4 workflow\n");
    exit(1);
}

$required = [
    'HEAD_REF: ${{ github.head_ref || github.ref_name }}',
    'X4_SLICE_BRANCH: feat/v1.2-x4-pagination-navigation',
    'if [[ "${HEAD_REF:-}" == "$X4_SLICE_BRANCH" ]]; then',
    "echo 'PASS: X4 slice-only ownership guard'",
    "echo 'FAIL: unplanned X4 file changed' >&2",
    "echo 'FAIL: X4 must not add production JavaScript' >&2",
    "echo 'FAIL: X4 introduced forbidden query/redirect/provider ownership' >&2",
];

foreach ($required as $marker) {
    if (! str_contains($workflow, $marker)) {
        fwrite(STDERR, "FAIL: X4 retained workflow missing marker: {$marker}\n");
        exit(1);
    }
}

$conditional = strpos($workflow, 'if [[ "${HEAD_REF:-}" == "$X4_SLICE_BRANCH" ]]; then');
$allowlistFailure = strpos($workflow, "echo 'FAIL: unplanned X4 file changed' >&2");
$jsFailure = strpos($workflow, "echo 'FAIL: X4 must not add production JavaScript' >&2");
$slicePass = strpos($workflow, "echo 'PASS: X4 slice-only ownership guard'");
$queryGuard = strpos($workflow, "echo 'FAIL: X4 introduced forbidden query/redirect/provider ownership' >&2");

if (false === $conditional || false === $allowlistFailure || false === $jsFailure || false === $slicePass || false === $queryGuard) {
    fwrite(STDERR, "FAIL: cannot resolve X4 retained ownership guard ordering\n");
    exit(1);
}

if (! ($conditional < $allowlistFailure && $allowlistFailure < $slicePass && $conditional < $jsFailure && $jsFailure < $slicePass)) {
    fwrite(STDERR, "FAIL: X4 implementation-only allowlist/JS guard must be branch-scoped\n");
    exit(1);
}

if (! ($slicePass < $queryGuard)) {
    fwrite(STDERR, "FAIL: X4 query/redirect/provider invariant must remain active for retained regression PRs\n");
    exit(1);
}

echo "PASS: X4 retained regression separates slice ownership from functional invariants\n";
