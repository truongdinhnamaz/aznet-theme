<?php
/** Digital 01 native template: generic WordPress-owned effective Homepage source/authoring contract. */
declare(strict_types=1);

namespace {
    if (!defined('ABSPATH')) { define('ABSPATH', dirname(__DIR__, 2) . '/'); }
    class WP_Post {
        public int $ID = 77;
        public string $post_type = 'page';
        public string $post_status = 'publish';
        public string $post_title = 'Digital Native Home';
        public string $post_content = '<p>WordPress-owned homepage content</p>';
        public string $post_excerpt = 'A public Page.';
    }
    function get_option(string $key, mixed $default = null): mixed {
        return 'show_on_front' === $key ? 'page' : ('page_on_front' === $key ? 77 : $default);
    }
    function get_post(int $id): ?WP_Post { return 77 === $id ? new WP_Post() : null; }
    function get_the_title(WP_Post $post): string { return $post->post_title; }
    function get_edit_post_link(int $id, string $context = 'display'): string { return '/wp-admin/post.php?post=' . $id . '&action=edit'; }
    function get_the_excerpt(WP_Post $post): string { return $post->post_excerpt; }
    function __($string, $domain = ''): string { return $string; }
    function wp_strip_all_tags(string $str): string { return strip_tags($str); }
    function wp_trim_words(string $str, int $n, string $more = '…'): string { return $str; }
    function sanitize_key(string $str): string { return strtolower(trim($str)); }
}
namespace AZnet\Theme {
    function setting(string $key, mixed $fallback = null): mixed {
        return 'homepage_preset' === $key ? 'digital-01' : $fallback;
    }
}
namespace {
    $root = dirname(__DIR__, 2);
    require $root . '/inc/theme/template-registry.php';
    require $root . '/inc/theme/homepage-content-map.php';
    require $root . '/inc/theme/homepage-surface-map.php';
    require $root . '/inc/theme/homepage-composer.php';
    require $root . '/inc/admin/homepage.php';
    $manifest = require $root . '/inc/theme/templates/digital-01/manifest.php';
    if (!\AZnet\Theme\register_template_manifest($manifest)) {
        fwrite(STDERR, "FAIL: native template not registered.\n");
        exit(1);
    }
    $map = \AZnet\Theme\homepage_effective_surface_map('digital-01');
    if (count($map) !== 1 || ($map[0]['key'] ?? '') !== 'front-page-content'
        || ($map[0]['boundary'] ?? '') !== 'native'
        || ($map[0]['source_id'] ?? 0) !== 77) {
        fwrite(STDERR, "FAIL: native template did not project its exact WordPress Front Page into effective surface map.\n");
        exit(1);
    }
    if (['content'] !== \AZnet\Theme\Admin\homepage_authoring_sections('digital-01')) {
        fwrite(STDERR, "FAIL: native Homepage authoring section absent.\n");
        exit(1);
    }
    $summary = \AZnet\Theme\Admin\homepage_authoring_source_summary('digital-01', 'content');
    if ('READY' !== ($summary['status'] ?? null)
        || 'Digital Native Home' !== ($summary['title'] ?? null)
        || !str_contains((string) ($summary['edit'] ?? ''), 'post=77')) {
        fwrite(STDERR, "FAIL: native Homepage authoring must link to the WordPress-owned static Front Page.\n");
        exit(1);
    }
    foreach (['inc/theme/homepage-surface-map.php', 'inc/admin/homepage.php'] as $file) {
        if (str_contains((string) file_get_contents($root . '/' . $file), "'digital-01'")) {
            fwrite(STDERR, "FAIL: generic Core gained a new Digital 01 industry-specific branch.\n");
            exit(1);
        }
    }
    echo "PASS: native template effective surface, WordPress authoring source and Core neutrality\n";
}
