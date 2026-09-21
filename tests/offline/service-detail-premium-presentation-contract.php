<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/service-page.css';

if (!is_file($cssPath)) {
    fwrite(STDERR, "FAIL: missing service detail stylesheet\n");
    exit(1);
}

$css = (string) file_get_contents($cssPath);

$required = [
    '--aznet-theme-service-burgundy: #7a0e18;',
    '--aznet-theme-service-burgundy-deep: #560912;',
    '--aznet-theme-service-gold: #b9892f;',
    '--aznet-theme-service-paper: #fffaf2;',
    '--aznet-theme-service-ink: #211a17;',
    '.aznet-theme-page--service-detail .aznet-theme-page__header::before',
    'radial-gradient(',
    'linear-gradient(125deg, var(--aznet-theme-service-burgundy-deep), var(--aznet-theme-service-burgundy))',
    '.aznet-theme-page--service-detail .aznet-theme-page__breadcrumbs',
    'color: rgba(255, 255, 255, .78);',
    '.aznet-theme-page--service-detail .aznet-theme-page__title',
    'color: #fff;',
    '.aznet-theme-page--service-detail .aznet-theme-page__lead',
    'color: rgba(255, 255, 255, .82);',
    '.aznet-theme-page--service-detail .aznet-theme-page__service-primary',
    'border-color: var(--aznet-theme-service-gold);',
    'background: var(--aznet-theme-service-gold);',
    '.aznet-theme-page--service-detail .aznet-theme-page__content-section',
    'background: linear-gradient(180deg, #fffdf8 0%, var(--aznet-theme-service-paper) 100%);',
    '.aznet-theme-page--service-detail .aznet-theme-page__content > h2::before',
    'background: var(--aznet-theme-service-gold);',
    '.aznet-theme-page--service-detail .aznet-theme-page__content :where(ul, ol)',
    '.aznet-theme-page--service-detail .aznet-theme-page__content ul > li::marker',
    'color: var(--aznet-theme-service-burgundy);',
    '.aznet-theme-page--service-detail .aznet-theme-page__content blockquote',
    'border-inline-start: 3px solid var(--aznet-theme-service-gold);',
    '.aznet-theme-page__service-siblings',
    'background: #fffdf8;',
    '.aznet-theme-page__service-card::before',
    'background: var(--aznet-theme-service-gold);',
    '.aznet-theme-page__service-card:hover',
    'transform: translateY(-3px);',
    '@media (max-width: 47.999rem)',
    '@media (prefers-reduced-motion: reduce)',
];

foreach ($required as $needle) {
    if (!str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: service detail premium presentation missing marker: {$needle}\n");
        exit(1);
    }
}

if (str_contains($css, 'url(/dich-vu/') || str_contains($css, 'luat-doanh-nghiep')) {
    fwrite(STDERR, "FAIL: service detail presentation must not hardcode service slugs or URLs\n");
    exit(1);
}

echo "PASS: service detail premium presentation contract\n";
