<?php
/**
 * D-064: AZnet Digital 01 Footer composition regression.
 *
 * This intentionally renders the real PHP template with WordPress-style
 * projections, rather than relying only on static CSS selector assertions.
 * The fixture does NOT certify a plugin/provider integration or live content.
 */
declare(strict_types=1);

namespace AZnet\Theme {
    function footer_context(): array {
        return $GLOBALS['d064_footer_context'];
    }
}

namespace {
    $root = dirname(__DIR__, 2);
    if (! defined('ABSPATH')) {
        define('ABSPATH', $root . '/');
    }

    function d064_fail(string $message): never {
        fwrite(STDERR, "FAIL D-064: {$message}\n");
        exit(1);
    }
    function esc_html(mixed $value): string {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
    function esc_attr(mixed $value): string { return esc_html($value); }
    function esc_url(mixed $value, array $schemes = []): string { return esc_attr($value); }
    function esc_attr__(string $text, string $domain = ''): string { return esc_attr($text); }
    function esc_html__(string $text, string $domain = ''): string { return esc_html($text); }
    function esc_html_e(string $text, string $domain = ''): void { echo esc_html($text); }
    function __(string $text, string $domain = ''): string { return $text; }
    function sanitize_html_class(string $class): string {
        return (string) preg_replace('/[^A-Za-z0-9_-]/', '', $class);
    }

    $context = [
        'preset' => 'professional',
        'skin' => 'default',
        'site_title' => 'AZnet Sentinel',
        'tagline' => 'Verified WordPress website tagline',
        'home_url' => 'https://example.test/',
        'logo_html' => '<img class="aznet-theme-site-footer__logo" src="logo.webp" alt="">',
        'about_intro' => '',
        'footer_columns_active' => false,
        'footer_columns' => [],
        'services' => [],
        'social_channels' => [],
        'contact_links' => [],
        'menus' => [
            'footer' => '<ul class="aznet-theme-site-footer__menu"><li><a href="/service/">Native service link</a></li></ul>',
            'footer-contact' => '<ul class="aznet-theme-site-footer__contact-menu"><li><a href="tel:000">Native contact link</a></li></ul>',
            'footer-social' => '<ul class="aznet-theme-site-footer__social-menu"><li><a href="https://example.test/social">Native social link</a></li></ul>',
            'footer-policy' => '<ul class="aznet-theme-site-footer__policy-menu"><li><a href="/terms/">Native policy link</a></li></ul>',
        ],
        'labels' => [
            'primary_heading' => 'Dịch vụ và giải pháp',
            'contact_heading' => 'Liên hệ',
            'social_heading' => 'Kết nối',
            'policy_heading' => 'Chính sách',
        ],
        'year' => '2026',
    ];

    function d064_markup(array $context): string {
        $GLOBALS['d064_footer_context'] = $context;
        ob_start();
        include dirname(__DIR__, 2) . '/template-parts/footer/site-footer.php';
        return (string) ob_get_clean();
    }

    function d064_check_footer_structure(string $markup, string $variant): void {
        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $loaded = $dom->loadHTML('<!doctype html><html><body>' . $markup . '</body></html>');
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (! $loaded) {
            d064_fail($variant . ': invalid HTML document');
        }
        $xp = new \DOMXPath($dom);
        $exact = static function (string $class): string {
            return 'contains(concat(" ", normalize-space(@class), " "), " ' . $class . ' ")';
        };
        $footer = $xp->query('//footer[' . $exact('aznet-theme-site-footer') . ']')->item(0);
        $inner = $xp->query('//div[' . $exact('aznet-theme-site-footer__inner') . ']')->item(0);
        $main = $xp->query('//div[' . $exact('aznet-theme-site-footer__main') . ']')->item(0);
        $bottom = $xp->query('//div[' . $exact('aznet-theme-site-footer__bottom') . ']')->item(0);
        if (! $footer || ! $inner || ! $main || ! $bottom) {
            d064_fail($variant . ': Footer inner/main/bottom landmark missing');
        }
        if (! $inner->parentNode?->isSameNode($footer)
            || ! $main->parentNode?->isSameNode($inner)
            || ! $bottom->parentNode?->isSameNode($inner)) {
            d064_fail($variant . ': bottom/main escaped the Footer inner shell');
        }
        if (1 !== $xp->query('//footer')->length) {
            d064_fail($variant . ': duplicate contentinfo Footer');
        }
    }

    $populated = d064_markup($context);
    d064_check_footer_structure($populated, 'native menus');
    foreach (['Native service link', 'Native contact link', 'Native social link', 'Native policy link'] as $item) {
        if (! str_contains($populated, $item)) {
            d064_fail('WordPress-authored menu projection lost: ' . $item);
        }
    }
    if (! str_contains($populated, 'Verified WordPress website tagline')
        || ! str_contains($populated, 'aznet-theme-site-footer__logo')) {
        d064_fail('WordPress logo/tagline must be preserved');
    }

    $empty = $context;
    foreach ($empty['menus'] as $name => $value) {
        $empty['menus'][$name] = '';
    }
    $emptyMarkup = d064_markup($empty);
    d064_check_footer_structure($emptyMarkup, 'unassigned WordPress menus');
    foreach (['Native service link', 'Native contact link', 'Native social link', 'Native policy link', 'Dịch vụ và giải pháp', 'Liên hệ', 'Kết nối'] as $missing) {
        if (str_contains($emptyMarkup, $missing)) {
            d064_fail('unassigned source must not invent a link or empty column: ' . $missing);
        }
    }
    $rich = $context;
    $rich['footer_columns_active'] = true;
    $rich['footer_columns'] = ['<p>Native WordPress block one</p>', '<p>Native WordPress block two</p>', '', ''];
    d064_check_footer_structure(d064_markup($rich), 'native wp_block columns');

    $css = file_get_contents($root . '/assets/css/components/site-footer.css');
    if (! is_string($css) || ! str_contains($css, 'D-064 Digital 01 source-backed Footer')
        || ! str_contains($css, 'body.aznet-theme-preset--digital-01 .aznet-theme-site-footer--professional')
        || ! str_contains($css, '.aznet-theme-site-footer__main')
        || ! str_contains($css, '@media (max-width: 48rem)')
        || ! str_contains($css, ':focus-visible')) {
        d064_fail('Digital 01 bounded Footer visual/responsive/focus CSS contract absent');
    }
    echo "PASS D-064: Footer HTML nesting, WordPress identity/menu/rich content, absence fallback, scoped responsive CSS\n";
}
