<?php
/** Prove the fourth registered template never falls through to Law 01 rendering. */
declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
    class WP_Post {}
    function is_front_page(): bool { return true; }
    function get_option(string $key, mixed $default = null): mixed {
        return 'show_on_front' === $key ? 'page' : ('page_on_front' === $key ? 77 : $default);
    }
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
    function get_theme_file_path(string $path): string { return dirname(__DIR__, 2) . '/' . $path; }
    function get_template_part(string $slug, mixed $name = null, array $args = []): void {
        $GLOBALS['rendered_template_parts'][] = $slug;
    }
}
namespace AZnet\Theme {
    function setting(string $key, mixed $default = null): mixed {
        return 'homepage_preset' === $key ? 'digital-01' : $default;
    }
    function homepage_page_reference(int $id): ?\WP_Post {
        return 77 === $id ? new \WP_Post() : null;
    }
    function homepage_effective_surface_map(?string $preset = null): array {
        return [['template' => 'services', 'boundary' => 'before']];
    }
}
namespace {
    $root = dirname(__DIR__, 2);
    require $root . '/inc/theme/template-registry.php';
    require $root . '/inc/theme/homepage-composer.php';
    $candidate = require $root . '/inc/theme/templates/digital-01/manifest.php';
    if (! \AZnet\Theme\register_template_manifest($candidate) || ! \AZnet\Theme\homepage_composer_active()) {
        fwrite(STDERR, "FAIL: fourth template cannot activate under the public Homepage preset.\n");
        exit(1);
    }
    $GLOBALS['rendered_template_parts'] = [];
    ob_start();
    \AZnet\Theme\render_homepage_before_content();
    \AZnet\Theme\render_homepage_after_content();
    $html = (string) ob_get_clean();
    if (! str_contains($html, 'aznet-theme-homepage--digital-01')
        || str_contains($html, 'aznet-theme-homepage--law-01')
        || 1 !== substr_count($html, '<div')
        || 1 !== substr_count($html, '</div>')
        || [] !== $GLOBALS['rendered_template_parts']) {
        fwrite(STDERR, "FAIL: registered fourth template leaked Law 01 composition or unbalanced the native body.\n");
        exit(1);
    }
    echo "PASS: Digital 01 native WordPress front-page compositor does not render Law 01\n";
}
