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


$curtain_css = file_get_contents(dirname(__DIR__, 2) . '/assets/css/presets/curtain-01.css');
if (false === $curtain_css) {
    fwrite(STDERR, "FAIL: unable to read curtain-01.css\n");
    exit(3);
}

$title_rule = "body.aznet-theme-preset--curtain-01 .aznet-theme-page__title {\n    max-width: none;\n}";
if (! str_contains($curtain_css, $title_rule)) {
    fwrite(STDERR, "FAIL: Curtain 01 page title must use available row width before wrapping\n");
    exit(4);
}

echo "PASS: Curtain 01 page title uses available row width\n";


$contrast_required = [
    '.page-id-726 .rqa-quote-dark',
    'color: var(--aznet-theme-on-inverse) !important;',
    '.page-id-726 .rqa-quote-dark figcaption',
    '.page-id-726 .rqa-quote-dark .wp-block-heading',
    '.page-id-726 .rqa-quote-dark .wp-block-paragraph',
];

foreach ($contrast_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote page dark-section contrast missing {$needle}\n");
        exit(5);
    }
}

echo "PASS: RQA quote page dark-section contrast contract\n";


$full_contrast_required = [
    '.page-id-726 .aznet-theme-page__content-inner',
    'color: var(--aznet-theme-text) !important;',
    '.page-id-726 .wp-block-cover .wp-block-heading',
    '.page-id-726 .wp-block-cover .wp-block-paragraph',
    '.page-id-726 .rqa-quote-cta-dark',
    '.page-id-726 .rqa-quote-cta-dark .wp-block-heading',
    '.page-id-726 .rqa-quote-cta-dark .wp-block-paragraph',
];

foreach ($full_contrast_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: full-page quote contrast missing {$needle}\n");
        exit(6);
    }
}

echo "PASS: RQA quote page full-section contrast contract\n";


$full_bleed_required = [
    '.page-id-726 .aznet-theme-page__content-inner > .wp-block-group',
    '.page-id-726 .aznet-theme-page__content-inner > .wp-block-cover',
    'width: 100vw;',
    'margin-inline: calc(50% - 50vw);',
    'padding-inline: max(',
    'calc((100vw - var(--aznet-theme-container-wide)) / 2)',
];

foreach ($full_bleed_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote page full-bleed section contract missing {$needle}\n");
        exit(7);
    }
}

echo "PASS: RQA quote page sections are full-bleed with constrained content\n";
