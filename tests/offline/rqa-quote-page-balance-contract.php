<?php
declare(strict_types=1);

$css = file_get_contents(dirname(__DIR__, 2) . '/assets/css/components/page.css');
if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read page.css\n");
    exit(1);
}

$required = [
    '.page-id-726 .aznet-theme-page__content-inner',
    '.page-id-726 .wp-block-columns > .wp-block-column',
    'display: flex;',
    'height: 100%;',
    '.page-id-726 .wp-block-image img',
    'aspect-ratio: 4 / 3;',
    'object-fit: cover;',
    '.page-id-726 .wp-block-button__link',
    'background: var(--aznet-theme-accent);',
    'color: var(--aznet-theme-color-on-primary);',
    '.page-id-726 .wp-block-group.has-background',
    'border-radius: var(--aznet-theme-radius-card);',
];

foreach ($required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote page balance contract missing {$needle}\n");
        exit(2);
    }
}

echo "PASS: RQA quote page balance contract\n";
