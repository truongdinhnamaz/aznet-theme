<?php

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', __DIR__);
    }

    $GLOBALS['homepage_styles'] = [];
    $GLOBALS['homepage_is_front_page'] = false;

    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null): void {
        $GLOBALS['homepage_styles'][$handle] = [
            'src' => (string) $src,
            'deps' => $deps,
            'ver' => $ver,
        ];
    }

    function get_theme_file_uri($path): string {
        return (string) $path;
    }

    function is_front_page(): bool {
        return (bool) ($GLOBALS['homepage_is_front_page'] ?? false);
    }

    function homepage_asset_fail(string $message): never {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    $root = dirname(__DIR__, 2);
    $assetsPath = $root . '/inc/theme/assets.php';
    $bootstrapPath = $root . '/inc/theme/bootstrap.php';
    $cssPath = $root . '/assets/css/components/homepage.css';

    $assetsSource = (string) file_get_contents($assetsPath);
    $bootstrapSource = (string) file_get_contents($bootstrapPath);

    if (! str_contains($assetsSource, 'function enqueue_homepage_blueprint_asset')) {
        homepage_asset_fail('homepage frontend asset helper missing');
    }
    if (! str_contains($assetsSource, 'function enqueue_homepage_blueprint_editor_asset')) {
        homepage_asset_fail('homepage editor asset helper missing');
    }
    if (! str_contains($bootstrapSource, "enqueue_block_editor_assets")) {
        homepage_asset_fail('homepage editor asset hook missing');
    }
    if (! is_file($cssPath)) {
        homepage_asset_fail('homepage.css missing');
    }

    require_once $assetsPath;

    $GLOBALS['homepage_is_front_page'] = false;
    $GLOBALS['homepage_styles'] = [];
    \AZnet\Theme\enqueue_homepage_blueprint_asset('1.1.0');
    if ([] !== $GLOBALS['homepage_styles']) {
        homepage_asset_fail('homepage stylesheet leaked off the Front Page');
    }

    $GLOBALS['homepage_is_front_page'] = true;
    $GLOBALS['homepage_styles'] = [];
    \AZnet\Theme\enqueue_homepage_blueprint_asset('1.1.0');
    $asset = $GLOBALS['homepage_styles']['aznet-theme-homepage'] ?? null;
    if (! is_array($asset)) {
        homepage_asset_fail('homepage stylesheet not enqueued on Front Page');
    }
    if ('/assets/css/components/homepage.css' !== ($asset['src'] ?? null)) {
        homepage_asset_fail('homepage stylesheet path mismatch');
    }
    if (['aznet-theme-tokens'] !== ($asset['deps'] ?? null)) {
        homepage_asset_fail('homepage stylesheet must depend only on Theme tokens');
    }
    if ('1.1.0' !== ($asset['ver'] ?? null)) {
        homepage_asset_fail('homepage stylesheet must use Theme version');
    }

    $css = (string) file_get_contents($cssPath);
    foreach (preg_split('/\R/', $css) ?: [] as $line) {
        $selector = trim($line);
        if ('' === $selector || ! str_ends_with($selector, '{') || str_starts_with($selector, '@')) {
            continue;
        }
        if (! str_contains($selector, '.aznet-theme-homepage-blueprint')) {
            homepage_asset_fail('unscoped homepage selector: ' . $selector);
        }
    }

    if (str_contains($css, 'javascript:') || str_contains($css, 'url(http')) {
        homepage_asset_fail('homepage CSS contains external/runtime content coupling');
    }

    echo "PASS: Homepage blueprint asset-scope contract\n";
}
