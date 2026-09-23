<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$setupPath = $root . '/inc/theme/setup.php';
$presentationPath = $root . '/inc/theme/woocommerce-presentation.php';
$cssPath = $root . '/assets/css/components/woocommerce-product.css';
$shopSidebarPath = $root . '/sidebar-shop.php';

foreach ([$setupPath, $presentationPath, $cssPath, $shopSidebarPath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "FAIL: missing required production file: {$path}\n");
        exit(1);
    }
}

$setup = (string) file_get_contents($setupPath);
$presentation = (string) file_get_contents($presentationPath);
$css = (string) file_get_contents($cssPath);
$shopSidebar = (string) file_get_contents($shopSidebarPath);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

if (!preg_match("/add_theme_support\\s*\\(\\s*'woocommerce'\\s*\\)/", $setup)) {
    $fail('AZnet Theme must declare native WooCommerce theme support');
}
foreach (['wc-product-gallery-zoom', 'wc-product-gallery-lightbox', 'wc-product-gallery-slider'] as $feature) {
    if (false === strpos($setup, "add_theme_support( '{$feature}' );")) {
        $fail('missing native Woo gallery support: ' . $feature);
    }
}
if (preg_match('/(?:remove|add)_action\\s*\\(\\s*[\'\"]woocommerce_(?:before|after)_main_content/i', $presentation)) {
    $fail('Theme must not take over WooCommerce main-content wrapper hooks');
}
if (false !== stripos($shopSidebar, 'dynamic_sidebar(') || false !== stripos($shopSidebar, 'get_sidebar(')) {
    $fail('Woo shop sidebar boundary must not reintroduce theme-compat/sidebar state');
}

if (!preg_match('/\\.single-product\\s+#main\\s+\\.product\\s*>\\s*\\*\\s*\\{[^}]*grid-column\\s*:\\s*1\\s*\\/\\s*-1/is', $css)) {
    $fail('direct Product extension children must default to full-width grid placement on native Woo #main');
}
if (!preg_match('/\\.single-product\\s+#main\\s+\\.product\\s*>\\s*\\.woocommerce-product-gallery\\s*\\{[^}]*grid-column\\s*:\\s*1\\b/is', $css)) {
    $fail('native Woo gallery must be explicitly placed in product grid column 1');
}
if (!preg_match('/\\.single-product\\s+#main\\s+\\.product\\s*>\\s*\\.summary\\s*\\{[^}]*grid-column\\s*:\\s*2\\b/is', $css)) {
    $fail('native Woo summary must be explicitly placed in product grid column 2');
}
if (!preg_match('/\\.single-product\\s+#main\\s+\\.price\\s*\\{[^}]*color\\s*:\\s*var\\(--aznet-theme-text/is', $css)) {
    $fail('native Woo price must receive accessible Theme text color on the actual #main wrapper');
}

if (!preg_match('/\\.single-product\\s+#main\\s+\\.variations\\s+select\\s*\\{[^}]*margin\\s*:\\s*0\\s*;/is', $css)) {
    $fail('native Woo variation select must reset the provider 1em right margin to prevent page overflow');
}

foreach (['choiceguide_', '.choiceguide-', 'convertflow'] as $forbidden) {
    if (false !== stripos($presentation . "\n" . $css, $forbidden)) {
        $fail('Theme Product shell must stay provider-agnostic; found forbidden coupling: ' . $forbidden);
    }
}

echo "PASS: Woo native Single Product shell integration regression\n";
