<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/homepage-law-01-variants.css';
if (! is_file($cssPath)) {
    fwrite(STDERR, "FAIL: Law 01 variants stylesheet missing\n");
    exit(1);
}

$css = file_get_contents($cssPath);

$required = [
    '--law01-brand-red: #8f111b;',
    '--law01-brand-red-deep: #6d0b13;',
    '--law01-warm-paper: #fffaf2;',
    '.aznet-theme-site-header--law01-burgundy-gold {',
    'background: rgba(255, 253, 248, .98);',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-hero {',
    'linear-gradient(90deg, #fffaf2 0%, #fffaf2 54%, rgba(255, 250, 242, .18) 74%, rgba(255, 250, 242, 0) 100%)',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-grid--services {',
    'grid-template-columns: repeat(6, minmax(0, 1fr));',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-team {',
    'background: #fffaf2;',
    '.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-grid--team {',
    'grid-template-columns: repeat(4, minmax(0, 1fr));',
];

foreach ($required as $needle) {
    assert(str_contains($css, $needle), "Law 01 approved visual direction missing: {$needle}");
}

echo "PASS: Law 01 approved visual direction contract\n";
