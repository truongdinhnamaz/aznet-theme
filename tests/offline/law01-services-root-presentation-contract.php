<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$required = [
    'template-parts/services/page.php',
    'assets/css/components/services-page.css',
];
foreach ($required as $relative) {
    if (! is_file($root . '/' . $relative)) {
        $fail('missing mapped Services Page artifact: ' . $relative);
    }
}

$pageExperience = (string) file_get_contents($root . '/inc/theme/page-experience.php');
$pageTemplate = (string) file_get_contents($root . '/template-parts/content/page.php');
$servicesTemplate = (string) file_get_contents($root . '/template-parts/services/page.php');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$css = (string) file_get_contents($root . '/assets/css/components/services-page.css');

foreach ([
    'function services_page_is_mapped',
    "setting( 'homepage_services_page', 0 )",
    'function services_page_presentation_active',
    'function services_page_children',
    "'post_parent'    => $services_id",
    "'orderby'        => 'menu_order title'",
] as $needle) {
    if (! str_contains($pageExperience, $needle)) {
        $fail('page-experience missing mapped Services Page marker: ' . $needle);
    }
}

foreach ([
    'services_page_presentation_active',
    "get_template_part(\n            'template-parts/services/page'",
    'aznet-theme-page--services-root',
] as $needle) {
    if (! str_contains($pageTemplate, $needle)) {
        $fail('shared Page template missing Services root routing marker: ' . $needle);
    }
}

foreach ([
    'services_page_children()',
    'service_page_contact_url()',
    'the_title()',
    "$authored_content = trim( (string) get_the_content() );",
    "if ( '' !== $authored_content )",
    'the_content()',
    'get_permalink( $service_page )',
    'get_the_title( $service_page )',
    'post_excerpt',
    'aznet-theme-services-page__hero',
    'aznet-theme-services-page__grid',
    'aznet-theme-services-page__card',
    'aznet-theme-services-page__consultation',
] as $needle) {
    if (! str_contains($servicesTemplate, $needle)) {
        $fail('Services Page template missing marker: ' . $needle);
    }
}

foreach ([
    'should_enqueue_services_page_assets',
    'enqueue_services_page_assets',
    '/assets/css/components/services-page.css',
    "'aznet-theme-services-page'",
] as $needle) {
    if (! str_contains($assets, $needle)) {
        $fail('asset boundary missing Services Page marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page--services-root',
    '.aznet-theme-services-page__hero',
    '.aznet-theme-services-page__grid',
    '.aznet-theme-services-page__card',
    '.aznet-theme-services-page__consultation',
    '--law01-services-burgundy',
    '@media (max-width:',
] as $needle) {
    if (! str_contains($css, $needle)) {
        $fail('Services Page CSS missing marker: ' . $needle);
    }
}

$production = $pageExperience . "\n" . $pageTemplate . "\n" . $servicesTemplate;
foreach ([
    "is_page( 'dich-vu' )",
    'REQUEST_URI',
    'parse_url(',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'Luật Doanh nghiệp',
    'Dân sự & Tranh chấp',
    'Hình sự',
    'Đất đai & Bất động sản',
    'Hôn nhân & Gia đình',
    'Lao động',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('Services Page must not infer route or clone domain content: ' . $forbidden);
    }
}

echo "PASS: mapped Law 01 Services Page presentation contract\n";
