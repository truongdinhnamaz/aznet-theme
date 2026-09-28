<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/homepage/law-01/services.php');
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-reference.css');

foreach ([
    '$services_scrollable = count( $items ) > 6;',
    'aznet-theme-law01-grid--services-scroll',
    'tabindex="0"',
] as $needle) {
    if (! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: Services horizontal rail missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-law01-grid--services-scroll',
    'grid-auto-flow: column;',
    'grid-auto-columns: calc((100% - 3.75rem) / 6);',
    'overflow-x: auto;',
    'scroll-snap-type: x proximity;',
    'scroll-snap-align: start;',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: Services horizontal rail CSS missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: Law 01 Services becomes horizontally scrollable only when more than six cards render\n";
