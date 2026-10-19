<?php
/**
 * Industrial 01 premium Footer, presentation-only and source-safe contract.
 *
 * This must never transform WordPress rich-content list items into invented
 * links or rewrite/contact-source social identities.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$sheet = (string) file_get_contents($root . '/assets/css/presets/industrial-01.css');
$footer = (string) file_get_contents($root . '/template-parts/footer/site-footer.php');
$helper = (string) file_get_contents($root . '/inc/theme/footer.php');

$fail = static function (bool $okay, string $message): void {
    if (!$okay) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};
$style = static function (string $selector) use ($sheet, $fail): string {
    preg_match('/(?:^|\})\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/m', $sheet, $matches);
    $fail(isset($matches[1]), 'Missing scoped premium Industrial 01 Footer style: ' . $selector);
    return (string) $matches[1];
};
$expect = static function (string $selector, array $declarations) use ($style, $fail): void {
    $css = $style($selector);
    foreach ($declarations as $needle) {
        $fail(str_contains($css, $needle), "Missing {$needle} in {$selector}");
    }
};
$parent = 'body.aznet-theme-preset--industrial-01 .aznet-theme-site-footer--professional';

$expect($parent, [
    'border-top: 3px solid #f5ca72;',
    'background:',
    'color: #eaf2f7;',
]);
$expect($parent . ' .aznet-theme-site-footer__main', [
    'grid-template-columns: minmax(0, 1.4fr) repeat(3, minmax(0, 1fr));',
]);
$expect($parent . ' .aznet-theme-site-footer__inner', [
    'width: min(calc(100% - (2 * var(--aznet-theme-gutter))), var(--aznet-theme-container-shell));',
    'max-width: var(--aznet-theme-container-shell);',
    'padding-top:',
]);
$expect($parent . ' .aznet-theme-site-footer__brand', ['display: flex;', 'gap:']);
$expect($parent . ' .aznet-theme-site-footer__logo', ['max-width: 6rem;', 'object-fit: contain;']);
$expect($parent . ' .aznet-theme-site-footer__brand-title', ['display: block;', 'color: #ffffff;']);
$expect($parent . ' .aznet-theme-site-footer__column-content:not(:first-child)', ['min-width: 0;']);
$expect($parent . ' .aznet-theme-site-footer__column-content:not(:first-child) .aznet-theme-site-footer__rich-content > p:first-child > strong', [
    'letter-spacing:',
    'color: #ffffff;',
]);
$expect($parent . ' .aznet-theme-site-footer__column-content:not(:first-child) .aznet-theme-site-footer__rich-content ul', [
    'list-style: none;',
    'padding: 0;',
]);
$expect($parent . ' .aznet-theme-site-footer__column-content:not(:first-child) .aznet-theme-site-footer__rich-content li', [
    'line-height:',
    'overflow-wrap: anywhere;',
]);
$expect($parent . ' .aznet-theme-site-footer__column-content--1 .aznet-theme-site-footer__rich-content', [
    'line-height: 1.9;',
]);
$expect($parent . ' .aznet-theme-site-footer__rich-content a', ['overflow-wrap: anywhere;']);
$expect($parent . ' .aznet-theme-site-footer__bottom', ['border-top:', 'margin-top:']);
$expect($parent . ' a:focus-visible', ['outline:', 'outline-offset:']);

// One container token must align Footer, Header and the Industrial
// Homepage shells. The previous 88rem Footer cap placed its left edge
// outside the shared 82rem shell at desktop widths.
$homepageCss = (string) file_get_contents($root . '/assets/css/components/homepage-industrial-01.css');
$headerCss = (string) file_get_contents($root . '/assets/css/components/site-header.css');
$fail(str_contains($homepageCss,
    'width: min(calc(100% - (2 * var(--aznet-theme-gutter))), var(--aznet-theme-container-shell));'),
    'Industrial Homepage must retain shared shell token width');
$fail(str_contains($headerCss, 'width: min(var(--aznet-theme-container-shell), calc(100% - var(--aznet-theme-gutter)));'),
    'Header must retain the same shared shell cap');

// Both media policies must appear AFTER scoped desktop rules, not be
// accidentally overridden by the default Professional four-column selector.
$atTablet = strpos($sheet, '@media (max-width: 70rem)');
$atMobile = strpos($sheet, '@media (max-width: 48rem)', (int) $atTablet);
$desktopPos = strpos($sheet, $parent . ' .aznet-theme-site-footer__main');
$fail(false !== $desktopPos && false !== $atTablet && false !== $atMobile && $desktopPos < $atTablet && $atTablet < $atMobile,
    'Scoped Industrial 01 desktop/tablet/mobile rules must have correct cascade order');
$tail = substr($sheet, $atTablet);
$fail((bool) preg_match('/@media \(max-width: 70rem\)\s*\{[\s\S]*?' . preg_quote($parent . ' .aznet-theme-site-footer__main', '/') .
    '\s*\{[^}]*grid-template-columns:\s*repeat\(2,\s*minmax\(0,\s*1fr\)\)/', $tail),
    'Tablet must show two Footer columns');
$tailMobile = substr($sheet, $atMobile);
$fail((bool) preg_match('/@media \(max-width: 48rem\)\s*\{[\s\S]*?' . preg_quote($parent . ' .aznet-theme-site-footer__main', '/') .
    '\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*1fr\)/', $tailMobile),
    'Mobile must stack one Footer column');
$fail((bool) preg_match('/@media \(max-width: 48rem\)\s*\{[\s\S]*?' . preg_quote($parent . ' .aznet-theme-site-footer__inner', '/') .
    '\s*\{([^}]*)\}/', $tailMobile, $mobileInner) &&
    !str_contains((string) ($mobileInner[1] ?? ''), 'width:'),
    'Mobile Footer must inherit the shared Homepage shell width; no independent gutter override');

// Source CSS must use the same content-derived URL versioning as other
// scoped Theme styles. Otherwise a draft edit leaves ?ver=ThemeVersion
// unchanged and the browser/CDN can keep the stale Footer presentation.
$assetSource = (string) file_get_contents($root . '/inc/theme/assets.php');
$assetStart = strpos($assetSource, 'function enqueue_visual_preset_asset(');
$assetEnd = strpos($assetSource, 'function enqueue_header_navigation_asset(', (int) $assetStart);
$fail(false !== $assetStart && false !== $assetEnd, 'Visual preset enqueue boundary must exist');
$assetMethod = substr($assetSource, (int) $assetStart, $assetEnd - $assetStart);
$fail(str_contains($assetMethod, "asset_content_version( '/assets/css/presets/' . \$preset . '.css', \$version )"),
    'Visual preset stylesheet must use content-derived cache versions so a Footer CSS change is visible in WP drafts');

// Theme renders WordPress-owned Footer rich-content; no hidden source/social
// storage mutation, no guesses about client social IDs or URLs.
$fail(str_contains($footer, 'footer_context()') && str_contains($footer, '$column_content') &&
    str_contains($footer, 'aznet-theme-site-footer__rich-content'), 'Footer must continue to consume WordPress content');
$fail(str_contains($helper, 'footer_columns') && str_contains($helper, 'wp_kses_post('),
    'WordPress Footer block projection remains the only source');
$fail(!str_contains($sheet, 'minhnguyen.vn') && !str_contains($sheet, 'facebook.com/') &&
    !str_contains($sheet, 'tiktok.com'), 'Reusable Theme stylesheet must not hard-code client/domain URLs');
$fail(!str_contains(substr($sheet, (int) $desktopPos), 'aznet-theme-site-footer--skin-law-01'),
    'Industrial footer delta must not duplicate Law 01 skin ownership');
echo "PASS: Industrial 01 Footer premium, 4/2/1 responsive, source-owned rich content and accessible branding\n";
