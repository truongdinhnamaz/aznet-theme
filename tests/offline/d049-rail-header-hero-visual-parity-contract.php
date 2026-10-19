<?php
/**
 * D-049 follow-up: visually align the optional Core Header category band
 * with the Industrial 01 Hero rail without duplicating WordPress menu data.
 *
 * RED -> GREEN static contract. Browser geometry/a11y gates are separate.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$header = (string) file_get_contents($root . '/template-parts/header/site-header.php');
$css = (string) file_get_contents($root . '/assets/css/components/catalog-navigation.css');
$hero = (string) file_get_contents($root . '/template-parts/homepage/industrial-01/hero.php');
$check = static function (bool $ok, string $name): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: D-049 visual alignment: {$name}\n");
        exit(1);
    }
};

$check(str_contains($header, 'aznet-theme-site-header__catalog-rail-label'),
    'the Header must render a category label above the Hero rail');
$check(str_contains($header, "! empty( \$context['catalog_home_rail'] )"),
    'Header label is strictly opt-in and only on the rail-supporting homepage');
$check(str_contains($css, '--aznet-theme-catalog-rail-width:'),
    'Header and Hero rail share one sizing token');
$check(str_contains($css, '.aznet-theme-site-header--catalog-home-rail .aznet-theme-site-header__catalog-rail-label'),
    'Header category label has a scoped presentation');
$check(str_contains($css, '.aznet-theme-site-header--catalog-home-rail .aznet-theme-site-header__nav'),
    'main navigation is positioned alongside the category label');
$check(!str_contains($css, '.aznet-theme-site-header--catalog-home-rail .aznet-theme-site-header__nav { grid-column: 1 / 4; }'),
    'main navigation may not take over the left category-label column');
$check(str_contains($css, 'grid-template-columns: var(--aznet-theme-catalog-rail-width) minmax(0, 1fr)'),
    'the Header and Hero rail must use the same first grid track');
$check(str_contains($css, '.aznet-theme-industrial01-hero--with-catalog .aznet-theme-industrial01-hero__catalog-heading'),
    'the Hero rail label is not visually duplicated under the Header label');
$check(str_contains($css, '@media (max-width: 1100px)'),
    'the category label and side rail yield to mobile navigation');
$check(str_contains($hero, 'header_catalog_menu_html'),
    'Hero still consumes WordPress native assigned catalog');
$check(!str_contains($header, 'get_terms(') && !str_contains($css, 'choiceguide_'),
    'presentation must not infer catalog data or depend on another plugin');
echo "PASS: D-049 header/Hero category-rail visual parity contract\n";
