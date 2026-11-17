<?php
/** Curtain 01 reference polish: calm Hero hover + editorial Project showcase. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = file_get_contents($root . '/template-parts/homepage/curtain-01/projects.php');
$css = file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$js = file_get_contents($root . '/assets/js/homepage-curtain-01.js');

if (! is_string($template) || ! is_string($css) || ! is_string($js)) {
    fwrite(STDERR, "FAIL: Curtain 01 reference presentation sources missing.\n");
    exit(1);
}

foreach ([
    'aznet-theme-curtain01-project-card__link',
    "esc_html_e( 'Công trình tiêu biểu', 'aznet-theme' )",
] as $needle) {
    if (! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: editorial Project showcase contract missing: {$needle}\n");
        exit(1);
    }
}

if (str_contains($template, "esc_html_e( 'Xem công trình', 'aznet-theme' )")) {
    fwrite(STDERR, "FAIL: per-card CTA must be removed; the card itself is the project link.\n");
    exit(1);
}

foreach ([
    '.aznet-theme-curtain01-project-card--lead {',
    '.aznet-theme-curtain01-project-card__link {',
    '.aznet-theme-curtain01-project-card__media::after {',
    '.aznet-theme-curtain01-project-card__body {',
    '.aznet-theme-curtain01-project-card:hover .aznet-theme-curtain01-project-card__image {',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: editorial Project presentation hook missing: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "hero.addEventListener('pointermove'",
    "hero.addEventListener('pointerleave'",
] as $forbidden) {
    if (str_contains($js, $forbidden)) {
        fwrite(STDERR, "FAIL: Hero image must not move in response to pointer hover: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 reference Project/Hero presentation contract\n";
