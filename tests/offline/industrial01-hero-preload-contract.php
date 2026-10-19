<?php
/**
 * Industrial 01 Hero background preload contract.
 * Only scoped Theme presentation is allowed to raise priority for a native
 * WordPress image; no provider data copy or global image preloading.
 */
declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) define('ABSPATH', dirname(__DIR__, 2) . '/');
    function wp_get_attachment_image_url(int $id, string $size): string|false {
        if ($size !== 'full' || $id === ($GLOBALS['aznet_invalid_media'] ?? -1)) return false;
        return 'https://example.test/uploads/' . $id . '.jpg';
    }
}
namespace AZnet\Theme {
    function homepage_preset(): string { return $GLOBALS['aznet_hero_preset'] ?? 'industrial-01'; }
    function homepage_composer_active(): bool { return $GLOBALS['aznet_hero_composer'] ?? true; }
    function homepage_effective_surface_map(string $preset): array {
        return $GLOBALS['aznet_hero_surfaces'] ?? [['template' => 'hero'], ['template' => 'categories']];
    }
    function setting(string $key, mixed $fallback = null): mixed {
        return $GLOBALS['aznet_hero_settings'][$key] ?? $fallback;
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    require_once $root . '/inc/theme/assets.php';
    $must = static function (bool $condition, string $message): void {
        if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
    };
    $callback = 'AZnet\\Theme\\industrial01_hero_preload_resources';
    $must(function_exists($callback), 'Industrial 01 Hero preload presenter is missing');

    // RED in the baseline: no bounded WordPress preload presentation hook.
    $assets = [['href' => 'https://example.test/styles.css', 'as' => 'style']];
    $GLOBALS['aznet_hero_settings'] = [
        'homepage_industrial01_hero_background' => 0,
        'homepage_industrial01_hero_image' => 1593,
    ];
    $result = $callback($assets);
    $must(count($result) === 2, 'The selected WordPress Hero image should add exactly one preload');
    $must($result[0] === $assets[0], 'Existing WordPress preloads must be preserved');
    $must($result[1] === [
        'href' => 'https://example.test/uploads/1593.jpg',
        'as' => 'image',
        'fetchpriority' => 'high',
    ], 'Hero preload must match the existing Hero background fallback URL and priority');

    // Explicit separate background must win, matching the Hero presenter.
    $GLOBALS['aznet_hero_settings']['homepage_industrial01_hero_background'] = 1615;
    $result = $callback($assets);
    $must(($result[1]['href'] ?? '') === 'https://example.test/uploads/1615.jpg',
        'Explicit native background attachment must win over separate foreground media');

    // Never duplicate an existing preload.
    $existing = [['href' => 'https://example.test/uploads/1615.jpg', 'as' => 'image']];
    $must($callback($existing) === $existing, 'Already preloaded image must not be duplicated');

    // No configured attachment or missing attachment => preserve upstream hints.
    $GLOBALS['aznet_hero_settings']['homepage_industrial01_hero_background'] = 0;
    $GLOBALS['aznet_hero_settings']['homepage_industrial01_hero_image'] = 0;
    $must($callback($assets) === $assets, 'Absent media must fail soft');

    $GLOBALS['aznet_hero_settings']['homepage_industrial01_hero_image'] = 1593;
    $GLOBALS['aznet_invalid_media'] = 1593;
    $must($callback($assets) === $assets, 'Missing WordPress media URL must fail soft');
    unset($GLOBALS['aznet_invalid_media']);

    // Scoped to the actual Industrial 01 Homepage Hero and only when rendered.
    $GLOBALS['aznet_hero_surfaces'] = [['template' => 'categories'], ['template' => 'solutions']];
    $must($callback($assets) === $assets, 'No Hero surface must not preload an unseen image');
    $GLOBALS['aznet_hero_surfaces'] = [['template' => 'hero']];

    foreach (['law-01', 'curtain-01', 'off'] as $preset) {
        $GLOBALS['aznet_hero_preset'] = $preset;
        $must($callback($assets) === $assets, 'No foreign Homepage preset may preload Industrial Hero');
    }
    $GLOBALS['aznet_hero_preset'] = 'industrial-01';
    $GLOBALS['aznet_hero_composer'] = false;
    $must($callback($assets) === $assets, 'Inactive Homepage composer must not emit a preload');
    $GLOBALS['aznet_hero_composer'] = true;

    $bootstrap = (string) file_get_contents($root . '/inc/theme/bootstrap.php');
    $must(str_contains($bootstrap, "'wp_preload_resources'"), 'Register callback with WordPress native preload filter');

    $source = (string) file_get_contents($root . '/inc/theme/assets.php');
    foreach (['get_option(', 'get_post_meta(', 'get_term_meta(', 'wc_get_products(', 'get_terms(', 'update_option('] as $unsafe) {
        $must(!str_contains(substr($source, strpos($source, 'function industrial01_hero_preload_resources')), $unsafe),
            'Hero preload may not access domain/private stores: ' . $unsafe);
    }

    echo "PASS: Industrial 01 source-owned Hero preload is scoped, prioritized, reversible and fail-soft\n";
}
