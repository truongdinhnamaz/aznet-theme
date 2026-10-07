<?php
declare(strict_types=1);

$css = file_get_contents(dirname(__DIR__, 2) . '/assets/css/components/page.css');
if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read page.css\n");
    exit(1);
}

$required = [
    '.page-id-29 .woocommerce-loop-category__title .count',
    'display: none;',
    '.page-id-29 .wp-block-button__link',
    'background: var(--aznet-theme-accent);',
    'color: var(--aznet-theme-color-on-primary);',
];

foreach ($required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA collection accessibility presentation missing {$needle}\n");
        exit(2);
    }
}

echo "PASS: RQA collection accessibility presentation contract\n";
