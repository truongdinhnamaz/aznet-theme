<?php

declare(strict_types=1);

function x5_fail(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function x5_source(string $relative): string {
    $path = dirname(__DIR__, 2) . '/' . $relative;
    if (! is_file($path)) {
        x5_fail('missing required file: ' . $relative);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        x5_fail('cannot read required file: ' . $relative);
    }

    return $source;
}

$assets    = x5_source('inc/theme/assets.php');
$comments  = x5_source('assets/css/components/comments.css');
$recovery  = x5_source('assets/css/components/recovery.css');
$forms     = x5_source('assets/css/components/forms.css');
$style     = x5_source('style.css');
$functions = x5_source('functions.php');

foreach ([
    'should_enqueue_form_assets',
    'enqueue_form_assets',
    "'aznet-theme-forms'",
    "'/assets/css/components/forms.css'",
    "is_search()",
    "is_404()",
    "is_singular( 'post' )",
    "is_page()",
] as $marker) {
    if (! str_contains($assets, $marker)) {
        x5_fail('X5 asset boundary missing marker: ' . $marker);
    }
}

foreach ([
    '.aznet-theme-comments .comment-form',
    '.aznet-theme-search-header__form .search-form',
    '.aznet-theme-recovery .search-form',
    '.aznet-theme-entry__content .post-password-form',
    '.aznet-theme-entry__content .wp-block-search',
    '.wp-block-categories-dropdown',
    'input:not([type="checkbox"])',
    'input[type="checkbox"]',
    'input[type="radio"]',
    'select',
    'textarea',
    'button',
    ':focus-visible',
    ':disabled',
    '[aria-invalid="true"]',
    'var(--aznet-theme-focus)',
    'var(--aznet-theme-color-danger)',
] as $marker) {
    if (! str_contains($forms, $marker)) {
        x5_fail('forms.css missing shared native-control marker: ' . $marker);
    }
}

foreach ([
    'woocommerce',
    'wc-block',
    'choiceguide_',
    'RootProfile',
    'ConvertFlow',
] as $marker) {
    if (stripos($forms, $marker) !== false) {
        x5_fail('forms.css contains forbidden provider/plugin-form selector marker: ' . $marker);
    }
}

$production = $assets . "\n" . $forms . "\n" . $comments . "\n" . $recovery;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    '$wpdb',
    'get_post_meta(',
    'wp_redirect(',
    'wp_safe_redirect(',
    'fetch(',
    'XMLHttpRequest',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        x5_fail('forbidden ownership/submission/routing marker: ' . $forbidden);
    }
}

if (! str_contains($functions, "define( 'AZNET_THEME_VERSION', '1.1.0' );")) {
    x5_fail('AZNET_THEME_VERSION must remain 1.1.0 during X5');
}
if (! str_contains($style, 'Version: 1.1.0')) {
    x5_fail('Theme stylesheet version must remain 1.1.0 during X5');
}

echo "PASS: X5 native form controls ownership and presentation contract\n";
