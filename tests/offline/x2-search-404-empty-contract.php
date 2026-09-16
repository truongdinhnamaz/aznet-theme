<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function x2_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x2_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (!is_file($path)) {
        x2_fail('missing required file: ' . $relative);
    }
    $source = file_get_contents($path);
    if (false === $source) {
        x2_fail('cannot read required file: ' . $relative);
    }
    return $source;
}

$search = x2_source('search.php');
$error404 = x2_source('404.php');
$archive = x2_source('archive.php');
$assets = x2_source('inc/theme/assets.php');
$style = x2_source('style.css');
$functions = x2_source('functions.php');
$recovery = x2_source('assets/css/components/recovery.css');
$forms = x2_source('assets/css/components/forms.css');

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination(',
    'get_search_form()',
    'aznet-theme-recovery',
] as $marker) {
    if (!str_contains($search, $marker)) {
        x2_fail('search.php missing native/recovery marker: ' . $marker);
    }
}

foreach ([
    "home_url( '/' )",
    'get_search_form()',
    'aznet-theme-recovery',
    'aznet-theme-recovery__code',
    'aznet-theme-recovery__actions',
] as $marker) {
    if (!str_contains($error404, $marker)) {
        x2_fail('404.php missing recovery marker: ' . $marker);
    }
}

foreach ([
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination(',
    'aznet-theme-recovery',
] as $marker) {
    if (!str_contains($archive, $marker)) {
        x2_fail('archive.php missing native/recovery marker: ' . $marker);
    }
}

foreach ([
    'should_enqueue_recovery_assets',
    'enqueue_recovery_assets',
    "is_search()",
    "is_404()",
    "is_archive()",
    "assets/css/components/recovery.css",
    "aznet-theme-recovery",
] as $marker) {
    if (!str_contains($assets . "\n" . $recovery, $marker)) {
        x2_fail('X2 asset boundary missing marker: ' . $marker);
    }
}

$production = $search . "\n" . $error404 . "\n" . $archive . "\n" . $assets . "\n" . $forms;
foreach ([
    'new WP_Query', 'WP_Query(', 'query_posts(', 'get_posts(', 'pre_get_posts',
    '$wpdb', 'get_post_meta(', 'wp_redirect(', 'wp_safe_redirect(',
    '<meta name=', 'rel="canonical"', 'application/ld+json', 'schema.org',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        x2_fail('forbidden query/routing/storage/SEO marker: ' . $forbidden);
    }
}

if (!str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.1.0' );")) {
    x2_fail('AZNET_THEME_VERSION must remain 1.1.0 during X2');
}
if (!str_contains($style, 'Version: 1.1.0')) {
    x2_fail('Theme stylesheet version must remain 1.1.0 during X2');
}

foreach ([
    '.aznet-theme-recovery',
    '.aznet-theme-recovery__actions',
] as $marker) {
    if (!str_contains($recovery, $marker)) {
        x2_fail('recovery.css missing presentation marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-recovery .search-form',
    '.aznet-theme-search-header__form .search-form',
    ':focus-visible',
] as $marker) {
    if (!str_contains($forms, $marker)) {
        x2_fail('forms.css missing retained X2 control marker: ' . $marker);
    }
}

echo "PASS: X2 Search/404/empty ownership and presentation contract\n";
