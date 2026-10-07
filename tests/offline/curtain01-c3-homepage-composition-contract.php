<?php
/** Curtain 01 C3 Homepage composition contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root . '/inc/theme/homepage-composer.php';
$wooPath      = $root . '/inc/integrations/woocommerce.php';
$cssPath      = $root . '/assets/css/components/homepage-curtain-01.css';

$composer = file_get_contents($composerPath);
$woo      = file_get_contents($wooPath);
$css      = file_get_contents($cssPath);
$front    = file_get_contents($root . '/front-page.php');

if (! is_string($composer) || ! str_contains($composer, 'render_curtain01_part')) {
    fwrite(STDERR, "FAIL: Curtain 01 section renderer is missing.\n");
    exit(1);
}

foreach (['hero', 'about', 'catalogue', 'knowledge', 'final-cta'] as $slug) {
    $part = $root . '/template-parts/homepage/curtain-01/' . $slug . '.php';
    if (! is_file($part)) {
        fwrite(STDERR, "FAIL: Curtain 01 Homepage part missing: {$slug}.\n");
        exit(1);
    }
}

$surfaceMap = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
if (
    ! str_contains($composer, "homepage_effective_surface_map( 'curtain-01' )")
    || ! is_string($surfaceMap)
    || ! str_contains($surfaceMap, "'hero',")
    || ! str_contains($surfaceMap, "'before',")
) {
    fwrite(STDERR, "FAIL: Curtain 01 Hero must remain on the shared effective surface map before the native Front Page body.\n");
    exit(1);
}

foreach (['about', 'catalogue', 'knowledge', 'final-cta'] as $slug) {
    if (! str_contains((string) $surfaceMap, "'{$slug}'")) {
        fwrite(STDERR, "FAIL: Curtain 01 shared surface map missing post-content surface: {$slug}.\n");
        exit(1);
    }
}

if (! is_string($woo) || ! str_contains($woo, 'homepage_products') || ! str_contains($woo, 'homepage_product_categories')) {
    fwrite(STDERR, "FAIL: WooCommerce public Homepage adapter is missing.\n");
    exit(1);
}

foreach (['wc_get_products(', "get_terms("] as $required) {
    if (! str_contains($woo, $required)) {
        fwrite(STDERR, "FAIL: WooCommerce adapter must use public APIs: {$required}\n");
        exit(1);
    }
}

foreach (['get_post_meta(', 'get_option(', '$wpdb', 'wp_insert_post(', 'wp_update_post(', 'update_post_meta('] as $forbidden) {
    if (str_contains((string) $woo, $forbidden)) {
        fwrite(STDERR, "FAIL: WooCommerce Homepage adapter crosses ownership boundary: {$forbidden}\n");
        exit(1);
    }
}

$hero = file_get_contents($root . '/template-parts/homepage/curtain-01/hero.php');
$about = file_get_contents($root . '/template-parts/homepage/curtain-01/about.php');
$catalogue = file_get_contents($root . '/template-parts/homepage/curtain-01/catalogue.php');
$knowledge = file_get_contents($root . '/template-parts/homepage/curtain-01/knowledge.php');
$cta = file_get_contents($root . '/template-parts/homepage/curtain-01/final-cta.php');

if (! str_contains((string) $hero, 'homepage_block_reference') || ! str_contains((string) $hero, 'do_blocks')) {
    fwrite(STDERR, "FAIL: Curtain 01 Hero must consume the WordPress-owned Hero block.\n");
    exit(1);
}

foreach ([
    "homepage_effective_source_value( 'curtain-01', 'contact' )",
    'Integrations\\WooCommerce\\shop_url',
    'aznet-theme-curtain01-hero__library-cta',
] as $heroCtaContract) {
    if (! str_contains((string) $hero, $heroCtaContract)) {
        fwrite(STDERR, "FAIL: Curtain 01 library Hero must expose mapped presentation CTAs without owning destination data: {$heroCtaContract}\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-curtain01-hero--library .wp-block-cover:first-child .screen-reader-text {',
    'position: static !important;',
    '.aznet-theme-curtain01-hero__library-cta {',
] as $heroPresentationRule) {
    if (! str_contains((string) $css, $heroPresentationRule)) {
        fwrite(STDERR, "FAIL: Curtain 01 library Hero must visibly present the authored first-slide message and CTA layer: {$heroPresentationRule}\n");
        exit(1);
    }
}

if (! str_contains((string) $about, "homepage_effective_source_value( 'curtain-01', 'about' )")) {
    fwrite(STDERR, "FAIL: Curtain 01 About must resolve its explicit typed Page through the preset-scoped source resolver.\n");
    exit(1);
}

foreach (["get_the_excerpt(", "wp_strip_all_tags(", "wp_trim_words("] as $forbiddenAboutSummary) {
    if (str_contains((string) $about, $forbiddenAboutSummary)) {
        fwrite(STDERR, "FAIL: Curtain 01 About must not auto-extract summary text from complex Page content: {$forbiddenAboutSummary}\n");
        exit(1);
    }
}

if (! str_contains((string) $about, "post_excerpt")) {
    fwrite(STDERR, "FAIL: Curtain 01 About summary must consume only the explicitly authored Page excerpt.\n");
    exit(1);
}

foreach ([
    '.aznet-theme-curtain01-about__content h2 {',
    'max-width: 20ch;',
    'overflow-wrap: normal;',
    'word-break: normal;',
    'hyphens: none;',
    'text-wrap: balance;',
] as $aboutTypographyRule) {
    if (! str_contains((string) $css, $aboutTypographyRule)) {
        fwrite(STDERR, "FAIL: Curtain 01 About heading must preserve natural Vietnamese word shaping/wrapping: {$aboutTypographyRule}\n");
        exit(1);
    }
}

foreach ([
    '@media (min-width: 64rem) {',
    '.aznet-theme-curtain01-hero__content h1,',
    '.aznet-theme-curtain01-section-heading h2,',
    '.aznet-theme-curtain01-about__content h2,',
    '.aznet-theme-curtain01-final-cta h2 {',
    'max-width: none;',
] as $desktopHeadingRule) {
    if (! str_contains((string) $css, $desktopHeadingRule)) {
        fwrite(STDERR, "FAIL: Curtain 01 desktop headings must use the available row width before wrapping: {$desktopHeadingRule}\n");
        exit(1);
    }
}

if (str_contains((string) $css, 'white-space: nowrap;')) {
    fwrite(STDERR, "FAIL: Curtain 01 must not force headings onto one line when the real container is too narrow.\n");
    exit(1);
}

if (
    ! str_contains((string) $catalogue, 'homepage_curtain01_catalogue_model')
    || ! str_contains((string) $surfaceMap, 'Integrations\\WooCommerce\\homepage_products')
    || ! str_contains((string) $surfaceMap, 'Integrations\\WooCommerce\\homepage_product_categories')
) {
    fwrite(STDERR, "FAIL: Curtain 01 catalogue must consume the bounded public WooCommerce adapter through its shared catalogue model.\n");
    exit(1);
}

if (str_contains((string) $catalogue, 'get_price_html') || str_contains((string) $catalogue, 'get_price(')) {
    fwrite(STDERR, "FAIL: Curtain 01 Homepage catalogue must not reinterpret quote/price semantics.\n");
    exit(1);
}

if (! str_contains((string) $knowledge, 'homepage_latest_posts')) {
    fwrite(STDERR, "FAIL: Curtain 01 Knowledge must use mapped WordPress categories.\n");
    exit(1);
}

if (! str_contains((string) $knowledge, "get_the_date( 'd/m/Y', $post )")) {
    fwrite(STDERR, "FAIL: Curtain 01 Knowledge must present dates in an explicit Vietnamese day/month/year format.\n");
    exit(1);
}

if (! str_contains((string) $cta, 'contact_surface_model') || ! str_contains((string) $cta, "homepage_effective_source_value( 'curtain-01', 'contact' )")) {
    fwrite(STDERR, "FAIL: Curtain 01 CTA must hand off through mapped WordPress/public contact sources.\n");
    exit(1);
}

if (! is_string($css)) {
    fwrite(STDERR, "FAIL: Curtain 01 stylesheet missing.\n");
    exit(1);
}

foreach ([
    '.aznet-theme-curtain01-hero',
    '.aznet-theme-curtain01-about',
    '.aznet-theme-curtain01-catalogue',
    '.aznet-theme-curtain01-knowledge',
    '.aznet-theme-curtain01-final-cta',
] as $selector) {
    if (! str_contains($css, $selector)) {
        fwrite(STDERR, "FAIL: Curtain 01 presentation selector missing: {$selector}\n");
        exit(1);
    }
}

if (substr_count((string) $front, 'the_content();') !== 1) {
    fwrite(STDERR, "FAIL: Front Page must retain exactly one native the_content() boundary.\n");
    exit(1);
}

foreach (['wp_insert_post(', 'wp_update_post(', 'wp_delete_post(', 'update_post_meta(', 'update_option('] as $forbidden) {
    if (str_contains((string) $composer, $forbidden)) {
        fwrite(STDERR, "FAIL: Curtain 01 composition must remain mutation-free: {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 C3 Homepage composition contract\n";
