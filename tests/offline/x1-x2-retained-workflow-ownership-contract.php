<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$cases = [
    [
        'label' => 'X1',
        'path' => '.github/workflows/x1-comments-surface.yml',
        'branch' => 'feat/v1.2-x1-comments-surface',
        'fail' => "echo 'FAIL: X1 must not change Theme version declarations' >&2",
        'pass' => "echo 'PASS: X1 slice-only version ownership guard'",
        'exact_head' => true,
    ],
    [
        'label' => 'X2',
        'path' => '.github/workflows/x2-search-404-empty.yml',
        'branch' => 'feat/v1.2-x2-search-404',
        'fail' => "echo 'FAIL: X2 must not change Theme version declarations' >&2",
        'pass' => "echo 'PASS: X2 slice-only version ownership guard'",
        'exact_head' => false,
    ],
];

foreach ($cases as $case) {
    $path = $root . '/' . $case['path'];
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing {$case['label']} workflow\n");
        exit(1);
    }

    $workflow = file_get_contents($path);
    if (false === $workflow) {
        fwrite(STDERR, "FAIL: cannot read {$case['label']} workflow\n");
        exit(1);
    }

    $required = [
        'HEAD_REF: ${{ github.head_ref || github.ref_name }}',
        "{$case['label']}_SLICE_BRANCH: {$case['branch']}",
        "if [[ \"\${HEAD_REF:-}\" == \"\${{$case['label']}_SLICE_BRANCH}\" ]]; then",
        $case['fail'],
        $case['pass'],
    ];

    if ($case['exact_head']) {
        $required[] = 'ref: ${{ github.event.pull_request.head.sha || github.sha }}';
    }

    foreach ($required as $marker) {
        if (! str_contains($workflow, $marker)) {
            fwrite(STDERR, "FAIL: {$case['label']} retained workflow missing marker: {$marker}\n");
            exit(1);
        }
    }

    $conditional = strpos($workflow, "if [[ \"\${HEAD_REF:-}\" == \"\${{$case['label']}_SLICE_BRANCH}\" ]]; then");
    $versionFailure = strpos($workflow, $case['fail']);
    $slicePass = strpos($workflow, $case['pass']);

    if (false === $conditional || false === $versionFailure || false === $slicePass) {
        fwrite(STDERR, "FAIL: cannot resolve {$case['label']} retained version guard ordering\n");
        exit(1);
    }

    if (! ($conditional < $versionFailure && $versionFailure < $slicePass)) {
        fwrite(STDERR, "FAIL: {$case['label']} implementation-only version guard must be branch-scoped\n");
        exit(1);
    }
}

echo "PASS: X1/X2 retained workflows separate slice-only version ownership from retained regression\n";
