<?php
/** D-051 v2 mapped renderer absence must never fall through to Law 01. */
declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
    class WP_Post {}
    function is_front_page(): bool { return true; }
    function get_option(string $key, mixed $default = null): mixed {
        return 'show_on_front' === $key
            ? ($GLOBALS['front_page_mode'] ?? 'page')
            : ('page_on_front' === $key ? 77 : $default);
    }
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}
namespace AZnet\Theme {
    function setting(string $key, mixed $default = null): mixed {
        return 'homepage_preset' === $key ? ($GLOBALS['active_homepage_preset'] ?? 'off') : $default;
    }
    function homepage_page_reference(int $id): ?\WP_Post { return 77 === $id ? new \WP_Post() : null; }
    function homepage_effective_surface_map(?string $preset = null): array { return []; }
}
namespace {
    $root = dirname(__DIR__, 2);
    require_once $root . '/inc/theme/template-registry.php';
    require_once $root . '/inc/theme/homepage-composer.php';

    $mapped = require $root . '/inc/theme/templates/digital-01/manifest.php';
    $mapped['id'] = 'fixture-v2-no-map';
    $mapped['presentation']['homepage_preset'] = 'fixture-v2-no-map';
    $mapped['homepage']['composition'] = ['mode' => 'mapped'];
    if (! \AZnet\Theme\register_template_manifest($mapped)) {
        fwrite(STDERR, "FAIL D-051: v2 mapped fixture registration failed.\n");
        exit(1);
    }
    $GLOBALS['active_homepage_preset'] = 'fixture-v2-no-map';
    if (\AZnet\Theme\homepage_composer_active()) {
        fwrite(STDERR, "FAIL D-051: missing v2 mapped renderer was treated as active (risks Law 01 takeover).\n");
        exit(1);
    }
    ob_start();
    \AZnet\Theme\render_homepage_before_content();
    \AZnet\Theme\render_homepage_after_content();
    $mappedOutput = (string) ob_get_clean();
    if ('' !== $mappedOutput) {
        fwrite(STDERR, "FAIL D-051: absent v2 mapped renderer emitted a Theme wrapper or foreign template.\n");
        exit(1);
    }

    $native = require $root . '/inc/theme/templates/digital-01/manifest.php';
    if (! \AZnet\Theme\register_template_manifest($native)) {
        fwrite(STDERR, "FAIL D-051: Digital 01 v2 native registration failed.\n");
        exit(1);
    }
    $GLOBALS['active_homepage_preset'] = 'digital-01';
    if (! \AZnet\Theme\homepage_composer_active()) {
        fwrite(STDERR, "FAIL D-051: valid static Front Page with v2 native must remain active.\n");
        exit(1);
    }
    ob_start();
    \AZnet\Theme\render_homepage_before_content();
    \AZnet\Theme\render_homepage_after_content();
    $nativeOutput = (string) ob_get_clean();
    if (1 !== substr_count($nativeOutput, 'aznet-theme-homepage--digital-01')
        || 1 !== substr_count($nativeOutput, '<div')
        || 1 !== substr_count($nativeOutput, '</div>')
        || str_contains($nativeOutput, 'law-01')) {
        fwrite(STDERR, "FAIL D-051: v2 native WordPress shell changed.\n");
        exit(1);
    }

    $GLOBALS['front_page_mode'] = 'posts';
    if (\AZnet\Theme\homepage_composer_active()) {
        fwrite(STDERR, "FAIL D-051: v2 native cannot take over when no static Front Page is configured.\n");
        exit(1);
    }
    unset($GLOBALS['front_page_mode']);

    $law = require $root . '/inc/theme/templates/law-01/manifest.php';
    if (! \AZnet\Theme\register_template_manifest($law)) {
        fwrite(STDERR, "FAIL D-051: retained v1 Law 01 registration failed.\n");
        exit(1);
    }
    $GLOBALS['active_homepage_preset'] = 'law-01';
    if (! \AZnet\Theme\homepage_composer_active()) {
        fwrite(STDERR, "FAIL D-051: retained v1 mapped composer behavior changed.\n");
        exit(1);
    }
    ob_start();
    \AZnet\Theme\render_homepage_before_content();
    \AZnet\Theme\render_homepage_after_content();
    $v1Output = (string) ob_get_clean();
    if (! str_contains($v1Output, 'aznet-theme-homepage--law-01')) {
        fwrite(STDERR, "FAIL D-051: retained v1 Law 01 wrapper missing.\n");
        exit(1);
    }

    echo "PASS: D-051 v2 mapped missing-renderer fail-soft plus native/v1 regression\n";
}
