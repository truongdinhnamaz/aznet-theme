<?php
/** Execute the real native Page loader against explicit presentation contexts. */
declare(strict_types=1);

namespace AZnet\Theme {
    function homepage_preset(): string { return $GLOBALS['w01_test_context']['preset']; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['w01_test_context']['woo'] ? 'cart' : null; }
}
namespace {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
    function is_page(): bool { return $GLOBALS['w01_test_context']['page']; }
    function is_front_page(): bool { return $GLOBALS['w01_test_context']['front']; }
    function get_theme_file_uri(string $path): string { return $path; }
    function get_theme_file_path(string $path): string { return ABSPATH . ltrim($path, '/'); }
    function wp_enqueue_style(string $handle, string $uri, array $dependencies, ?string $version): void {
        $GLOBALS['w01_test_styles'][$handle] = compact('uri', 'dependencies', 'version');
    }
    require ABSPATH . 'inc/theme/assets.php';
    $cases = [
        ['wellness-01', true, false, false, true],
        ['off', true, false, false, false],
        ['law-01', true, false, false, false],
        ['digital-01', true, false, false, false],
        ['wellness-01', true, true, false, false],
        ['wellness-01', true, false, true, false],
        ['wellness-01', false, false, false, false],
    ];
    foreach ($cases as [$preset, $page, $front, $woo, $expected]) {
        $GLOBALS['w01_test_context'] = compact('preset', 'page', 'front', 'woo');
        $before = $GLOBALS['w01_test_context'];
        $GLOBALS['w01_test_styles'] = [];
        \AZnet\Theme\enqueue_page_assets('test');
        $asset = $GLOBALS['w01_test_styles']['aznet-theme-page-wellness-01'] ?? null;
        if (($asset !== null) !== $expected) {
            fwrite(STDERR, "FAIL: Wellness Page asset selection: " . json_encode($before) . "\n"); exit(1);
        }
        if ($expected && (
            $asset['uri'] !== '/assets/css/components/page-wellness-01.css'
            || !in_array('aznet-theme-page', $asset['dependencies'], true)
            || !in_array('aznet-theme-media', $asset['dependencies'], true)
            || $asset['version'] !== 'test-' . substr(hash_file('sha256', get_theme_file_path($asset['uri'])), 0, 12)
        )) { fwrite(STDERR, "FAIL: content-hashed Page/media style dependency\n"); exit(1); }
        if ($before !== $GLOBALS['w01_test_context']) { fwrite(STDERR, "FAIL: loader mutated settings\n"); exit(1); }
    }
    echo "PASS: seven real Page-loader scope/cache contexts; no settings write\n";
}
