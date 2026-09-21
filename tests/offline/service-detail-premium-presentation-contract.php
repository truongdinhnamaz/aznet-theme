<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$cssPath = $root . '/assets/css/components/service-page.css';
$tokensPath = $root . '/assets/css/tokens.css';

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

if (!is_file($cssPath) || !is_file($tokensPath)) {
    $fail('missing service detail stylesheet or Theme token source');
}

$css = (string) file_get_contents($cssPath);
$tokens = (string) file_get_contents($tokensPath);

$templatePath = $root . '/template-parts/content/page.php';
if (!is_file($templatePath)) {
    $fail('missing shared Page presentation template');
}
$template = (string) file_get_contents($templatePath);

foreach ([
    '$is_law01_service_detail',
    'header_law01_active()',
    'aznet-theme-page--service-detail-law01',
] as $needle) {
    if (!str_contains($template, $needle)) {
        $fail('Law 01 service-detail presentation scope missing from Page template: ' . $needle);
    }
}

foreach ([
    '--aznet-theme-law01-burgundy: #7a0e18;',
    '--aznet-theme-law01-burgundy-deep: #560912;',
    '--aznet-theme-law01-gold: #b9892f;',
    '--aznet-theme-law01-gold-soft: #d8b66f;',
    '--aznet-theme-law01-paper: #fffaf2;',
    '--aznet-theme-law01-ink: #211a17;',
    '--aznet-theme-law01-muted: #6a5b52;',
] as $needle) {
    if (!str_contains($tokens, $needle)) {
        $fail('Law 01 semantic token missing from tokens.css: ' . $needle);
    }
}

foreach ([
    '--aznet-theme-service-burgundy: var(--aznet-theme-law01-burgundy);',
    '--aznet-theme-service-burgundy-deep: var(--aznet-theme-law01-burgundy-deep);',
    '--aznet-theme-service-gold: var(--aznet-theme-law01-gold);',
    '--aznet-theme-service-gold-soft: var(--aznet-theme-law01-gold-soft);',
    '--aznet-theme-service-paper: var(--aznet-theme-law01-paper);',
    '--aznet-theme-service-ink: var(--aznet-theme-law01-ink);',
    '--aznet-theme-service-muted: var(--aznet-theme-law01-muted);',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__header::before',
    'radial-gradient(',
    'linear-gradient(125deg, var(--aznet-theme-service-burgundy-deep), var(--aznet-theme-service-burgundy))',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__breadcrumbs',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__title',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__lead',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-primary',
    'border-color: var(--aznet-theme-service-gold);',
    'background: var(--aznet-theme-service-gold);',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__content-section',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__content > h2::before',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__content :where(ul, ol)',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__content ul > li::marker',
    'color: var(--aznet-theme-service-burgundy);',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__content blockquote',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-siblings {',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-eyebrow {',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-card::before {',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-card:hover {',
    'transform: translateY(-3px);',
    '@media (max-width: 47.999rem)',
    '@media (prefers-reduced-motion: reduce)',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('service detail premium presentation missing marker: ' . $needle);
    }
}

if (preg_match('/#(?:[0-9a-fA-F]{3}){1,2}\b/', $css)) {
    $fail('service detail stylesheet must consume semantic Theme tokens instead of hard-coded colors');
}

if (str_contains($css, '.aznet-theme-page--service-detail-law01 .aznet-theme-page--service-detail-law01')) {
    $fail('Law 01 service-detail selectors must not contain a duplicated scope modifier');
}

if (str_contains($css, 'url(/dich-vu/') || str_contains($css, 'luat-doanh-nghiep')) {
    $fail('service detail presentation must not hardcode service slugs or URLs');
}

echo "PASS: service detail premium presentation contract\n";
