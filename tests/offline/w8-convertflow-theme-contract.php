<?php

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', '0.1.0-alpha.7');

    $GLOBALS['w8_styles'] = [];
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null): void {
        $GLOBALS['w8_styles'][$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver];
    }
    function get_theme_file_uri($path): string { return 'https://example.test/theme' . $path; }
    function get_stylesheet_uri(): string { return 'https://example.test/theme/style.css'; }
}

namespace AZnet\Theme {
    function should_enqueue_generic_content_assets(): bool { return false; }
    function should_enqueue_woocommerce_product_assets(): bool { return false; }
    function should_enqueue_woocommerce_archive_assets(): bool { return false; }
    function should_enqueue_woocommerce_cart_assets(): bool { return false; }
    function should_enqueue_woocommerce_checkout_assets(): bool { return false; }
    function should_enqueue_woocommerce_account_assets(): bool { return false; }
}

namespace {
    function fail_test(string $message): never {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    $root = dirname(__DIR__, 2);
    $contractPath = $root . '/assets/css/integrations/convertflow.css';
    if (!is_file($contractPath)) {
        fail_test('ConvertFlow public theme-contract stylesheet is missing');
    }

    $css = (string) file_get_contents($contractPath);
    $required = [
        '--convertflow-theme-font-heading',
        '--convertflow-theme-font-body',
        '--convertflow-theme-heading-weight',
        '--convertflow-theme-heading-line-height',
        '--convertflow-theme-body-line-height',
        '--convertflow-theme-color-primary',
        '--convertflow-theme-color-accent',
        '--convertflow-theme-color-text',
        '--convertflow-theme-color-muted',
        '--convertflow-theme-color-surface',
        '--convertflow-theme-color-surface-soft',
        '--convertflow-theme-color-surface-inverse',
        '--convertflow-theme-color-on-inverse',
        '--convertflow-theme-color-border',
        '--convertflow-theme-radius-card',
        '--convertflow-theme-radius-control',
        '--convertflow-theme-radius-button',
        '--convertflow-theme-shadow-card',
        '--convertflow-theme-shadow-float',
        '--convertflow-theme-space-xs',
        '--convertflow-theme-space-sm',
        '--convertflow-theme-space-md',
        '--convertflow-theme-space-lg',
        '--convertflow-theme-space-xl',
        '--convertflow-theme-section-space',
        '--convertflow-theme-button-padding-x',
        '--convertflow-theme-button-weight',
        '--convertflow-theme-container-max',
        '--convertflow-theme-container-gutter',
        '--convertflow-theme-container-gutter-mobile',
        '--convertflow-theme-motion-fast',
        '--convertflow-theme-motion-base',
        '--convertflow-theme-focus-ring',
    ];

    foreach ($required as $property) {
        if (1 !== substr_count($css, $property . ':')) {
            fail_test("public contract property must be exposed exactly once: {$property}");
        }
    }

    if (preg_match('/\.choiceguide[-_]/', $css)) {
        fail_test('Theme integration CSS must not target ConvertFlow-owned DOM selectors');
    }
    if (str_contains($css, 'choiceguide_') || str_contains($css, '_choiceguide')) {
        fail_test('Theme integration CSS must not copy ConvertFlow storage/domain keys');
    }

    require $root . '/inc/theme/assets.php';
    \AZnet\Theme\enqueue_assets();

    $handle = 'aznet-theme-convertflow-contract';
    if (!isset($GLOBALS['w8_styles'][$handle])) {
        fail_test('ConvertFlow public theme-contract stylesheet must be exposed independently of provider presence');
    }
    if ($GLOBALS['w8_styles'][$handle]['deps'] !== ['aznet-theme-tokens']) {
        fail_test('ConvertFlow theme-contract stylesheet must depend only on Theme semantic tokens');
    }
    if (!str_ends_with($GLOBALS['w8_styles'][$handle]['src'], '/assets/css/integrations/convertflow.css')) {
        fail_test('ConvertFlow contract handle must point to the dedicated integration stylesheet');
    }

    echo "PASS: W8 ConvertFlow public theme-contract bridge\n";
}
