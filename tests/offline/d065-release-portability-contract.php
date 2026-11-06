<?php
/** D-065: retain cross-site Theme release portability across all v1 candidates. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL D-065 release portability: {$reason}\n");
    exit(1);
};
$read = static function (string $path) use ($root, $fail): string {
    $absolute = $root . '/' . $path;
    if (! is_file($absolute)) {
        $fail("required source or test missing: {$path}");
    }
    return (string) file_get_contents($absolute);
};

$policy = $read('docs/source/AZT-04-roadmap-qa-decisions.md');
foreach ([
    'D-065 — Universal Theme Release Portability Gate',
    'Core vs. template vs. site-local',
    'C7 three-pilot',
    'C8 synthetic fourth-template',
    'WordPress-clean',
    'Pilot deployment is not a Core release gate',
] as $required) {
    if (! str_contains($policy, $required)) {
        $fail("accepted policy invariant missing: {$required}");
    }
}

$verification = $read('scripts/verify-v1-core.sh');
foreach ([
    'tests/offline/d065-release-portability-contract.php',
    'tests/offline/template-three-pilot-regression-contract.php',
    'tests/offline/template-fourth-template-proof-contract.php',
    'tests/offline/footer-core-universal-contract.php',
] as $required) {
    $read($required);
    if (! str_contains($verification, $required)) {
        $fail("mandatory reusable Core verification dropped: {$required}");
    }
}

foreach ([
    '.github/workflows/v1-core-ci.yml',
    '.github/workflows/v1-main-verification.yml',
    '.github/workflows/x6-cross-surface-release.yml',
] as $workflow) {
    if (! str_contains($read($workflow), 'bash scripts/verify-v1-core.sh')) {
        $fail("PR/exact-main/release Core verification path dropped: {$workflow}");
    }
}

foreach ([
    '.github/workflows/v1-core-ci.yml',
    '.github/workflows/x6-cross-surface-release.yml',
] as $workflow) {
    $source = $read($workflow);
    if (! str_contains($source, 'wp core download --version=6.9')
        || ! str_contains($source, 'wp theme activate aznet-theme')) {
        $fail("WordPress-clean support-floor smoke missing: {$workflow}");
    }
}

echo "PASS: D-065 release portability policy/three-pilot/fourth-template/WordPress-clean gates retained\n";
