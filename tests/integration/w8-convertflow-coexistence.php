<?php

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', '0.1.0-alpha.7');

    $providerRoot = getenv('CONVERTFLOW_ROOT') ?: '';
    if ('' === $providerRoot) {
        fwrite(STDERR, "FAIL: CONVERTFLOW_ROOT is required\n");
        exit(1);
    }

    define('CHOICEGUIDE_PATH', rtrim($providerRoot, '/') . '/choiceguide/');
    define('CHOICEGUIDE_URL', 'https://example.test/wp-content/plugins/choiceguide/');
    define('CHOICEGUIDE_VERSION', '0.1.0');

    $GLOBALS['w8_registered_styles'] = [];
    $GLOBALS['w8_enqueued_styles'] = [];
    $GLOBALS['w8_actions'] = [];
    $GLOBALS['w8_woo_surface'] = null;

    function add_action($hook, $callback, $priority = 10): void {
        $GLOBALS['w8_actions'][] = [$hook, $callback, $priority];
    }
    function is_admin(): bool { return false; }
    function wp_register_style($handle, $src = '', $deps = [], $ver = null): void {
        $GLOBALS['w8_registered_styles'][$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver];
    }
    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null): void {
        $GLOBALS['w8_enqueued_styles'][$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver];
    }
    function get_theme_file_uri($path): string { return 'https://example.test/wp-content/themes/aznet-theme' . $path; }
    function get_stylesheet_uri(): string { return 'https://example.test/wp-content/themes/aznet-theme/style.css'; }
}

namespace AZnet\Theme {
    function should_enqueue_generic_content_assets(): bool { return false; }
    function should_enqueue_woocommerce_product_assets(): bool { return 'product' === ($GLOBALS['w8_woo_surface'] ?? null); }
    function should_enqueue_woocommerce_archive_assets(): bool { return 'archive' === ($GLOBALS['w8_woo_surface'] ?? null); }
    function should_enqueue_woocommerce_cart_assets(): bool { return 'cart' === ($GLOBALS['w8_woo_surface'] ?? null); }
    function should_enqueue_woocommerce_checkout_assets(): bool { return 'checkout' === ($GLOBALS['w8_woo_surface'] ?? null); }
    function should_enqueue_woocommerce_account_assets(): bool { return 'account' === ($GLOBALS['w8_woo_surface'] ?? null); }
}

namespace {
    function fail_test(string $message): never {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    function git_blob_sha(string $bytes): string {
        return sha1('blob ' . strlen($bytes) . "\0" . $bytes);
    }

    function read_exact(string $path, string $expectedBlob): string {
        if (!is_file($path)) {
            fail_test("missing exact dependency source: {$path}");
        }
        $bytes = (string) file_get_contents($path);
        $actual = git_blob_sha($bytes);
        if ($actual !== $expectedBlob) {
            fail_test("dependency blob mismatch for {$path}: {$actual}");
        }
        return $bytes;
    }

    $themeRoot = dirname(__DIR__, 2);
    $providerRoot = rtrim((string) getenv('CONVERTFLOW_ROOT'), '/');

    read_exact(
        $providerRoot . '/choiceguide/src/Frontend/ThemeIntegration/ThemeIntegrationAssets.php',
        '35e0e474766520f06670991e7e9622f485505786'
    );
    $providerContractCss = read_exact(
        $providerRoot . '/choiceguide/assets/css/theme-integration.css',
        'f1d2a93583496f49515d8062ee8bf900823e9f86'
    );
    $providerFrontendAssets = read_exact(
        $providerRoot . '/choiceguide/src/Frontend/Assets.php',
        '0891c46e0029dd4edc2034ed04a1a02ae5cf2eb8'
    );

    $themeContractCss = (string) file_get_contents($themeRoot . '/assets/css/integrations/convertflow.css');
    preg_match_all('/--convertflow-theme-[a-z0-9-]+/', $providerContractCss, $providerMatches);
    preg_match_all('/--convertflow-theme-[a-z0-9-]+(?=\s*:)/', $themeContractCss, $themeMatches);
    $providerProperties = array_values(array_unique($providerMatches[0]));
    $themeProperties = array_values(array_unique($themeMatches[0]));
    sort($providerProperties);
    sort($themeProperties);

    if (33 !== count($providerProperties)) {
        fail_test('actual ConvertFlow v1 contract must expose the expected 33 public properties');
    }
    if ($providerProperties !== $themeProperties) {
        fail_test('AZnet Theme public bridge does not exactly match current ConvertFlow contract vocabulary');
    }

    if (!str_contains($providerFrontendAssets, 'array(ThemeIntegrationAssets::STYLE_HANDLE)')) {
        fail_test('actual ConvertFlow frontend stylesheet is not chained through its Theme Integration bridge');
    }

    require $themeRoot . '/inc/theme/assets.php';

    $GLOBALS['w8_woo_surface'] = null;
    \AZnet\Theme\enqueue_assets();
    if (!isset($GLOBALS['w8_enqueued_styles']['aznet-theme-convertflow-contract'])) {
        fail_test('Theme must expose the public bridge when Woo/ConvertFlow runtime surfaces are absent');
    }
    if (isset($GLOBALS['w8_enqueued_styles']['choiceguide-theme-integration'])) {
        fail_test('Theme must not enqueue or own ConvertFlow plugin assets');
    }

    $GLOBALS['w8_woo_surface'] = 'product';
    $GLOBALS['w8_enqueued_styles'] = [];
    \AZnet\Theme\enqueue_assets();
    if (!isset($GLOBALS['w8_enqueued_styles']['aznet-theme-convertflow-contract'])) {
        fail_test('Theme public bridge must remain available on Woo product surfaces');
    }
    if (!isset($GLOBALS['w8_enqueued_styles']['aznet-theme-woocommerce-product'])) {
        fail_test('retained Woo product presentation asset must coexist with the ConvertFlow bridge');
    }

    require $providerRoot . '/choiceguide/src/Frontend/ThemeIntegration/ThemeIntegrationAssets.php';
    $providerAssets = new \ChoiceGuide\Frontend\ThemeIntegration\ThemeIntegrationAssets();
    $providerAssets->register();
    $providerAssets->registerStyle();

    if (!isset($GLOBALS['w8_registered_styles']['choiceguide-theme-integration'])) {
        fail_test('actual ConvertFlow Theme Integration stylesheet did not register');
    }
    if ($GLOBALS['w8_registered_styles']['choiceguide-theme-integration']['deps'] !== []) {
        fail_test('ConvertFlow public bridge must remain independent from AZnet Theme asset handles');
    }

    $themeChanged = $themeContractCss . "\n" . (string) file_get_contents($themeRoot . '/inc/theme/assets.php');
    foreach (['ChoiceGuide\\', 'CHOICEGUIDE_', 'choiceguide-theme-integration', 'get_option(', 'get_post_meta(', '$wpdb'] as $forbidden) {
        if (str_contains($themeChanged, $forbidden)) {
            fail_test("Theme W8 must not depend on ConvertFlow private/runtime internals: {$forbidden}");
        }
    }

    echo "PASS: W8 exact-byte ConvertFlow coexistence matrix (provider absent/present, Woo off/on)\n";
}
