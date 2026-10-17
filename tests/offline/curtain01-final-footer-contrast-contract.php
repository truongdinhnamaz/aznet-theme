<?php
/**
 * Curtain 01 closing CTA / Footer palette-boundary contract.
 *
 * Theme owns the two presentation surfaces. Read public semantic tokens,
 * not the site's WordPress content or an inferred footer configuration.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$home = file_get_contents($root . '/assets/css/components/homepage-curtain-01.css');
$preset = file_get_contents($root . '/assets/css/presets/curtain-01.css');
$footer = file_get_contents($root . '/assets/css/components/site-footer.css');

function fail_boundary(string $message): never
{
    fwrite(STDERR, "FAIL: Curtain 01 CTA/Footer boundary: {$message}\n");
    exit(1);
}

if (! is_string($home) || ! is_string($preset) || ! is_string($footer)) {
    fail_boundary('required Theme-owned CSS assets missing');
}

function hex_token(string $css, string $name): string
{
    $pattern = '/'.preg_quote($name, '/').'\s*:\s*(#[0-9a-f]{6})\s*;/i';
    if (1 !== preg_match($pattern, $css, $match)) {
        fail_boundary("semantic color {$name} unavailable");
    }

    return strtolower($match[1]);
}

function css_rule(string $css, string $selector): string
{
    $pattern = '~'.preg_quote($selector, '~').'\s*\{([^}]*)\}~s';
    if (1 !== preg_match($pattern, $css, $match)) {
        fail_boundary("presentation rule missing: {$selector}");
    }

    return $match[1];
}

function luminance(string $hex): float
{
    $values = [];
    for ($i = 1; $i <= 5; $i += 2) {
        $channel = hexdec(substr($hex, $i, 2)) / 255.0;
        $values[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
    }

    return $values[0] * 0.2126 + $values[1] * 0.7152 + $values[2] * 0.0722;
}

function contrast_ratio(string $a, string $b): float
{
    $first = luminance($a);
    $second = luminance($b);

    return (max($first, $second) + 0.05) / (min($first, $second) + 0.05);
}

$footerBase = hex_token($preset, '--aznet-theme-surface-inverse');
$ctaBase = hex_token($preset, '--aznet-theme-color-primary');
if (! str_contains($footer, 'var(--aznet-theme-surface-inverse)')) {
    fail_boundary('Footer no longer consumes the known semantic inverse surface');
}

$ctaRule = css_rule($home, '.aznet-theme-curtain01-final-cta');
if (
    1 !== preg_match('/background:\s*var\(--aznet-theme-color-primary,\s*(#[0-9a-f]{6})\s*\);/i', $ctaRule, $match)
    || strtolower($match[1]) !== $ctaBase
) {
    fail_boundary('closing CTA must consume Curtain 01 primary with the same safe fallback');
}

$surfaceContrast = contrast_ratio($ctaBase, $footerBase);
if ($surfaceContrast < 2.5) {
    fail_boundary(sprintf('CTA/Footer backgrounds too similar (%.2f:1; minimum 2.50:1)', $surfaceContrast));
}

if (contrast_ratio('#ffffff', $ctaBase) < 4.5) {
    fail_boundary('white CTA text no longer meets 4.5:1 against its surface');
}

foreach ([
    '.aznet-theme-curtain01-final-cta' => 'color: #fff;',
    '.aznet-theme-curtain01-final-cta h2' => 'color: #fff;',
    '.aznet-theme-curtain01-final-cta p' => 'color: #fff;',
] as $selector => $required) {
    if (! str_contains(css_rule($home, $selector), $required)) {
        fail_boundary("required accessible text treatment missing: {$selector}");
    }
}

echo sprintf(
    "PASS: Curtain 01 CTA/Footer palette boundary %.2f:1 and CTA text contrast %.2f:1\n",
    $surfaceContrast,
    contrast_ratio('#ffffff', $ctaBase)
);
