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

foreach (['.choiceguide-', '.choiceguide_', 'get_option(', 'get_post_meta(', '$wpdb'] as $forbidden) {
    if (false !== strpos($css, $forbidden)) {
        fwrite(STDERR, "sticky submenu integration crossed provider/private boundary: {$forbidden}\n");
        exit(3);
    }
}

echo "PASS: ConvertFlow sticky submenu public-hook presentation contract\n";
