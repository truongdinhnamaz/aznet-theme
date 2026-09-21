<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$files = [
    'page.php',
    'template-parts/content/page.php',
    'assets/css/components/page.css',
    'assets/css/components/services-page.css',
    'assets/css/components/contact-page.css',
    'assets/css/components/service-page.css',
];

foreach ($files as $relative) {
    if (!is_file($root . '/' . $relative)) {
        $fail('missing Page full-bleed artifact: ' . $relative);
    }
}

$page = (string) file_get_contents($root . '/page.php');
$template = (string) file_get_contents($root . '/template-parts/content/page.php');
$pageCss = (string) file_get_contents($root . '/assets/css/components/page.css');
$servicesCss = (string) file_get_contents($root . '/assets/css/components/services-page.css');
$contactCss = (string) file_get_contents($root . '/assets/css/components/contact-page.css');
$serviceDetailCss = (string) file_get_contents($root . '/assets/css/components/service-page.css');

foreach ([
    'aznet-theme-main--page',
] as $needle) {
    if (!str_contains($page, $needle)) {
        $fail('native Page main shell missing full-bleed marker: ' . $needle);
    }
}

foreach ([
    'aznet-theme-page--full-bleed',
] as $needle) {
    if (!str_contains($template, $needle)) {
        $fail('shared Page presentation missing full-bleed class marker: ' . $needle);
    }
}

if (!preg_match('/\.aznet-theme-main--page\s*\{([^}]*)\}/s', $pageCss, $mainMatch)) {
    $fail('generic Page CSS missing .aznet-theme-main--page rule');
}

$mainRule = $mainMatch[1];
foreach ([
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-block: 0;',
    'overflow-x: clip;',
] as $needle) {
    if (!str_contains($mainRule, $needle)) {
        $fail('native Page main shell still inherits constrained global layout: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page {',
    'width: 100%;',
    'max-width: none;',
    'margin-inline: 0;',
    'padding-block: 0;',
    '.aznet-theme-page > :first-child',
    'margin-block-start: 0;',
    '.aznet-theme-page > :last-child',
    'margin-block-end: 0;',
] as $needle) {
    if (!str_contains($pageCss, $needle)) {
        $fail('generic Page CSS missing full-bleed marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page--services-root {',
    'width: 100%;',
    'padding-block: 0;',
    '.aznet-theme-services-page {',
    'gap: 0;',
    '.aznet-theme-services-page__hero {',
    'border-top-left-radius: 0;',
    'border-top-right-radius: 0;',
    'border-top: 0;',
    '.aznet-theme-services-page__consultation {',
    'border-bottom-left-radius: 0;',
    'border-bottom-right-radius: 0;',
] as $needle) {
    if (!str_contains($servicesCss, $needle)) {
        $fail('Services Page CSS missing full-section edge marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page--contact {',
    'width: 100%;',
    'padding-block: 0;',
    '.aznet-theme-contact-page {',
    'gap: 0;',
    '.aznet-theme-contact-page__hero {',
    'border-top-left-radius: 0;',
    'border-top-right-radius: 0;',
    '.aznet-theme-contact-page > :last-child',
    'border-bottom-left-radius: 0;',
    'border-bottom-right-radius: 0;',
    'border-bottom: 0;',
] as $needle) {
    if (!str_contains($contactCss, $needle)) {
        $fail('Contact Page CSS missing full-section edge marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-page--service-detail .aznet-theme-page__header {',
    'width: 100%;',
    'max-width: none;',
    'margin-block-end: 0;',
    'border-top: 0;',
    'border-inline: 0;',
    'border-top-left-radius: 0;',
    'border-top-right-radius: 0;',
    '.aznet-theme-page__service-siblings {',
    'max-width: none;',
    'margin: 0;',
    'padding-block:',
] as $needle) {
    if (!str_contains($serviceDetailCss, $needle)) {
        $fail('Service detail Page CSS missing full-section edge marker: ' . $needle);
    }
}

echo "PASS: native Pages use a viewport-width main shell with full-bleed outer sections and flush first/last edges\n";
