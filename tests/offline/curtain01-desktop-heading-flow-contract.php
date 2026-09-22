<?php
/** Curtain 01 desktop heading-flow regression contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/homepage-curtain-01.css';
$css = is_file($cssPath) ? (string) file_get_contents($cssPath) : '';

if ('' === $css) {
    fwrite(STDERR, "FAIL: Curtain 01 stylesheet missing.\n");
    exit(1);
}

foreach ([
    '@media (min-width: 64rem) {',
    '.aznet-theme-curtain01-hero__content h1,',
    '.aznet-theme-curtain01-section-heading h2,',
    '.aznet-theme-curtain01-about__content h2,',
    '.aznet-theme-curtain01-final-cta h2 {',
    'max-width: none;',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: desktop heading flow still constrains usable row width: {$needle}\n");
        exit(1);
    }
}

if (str_contains($css, 'white-space: nowrap;')) {
    fwrite(STDERR, "FAIL: desktop heading flow must not force overflow with nowrap.\n");
    exit(1);
}

echo "PASS: Curtain 01 desktop headings use available row width before wrapping\n";
