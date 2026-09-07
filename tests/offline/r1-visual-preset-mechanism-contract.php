<?php
/**
 * R1 visual preset mechanism evidence contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$evidence_file = dirname(__DIR__, 2) . '/docs/evidence/R1_VISUAL_PRESET_FEASIBILITY.md';
$evidence = file_get_contents($evidence_file);

if (false === $evidence) {
    fwrite(STDERR, "FAIL: unable to read R1 visual preset feasibility evidence\n");
    exit(1);
}

if (! str_contains($evidence, 'WordPress floor under probe: `6.9`')) {
    fwrite(STDERR, "FAIL: WordPress 6.9 probe floor is not recorded\n");
    exit(1);
}

$allowed = [
    'Mechanism: `NATIVE_STYLE_VARIATIONS`',
    'Mechanism: `THEME_VISUAL_PRESET_MAPPING`',
];

$matches = array_filter(
    $allowed,
    static fn(string $needle): bool => str_contains($evidence, $needle)
);

if (1 !== count($matches)) {
    fwrite(STDERR, "FAIL: feasibility evidence must record exactly one final mechanism\n");
    exit(1);
}

if (str_contains($evidence, '**Status:** PENDING') || str_contains($evidence, 'Mechanism: `PENDING`')) {
    fwrite(STDERR, "FAIL: feasibility evidence is still pending\n");
    exit(1);
}

echo "PASS: R1 visual preset mechanism evidence contract\n";
