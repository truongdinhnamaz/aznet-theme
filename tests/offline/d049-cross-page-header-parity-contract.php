<?php
/**
 * D-049 live-pilot regression: one Core catalog Header across WordPress surfaces.
 * RED before the CSS fix; browser geometry/keyboard/a11y is the L4 gate.
 */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/catalog-navigation.css');
$header = (string) file_get_contents($root . '/template-parts/header/site-header.php');

$check = static function (bool $ok, string $what): void {
    if (!$ok) {
        fwrite(STDERR, "FAIL: D-049 cross-page catalog Header: {$what}\n");
        exit(1);
    }
};

$check(
    1 === preg_match(
        '/\.aznet-theme-site-header--catalog,\s*\.aznet-theme-industrial01-hero--with-catalog\s*\{\s*--aznet-theme-catalog-rail-width:/',
        $css
    ),
    'homepage and inner Headers must reuse one column-width token'
);

$check(str_contains($css, '.aznet-theme-site-header--catalog .aznet-theme-site-header__brand-title'),
    'brand text must render with native logo on every catalog-enabled desktop surface');

$check(str_contains($css, '.aznet-theme-site-header--catalog .aznet-theme-site-header__inner {'),
    'every catalog-enabled desktop Header must use the same shell and grid');

$check(str_contains($css, '.aznet-theme-site-header--catalog .aznet-theme-site-header__nav .aznet-theme-site-header__menu'),
    'primary navigation must start beside product-category control, not jump right on inner pages');

$check(
    1 === preg_match(
        '/\.aznet-theme-industrial01-hero--with-catalog \.aznet-theme-industrial01-hero__catalog\s*\{([^}]*)\}/s',
        $css,
        $rail
    ) && 1 === preg_match('/\balign-self:\s*start\s*;/', $rail[1]),
    'short native catalog menu must size to content rather than stretch over full Hero'
);

$check(str_contains($header, 'catalog_home_rail') && str_contains($header, 'catalog_menu'),
    'only one WordPress catalog menu is composed on each surface');

echo "PASS: D-049 cross-page catalog Header and compact Hero menu contract\n";
