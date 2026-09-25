<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');

foreach ([
    "homepage_source_value( 'law-01', 'process' )",
    "setting( 'homepage_process_page', 0 )",
    "homepage_source_value( 'law-01', 'faq' )",
    "setting( 'homepage_faq_page', 0 )",
    "'value' => __( 'Quy trình', 'aznet-theme' )",
    "'value' => __( 'Hỏi đáp', 'aznet-theme' )",
] as $needle) {
    if (! str_contains($profile, $needle)) {
        fwrite(STDERR, "FAIL: Profile fact compatibility path missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Profile keeps legacy surface and resolves Process/FAQ facts from scoped-or-legacy mappings.\n";
