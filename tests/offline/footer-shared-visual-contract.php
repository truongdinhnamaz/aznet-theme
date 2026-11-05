<?php
/**
 * Shared Footer presentation contract: all site templates use one visual system.
 * Legacy Footer preset keys remain content selectors only; no domain ownership moves.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$fail = static function (string $why): never {
    fwrite(STDERR, "FAIL shared Footer: {$why}\n");
    exit(1);
};
$read = static function (string $path) use ($root, $fail): string {
    $data = file_get_contents($root . '/' . $path);
    if (! is_string($data)) { $fail('Missing: ' . $path); }
    return $data;
};
$css = $read('assets/css/components/site-footer.css');
$assets = $read('inc/theme/assets.php');
$template = $read('template-parts/footer/site-footer.php');
$helper = $read('inc/theme/footer.php');

foreach ([
    '.aznet-theme-site-footer {',
    '.aznet-theme-site-footer__inner {',
    '.aznet-theme-site-footer__main {',
    '.aznet-theme-site-footer__bottom {',
    '.aznet-theme-site-footer__rich-content',
    ':focus-visible',
    '@media (max-width: 48rem)',
] as $required) {
    if (! str_contains($css, $required)) { $fail('Shared CSS requirement missing: ' . $required); }
}
if (preg_match('/\\.aznet-theme-site-footer--(?:law-01|skin-law-01|minimal|classic|professional|split|centered|compact)\\b/', $css)) {
    $fail('Shared CSS still changes visual rules by Footer content preset');
}
if (preg_match('/body\\.aznet-theme-preset--[^\\s{]+\\s+\\.aznet-theme-site-footer/', $css)) {
    $fail('Shared CSS contains industry/template-specific rules');
}
foreach ([
    'assets/css/presets/curtain-01.css',
    'assets/css/presets/digital-01.css',
    'assets/css/presets/industrial-01.css',
] as $path) {
    if (str_contains($read($path), 'aznet-theme-site-footer')) {
        $fail('Site preset must not restyle shared Footer: ' . $path);
    }
}
if (str_contains($assets, "'aznet-theme-site-footer-digital-01'")
    || str_contains($assets, 'site-footer-digital-01.css')) {
    $fail('Digital-only Footer stylesheet still enqueued');
}
if (! str_contains($assets, "'aznet-theme-site-footer'")
    || ! str_contains($assets, "asset_content_version( '/assets/css/components/site-footer.css'")) {
    $fail('Single shared Footer asset must be content-versioned');
}
if (! str_contains($template, 'footer_context()')
    || ! str_contains($template, 'footer_columns_active')
    || ! str_contains($template, 'aznet-theme-site-footer__rich-content')
    || ! str_contains($template, 'data-aznet-theme-site-footer')) {
    $fail('Footer must preserve the same source-backed rich-content renderer');
}
if (! str_contains($helper, "'footer_columns'")
    || ! str_contains($helper, 'wp_kses_post(')
    || ! str_contains($helper, 'wp_nav_menu(')) {
    $fail('WordPress-owned Footer content and menus must be preserved');
}
foreach (['get_option(', 'get_post_meta(', '$wpdb', 'get_user_meta('] as $forbidden) {
    if (str_contains($template, $forbidden)) {
        $fail('Footer template must not read authoritative storage: ' . $forbidden);
    }
}
echo "PASS: single shared Footer visuals, responsive/focus, source-backed content, no preset CSS override\n";
