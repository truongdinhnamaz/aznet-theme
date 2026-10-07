<?php
declare(strict_types=1);

$css = file_get_contents(dirname(__DIR__, 2) . '/assets/css/components/page.css');
if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read page.css\n");
    exit(1);
}

$required = [
    '.page-id-45 .rqa-contact-hero',
    '.page-id-45 .rqa-contact-quick',
    '.page-id-45 .rqa-contact-main',
    '.page-id-45 .rqa-contact-form',
    '.page-id-45 .rqa-contact-trust',
    '.page-id-45 .rqa-contact-cta',
    'width: 100vw;',
    'margin-inline: calc(50% - 50vw);',
    'grid-template-columns:',
    'background: var(--aznet-theme-surface);',
    'border: 1px solid var(--aznet-theme-border);',
    'box-shadow: var(--aznet-theme-shadow-card);',
    'color: var(--aznet-theme-on-inverse) !important;',
];

foreach ($required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA contact page presentation missing {$needle}\n");
        exit(2);
    }
}

echo "PASS: RQA contact page presentation contract\n";
