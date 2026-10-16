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
    fwrite(STDERR, "FAIL: Curtain 01 quote presentation must not depend on WordPress Page IDs\n");
    exit(2);
}

$scope = 'body.aznet-theme-preset--curtain-01 .aznet-theme-page--landing:has(.rqa-quote-factors)';

$required = [
    $scope,
    $scope . ' .wp-block-columns > .wp-block-column',
    'display: flex;',
    'height: 100%;',
    $scope . ' .wp-block-image img',
    'aspect-ratio: 4 / 3;',
    'object-fit: cover;',
    $scope . ' .wp-block-button__link',
    'background: var(--aznet-theme-accent);',
    'color: var(--aznet-theme-color-on-primary);',
    $scope . ' .wp-block-group.has-background',
    'border-radius: var(--aznet-theme-radius-card);',
];

foreach ($required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: scoped quote page balance contract missing {$needle}\n");
        exit(3);
    }
}

echo "PASS: RQA quote page balance is Curtain 01 scoped\n";

$title_rule = "body.aznet-theme-preset--curtain-01 .aznet-theme-page__title {\n    max-width: none;\n}";
if (! str_contains($css, $title_rule)) {
    fwrite(STDERR, "FAIL: Curtain 01 page title must use available row width before wrapping\n");
    exit(4);
}

echo "PASS: Curtain 01 page title uses available row width\n";

$contrast_required = [
    'body.aznet-theme-preset--curtain-01 .rqa-quote-dark',
    'color: var(--aznet-theme-on-inverse) !important;',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-dark figcaption',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-dark .wp-block-heading',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-dark .wp-block-paragraph',
];

foreach ($contrast_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote page dark-section contrast missing {$needle}\n");
        exit(5);
    }
}

echo "PASS: RQA quote page dark-section contrast contract\n";

$full_contrast_required = [
    $scope,
    'color: var(--aznet-theme-text) !important;',
    $scope . ' .wp-block-cover .wp-block-heading',
    $scope . ' .wp-block-cover .wp-block-paragraph',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-cta-dark',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-cta-dark .wp-block-heading',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-cta-dark .wp-block-paragraph',
];

foreach ($full_contrast_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: full-page quote contrast missing {$needle}\n");
        exit(6);
    }
}

echo "PASS: RQA quote page full-section contrast contract\n";

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
        exit(7);
    }
}

echo "PASS: RQA quote page inherits shared full-width section contract\n";

$factors_required = [
    'body.aznet-theme-preset--curtain-01 .rqa-quote-factors',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-factors .wp-block-column',
    'background: var(--aznet-theme-surface);',
    'border: 1px solid var(--aznet-theme-border);',
    'box-shadow: var(--aznet-theme-shadow-card);',
    'border-radius: var(--aznet-theme-radius-card);',
    'padding: clamp(',
    'min-height: 100%;',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-factors .wp-block-heading',
];

foreach ($factors_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote factors presentation missing {$needle}\n");
        exit(8);
    }
}

echo "PASS: RQA quote factors use balanced visual cards\n";

$process_required = [
    'body.aznet-theme-preset--curtain-01 .rqa-quote-process',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-process .wp-block-column',
    'background: var(--aznet-theme-surface);',
    'border: 1px solid var(--aznet-theme-border);',
    'box-shadow: var(--aznet-theme-shadow-card);',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-process .wp-block-heading',
    'border-radius: 999px;',
    'background: var(--aznet-theme-accent);',
    'color: var(--aznet-theme-color-on-primary) !important;',
];

foreach ($process_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote process presentation missing {$needle}\n");
        exit(9);
    }
}

echo "PASS: RQA quote process uses balanced step cards\n";

$polish_required = [
    'body.aznet-theme-preset--curtain-01 .rqa-quote-trust .wp-block-column',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-trust .wp-block-column::before',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-proof .wp-block-column',
    'backdrop-filter: blur(',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-faq .wp-block-column',
    'body.aznet-theme-preset--curtain-01 .rqa-quote-faq .wp-block-heading:not(:first-child)',
    'border-block-start: 1px solid var(--aznet-theme-border);',
];

foreach ($polish_required as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: quote secondary-section polish missing {$needle}\n");
        exit(10);
    }
}

echo "PASS: RQA quote trust, proof and FAQ sections are visually polished\n";
