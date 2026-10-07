<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssFile = $root . '/assets/css/integrations/convertflow.css';

if (!is_file($cssFile)) {
    fwrite(STDERR, "missing ConvertFlow integration stylesheet\n");
    exit(1);
}

$css = (string) file_get_contents($cssFile);

$required = [
    '[data-choiceguide-sticky-submenu]',
    'position: sticky',
    'z-index: 998',
    'overflow-x: auto',
    'grid-template-columns: repeat(6, minmax(0, 1fr)) max-content',
    'grid-auto-flow: column',
    'white-space: nowrap',
    'font-size: clamp('
    'body:has(.aznet-theme-site-header--sticky)',
    'body:has(.aznet-theme-site-header--sticky-compact)',
    'is-aznet-theme-header-compact',
    '.admin-bar',
    '32px',
    '46px',
    '[data-choiceguide-section-id]',
    'scroll-margin-top:',
];

foreach ($required as $needle) {
    if (false === strpos($css, $needle)) {
        fwrite(STDERR, "missing sticky submenu presentation contract: {$needle}\n");
        exit(2);
    }
}

foreach ([
    '.choiceguide-',
    '.choiceguide_',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'grid-template-columns: repeat(3, minmax(0, 1fr))',
    'grid-template-columns: repeat(2, minmax(0, 1fr))',
    'white-space: normal',
] as $forbidden) {
    if (false !== strpos($css, $forbidden)) {
        fwrite(STDERR, "sticky submenu integration crossed provider/private boundary: {$forbidden}\n");
        exit(3);
    }
}

echo "PASS: ConvertFlow sticky submenu public-hook presentation contract\n";
