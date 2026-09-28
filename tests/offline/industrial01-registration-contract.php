<?php
/**
 * Industrial 01 MVP registration and lexicon-isolation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings = file_get_contents($root . '/inc/theme/settings.php');
$design = file_get_contents($root . '/inc/theme/design-system.php');
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');

foreach ([
    [$settings, "'industrial-01'", 'Settings must register Industrial 01.'],
    [$design, "'industrial-01'", 'Visual preset allow-list must register Industrial 01.'],
    [$composer, "'industrial-01'", 'Homepage Composer must register Industrial 01.'],
] as [$source, $needle, $message]) {
    assert(is_string($source) && str_contains($source, $needle), $message);
}

$lexiconPath = $root . '/inc/theme/preset-lexicon.php';
assert(is_file($lexiconPath), 'Preset lexicon adapter must exist.');
$lexicon = file_get_contents($lexiconPath);
assert(is_string($lexicon));
assert(str_contains($lexicon, 'function preset_lexicon('), 'Theme must expose one presentation-only preset lexicon adapter.');
assert(str_contains($lexicon, "'industrial-01'"), 'Industrial 01 lexicon must be present.');
assert(str_contains($lexicon, "'law-01'"), 'Law 01 lexicon must remain isolated.');
assert(str_contains($lexicon, "'curtain-01'"), 'Curtain 01 lexicon must remain isolated.');

foreach ([
    'Danh mục thiết bị',
    'Thiết bị & phụ kiện',
    'Yêu cầu báo giá',
    'Kiến thức kỹ thuật',
] as $industrialTerm) {
    assert(str_contains($lexicon, $industrialTerm), "Industrial 01 term missing: {$industrialTerm}");
}

foreach ([
    'Lĩnh vực hành nghề',
    'Luật sư',
    'Dòng rèm',
] as $foreignTerm) {
    $industrialBlock = preg_match("/'industrial-01'\s*=>\s*\[(.*?)\n\s*\],/s", $lexicon, $matches) ? ($matches[1] ?? '') : '';
    assert(! str_contains($industrialBlock, $foreignTerm), "Foreign preset term leaked into Industrial 01: {$foreignTerm}");
}

$composerSource = file_get_contents($root . '/inc/theme/homepage-composer.php');
$assetsSource = file_get_contents($root . '/inc/theme/assets.php');
assert(is_string($composerSource) && str_contains($composerSource, 'render_industrial01_part'));
assert(str_contains($composerSource, "[ 'hero', 'categories', 'products' ]"), 'Industrial 01 MVP must render the approved product-first homepage order before native content.');
assert(str_contains($composerSource, "render_industrial01_part( 'cta' )"), 'Industrial 01 MVP must close with its quote CTA after native content.');
assert(is_string($assetsSource) && str_contains($assetsSource, 'enqueue_homepage_industrial01_asset'));
assert(str_contains($assetsSource, 'homepage-industrial-01.css'));

foreach (['hero', 'categories', 'products', 'cta'] as $section) {
    $path = $root . '/template-parts/homepage/industrial-01/' . $section . '.php';
    assert(is_file($path), "Industrial 01 homepage section missing: {$section}");
}

$categories = file_get_contents($root . '/template-parts/homepage/industrial-01/categories.php');
$products = file_get_contents($root . '/template-parts/homepage/industrial-01/products.php');
assert(is_string($categories) && str_contains($categories, "taxonomy_exists( 'product_cat' )"), 'Industrial categories must fail soft when WooCommerce taxonomy is absent.');
assert(str_contains($categories, "get_terms("), 'Industrial categories must consume public WordPress taxonomy APIs.');
assert(is_string($products) && str_contains($products, "function_exists( 'wc_get_products' )"), 'Industrial products must fail soft when WooCommerce is absent.');
assert(str_contains($products, 'wc_get_products('), 'Industrial products must consume WooCommerce public product APIs.');
foreach (['get_option(', 'get_post_meta(', 'update_option(', 'update_post_meta('] as $privateStoreNeedle) {
    assert(! str_contains($categories . $products, $privateStoreNeedle), "Industrial 01 must not read or mutate private/domain storage directly: {$privateStoreNeedle}");
}

$adminSource = file_get_contents($root . '/inc/admin/homepage.php');
assert(is_string($adminSource) && str_contains($adminSource, "'template_id'   => 'industrial-01'"), 'Industrial 01 must be selectable in the Theme Template Library.');
assert(str_contains($adminSource, "'name'          => __( 'Industrial 01', 'aznet-theme' )"), 'Industrial 01 Template Library card label must be present.');
assert(str_contains($adminSource, 'website B2B bán thiết bị công nghiệp và phụ kiện'), 'Industrial 01 admin copy must describe its reusable B2B industrial purpose.');

$homepageCssPath = $root . '/assets/css/components/homepage-industrial-01.css';
assert(is_file($homepageCssPath), 'Industrial 01 homepage stylesheet must exist.');
$homepageCss = file_get_contents($homepageCssPath);
assert(is_string($homepageCss) && str_contains($homepageCss, '.aznet-theme-homepage--industrial-01'));
assert(! str_contains($homepageCss, 'minhnguyen'), 'Client identity must not leak into reusable Industrial 01 homepage presentation.');

$industrialCssPath = $root . '/assets/css/presets/industrial-01.css';
assert(is_file($industrialCssPath), 'Industrial 01 visual preset stylesheet must exist.');
$industrialCss = file_get_contents($industrialCssPath);
assert(is_string($industrialCss) && str_contains($industrialCss, 'body.aznet-theme-preset--industrial-01'));
assert(! str_contains($industrialCss, 'minhnguyen'), 'Client identity must not be hard-coded into the reusable Industrial 01 preset.');

echo "PASS: Industrial 01 registration and lexicon isolation contract\n";
