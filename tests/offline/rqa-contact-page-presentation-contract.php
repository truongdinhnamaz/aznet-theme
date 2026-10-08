<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = file_get_contents($root . '/assets/css/presets/curtain-01.css');
$page_css = file_get_contents($root . '/assets/css/components/page.css');

if (false === $css || false === $page_css) {
    fwrite(STDERR, "FAIL: unable to read Curtain 01 or generic Page CSS\n");
    exit(1);
}

if (str_contains($css, '.page-id-')) {
    fwrite(STDERR, "FAIL: Curtain 01 contact presentation must not depend on WordPress Page IDs\n");
    exit(2);
}


$required = [
    'body.aznet-theme-preset--curtain-01 .rqa-contact-hero',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-quick',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-main',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-form',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-trust',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-cta',
    'grid-template-columns:',
    'background: var(--aznet-theme-surface);',
    'border: 1px solid var(--aznet-theme-border);',
    'box-shadow: var(--aznet-theme-shadow-card);',
    'color: var(--aznet-theme-on-inverse) !important;',
];

foreach ($required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA contact page presentation missing {$needle}\n");
        exit(3);
    }
}

$shared_full_width_required = [
    '.aznet-theme-page--landing .aznet-theme-page__content-inner',
    'width: 100%;',
    'max-width: none;',
    '.aznet-theme-page--landing .aznet-theme-page__content-inner > .wp-block-group',
    '.aznet-theme-page--landing .aznet-theme-page__content-inner > .wp-block-cover',
    'margin-inline: 0;',
    'padding-inline: max(',
    'calc((100vw - var(--aznet-theme-container-wide)) / 2)',
    'padding-inline: var(--aznet-theme-gutter-mobile);',
];

foreach ($shared_full_width_required as $needle) {
    if (! str_contains($page_css, $needle)) {
        fwrite(STDERR, "FAIL: shared Page full-width/constrained-inner contract missing {$needle}\n");
        exit(4);
    }
}

echo "PASS: RQA contact page presentation is Curtain 01 scoped and inherits shared Page width rules\n";

$polish_required = [
    'body.aznet-theme-preset--curtain-01 .rqa-contact-quick .wp-block-column::before',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-quick .wp-block-column:nth-child(1)::before',
    'margin-block-start: auto;',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-main > .wp-block-columns',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-form .wpcf7-form-control-wrap',
    'display: block;',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-form input[type="submit"]',
    'width: 100%;',
    'body.aznet-theme-preset--curtain-01 .rqa-contact-trust .wp-block-column',
    'text-align: center;',
];

foreach ($polish_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: RQA contact visual polish missing {$needle}\n");
        exit(5);
    }
}

echo "PASS: RQA contact visual polish contract\n";
