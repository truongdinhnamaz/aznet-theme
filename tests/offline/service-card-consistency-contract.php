<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$required = [
    'template-parts/services/card.php',
    'assets/css/components/service-card.css',
    'template-parts/services/page.php',
    'template-parts/content/page.php',
    'inc/theme/assets.php',
    'assets/css/components/services-page.css',
    'assets/css/components/service-page.css',
];

foreach ($required as $relative) {
    if (! is_file($root . '/' . $relative)) {
        $fail('missing shared service-card artifact: ' . $relative);
    }
}

$card = (string) file_get_contents($root . '/template-parts/services/card.php');
$services = (string) file_get_contents($root . '/template-parts/services/page.php');
$page = (string) file_get_contents($root . '/template-parts/content/page.php');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$cardCss = (string) file_get_contents($root . '/assets/css/components/service-card.css');
$servicesCss = (string) file_get_contents($root . '/assets/css/components/services-page.css');
$serviceCss = (string) file_get_contents($root . '/assets/css/components/service-page.css');

foreach ([
    'aznet-theme-service-card',
    'aznet-theme-service-card__top',
    'aznet-theme-service-card__index',
    'aznet-theme-service-card__icon',
    'aznet-theme-service-card__title',
    'aznet-theme-service-card__excerpt',
    'aznet-theme-service-card__link',
    "esc_html_e( 'Xem dịch vụ', 'aznet-theme' )",
    "get_permalink( \$service_page )",
    "get_the_title( \$service_page )",
    'post_excerpt',
] as $needle) {
    if (! str_contains($card, $needle)) {
        $fail('shared service-card template missing marker: ' . $needle);
    }
}

foreach ([
    "get_template_part(",
    "'template-parts/services/card'",
    "'service_page' => \$service_page",
    "'index'        => \$index + 1",
    'aznet-theme-service-card-grid',
] as $needle) {
    if (! str_contains($services, $needle)) {
        $fail('Services root must render the shared service-card partial: ' . $needle);
    }
}

foreach ([
    "get_template_part(",
    "'template-parts/services/card'",
    "'service_page' => \$service_page",
    "'index'        => \$service_positions[ (int) \$service_page->ID ]",
    'services_page_children()',
    'aznet-theme-service-card-grid',
] as $needle) {
    if (! str_contains($page, $needle)) {
        $fail('service detail siblings must render the shared service-card partial with canonical index: ' . $needle);
    }
}

foreach ([
    'function enqueue_service_card_asset',
    "'aznet-theme-service-card'",
    '/assets/css/components/service-card.css',
    "enqueue_service_card_asset( \$version );",
] as $needle) {
    if (! str_contains($assets, $needle)) {
        $fail('shared service-card asset boundary missing marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-service-card {',
    'min-height: 19rem;',
    '.aznet-theme-service-card::before',
    'linear-gradient(90deg, var(--aznet-theme-service-card-burgundy), var(--aznet-theme-service-card-gold))',
    '.aznet-theme-service-card__top',
    '.aznet-theme-service-card__index',
    '.aznet-theme-service-card__icon',
    '.aznet-theme-service-card__title',
    '.aznet-theme-service-card__excerpt',
    '.aznet-theme-service-card__link',
    'margin-top: auto;',
    '.aznet-theme-service-card:hover',
    'transform: translateY(-3px);',
    '@media (max-width: 47.999rem)',
    '@media (prefers-reduced-motion: reduce)',
] as $needle) {
    if (! str_contains($cardCss, $needle)) {
        $fail('shared service-card CSS missing marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-services-page__card {',
    '.aznet-theme-services-page__card-top',
    '.aznet-theme-services-page__card-index',
    '.aznet-theme-services-page__card-icon',
    '.aznet-theme-services-page__card-title',
    '.aznet-theme-services-page__card-excerpt',
    '.aznet-theme-services-page__card-link',
] as $forbidden) {
    if (str_contains($servicesCss, $forbidden)) {
        $fail('Services root must not retain a parallel card implementation: ' . $forbidden);
    }
}

foreach ([
    '.aznet-theme-page__service-card {',
    '.aznet-theme-page__service-card h3',
    '.aznet-theme-page--service-detail-law01 .aznet-theme-page__service-card',
] as $forbidden) {
    if (str_contains($serviceCss, $forbidden)) {
        $fail('service detail must not retain a parallel card implementation: ' . $forbidden);
    }
}

$production = $card . "\n" . $services . "\n" . $page;
foreach ([
    'luat-doanh-nghiep',
    'Luật Doanh nghiệp',
    'Dân sự & Tranh chấp',
    'Hình sự',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('shared service-card presentation must not hardcode service identity: ' . $forbidden);
    }
}

echo "PASS: service root and service detail share one canonical service-card presentation\n";
