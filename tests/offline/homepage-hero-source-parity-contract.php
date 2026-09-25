<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');
$admin = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');

foreach ([
    "homepage_source_value( 'law-01', 'hero', \$theme_settings )",
    "homepage_source_value( 'law-01', 'hero_variant', \$theme_settings )",
    "homepage_source_value( 'law-01', 'hero_page', \$theme_settings )",
    "homepage_source_value( 'law-01', 'contact', \$theme_settings )",
    "homepage_source_value( 'law-01', 'services', \$theme_settings )",
] as $needle) {
    if (! str_contains($hero, $needle)) {
        fwrite(STDERR, "FAIL: Law 01 frontend is not consuming the effective scoped source: {$needle}\n");
        exit(1);
    }
}

if (! str_contains($admin, "homepage_source_value( 'law-01', 'hero', settings() )")) {
    fwrite(STDERR, "FAIL: Hero save path is not bound to the effective Law 01 Hero source.\n");
    exit(1);
}

foreach ([
    "setting( 'homepage_hero_block'",
    "setting( 'homepage_hero_variant'",
    "setting( 'homepage_hero_page'",
    "setting( 'homepage_contact_page'",
    "setting( 'homepage_services_page'",
] as $forbidden) {
    if (str_contains($hero, $forbidden)) {
        fwrite(STDERR, "FAIL: Law 01 Hero frontend still reads legacy setting directly: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Hero save and Law 01 frontend share the same effective scoped source resolver.\n";
