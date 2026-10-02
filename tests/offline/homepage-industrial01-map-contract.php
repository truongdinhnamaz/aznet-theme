<?php
/** D-043 C3 Industrial 01 shared Homepage surface-map contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$surface = (string) file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$composer = (string) file_get_contents($root . '/inc/theme/homepage-composer.php');

foreach ([
    'function homepage_industrial01_effective_surface_map',
    "'hero'",
    "'categories'",
    "'products'",
    "'front-page-content'",
    "'cta'",
] as $needle) {
    if (! str_contains($surface, $needle)) {
        fwrite(STDERR, "FAIL: Industrial 01 shared surface map missing: {$needle}.\n");
        exit(1);
    }
}

if (! str_contains($surface, "'industrial-01' === $preset")) {
    fwrite(STDERR, "FAIL: shared Homepage surface resolver does not route Industrial 01.\n");
    exit(1);
}

if (! str_contains($composer, "homepage_effective_surface_map( 'industrial-01' )")) {
    fwrite(STDERR, "FAIL: Industrial 01 frontend composition does not consume shared surface map.\n");
    exit(1);
}

if (str_contains($composer, "foreach ( [ 'hero', 'categories', 'products' ] as $slug )")) {
    fwrite(STDERR, "FAIL: Industrial 01 before-content order is still duplicated in Homepage Composer.\n");
    exit(1);
}

if (str_contains($composer, "render_industrial01_part( 'cta' )")) {
    fwrite(STDERR, "FAIL: Industrial 01 after-content CTA is still hard-coded outside shared surface map.\n");
    exit(1);
}

echo "PASS: D-043 C3 Industrial 01 shared Homepage surface map\n";
