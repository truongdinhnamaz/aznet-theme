<?php
/**
 * Industrial 01 background Hero and contact-CTA presentation regression.
 * Self-contained render harness: no WordPress or provider storage required.
 */
declare(strict_types=1);

namespace {
    define('ABSPATH', '/test/');
    class WP_Post {
        public string $post_excerpt = '';
    }
    function get_bloginfo(string $key): string {
        return 'name' === $key ? 'Minh Nguyên' : 'Máy và thiết bị chế biến gỗ';
    }
    function __(string $value, string $domain = ''): string { return $value; }
    function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
    function esc_url(string $value): string { return $value; }
    function esc_url_raw(string $value): string { return $value; }
    function esc_html_e(string $value, string $domain = ''): void { echo esc_html($value); }
    function home_url(string $path): string { return 'https://example.test' . $path; }
    function get_permalink(WP_Post $post): string { return 'https://example.test/contact/'; }
    function get_the_excerpt(WP_Post $post): string { return 'LIÊN HỆ TRỰC TUYẾN Gửi thông tin Đang tiếp nhận'; }
    function wp_get_attachment_image_url(int $id, string $size): string|false {
        return $id === ($GLOBALS['azt_invalid_media_id'] ?? 0)
            ? false
            : 'https://example.test/uploads/' . $id . '.jpg';
    }
    function wp_get_attachment_image(int $id, string $size, bool $icon, array $attributes): string {
        return '<img data-attachment-id="' . $id . '" alt="">';
    }
}

namespace AZnet\Theme {
    function setting(string $key, mixed $fallback = ''): mixed {
        return $GLOBALS['azt_settings'][$key] ?? $fallback;
    }
    function preset_term(string $key, string $fallback, string $preset): string {
        return $fallback;
    }
    function homepage_effective_source_value(string $preset, string $surface): int { return 0; }
    function homepage_page_reference(int $id): ?\WP_Post {
        return $GLOBALS['azt_contact_page'] ?? null;
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    $render = static function (string $path): string {
        ob_start();
        include $path;
        return (string) ob_get_clean();
    };
    $check = static function (bool $result, string $message): void {
        if (!$result) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    };
    $hero = $root . '/template-parts/homepage/industrial-01/hero.php';
    $cta  = $root . '/template-parts/homepage/industrial-01/cta.php';
    $css  = $root . '/assets/css/components/homepage-industrial-01.css';
    $check(is_file($hero) && is_file($cta) && is_file($css), 'Required Industrial 01 sources exist');

    // RED on the original: background was unset even when a valid Hero image exists.
    $GLOBALS['azt_settings'] = ['homepage_industrial01_hero_image' => 1593, 'homepage_industrial01_hero_background' => 0];
    $output = $render($hero);
    $check(str_contains($output, 'aznet-theme-industrial01-hero--with-background'), 'Selected Hero media becomes a background when a separate background is missing');
    $check(str_contains($output, 'aznet-theme-industrial01-hero--background-only'), 'Single-image Hero selects full-bleed layout');
    $check(str_contains($output, '/uploads/1593.jpg'), 'WordPress attachment URL is used as-is');
    $check(!str_contains($output, 'data-attachment-id="1593"'), 'Avoid duplicating the same attachment as a separate foreground panel');

    $GLOBALS['azt_settings']['homepage_industrial01_hero_background'] = 1615;
    $output = $render($hero);
    $check(str_contains($output, 'aznet-theme-industrial01-hero--with-background'), 'Explicit background is rendered');
    $check(!str_contains($output, 'aznet-theme-industrial01-hero--background-only'), 'A distinct background keeps the foreground Hero presentation');
    $check(str_contains($output, '/uploads/1615.jpg'), 'Explicit background wins over Hero fallback');
    $check(str_contains($output, 'data-attachment-id="1593"'), 'Distinct foreground Hero image remains visible');

    $GLOBALS['azt_settings'] = ['homepage_industrial01_hero_image' => 0, 'homepage_industrial01_hero_background' => 0];
    $output = $render($hero);
    $check(!str_contains($output, 'aznet-theme-industrial01-hero--with-background'), 'Absent media must fall back to existing dark surface');
    $check(!str_contains($output, 'background-image:url('), 'Absent media must not emit a broken URL');

    $GLOBALS['azt_invalid_media_id'] = 1593;
    $GLOBALS['azt_settings']['homepage_industrial01_hero_image'] = 1593;
    $output = $render($hero);
    $check(!str_contains($output, 'aznet-theme-industrial01-hero--background-only'), 'Invalid attachment URL must not hide foreground image');
    $check(str_contains($output, 'data-attachment-id="1593"'), 'Failed background selection falls back to the foreground image');

    $GLOBALS['azt_contact_page'] = new WP_Post();
    $output = $render($cta);
    $check(!str_contains($output, 'LIÊN HỆ TRỰC TUYẾN'), 'Contact form labels must not be used as promotional copy');
    $check(str_contains($output, 'Gửi nhu cầu, mã sản phẩm'), 'A missing explicit excerpt uses the approved fallback');
    $GLOBALS['azt_contact_page']->post_excerpt = 'Nội dung tư vấn do biên tập viên cung cấp.';
    $output = $render($cta);
    $check(str_contains($output, 'Nội dung tư vấn do biên tập viên cung cấp.'), 'Explicit WordPress excerpt takes priority');

    $stylesheet = (string) file_get_contents($css);
    foreach ([
        '.aznet-theme-industrial01-hero--with-background',
        '.aznet-theme-industrial01-hero--background-only',
        'var(--industrial01-hero-background-image, none)',
        '@media (max-width: 48rem)',
        'grid-template-columns: repeat(2, minmax(0, 1fr));',
        '@media (prefers-reduced-motion: reduce)',
    ] as $needle) {
        $check(str_contains($stylesheet, $needle), 'Missing responsive / accessible Hero CSS: ' . $needle);
    }
    echo "PASS: Industrial 01 Hero background/CTA rendering, absent media fallback and scoped CSS\n";
}
