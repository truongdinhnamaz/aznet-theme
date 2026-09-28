<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/services/page.php');
$css = (string) file_get_contents($root . '/assets/css/components/services-page.css');

foreach ([
    'homepage_renderable_child_pages( (int) $service_page->ID, 8 )',
    'aznet-theme-services-page__group-grid',
    'aznet-theme-services-page__subservices',
    'aznet-theme-services-page__subservice-link',
] as $needle) {
    if (! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: nested Services root presentation missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-services-page__group-grid',
    'grid-template-columns: repeat(2, minmax(0, 1fr));',
    '.aznet-theme-services-page__subservices',
    '.aznet-theme-services-page__subservice-link',
    '@media (max-width: 47.999rem)',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: nested Services root CSS missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Services root renders four primary groups in a two-column grid with native child-service links\n";
