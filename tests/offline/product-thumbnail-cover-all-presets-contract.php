<?php
/**
 * Theme-wide product-card media crop contract.
 *
 * Only thumbnails inside Theme / Woo public product card surfaces are covered.
 * Full product galleries, editorial imagery and category thumbnails remain
 * outside this presentation policy.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'classic'   => 'assets/css/components/woocommerce-archive.css',
    'related'   => 'assets/css/components/woocommerce-product.css',
    'blocks'    => 'assets/css/components/woocommerce-blocks.css',
    'industrial'=> 'assets/css/components/homepage-industrial-01.css',
    'curtain'   => 'assets/css/components/homepage-curtain-01.css',
    'preset'    => 'assets/css/presets/industrial-01.css',
];

$fail = static function(string $why): never {
    fwrite(STDERR, "FAIL: {$why}\n");
    exit(1);
};

$css = [];
foreach ($files as $key => $path) {
    $body = @file_get_contents($root . '/' . $path);
    if (!is_string($body)) {
        $fail("Missing product media stylesheet: {$path}");
    }
    $css[$key] = $body;
}

/** Extract all matching CSS declaration bodies for one EXACT selector. */
$rules = static function(string $sheet, string $selector): array {
    $expression = '/(?:^|\})\s*' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/m';
    preg_match_all($expression, $sheet, $found);
    return $found[1] ?? [];
};
$expect = static function(
    string $key,
    string $selector,
    array $declarations
) use ($css, $rules, $fail): void {
    $matches = $rules($css[$key], $selector);
    if ([] === $matches) {
        $fail("Missing product media selector [{$key}] {$selector}");
    }
    foreach ($declarations as $part) {
        if (!(bool) array_filter($matches, static fn(string $block): bool => str_contains($block, $part))) {
            $fail("Missing [{$key}] {$selector} / {$part}");
        }
    }
};

// Classic WooCommerce cards across every Theme visual preset and density.
$expect('classic', 'body.aznet-theme-woo-card--comfortable', ['--aznet-theme-woo-card-media-ratio: 1 / 1;']);
$expect('classic', 'body.aznet-theme-woo-card--balanced', ['--aznet-theme-woo-card-media-ratio: 1 / 1;']);
$expect('classic', 'body.aznet-theme-woo-card--compact', ['--aznet-theme-woo-card-media-ratio: 1 / 1;']);
$expect('classic', '.woocommerce ul.products li.product a img', [
    'aspect-ratio: var(--aznet-theme-woo-card-media-ratio, 1 / 1);',
    'height: auto;',
    'object-fit: cover;',
    'object-position: center;',
]);

// The Industrial visual preset MUST NOT override Woo product thumbnails to contain.
$expect('preset', 'body.aznet-theme-preset--industrial-01 .woocommerce ul.products li.product a img', [
    'aspect-ratio: 1 / 1;',
    'object-fit: cover;',
]);

// Home: the industrial template and the more-specific premium candidate
// must agree. A padded contain rule silently cancels the global cover.
$expect('industrial', '.aznet-theme-industrial01-product-card__media', ['aspect-ratio: 1 / 1;']);
$expect('industrial', '.aznet-theme-industrial01-product-card__media img', ['object-fit: cover;']);
$expect('industrial', '.aznet-theme-industrial01-products .aznet-theme-industrial01-product-card__media', [
    'aspect-ratio: 1 / 1;',
    'padding: 0;',
]);
$expect('industrial', '.aznet-theme-industrial01-products .aznet-theme-industrial01-product-card__media img', [
    'object-fit: cover;',
    'object-position: center;',
]);
$expect('industrial', '.aznet-theme-industrial01-products .aznet-theme-industrial01-product-card__media--empty', [
    'aspect-ratio: 1 / 1;',
]);
// Mobile must not switch the frame back to a landscape ratio.
$mobileRule = '/@media\s*\(max-width:\s*48rem\)\s*\{.*?\.aznet-theme-industrial01-products\s+\.aznet-theme-industrial01-product-card__media\s*\{([^}]*)\}/s';
if (!preg_match($mobileRule, $css['industrial'], $mobileMatch) ||
    !str_contains($mobileMatch[1], 'aspect-ratio: 1 / 1;')) {
    $fail('Industrial mobile product media must keep a square frame');
}

// Curtain: catalogue cards, not project/knowledge photos.
$expect('curtain', '.aznet-theme-curtain01-product-card__image', [
    'aspect-ratio: 1 / 1;',
    'height: auto;',
    'object-fit: cover;',
    'object-position: center;',
]);

// Related / upsell product cards only; native single-product main gallery is exempt.
$expect('related', '.single-product #main :is(.related, .upsells) ul.products li.product a img', [
    'aspect-ratio: 1 / 1;',
    'object-fit: cover;',
]);
$expect('related', '.single-product #main .woocommerce-product-gallery__image img', [
    'height: auto;',
]);

// Source-owned Woo Blocks: Product Collection plus legacy product-grid images.
// Do not apply cropping to the native product gallery or unrelated WP images.
$expect('blocks', '.wp-block-woocommerce-product-collection :is(.wp-block-woocommerce-product-image, .wp-block-post-featured-image) img', [
    'aspect-ratio: 1 / 1;',
    'object-fit: cover;',
]);
$expect('blocks', '.wc-block-grid__product-image img', [
    'aspect-ratio: 1 / 1;',
    'object-fit: cover;',
]);

foreach (['industrial', 'preset'] as $key) {
    if (preg_match('/(?:product-card__media\s+img|ul\.products\s+li\.product\s+a\s+img)\s*\{[^}]*object-fit:\s*contain\s*;/s', $css[$key])) {
        $fail("Product image contain override survives in {$key} sheet");
    }
}

foreach ($css as $key => $source) {
    if (str_contains($source, 'minhnguyen.vn') || str_contains($source, 'remquocanh.vn')) {
        $fail("Reusable product stylesheets must not hardcode a client site: {$key}");
    }
}
echo "PASS: product thumbnails square/cover across Woo classic, related, Blocks and Industrial/Curtain homepage, gallery excluded\n";
