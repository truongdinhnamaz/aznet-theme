<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$page = (string) file_get_contents($root . '/inc/theme/page-experience.php');
$archive = (string) file_get_contents($root . '/inc/theme/archive-presentation.php');
foreach ([
    "homepage_source_value( 'law-01', 'services' )",
    "homepage_source_value( 'law-01', 'contact' )",
] as $needle) {
    assert(str_contains($page, $needle), "Page experience missing Law 01 scoped source: {$needle}");
}
assert(str_contains($archive, "homepage_source_value( 'law-01', 'contact' )"));
foreach ([
    "setting( 'homepage_services_page', 0 )",
    "setting( 'homepage_contact_page', 0 )",
] as $forbidden) {
    assert(! str_contains($page . $archive, $forbidden), "Non-front consumer still reads generic mapping: {$forbidden}");
}
echo "PASS: D-038 non-front Law 01 consumers use scoped resolver\n";
