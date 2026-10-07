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
