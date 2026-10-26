<?php
/**
 * Digital 01 commercial-media visual surface contract.
 *
 * Public screenshot evidence at 390/1440 on 2026-10-26: the dark
 * featured card makes an existing third-party raster logo's dark
 * lettering difficult to read. Theme only owns the presentation
 * surface of the image; WooCommerce and WordPress own its image data.
 *
 * No price/cart state, product classification or image metadata is read.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-digital-01.css');

$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: Digital 01 commercial media surface: {$reason}\n");
    exit(1);
};

$card = 'body.aznet-theme-preset--digital-01 .aznet-digital01__service .aznet-digital01__commercial-media';
$findDeclaration = static function (string $selector) use ($css): ?string {
    return preg_match('/' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/s', $css, $match)
        ? trim($match[1])
        : null;
};

$figureRules = $findDeclaration($card);
if ($figureRules === null) {
    $fail('missing preset/card-scoped presentation for native commercial-media figure');
}
if (!preg_match('/(?:^|;)\s*background\s*:\s*#(?:fff|ffffff)\s*(?:;|$)/i', $figureRules)) {
    $fail('dark featured cards need an opaque white media surface for raster logos');
}
if (!preg_match('/(?:^|;)\s*padding\s*:\s*(\d+)px\s*(?:;|$)/i', $figureRules, $padding) || (int) $padding[1] < 8) {
    $fail('media figure needs a positive protective inner gutter');
}
if (preg_match('/!\s*important/i', $figureRules)) {
    $fail('media surface must not rely on !important overrides');
}

$imageRules = $findDeclaration($card . ' img');
if ($imageRules === null
    || !preg_match('/(?:^|;)\s*object-fit\s*:\s*contain\s*(?:;|$)/i', $imageRules)
    || !preg_match('/(?:^|;)\s*max-width\s*:\s*100%\s*(?:;|$)/i', $imageRules)) {
    $fail('responsive native product images must stay contained within the light figure surface');
}

echo "PASS: Digital 01 opaque, padded, scoped commercial-media figure and contained image\n";
