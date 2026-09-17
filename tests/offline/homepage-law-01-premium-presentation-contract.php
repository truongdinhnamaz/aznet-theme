<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/homepage-law-01.css';
if (! is_file($cssPath)) {
    fwrite(STDERR, "FAIL: Law 01 stylesheet missing\n");
    exit(1);
}

$css = file_get_contents($cssPath);

$required = [
    '.aznet-theme-law01-hero__grid--text::after',
    'min-height: clamp(18rem',
    'grid-column: 2;',
    'grid-row: 1;',
    '.aznet-theme-law01-hero__grid--text .aznet-theme-law01-hero__content',
    'grid-column: 1;',
    '.aznet-theme-law01-editorial__grid > :only-child',
    'max-width: 48rem;',
    'counter-reset: law01-service;',
    'counter-increment: law01-service;',
    '.aznet-theme-law01-card::before',
    'counter(law01-service, decimal-leading-zero)',
    '.aznet-theme-law01-team { background: var(--law01-navy-deep); color: #fff; }',
    '.aznet-theme-law01-team .aznet-theme-law01-panel',
    'background: transparent;',
    '.aznet-theme-law01-topics .aznet-theme-law01-topic::after',
    "content: '→';",
    '.aznet-theme-law01-grid--articles .aznet-theme-law01-article-card:first-child',
    'grid-column: span 2;',
    '.aznet-theme-law01-article-card:first-child h3',
    'font-size: clamp(1.55rem',
    '.aznet-theme-law01-final-cta .aznet-theme-law01-actions',
    'margin-top: 2rem;',
];

foreach ($required as $needle) {
    assert(str_contains($css, $needle), "Premium Law 01 presentation contract missing: {$needle}");
}

foreach (['500+', '98%', '1.000+', '1000+', 'Nguyễn Văn A'] as $forbidden) {
    assert(! str_contains($css, $forbidden), "Fabricated trust content must not be introduced by presentation: {$forbidden}");
}

echo "PASS: Law 01 premium presentation contract\n";
