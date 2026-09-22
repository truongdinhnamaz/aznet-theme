<?php
/** Curtain 01 Woo category showcase contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composerPath = $root . '/inc/theme/homepage-composer.php';
$wooPath      = $root . '/inc/integrations/woocommerce.php';
$partPath     = $root . '/template-parts/homepage/curtain-01/category-showcase.php';
$cssPath      = $root . '/assets/css/components/homepage-curtain-01.css';

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([$composerPath, $wooPath, $cssPath] as $path) {
    if (!is_file($path)) {
        $fail('missing Curtain 01 category-showcase dependency: ' . basename($path));
    }
}

if (!is_file($partPath)) {
    $fail('Curtain 01 category showcase template part is missing.');
}

$composer = (string) file_get_contents($composerPath);
$woo      = (string) file_get_contents($wooPath);
$part     = (string) file_get_contents($partPath);
$css      = (string) file_get_contents($cssPath);

if (!str_contains($composer, "'about', 'category-showcase', 'catalogue'")) {
    $fail('Category showcase must render between About and Catalogue.');
}

foreach ([
    'homepage_product_category_showcase_terms',
    "'taxonomy'   => 'product_cat'",
    "'hide_empty' => true",
    "'parent'     => 0",
] as $needle) {
    if (!str_contains($woo, $needle)) {
        $fail('Woo category showcase adapter missing public taxonomy behavior: ' . $needle);
    }
}

foreach ([
    'get_term_meta(',
    'get_option(',
    '$wpdb',
    'wp_insert_post(',
    'wp_update_post(',
    'update_post_meta(',
] as $forbidden) {
    if (str_contains($woo, $forbidden)) {
        $fail('Woo category showcase adapter crosses ownership boundary: ' . $forbidden);
    }
}

foreach ([
    'homepage_product_category_showcase_terms',
    'woocommerce_subcategory_thumbnail',
    'woocommerce-placeholder',
    'get_term_link',
    'aznet-theme-curtain01-category-showcase',
    'Khám phá theo dòng rèm',
] as $needle) {
    if (!str_contains($part, $needle)) {
        $fail('Curtain 01 category showcase template missing behavior marker: ' . $needle);
    }
}

if (str_contains($part, 'get_term_meta(')) {
    $fail('Curtain 01 category showcase must not read WooCommerce category image storage directly.');
}

foreach ([
    '.aznet-theme-curtain01-category-showcase',
    '.aznet-theme-curtain01-category-showcase__grid',
    '.aznet-theme-curtain01-category-showcase__card',
    '.aznet-theme-curtain01-category-showcase__media',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Curtain 01 category showcase CSS missing: ' . $needle);
    }
}

echo "PASS: Curtain 01 category showcase uses public WooCommerce category output and fails soft\n";
