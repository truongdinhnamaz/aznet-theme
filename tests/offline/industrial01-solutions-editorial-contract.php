<?php
/**
 * Industrial 01 source-owned solutions editorial Homepage contract.
 * Exercises the PHP presenter without requiring a WordPress installation.
 */
declare(strict_types=1);

namespace {
    define('ABSPATH', '/test/');
    class WP_Post {
        public string $post_content = '';
        public string $post_title = 'Giải pháp';
    }
    function __(string $value, string $domain = ''): string { return $value; }
    function esc_html(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
    function esc_attr(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
    function esc_url(string $value): string { return $value; }
    function esc_html_e(string $value, string $domain = ''): void { echo esc_html($value); }
    function get_permalink(WP_Post $page): string { return 'https://example.test/giai-phap/'; }
    function get_the_title(WP_Post $page): string { return $page->post_title; }
    function wp_strip_all_tags(string $html): string { return strip_tags($html); }
    function apply_filters(string $tag, string $text): string {
        return $text === 'legacy' ? '<h3>Giải pháp cũ A</h3><h3>Giải pháp cũ B</h3>' : $text;
    }
    function parse_blocks(string $raw): array { return $GLOBALS['azt_fixture_blocks'][$raw] ?? []; }
    function wp_get_attachment_image(int $id, string $size, bool $icon = false, array $attr = []): string {
        if ($id === ($GLOBALS['azt_missing_attachment'] ?? -1)) return '';
        return '<img data-wordpress-media="' . $id . '" alt="WordPress Media Library" class="' .
            esc_attr((string) ($attr['class'] ?? '')) . '">';
    }
}

namespace AZnet\Theme {
    function homepage_effective_source_value(string $preset, string $surface): int {
        return $preset === 'industrial-01' && $surface === 'solutions' ? 645 : 0;
    }
    function homepage_page_reference(int $id): ?\WP_Post {
        return $id === 645 ? ($GLOBALS['azt_source_page'] ?? null) : null;
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    $template = $root . '/template-parts/homepage/industrial-01/solutions.php';
    $style = $root . '/assets/css/components/homepage-industrial-01.css';
    $fail = static function (bool $okay, string $message): void {
        if (!$okay) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
    };
    $block = static function (string $name, array $attrs = [], string $inner = '', array $children = []): array {
        return ['blockName' => $name, 'attrs' => $attrs, 'innerHTML' => $inner, 'innerBlocks' => $children];
    };
    $card = static function (string $label, string $description) use ($block): array {
        return $block('core/group', ['className' => 'aznet-theme-industrial01-solution-card'], '', [
            $block('core/paragraph', ['className' => 'aznet-theme-industrial01-solution-card__index'], '<p>01</p>'),
            $block('core/heading', ['level' => 3], '<h3>' . esc_html($label) . '</h3>'),
            $block('core/paragraph', [], '<p>' . esc_html($description) . '</p>'),
        ]);
    };
    $titles = [
        'Đầu tư hoặc mở rộng xưởng',
        'Bổ sung một công đoạn',
        'Xử lý điểm nghẽn',
        'Đối chiếu trước báo giá',
    ];
    $details = [
        'Xác định các công đoạn cần hình thành và quy mô đầu ra.',
        'Tìm nhóm thiết bị phù hợp công đoạn cần bổ sung.',
        'Làm rõ năng suất, sai số và điểm nghẽn vận hành.',
        'So sánh yêu cầu kỹ thuật và dữ kiện còn thiếu.',
    ];
    $GLOBALS['azt_fixture_blocks']['valid'] = [
        $block('core/group', ['className' => 'aznet-theme-industrial01-solution-page'], '', [
            $block('core/group', ['className' => 'aznet-theme-industrial01-solution-hero'], '', [
                $block('core/heading', ['level' => 2], '<h2>Bắt đầu từ nhu cầu sản xuất, không bắt đầu từ tên máy</h2>'),
                $block('core/paragraph', ['className' => 'aznet-theme-industrial01-solution-lead'], '<p>Đối chiếu vật liệu, sản lượng và điều kiện vận hành.</p>'),
                $block('core/group', ['className' => 'aznet-theme-industrial01-solution-hero__media'], '', [
                    $block('core/image', ['id' => 666], '<figure><img></figure>'),
                ]),
            ]),
            $block('core/group', ['anchor' => 'tinh-huong'], '', array_map($card, $titles, $details)),
            $block('core/group', ['anchor' => 'cong-doan'], '', [
                $card('Cắt và chia phôi', 'This is not a homepage situational solution.'),
            ]),
        ]),
    ];
    $page = new WP_Post();
    $page->post_content = 'valid';
    $GLOBALS['azt_source_page'] = $page;
    $render = static function () use ($template): string {
        ob_start(); require $template; return (string) ob_get_clean();
    };

    // RED on original presenter: individual source descriptions and WordPress-selected
    // visual are absent; all four cards are only short numeric labels.
    $html = $render();
    $fail(str_contains($html, 'id="aznet-homepage-industrial-solutions"'), 'Solutions surface present');
    $fail(str_contains($html, 'aria-labelledby="aznet-industrial01-solutions-title"'), 'Accessible section label');
    $fail(str_contains($html, 'Bắt đầu từ nhu cầu sản xuất'), 'Native WordPress hero headline projected');
    $fail(str_contains($html, 'Đối chiếu vật liệu, sản lượng'), 'Native WordPress editorial lead projected');
    $fail(str_contains($html, 'data-wordpress-media="666"'), 'Image is consumed from the mapped WordPress Page, not a Theme hard-coded ID');
    $fail(substr_count($html, 'class="aznet-theme-industrial01-solutions__card"') === 4, 'Exactly four native situational cards');
    foreach ($titles as $i => $title) {
        $fail(str_contains($html, esc_html($title)), 'Missing source heading: ' . $title);
        $fail(str_contains($html, esc_html($details[$i])), 'Missing source-owned description: ' . $title);
    }
    $fail(!str_contains($html, 'This is not a homepage situational solution.'), 'Unrelated WordPress work-stage cards excluded');
    $fail(str_contains($html, 'https://example.test/giai-phap/#tinh-huong'), 'Card link targets existing native WordPress anchor');

    $GLOBALS['azt_missing_attachment'] = 666;
    $noImage = $render();
    $fail(!str_contains($noImage, 'data-wordpress-media'), 'Missing attachment not rendered as broken media');
    $fail(str_contains($noImage, 'aznet-theme-industrial01-solutions--no-media'), 'Visual absence produces intentional card-only fallback');
    $fail(str_contains($noImage, esc_html($details[2])), 'Media failure must preserve readable source cards');

    $page->post_content = 'legacy';
    $legacy = $render();
    $fail(str_contains($legacy, 'Giải pháp cũ A'), 'Unstructured native markup uses the legacy safe fallback');
    $fail(!str_contains($legacy, '#tinh-huong'), 'Missing WordPress anchor must not produce false deep links');

    $page->post_content = '';
    $fail(trim($render()) === '', 'Empty WordPress source omits entire surface');
    $GLOBALS['azt_source_page'] = null;
    $fail(trim($render()) === '', 'Absent Page source omits entire surface');

    $css = is_file($style) ? (string) file_get_contents($style) : '';
    foreach ([
        '.aznet-theme-industrial01-solutions__intro',
        '.aznet-theme-industrial01-solutions__layout',
        '.aznet-theme-industrial01-solutions__visual',
        '.aznet-theme-industrial01-solutions__card-description',
        '.aznet-theme-industrial01-solutions__card:focus-visible',
        '@media (max-width: 48rem)',
        '@media (prefers-reduced-motion: reduce)',
    ] as $selector) {
        $fail(str_contains($css, $selector), 'Missing scoped responsive/a11y style: ' . $selector);
    }
    $fail(!str_contains($css, 'background-image: url(https://minhnguyen.vn'), 'No site-specific media URL in reusable CSS');
    echo "PASS: Industrial 01 source-owned solutions editorial, media fallback and responsive presentation\n";
}
