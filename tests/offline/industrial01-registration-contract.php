<?php
/**
 * Industrial 01 MVP registration and lexicon-isolation contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
if (! defined('ABSPATH')) {
    define('ABSPATH', $root . '/');
}
require_once $root . '/inc/theme/settings.php';
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');

assert(in_array('industrial-01', AZnet\Theme\homepage_preset_ids(), true), 'Template Registry must register Industrial 01 Homepage preset.');
assert(in_array('industrial-01', AZnet\Theme\visual_preset_ids(), true), 'Template Registry must register Industrial 01 visual preset.');
assert(is_string($composer) && str_contains($composer, "'industrial-01'"), 'Homepage Composer must retain Industrial 01 until C3 migration.');

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
$industrialManifestSource = file_get_contents($root . '/inc/theme/templates/industrial-01/manifest.php');
assert(is_string($composerSource) && str_contains($composerSource, 'render_industrial01_part'));
assert(str_contains($composerSource, "homepage_effective_surface_map( 'industrial-01' )"), 'Industrial 01 composition must consume the shared effective Homepage surface map.');
assert(! str_contains($composerSource, "[ 'hero', 'categories', 'products' ]"), 'Industrial 01 section order must not remain duplicated in the Composer.');
assert(! str_contains($composerSource, "render_industrial01_part( 'cta' )"), 'Industrial 01 CTA must be routed through the shared surface map.');
assert(is_string($assetsSource) && str_contains($assetsSource, 'enqueue_homepage_industrial01_asset'));
assert(str_contains($assetsSource, 'template_homepage_asset_descriptors'), 'Industrial 01 Homepage assets must consume the generic manifest asset registry.');
assert(is_string($industrialManifestSource) && str_contains($industrialManifestSource, 'homepage-industrial-01.css'));

foreach (['hero', 'categories', 'products', 'cta'] as $section) {
    $path = $root . '/template-parts/homepage/industrial-01/' . $section . '.php';
    assert(is_file($path), "Industrial 01 homepage section missing: {$section}");
}

$categories = file_get_contents($root . '/template-parts/homepage/industrial-01/categories.php');
$products = file_get_contents($root . '/template-parts/homepage/industrial-01/products.php');
assert(is_string($categories) && str_contains($categories, 'Integrations\\WooCommerce\\homepage_product_category_showcase_terms'), 'Industrial categories must consume the bounded WooCommerce integration adapter.');
assert(is_string($products) && str_contains($products, 'Integrations\\WooCommerce\\homepage_products'), 'Industrial products must consume the bounded WooCommerce integration adapter.');
$heroSource = file_get_contents($root . '/template-parts/homepage/industrial-01/hero.php');
assert(is_string($heroSource) && str_contains($heroSource, 'Integrations\\WooCommerce\\shop_url'), 'Industrial shop CTA must consume the bounded WooCommerce integration adapter.');
assert(str_contains($heroSource, "homepage_effective_source_value( 'industrial-01', 'about' )"), 'Industrial Hero should reuse mapped WordPress-owned About copy when explicit Hero lead is empty.');
assert(str_contains($heroSource, 'strip_shortcodes'), 'Industrial Hero fallback lead must remove legacy builder shortcodes.');
assert(str_contains($heroSource, 'preg_replace'), 'Industrial Hero fallback lead must remove unregistered legacy shortcode wrappers.');
assert(str_contains($aboutSource, 'preg_replace'), 'Industrial About summary must remove unregistered legacy shortcode wrappers.');
foreach (['get_option(', 'get_post_meta(', 'update_option(', 'update_post_meta(', 'wc_get_products(', 'get_terms('] as $privateStoreNeedle) {
    assert(! str_contains($categories . $products, $privateStoreNeedle), "Industrial 01 must not read or mutate private/domain storage directly: {$privateStoreNeedle}");
}

$controlCenterSource = file_get_contents($root . '/inc/admin/control-center.php');
assert('Industrial 01' === (AZnet\Theme\visual_preset_choices()['industrial-01'] ?? null), 'Industrial 01 visual preset must be projected from Template Registry metadata.');
assert(is_string($controlCenterSource) && str_contains($controlCenterSource, 'visual_preset_choices()'), 'Design and Quick Setup must consume manifest-driven visual choices.');

$adminSource = file_get_contents($root . '/inc/admin/homepage.php');
$industrialManifest = AZnet\Theme\template_manifest('industrial-01');
assert(is_array($industrialManifest), 'Industrial 01 manifest must be registered.');
assert('Industrial 01' === ($industrialManifest['name'] ?? null), 'Industrial 01 Template Library label must come from manifest metadata.');
assert('Website B2B bán thiết bị công nghiệp và phụ kiện.' === ($industrialManifest['description'] ?? null), 'Industrial 01 reusable B2B description must come from manifest metadata.');
assert(is_string($adminSource) && str_contains($adminSource, 'template_manifests()'), 'Theme Template Library must consume Template Manifest Registry.');

$homepageCssPath = $root . '/assets/css/components/homepage-industrial-01.css';
assert(is_file($homepageCssPath), 'Industrial 01 homepage stylesheet must exist.');
$homepageCss = file_get_contents($homepageCssPath);
assert(is_string($homepageCss) && str_contains($homepageCss, '.aznet-theme-homepage--industrial-01'));
assert(! str_contains($homepageCss, 'minhnguyen'), 'Client identity must not leak into reusable Industrial 01 homepage presentation.');
assert(str_contains($homepageCss, '.aznet-theme-industrial01-cta .aznet-theme-industrial01-kicker'), 'Industrial 01 dark CTA must provide an explicit accessible kicker color.');
assert(str_contains((string) $products, 'aria-label="<?php echo esc_attr( $product->get_name() ); ?>"'), 'Industrial 01 product media links must have discernible accessible names.');


$industrialCssPath = $root . '/assets/css/presets/industrial-01.css';
assert(is_file($industrialCssPath), 'Industrial 01 visual preset stylesheet must exist.');
$industrialCss = file_get_contents($industrialCssPath);
assert(is_string($industrialCss) && str_contains($industrialCss, 'body.aznet-theme-preset--industrial-01'));
assert(! str_contains($industrialCss, 'minhnguyen'), 'Client identity must not be hard-coded into the reusable Industrial 01 preset.');
foreach ([
    'body.aznet-theme-preset--industrial-01 .aznet-theme-site-header',
    'body.aznet-theme-preset--industrial-01 .aznet-theme-site-footer',
    'body.aznet-theme-preset--industrial-01 .woocommerce ul.products li.product',
    'body.aznet-theme-preset--industrial-01.single-product #main .woocommerce-product-gallery',
    'body.aznet-theme-preset--industrial-01.single-product #main .summary',
    '@media (max-width: 48rem)',
] as $pilotReadySelector) {
    assert(str_contains($industrialCss, $pilotReadySelector), "Industrial 01 pilot-ready presentation missing: {$pilotReadySelector}");
}
assert(! str_contains($industrialCss, 'display: none'), 'Industrial 01 preset must not hide WooCommerce truth-bearing surfaces to simulate a design.');




$authoringSource = file_get_contents($root . '/inc/theme/homepage-authoring.php');
assert(is_string($authoringSource), 'Homepage authoring registry must be readable.');
foreach ([
    "'about'       => [ 'type' => 'page', 'key' => 'homepage_industrial01_about_page'",
    "'process'     => [ 'type' => 'page', 'key' => 'homepage_industrial01_process_page'",
    "'knowledge'   => [ 'type' => 'categories', 'key' => 'homepage_industrial01_knowledge_terms'",
    "'contact'     => [ 'type' => 'page', 'key' => 'homepage_industrial01_contact_page'",
] as $industrialSourceDescriptor) {
    assert(str_contains($authoringSource, $industrialSourceDescriptor), "Industrial 01 source mapping missing: {$industrialSourceDescriptor}");
}

foreach (['about', 'process', 'knowledge'] as $section) {
    $path = $root . '/template-parts/homepage/industrial-01/' . $section . '.php';
    assert(is_file($path), "Industrial 01 standard homepage section missing: {$section}");
}

$surfaceMapSource = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
assert(is_string($surfaceMapSource));
foreach (["'about'", "'process'", "'knowledge'"] as $surfaceKey) {
    assert(str_contains($surfaceMapSource, $surfaceKey), "Industrial 01 effective surface missing: {$surfaceKey}");
}

$aboutSource = file_get_contents($root . '/template-parts/homepage/industrial-01/about.php');
$processSource = file_get_contents($root . '/template-parts/homepage/industrial-01/process.php');
$knowledgeSource = file_get_contents($root . '/template-parts/homepage/industrial-01/knowledge.php');
assert(is_string($aboutSource) && str_contains($aboutSource, "homepage_effective_source_value( 'industrial-01', 'about' )"), 'Industrial 01 About must consume a typed WordPress Page reference.');
assert(is_string($processSource) && str_contains($processSource, "homepage_effective_source_value( 'industrial-01', 'process' )"), 'Industrial 01 Process must consume a typed WordPress Page reference.');
assert(is_string($knowledgeSource) && str_contains($knowledgeSource, "homepage_effective_source_value( 'industrial-01', 'knowledge' )"), 'Industrial 01 Knowledge must consume typed WordPress categories.');
assert(str_contains($aboutSource, 'strip_shortcodes'), 'Industrial 01 About must strip legacy builder shortcodes from homepage summary copy.');
assert(! str_contains($processSource, 'echo wp_kses_post( $content )'), 'Industrial 01 Process must not dump the complete mapped service page into the Homepage.');
assert(str_contains($processSource, 'preg_match_all'), 'Industrial 01 Process must derive compact presentation cards from authored service headings.');
assert(str_contains($processSource, 'wp_trim_words'), 'Industrial 01 Process must render a bounded source-derived summary.');
assert(str_contains((string) $categories, 'woocommerce_subcategory_thumbnail'), 'Industrial 01 category cards should use WooCommerce public thumbnail presentation when available.');
assert(str_contains((string) $products, 'aznet-theme-industrial01-product-card__action'), 'Industrial 01 product cards need a neutral detail action without inventing commerce state.');

foreach ([
    '.aznet-theme-industrial01-about',
    '.aznet-theme-industrial01-process',
    '.aznet-theme-industrial01-knowledge',
    '.aznet-theme-industrial01-category-card__media',
    '.aznet-theme-industrial01-product-card__action',
] as $standardSelector) {
    assert(str_contains($homepageCss, $standardSelector), "Industrial 01 standard presentation selector missing: {$standardSelector}");
}

echo "PASS: Industrial 01 registration and lexicon isolation contract\n";
