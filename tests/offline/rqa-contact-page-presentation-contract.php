<?php
declare(strict_types=1);

$css = file_get_contents(dirname(__DIR__, 2) . '/assets/css/compatibility/curtain01-rqa-pages.css');
if (false === $css) {
    fwrite(STDERR, "FAIL: unable to read Curtain 01 RQA compatibility CSS\n");
    exit(1);
}

$required = [
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-hero',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-quick',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-main',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-form',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-trust',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-cta',
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
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-quick .wp-block-column::before',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-quick .wp-block-column:nth-child(1)::before',
    'margin-block-start: auto;',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-main > .wp-block-columns',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-form .wpcf7-form-control-wrap',
    'display: block;',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-form input[type="submit"]',
    'width: 100%;',
    'body.aznet-theme-preset--curtain-01:has(.rqa-contact-hero) .rqa-contact-trust .wp-block-column',
    'text-align: center;',
];

foreach ($polish_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA contact visual polish missing {$needle}\n");
        exit(3);
    }
}

echo "PASS: RQA contact visual polish contract\n";
