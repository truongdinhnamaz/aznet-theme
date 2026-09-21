<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$required = [
    'inc/theme/page-experience.php',
    'inc/theme/assets.php',
    'template-parts/content/page.php',
    'template-parts/contact/page.php',
    'assets/css/components/contact-page.css',
];

foreach ($required as $relative) {
    if (!is_file($root . '/' . $relative)) {
        $fail('missing Contact Page presentation artifact: ' . $relative);
    }
}

$pageExperience = (string) file_get_contents($root . '/inc/theme/page-experience.php');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$contentPage = (string) file_get_contents($root . '/template-parts/content/page.php');
$contactPage = (string) file_get_contents($root . '/template-parts/contact/page.php');
$css = (string) file_get_contents($root . '/assets/css/components/contact-page.css');

foreach ([
    'function contact_page_is_mapped',
    "setting( 'homepage_contact_page', 0 )",
] as $needle) {
    if (!str_contains($pageExperience, $needle)) {
        $fail('mapped Contact Page helper missing marker: ' . $needle);
    }
}

foreach ([
    'should_enqueue_contact_page_assets',
    'enqueue_contact_page_assets',
    '/assets/css/components/contact-page.css',
    "'aznet-theme-contact-page'",
] as $needle) {
    if (!str_contains($assets, $needle)) {
        $fail('Contact Page asset boundary missing marker: ' . $needle);
    }
}

foreach ([
    'contact_page_is_mapped',
    'aznet-theme-page--contact',
    "template-parts/contact/page",
] as $needle) {
    if (!str_contains($contentPage, $needle)) {
        $fail('native Page composer missing Contact Page presentation marker: ' . $needle);
    }
}

if (1 !== substr_count($contactPage, 'the_content();')) {
    $fail('Contact Page template must retain exactly one native the_content() boundary.');
}

foreach ([
    'aznet-theme-contact-page__hero',
    'aznet-theme-contact-page__content-card',
    'aznet-theme-contact-page__provider',
    'render_contact_surface',
] as $needle) {
    if (!str_contains($contactPage, $needle)) {
        $fail('Contact Page template missing presentation marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page--contact',
    '.aznet-theme-contact-page__hero',
    '.aznet-theme-contact-page__content-card',
    '--law01-contact-burgundy',
    '@media (max-width:',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Contact Page CSS missing presentation marker: ' . $needle);
    }
}

$production = $pageExperience . "\n" . $contentPage . "\n" . $contactPage;
foreach ([
    "is_page( 'lien-he' )",
    'is_page("lien-he")',
    "get_page_by_path( 'lien-he' )",
    '/lien-he/',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('Contact Page presentation must use explicit mapped Page reference, not route heuristics: ' . $forbidden);
    }
}

echo "PASS: mapped Law 01 Contact Page presentation contract\n";
