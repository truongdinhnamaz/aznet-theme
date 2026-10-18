<?php
/**
 * Industrial 01 category-gallery contract: source-owned terms and 4x2
 * desktop presentation, with bounded fail-soft WooCommerce behavior.
 */
declare(strict_types=1);

namespace {
    define('ABSPATH', '/test/');
    class WP_Term {
        public int $term_id;
        public string $name;
        public string $description;
        public function __construct(int $id, string $name, string $description = '') {
            $this->term_id = $id; $this->name = $name; $this->description = $description;
        }
    }
    class WP_Error {}
    function __(string $text, string $domain = ''): string { return $text; }
    function esc_html_e(string $text, string $domain = ''): void { echo htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function esc_html(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function esc_attr(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function esc_url(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function wp_kses_post(string $text): string { return $text; }
    function wp_strip_all_tags(string $text): string { return strip_tags($text); }
    function wp_trim_words(string $text, int $limit = 55): string { return implode(' ', array_slice(explode(' ', $text), 0, $limit)); }
    function is_wp_error(mixed $value): bool { return $value instanceof WP_Error; }
    function get_term_link(WP_Term $term): string|WP_Error {
        return $term->term_id === 999 ? new WP_Error() : 'https://example.test/product-category/' . $term->term_id . '/';
    }
    function woocommerce_subcategory_thumbnail(WP_Term $term): void {
        echo '<img src="/media/' . $term->term_id . '.jpg" alt="' . esc_attr($term->name) . '" />';
    }
}

namespace AZnet\Theme {
    function preset_term(string $role, string $fallback, string $preset): string {
        return $fallback;
    }
}

namespace AZnet\Theme\Integrations\WooCommerce {
    function homepage_product_category_showcase_terms(int $limit = 8): array {
        return array_slice($GLOBALS['fixture_categories'] ?? [], 0, $limit);
    }
    function shop_url(): string { return $GLOBALS['fixture_shop_url'] ?? 'https://example.test/shop/'; }
}

namespace {
    $root = dirname(__DIR__, 2);
    $template = $root . '/template-parts/homepage/industrial-01/categories.php';
    $cssPath = $root . '/assets/css/components/homepage-industrial-01.css';
    $fail = static function (bool $condition, string $message): void {
        if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
    };
    $render = static function () use ($template): string {
        ob_start(); require $template; return (string) ob_get_clean();
    };
    $GLOBALS['fixture_categories'] = [
        new WP_Term(101, 'Phụ kiện thay thế máy gỗ'),
        new WP_Term(102, 'Máy chế biến gỗ', 'Máy cho nhiều bước sản xuất'),
        new WP_Term(103, 'Máy đã qua sử dụng'),
        new WP_Term(104, 'Phụ kiện thùng gỗ'),
        new WP_Term(105, 'Keo dán gỗ'),
        new WP_Term(106, 'Dao cụ - Lưỡi cưa'),
        new WP_Term(107, 'Loại khác'),
    ];
    $html = $render();

    // RED on original: plain identical tiles do not provide an editorial
    // introduction nor large media-first, accessible category links.
    $fail(str_contains($html, 'Danh mục thiết bị'), 'Retain native preset title');
    $fail(str_contains($html, 'aznet-theme-industrial01-categories__intro-card'), 'Editorial intro card missing');
    $fail(str_contains($html, '07 danh mục'), 'Count must reflect actual rendered source terms');
    $fail(str_contains($html, 'https://example.test/shop/'), 'Shop CTA must consume bounded Woo integration adapter');
    $fail(substr_count($html, 'class="aznet-theme-industrial01-card aznet-theme-industrial01-category-card"') === 7,
        'Exactly seven distinct WooCommerce-owned category links must be retained');
    $fail(substr_count($html, 'aznet-theme-industrial01-category-card__media') === 7, 'Every provided Woo thumbnail remains in the gallery');
    $fail(substr_count($html, 'aznet-theme-industrial01-category-card__arrow') === 7, 'Category arrows use consistent presentational SVG');
    foreach ($GLOBALS['fixture_categories'] as $term) {
        $fail(str_contains($html, esc_html($term->name)), 'Source category name missing: ' . $term->name);
        $fail(str_contains($html, '/media/' . $term->term_id . '.jpg'), 'Native Woo thumbnail missing: ' . $term->name);
        $fail(str_contains($html, 'product-category/' . $term->term_id . '/'), 'Native Woo category permalink missing');
    }
    $fail(str_contains($html, 'Máy cho nhiều bước sản xuất'), 'Native Woo description retained');

    // Reversible presentation rule must not replace or infer authoritative
    // category ordering. With 8 valid groups the intro yields to 8 image cards.
    $GLOBALS['fixture_categories'][] = new WP_Term(108, 'Nhóm thứ tám');
    $eight = $render();
    $fail(substr_count($eight, 'class="aznet-theme-industrial01-card aznet-theme-industrial01-category-card"') === 8,
        'Eight source terms are preserved without truncation beyond the contract limit');
    $fail(!str_contains($eight, 'aznet-theme-industrial01-categories__intro-card'),
        'When the grid is full, do not add an empty ninth decorative tile');

    $GLOBALS['fixture_categories'] = [new WP_Term(999, 'Broken link'), new WP_Term(101, 'Nhóm hợp lệ')];
    $filtered = $render();
    $fail(!str_contains($filtered, 'Broken link'), 'Invalid link must be omitted safely');
    $fail(str_contains($filtered, 'Nhóm hợp lệ'), 'Remaining valid source term retained');
    $GLOBALS['fixture_categories'] = [];
    $fail(trim($render()) === '', 'Provider absent/empty must omit the section');

    $css = (string) file_get_contents($cssPath);
    foreach ([
        '.aznet-theme-industrial01-categories__intro-card',
        '.aznet-theme-industrial01-category-card__veil',
        '.aznet-theme-industrial01-category-card__arrow',
        '.aznet-theme-industrial01-grid--categories',
        '@media (max-width: 64rem)',
        '@media (max-width: 48rem)',
        '@media (prefers-reduced-motion: reduce)',
    ] as $needle) {
        $fail(str_contains($css, $needle), 'Missing scoped category visual/a11y CSS: ' . $needle);
    }
    $fail(!str_contains($css, 'minhnguyen.vn'), 'Reusable CSS must not hardcode client domain/media');
    $source = (string) file_get_contents($template);
    foreach (['get_term_meta(', 'update_term_meta(', 'wc_get_products(', 'get_option(', 'get_terms(', 'get_post_meta('] as $privateRead) {
        $fail(!str_contains($source, $privateRead), 'Theme must not bypass Woo public adapter: ' . $privateRead);
    }

    echo "PASS: Industrial 01 category gallery, native Woo terms/media/links, intro, fail-soft and responsive CSS\n";
}
