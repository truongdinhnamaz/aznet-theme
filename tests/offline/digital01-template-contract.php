<?php
/** Digital 01 fourth production template: manifest, ownership, and native Page contract. */
declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', dirname(__DIR__, 2) . '/');
}
$root = dirname(__DIR__, 2);
$manifestPath = $root . '/inc/theme/templates/digital-01/manifest.php';
$patternPath = $root . '/patterns/homepage-digital-01.php';
$visualPath = $root . '/assets/css/presets/digital-01.css';
$homepagePath = $root . '/assets/css/components/homepage-digital-01.css';
foreach ([$manifestPath, $patternPath, $visualPath, $homepagePath] as $path) {
    if (! is_file($path)) {
        fwrite(STDERR, 'FAIL: Digital 01 production file missing: ' . basename($path) . "\n");
        exit(1);
    }
}
require_once $root . '/inc/theme/template-registry.php';
require_once $root . '/inc/theme/template-loader.php';
require_once $root . '/inc/theme/assets.php';
$store =& \AZnet\Theme\template_manifest_store();
$store = [];
$result = \AZnet\Theme\load_local_template_manifests();
if ([] !== $result['rejected'] || ! in_array('digital-01', $result['loaded'], true)) {
    fwrite(STDERR, "FAIL: Digital 01 cannot be loaded through the v2 manifest contract.\n");
    exit(1);
}
$manifest = \AZnet\Theme\template_manifest('digital-01');
if (! is_array($manifest)
    || 2 !== $manifest['contract_version']
    || 'Digital 01' !== $manifest['name']
    || 'digital-01' !== ($manifest['presentation']['visual_preset'] ?? null)
    || 'digital-01' !== ($manifest['presentation']['homepage_preset'] ?? null)
    || ! in_array('homepage', $manifest['capabilities'], true)
    || ! in_array('woocommerce', $manifest['capabilities'], true)) {
    fwrite(STDERR, "FAIL: Digital 01 contract or presentation IDs are invalid.\n");
    exit(1);
}
if ('native' !== ($manifest['homepage']['composition']['mode'] ?? null)
    || isset($manifest['assets']['homepage']['composition'])) {
    fwrite(STDERR, "FAIL: Digital 01 must declare native mode under Homepage, not assets.\\n");
    exit(1);
}
if (['content'] !== ($manifest['homepage']['authoring']['sections'] ?? null)
    || true === ($manifest['homepage']['hero']['enabled'] ?? false)
    || [] !== ($manifest['provisioning'] ?? [])) {
    fwrite(STDERR, "FAIL: Digital 01 must use WordPress-native authored Homepage without auto-provisioning or phantom Hero controls.\n");
    exit(1);
}
$assets = \AZnet\Theme\template_homepage_asset_descriptors('digital-01');
if (1 !== count($assets)
    || 'aznet-theme-homepage-digital-01' !== ($assets[0]['handle'] ?? null)
    || '/assets/css/components/homepage-digital-01.css' !== ($assets[0]['path'] ?? null)) {
    fwrite(STDERR, "FAIL: Digital 01 must load its own single Homepage asset through the manifest.\n");
    exit(1);
}
$pattern = (string) file_get_contents($patternPath);
if (! str_contains($pattern, 'Slug: aznet-theme/homepage-digital-01')
    || ! str_contains($pattern, 'wp:query')
    || ! str_contains($pattern, 'aznet-digital01')
    || str_contains($pattern, 'woocommerce/product-collection')) {
    fwrite(STDERR, "FAIL: Digital 01 native-pattern/news query or optional Woo separation invalid.\n");
    exit(1);
}
foreach ([$visualPath, $homepagePath] as $path) {
    $css = (string) file_get_contents($path);
    if (! str_contains($css, 'digital-01') && ! str_contains($css, 'aznet-digital01')) {
        fwrite(STDERR, "FAIL: Digital 01 CSS is not independently scoped.\n");
        exit(1);
    }
}
$homepageCss = (string) file_get_contents($homepagePath);
// WordPress core/query sets source image width/height attributes; Theme must not
// keep an intrinsic 630px thumbnail height when the 3-card grid becomes narrow.
$queryImageSelector = 'body.aznet-theme-preset--digital-01 .aznet-digital01__articles .wp-block-post-featured-image img';
if (! preg_match('~' . preg_quote($queryImageSelector, '~') . '\\s*\\{([^}]*)\\}~', $homepageCss, $queryImageRule)
    || ! preg_match('/(?:^|;)\\s*height\\s*:\\s*auto\\s*;/i', $queryImageRule[1])) {
    fwrite(STDERR, "FAIL: Digital 01 WordPress Query thumbnails must size fluidly rather than use source height.\\n");
    exit(1);
}

if (preg_match('/\.aznet-digital01__hero\\s+\\.wp-block-heading\\s*\\{[^}]*max-width:\\s*[0-9]+ch/i', $homepageCss)) {
    fwrite(STDERR, "FAIL: Digital 01 Hero heading hard-width cap violates D-036 desktop natural wrapping.\\n");
    exit(1);
}
// Site-specific mobile Lighthouse accessibility regression: CTA eyebrow is
// small foreground text and must meet WCAG AA 4.5:1 against the CTA surface.
preg_match('/--d01-accent:\s*(#[0-9a-f]{6})/i', $homepageCss, $accentMatch);
preg_match('/\.aznet-digital01__cta\s+\.aznet-digital01__eyebrow\s*\{\s*color:\s*(#[0-9a-f]{6})/i', $homepageCss, $eyebrowMatch);
if (empty($accentMatch[1]) || empty($eyebrowMatch[1])) {
    fwrite(STDERR, "FAIL: Digital 01 CTA eyebrow contrast tokens missing.\n");
    exit(1);
}
$relativeLuminance = static function (string $hex): float {
    $hex = ltrim($hex, '#');
    $rgb = [
        hexdec(substr($hex, 0, 2)) / 255.0,
        hexdec(substr($hex, 2, 2)) / 255.0,
        hexdec(substr($hex, 4, 2)) / 255.0,
    ];
    $linear = array_map(static fn (float $v): float =>
        $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4, $rgb);
    return $linear[0] * 0.2126 + $linear[1] * 0.7152 + $linear[2] * 0.0722;
};
$ctaLuminance = $relativeLuminance($accentMatch[1]);
$eyebrowLuminance = $relativeLuminance($eyebrowMatch[1]);
$higher = max($ctaLuminance, $eyebrowLuminance);
$lower = min($ctaLuminance, $eyebrowLuminance);
$contrast = ($higher + 0.05) / ($lower + 0.05);
if ($contrast < 4.5) {
    fwrite(STDERR, sprintf("FAIL: Digital 01 CTA eyebrow contrast %.2f:1 is below WCAG AA 4.5:1.\n", $contrast));
    exit(1);
}

// Separate real-site Lighthouse risk: featured service card is on navy, while
// its ordinal and anchor text must be legible without relying on font weight.
preg_match('/\.aznet-digital01__service:first-child\s*\{\s*background:\s*(#[0-9a-f]{6})/i', $homepageCss, $cardBgMatch);
preg_match('/\.aznet-digital01__service:first-child\s+\.aznet-digital01__service-index,\s*body\.aznet-theme-preset--digital-01\s+\.aznet-digital01__service:first-child\s+a\s*\{\s*color:\s*(#[0-9a-f]{6})/i', $homepageCss, $cardFgMatch);
if (empty($cardBgMatch[1]) || empty($cardFgMatch[1])) {
    fwrite(STDERR, "FAIL: Digital 01 featured service card small-text contrast tokens missing.\n");
    exit(1);
}
$bgLum = $relativeLuminance($cardBgMatch[1]);
$fgLum = $relativeLuminance($cardFgMatch[1]);
$cardContrast = (max($bgLum, $fgLum) + 0.05) / (min($bgLum, $fgLum) + 0.05);
if ($cardContrast < 4.5) {
    fwrite(STDERR, sprintf("FAIL: Digital 01 featured service card small-text contrast %.2f:1 below WCAG AA.\n", $cardContrast));
    exit(1);
}

// D-055: Light service-card description is normal-size text and needs AA 4.5:1.
preg_match('/\.aznet-digital01__service\s*\{\s*background:\s*(#[0-9a-f]{6})/i', $homepageCss, $lightCardBg);
preg_match('/\.aznet-digital01__service p:not\(\.aznet-digital01__service-index\)\s*\{\s*color:\s*(#[0-9a-f]{6})/i', $homepageCss, $lightCardText);
if (empty($lightCardBg[1]) || empty($lightCardText[1])) {
    fwrite(STDERR, "FAIL: Digital 01 light service-card text/background colors missing.\n");
    exit(1);
}
$lightL = $relativeLuminance($lightCardBg[1]);
$textL = $relativeLuminance($lightCardText[1]);
$serviceContrast = (max($lightL, $textL) + 0.05) / (min($lightL, $textL) + 0.05);
if ($serviceContrast < 4.5) {
    fwrite(STDERR, sprintf("FAIL: Digital 01 light service description %.2f:1 below WCAG AA.\n", $serviceContrast));
    exit(1);
}

foreach (['law-01', 'curtain-01', 'industrial-01'] as $retained) {
    if (! is_array(\AZnet\Theme\template_manifest($retained))) {
        fwrite(STDERR, "FAIL: previously accepted template missing: {$retained}.\n");
        exit(1);
    }
}
echo "PASS: Digital 01 manifest, WordPress-native pattern, scoped assets, retained templates\n";
