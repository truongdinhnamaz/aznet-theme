<?php
/** Regression: Digital 01 Process CTA must not inherit browser-blue on navy. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-digital-01.css');
$fail = static function (string $why): void {
    fwrite(STDERR, "FAIL: Digital 01 Process link AA: {$why}\n");
    exit(1);
};

$selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__process .aznet-digital01__text-link > a';
if (!preg_match('/' . preg_quote($selector, '/') . '\\s*\\{([^}]*)\\}/s', $css, $match)) {
    $fail('Process anchor has no explicitly scoped foreground rule; browser default blue may win');
}
if (!preg_match('/(?:^|;)\\s*color\\s*:\\s*(#[0-9a-f]{6})\\s*(?:;|$)/i', trim($match[1]), $foregroundMatch)) {
    $fail('Process anchor must declare a concrete tested foreground');
}
if (!preg_match('/--d01-navy\\s*:\\s*(#[0-9a-f]{6})/i', $css, $backgroundMatch)) {
    $fail('Process background token must be identifiable');
}

$luminance = static function (string $hex): float {
    $channels = [
        hexdec(substr($hex, 1, 2)) / 255,
        hexdec(substr($hex, 3, 2)) / 255,
        hexdec(substr($hex, 5, 2)) / 255,
    ];
    $channels = array_map(
        static fn (float $v): float => $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
        $channels
    );
    return $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
};

$foreground = $luminance($foregroundMatch[1]);
$background = $luminance($backgroundMatch[1]);
$ratio = (max($foreground, $background) + 0.05) / (min($foreground, $background) + 0.05);
if ($ratio < 4.5) {
    $fail(sprintf('Process link contrast %.2f:1 is below WCAG AA 4.5:1', $ratio));
}

echo sprintf("PASS: Digital 01 Process anchor contrast %.2f:1, explicitly scoped\n", $ratio);
