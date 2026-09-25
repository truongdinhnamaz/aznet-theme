<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$surfaces = [
    'process' => [
        'path' => $root . '/template-parts/homepage/law-01/process.php',
        'scoped' => "homepage_source_value( 'law-01', 'process' )",
        'legacy' => "setting( 'homepage_process_page', 0 )",
    ],
    'faq' => [
        'path' => $root . '/template-parts/homepage/law-01/faq.php',
        'scoped' => "homepage_source_value( 'law-01', 'faq' )",
        'legacy' => "setting( 'homepage_faq_page', 0 )",
    ],
];

foreach ($surfaces as $name => $contract) {
    $source = (string) file_get_contents($contract['path']);
    $scopedPosition = strpos($source, $contract['scoped']);
    $legacyPosition = strpos($source, $contract['legacy']);

    if (false === $scopedPosition) {
        fwrite(STDERR, "FAIL: Law 01 {$name} section does not read the preset-scoped source first.\n");
        exit(1);
    }

    if (false === $legacyPosition) {
        fwrite(STDERR, "FAIL: Law 01 {$name} section lost the proven legacy fallback.\n");
        exit(1);
    }

    if ($scopedPosition > $legacyPosition) {
        fwrite(STDERR, "FAIL: Law 01 {$name} section resolves legacy mapping before preset-scoped source.\n");
        exit(1);
    }
}

echo "PASS: Law 01 Process/FAQ sections use scoped-first source resolution with legacy fallback.\n";
