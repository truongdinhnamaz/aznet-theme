<?php
/**
 * Rèm 01 authored dark Cover: preserve Gutenberg color readability.
 *
 * WP theme.json still exposes the legacy "text" palette slug. WordPress
 * consequently generates .has-text-color { color: text !important; }, which
 * can override a native block's explicit white inline style. This test locks
 * the scoped presentation repair without changing the public palette contract.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$preset = (string) file_get_contents($root . '/assets/css/presets/curtain-01.css');
$themeJson = json_decode((string) file_get_contents($root . '/theme.json'), true, 512, JSON_THROW_ON_ERROR);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: Curtain 01 process Cover contrast: {$message}\n");
    exit(1);
};

// Presentation fix must not silently change the existing editor palette API.
$palette = array_column($themeJson['settings']['color']['palette'] ?? [], 'slug');
if (!in_array('text', $palette, true)) {
    $fail('legacy palette slug changed without a public compatibility decision');
}

$selector = '.aznet-theme-curtain01-process__rail > .wp-block-cover:has(> .wp-block-cover__background.has-background-dim-70) .wp-block-cover__inner-container :is(.wp-block-heading, .wp-block-paragraph, .wp-block-list, li, a:not(.wp-block-button__link), figcaption)';
$pattern = '~' . preg_quote($selector, '~') . '\\s*\\{([^}]*)\\}~s';
if (1 !== preg_match($pattern, $css, $match)) {
    $fail('missing scoped native Gutenberg dark Cover text override');
}
if (!str_contains($match[1], 'color: var(--aznet-theme-on-inverse, #fff) !important;')) {
    $fail('Gutenberg palette specificity still overrides the authored inverse text color');
}
if (!str_contains($css, '.aznet-theme-curtain01-process__rail > .wp-block-cover:has(> .wp-block-cover__background.has-background-dim-70)')) {
    $fail('must remain within Theme-owned Process presentation surface');
}

if (1 !== preg_match('/--aznet-theme-on-inverse:\\s*(#[0-9a-f]{6})\\s*;/i', $preset, $value)) {
    $fail('approved inverse contrast token is missing');
}

$luminance = static function (string $hex): float {
    $rgb = [];
    for ($i = 1; $i <= 5; $i += 2) {
        $unit = hexdec(substr($hex, $i, 2)) / 255.0;
        $rgb[] = $unit <= 0.04045 ? $unit / 12.92 : (($unit + 0.055) / 1.055) ** 2.4;
    }
    return $rgb[0] * 0.2126 + $rgb[1] * 0.7152 + $rgb[2] * 0.0722;
};

// Worst-case bright image (#fff) beneath #111 at 70% opacity.
$brightestComposite = sprintf('#%02x%02x%02x', ...array_fill(0, 3, (int) round(255 * 0.30 + 17 * 0.70)));
$l1 = $luminance(strtolower($value[1]));
$l2 = $luminance($brightestComposite);
$contrast = (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
if ($contrast < 4.5) {
    $fail(sprintf('inverse text vs maximum composite background falls below WCAG AA (%.2f:1)', $contrast));
}

echo sprintf("PASS: Curtain 01 authored Process Cover contrast %.2f:1 (minimum 4.5:1)\n", $contrast);
