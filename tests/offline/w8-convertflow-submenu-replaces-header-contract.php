<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$css = (string) file_get_contents($root . '/assets/css/integrations/convertflow.css');
$jsFile = $root . '/assets/js/convertflow-sticky-submenu.js';

$requiredAssets = [
    "aznet-theme-convertflow-sticky-submenu",
    "/assets/js/convertflow-sticky-submenu.js",
    "should_enqueue_woocommerce_product_assets()",
];

foreach ($requiredAssets as $needle) {
    if (false === strpos($assets, $needle)) {
        fwrite(STDERR, "missing submenu replacement asset contract: {$needle}\n");
        exit(1);
    }
}

if (!is_file($jsFile)) {
    fwrite(STDERR, "missing submenu replacement script\n");
    exit(2);
}

$js = (string) file_get_contents($jsFile);
foreach ([
    '[data-choiceguide-sticky-submenu]',
    '[data-aznet-theme-site-header]',
    '[data-choiceguide-product-journey]',
    'is-aznet-theme-submenu-replacing-header',
    'requestAnimationFrame',
    'window.scrollY',
] as $needle) {
    if (false === strpos($js, $needle)) {
        fwrite(STDERR, "missing submenu replacement runtime contract: {$needle}\n");
        exit(3);
    }
}

foreach ([
    'body.is-aznet-theme-submenu-replacing-header [data-aznet-theme-site-header]',
    'body.is-aznet-theme-submenu-replacing-header [data-choiceguide-sticky-submenu]',
    'transform: translateY(-100%)',
    'top: 32px',
    'top: 46px',
] as $needle) {
    if (false === strpos($css, $needle)) {
        fwrite(STDERR, "missing submenu replacement presentation contract: {$needle}\n");
        exit(4);
    }
}

echo "PASS: sticky submenu replaces main header when active\n";
