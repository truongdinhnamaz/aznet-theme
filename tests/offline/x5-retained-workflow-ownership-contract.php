<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$path = $root . '/.github/workflows/x5-native-form-controls.yml';

if (! is_file($path)) {
    fwrite(STDERR, "FAIL: missing X5 workflow\n");
    exit(1);
}

$workflow = file_get_contents($path);
if (false === $workflow) {
    fwrite(STDERR, "FAIL: cannot read X5 workflow\n");
    exit(1);
}

$required = [
    'HEAD_REF: ${{ github.head_ref || github.ref_name }}',
    'X5_SLICE_BRANCH: feat/v1.2-x5-native-form-controls',
    'if [[ "${HEAD_REF:-}" == "$X5_SLICE_BRANCH" ]]; then',
    "echo 'PASS: X5 slice-only ownership guard'",
    "echo 'FAIL: unplanned X5 file changed' >&2",
    "echo 'FAIL: X5 must not add production JavaScript' >&2",
    "echo 'FAIL: X5 introduced forbidden state/query/provider/form-engine ownership' >&2",
];

foreach ($required as $marker) {
    if (! str_contains($workflow, $marker)) {
        fwrite(STDERR, "FAIL: X5 retained workflow missing marker: {$marker}\n");
        exit(1);
    }
}

$conditional = strpos($workflow, 'if [[ "${HEAD_REF:-}" == "$X5_SLICE_BRANCH" ]]; then');
$allowlistFailure = strpos($workflow, "echo 'FAIL: unplanned X5 file changed' >&2");
$jsFailure = strpos($workflow, "echo 'FAIL: X5 must not add production JavaScript' >&2");
$slicePass = strpos($workflow, "echo 'PASS: X5 slice-only ownership guard'");
$invariantGuard = strpos($workflow, "echo 'FAIL: X5 introduced forbidden state/query/provider/form-engine ownership' >&2");

if (false === $conditional || false === $allowlistFailure || false === $jsFailure || false === $slicePass || false === $invariantGuard) {
    fwrite(STDERR, "FAIL: cannot resolve X5 retained ownership guard ordering\n");
    exit(1);
}

if (! ($conditional < $allowlistFailure && $allowlistFailure < $slicePass && $conditional < $jsFailure && $jsFailure < $slicePass)) {
    fwrite(STDERR, "FAIL: X5 implementation-only allowlist/JS guard must be branch-scoped\n");
    exit(1);
}

if (! ($slicePass < $invariantGuard)) {
    fwrite(STDERR, "FAIL: X5 ownership invariant must remain active for retained regression PRs\n");
    exit(1);
}

echo "PASS: X5 retained regression separates slice ownership from functional invariants\n";
