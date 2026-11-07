<?php
/**
 * Industrial 01 technical services: native WordPress content, premium card
 * presentation, safe fallback and responsive contract.
 *
 * Theme must not own service offerings, service-specific URLs or guarantees.
 */
declare(strict_types=1);

namespace {
    define('ABSPATH', '/test/');
    class WP_Post {
        public string $post_content = '';
        public string $post_excerpt = '';
        public string $post_title = 'Dịch vụ kỹ thuật';
    }
    function __(string $text, string $domain = ''): string { return $text; }
    function esc_html(string $text): string { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
    function esc_attr(string $text): string { return esc_html($text); }
    function esc_url(string $text): string { return esc_html($text); }
    function esc_html_e(string $text, string $domain = ''): void { echo esc_html($text); }
    function get_the_title(WP_Post $page): string { return $page->post_title; }
    function get_permalink(WP_Post $page): string { return 'https://example.test/dich-vu-ky-thuat/'; }
    function apply_filters(string $tag, string $content): string { return $content; }
    function wp_strip_all_tags(string $text): string { return strip_tags($text); }
    function strip_shortcodes(string $text): string { return $text; }
    function wp_trim_words(string $text, int $count, string $more = ''): string {
        $parts = preg_split('/\s+/u', trim($text)) ?: [];
        return implode(' ', array_slice($parts, 0, $count)) . (count($parts) > $count ? $more : '');
    }
}

namespace AZnet\Theme {
    function homepage_effective_source_value(string $preset, string $surface): int {
        return $preset === 'industrial-01' && $surface === 'process' ? 649 : 0;
    }
    function homepage_page_reference(int $id): ?\WP_Post {
        return $id === 649 ? ($GLOBALS['aznet_service_page'] ?? null) : null;
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    $template = $root . '/template-parts/homepage/industrial-01/process.php';
    $css_file = $root . '/assets/css/components/homepage-industrial-01.css';
    $fail = static function(bool $okay, string $message): void {
        if (!$okay) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    };
    $render = static function() use ($template): string {
        ob_start();
        require $template;
        return (string) ob_get_clean();
    };
    $page = new WP_Post();
    $page->post_excerpt = 'Câu mô tả tự động đã bị cắt giữa ý…';
    $intro = 'Một hệ thống máy chế biến gỗ cần được lựa chọn đúng. Trong quá trình lắp đặt, vận hành và sử dụng, doanh nghiệp cần một đầu mối kỹ thuật có thể tiếp nhận thông tin, xác định vấn đề và trao đổi phương án xử lý phù hợp.';
    $labels = [
        'Tư vấn lựa chọn máy và công đoạn',
        'Lắp đặt, chạy thử và hướng dẫn vận hành',
        'Bảo hành',
        'Sửa chữa và xử lý sự cố',
        'Nội dung dịch vụ thứ năm không xuất hiện ở đây',
    ];
    $page->post_content = '<p>' . esc_html($intro) . '</p><h2>Các nhóm hỗ trợ</h2>'
        . implode('', array_map(static fn(string $label): string => '<h3>' . esc_html($label) . '</h3><p>Mô tả của dịch vụ.</p>', $labels));
    $GLOBALS['aznet_service_page'] = $page;
    $html = $render();

    // Source ownership: use the complete authored first paragraph, not an
    // auto-generated excerpt ending in an ellipsis or a fabricated short claim.
    $fail(str_contains($html, esc_html($intro)), 'Complete WordPress introduction must survive without mid-sentence truncation');
    $fail(!str_contains($html, 'đã bị cắt giữa ý'), 'Auto-truncated excerpt must not override complete authored paragraph');
    $fail(str_contains($html, 'aria-labelledby="aznet-industrial01-process-title"'), 'Keep accessible section title');
    $fail(str_contains($html, 'id="aznet-industrial01-process-title"'), 'Visible H2 must have stable id');
    $fail(str_contains($html, 'Đồng hành kỹ thuật'), 'Section editorial kicker distinguishes itself from sourced title');
    $fail(str_contains($html, '>Dịch vụ kỹ thuật</h2>'), 'Page title remains the display source');
    $fail(substr_count($html, 'class="aznet-theme-industrial01-process__card"') === 4, 'Render exactly four page-authored headings');
    $fail(substr_count($html, 'aznet-theme-industrial01-process__card-top') === 4, 'Each card has visual index and decorative technical icon');
    $fail(substr_count($html, 'aznet-theme-industrial01-process__card-icon') === 4, 'Each card has a decorative icon independent of any business promise');
    foreach (array_slice($labels, 0, 4) as $label) {
        $fail(str_contains($html, esc_html($label)), 'Source service heading missing: ' . $label);
    }
    $fail(!str_contains($html, esc_html($labels[4])), 'Never fabricate or expand past the four-source-item cap');
    $fail(str_contains($html, 'href="https://example.test/dich-vu-ky-thuat/"'), 'Single public WordPress Page URL stays authoritative');
    $fail(substr_count($html, 'https://example.test/dich-vu-ky-thuat/') === 1, 'No per-service invented deep links');
    $fail(strpos($html, 'aznet-theme-industrial01-process__footer') > strpos($html, 'aznet-theme-industrial01-process__grid'),
        'Full service CTA must sit after the cards');
    $fail(str_contains($html, 'Xem đầy đủ dịch vụ'), 'Keep the existing full-service CTA');
    $fail(!str_contains($html, 'onerror='), 'Do not output unsupported or unsafe HTML from source labels');

    $page->post_content = '<h3>Chỉ có một nhóm hỗ trợ</h3>';
    $page->post_excerpt = 'Đoạn giới thiệu do WordPress cung cấp.';
    $single = $render();
    $fail(substr_count($single, 'class="aznet-theme-industrial01-process__card"') === 1,
        'Native source controls card count, not a fixed fake four-card mock');
    $fail(str_contains($single, 'Đoạn giới thiệu do WordPress cung cấp.'), 'Fallback to WordPress excerpt if no authored opening paragraph');

    $page->post_content = '<p>Chỉ có đoạn giới thiệu, chưa liệt kê nhóm dịch vụ.</p>';
    $noCards = $render();
    $fail(!str_contains($noCards, 'aznet-theme-industrial01-process__grid'), 'Missing headings must not create speculative service cards');
    $fail(str_contains($noCards, 'https://example.test/dich-vu-ky-thuat/'), 'No-card source retains public full Page navigation');

    $page->post_content = '';
    $fail('' === trim($render()), 'Empty Page omits the entire Homepage service section');
    $GLOBALS['aznet_service_page'] = null;
    $fail('' === trim($render()), 'Absent mapped Page omits the section without fallback claims');

    $css = (string) file_get_contents($css_file);
    $selector = static function(string $name) use ($css, $fail): string {
        preg_match('/' . preg_quote($name, '/') . '\s*\{([^}]+)\}/s', $css, $m);
        $fail(isset($m[1]), 'Missing isolated service style: ' . $name);
        return $m[1];
    };
    $fail(str_contains($selector('.aznet-theme-industrial01-process'), 'background: linear-gradient('),
        'Process section uses the light premium editorial surface');
    $fail(str_contains($selector('.aznet-theme-industrial01-process__grid'), 'grid-template-columns: repeat(2, minmax(0, 1fr));'),
        'Desktop service cards must use two columns');
    $fail(str_contains($selector('.aznet-theme-industrial01-process__card'), 'background: linear-gradient('),
        'Service cards use a deep navy premium panel');
    $fail(str_contains($selector('.aznet-theme-industrial01-process__card-title'), 'color: #fff;'),
        'Service title must be readable on navy');
    $fail(str_contains($selector('.aznet-theme-industrial01-process__card-number'), 'color: #f5ca72;'),
        'Service number uses signal gold for visual coherence');
    $fail(str_contains($selector('.aznet-theme-industrial01-process__footer'), 'margin-top:'),
        'Read-more CTA is visually separated from the cards');
    $mobile = strpos($css, '@media (max-width: 48rem)', strpos($css, '.aznet-theme-industrial01-process__card-title'));
    $fail(false !== $mobile && str_contains(substr($css, $mobile), '.aznet-theme-industrial01-process__grid'),
        'Mobile must retain a scoped single-column card rule');
    $fail(str_contains(substr($css, $mobile), 'grid-template-columns: minmax(0, 1fr);'),
        'Mobile service cards stack in one column');
    $fail(str_contains($css, '.aznet-theme-industrial01-process__all:focus-visible'), 'Service CTA accessible keyboard focus');

    foreach ([
        'get_option(', 'get_post_meta(', 'get_posts(', 'WP_Query(', 'update_post_meta(', 'wp_update_post('
    ] as $forbidden) {
        $fail(!str_contains((string) file_get_contents($template), $forbidden), 'Presentation must not own or mutate WordPress/domain data: ' . $forbidden);
    }
    $fail(!str_contains($css, 'minhnguyen.vn'), 'CSS remains generic to Industrial 01, not client-specific');
    echo "PASS: Industrial 01 service cards source-safe, complete editorial intro, 2x2 responsive visual and accessibility\n";
}
