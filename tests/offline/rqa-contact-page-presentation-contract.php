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


$polish_required = [
    '.page-id-45 .rqa-contact-quick .wp-block-column::before',
    '.page-id-45 .rqa-contact-quick .wp-block-column:nth-child(1)::before',
    'margin-block-start: auto;',
    '.page-id-45 .rqa-contact-main > .wp-block-columns',
    '.page-id-45 .rqa-contact-form .wpcf7-form-control-wrap',
    'display: block;',
    '.page-id-45 .rqa-contact-form input[type="submit"]',
    'width: 100%;',
    '.page-id-45 .rqa-contact-trust .wp-block-column',
    'text-align: center;',
];

foreach ($polish_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA contact visual polish missing {$needle}\n");
        exit(3);
    }
}

echo "PASS: RQA contact visual polish contract\n";
