<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');
$pattern = (string) file_get_contents($root . '/patterns/homepage-hero-content.php');

foreach ([
    '.aznet-theme-homepage-hero-content > .aznet-theme-homepage-hero-content__layout',
    '.aznet-theme-homepage-hero-content > .aznet-theme-homepage-hero-content__trust',
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    '.aznet-theme-homepage-hero-content__actions .wp-block-button:not(.is-style-outline) .wp-block-button__link',
    'background: var(--law01-navy) !important;',
    'height: clamp(24rem, 32vw, 34rem);',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: Hero frontend presentation missing {$needle}\n");
        exit(1);
    }
}

if (str_contains($pattern, '"layout":{"type":"constrained"}')) {
    fwrite(STDERR, "FAIL: Hero scaffold still serializes Gutenberg constrained layout.\n");
    exit(1);
}

if (! str_contains($pattern, '"layout":{"type":"default"}')) {
    fwrite(STDERR, "FAIL: Hero scaffold does not explicitly opt out of constrained layout.\n");
    exit(1);
}

echo "PASS: Hero frontend uses full Law 01 shell, bounded media height and scoped CTA presentation.\n";
