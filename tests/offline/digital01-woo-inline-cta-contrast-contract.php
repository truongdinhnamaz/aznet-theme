<?php
/**
 * Regression for Theme-owned Woo inline button color in Digital 01 native cards.
 *
 * The button is rendered by WooCommerce. This contract owns ONLY the
 * presentation of its public-safe anchor inside a Theme visual preset card.
 * It never infers price, product state, cart behavior or provider storage.
 *
 * Public mobile evidence: #ff9d85 text over Woo button #e9e6ed -> 1.63:1.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-digital-01.css');
$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: Digital 01 Woo inline CTA AA: {$reason}\n");
    exit(1);
};

$selector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__service .woocommerce a.button';
if (!preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/s', $css, $match)) {
    $fail('Woo inline add-to-cart link lacks scoped foreground on its native button');
}
$declaration = trim($match[1]);
if (!preg_match('/(?:^|;)\s*color\s*:\s*(#[0-9a-f]{6})\s*(?:;|$)/i', $declaration, $foreground)) {
    $fail('Woo inline add-to-cart button must have a concrete tested foreground');
}
if (preg_match('/!\s*important/i', $declaration)) {
    $fail('Do not use !important to override WooCommerce presentation');
}

$luminance = static function (string $hex): float {
    $values = [
        hexdec(substr($hex, 1, 2)) / 255,
        hexdec(substr($hex, 3, 2)) / 255,
        hexdec(substr($hex, 5, 2)) / 255,
    ];
    $linear = array_map(
        static fn (float $v): float => $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
        $values
    );
    return $linear[0] * 0.2126 + $linear[1] * 0.7152 + $linear[2] * 0.0722;
};

$background = '#e9e6ed'; // Public WooCommerce button computed background, 26 Oct 2026.
$a = $luminance($foreground[1]);
$b = $luminance($background);
$contrast = (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
if ($contrast < 4.5) {
    $fail(sprintf('Native Woo CTA contrast %.2f:1 on %s is below WCAG AA', $contrast, $background));
}

echo sprintf("PASS: Digital 01 Woo inline button text %s on %s = %.2f:1 (AA), scoped to preset card\n",
    $foreground[1], $background, $contrast);
