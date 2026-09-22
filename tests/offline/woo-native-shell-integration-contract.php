<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$setupPath = $root . '/inc/theme/setup.php';
$presentationPath = $root . '/inc/theme/woocommerce-presentation.php';
$cssPath = $root . '/assets/css/components/woocommerce-product.css';

foreach ([$setupPath, $presentationPath, $cssPath] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, "FAIL: missing required production file: {$path}\n");
        exit(1);
    }
}

$setup = (string) file_get_contents($setupPath);
$presentation = (string) file_get_contents($presentationPath);
$css = (string) file_get_contents($cssPath);

$fail = static function (string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

if (!preg_match("/add_theme_support\s*\(\s*'woocommerce'\s*\)/", $setup)) {
    $fail('AZnet Theme must declare native WooCommerce theme support');
}

foreach ([
    "remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 )",
    "remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 )",
    "add_action( 'woocommerce_before_main_content', __NAMESPACE__ . '\\\\woocommerce_content_wrapper_start', 10 )",
    "add_action( 'woocommerce_after_main_content', __NAMESPACE__ . '\\\\woocommerce_content_wrapper_end', 10 )",
] as $needle) {
    if (false === strpos($presentation, $needle)) {
        $fail('missing public Woo wrapper wiring: ' . $needle);
    }
}

if (false === strpos($presentation, 'function woocommerce_content_wrapper_start(): void')) {
    $fail('Theme-owned Woo wrapper start function is missing');
}
if (false === strpos($presentation, 'function woocommerce_content_wrapper_end(): void')) {
    $fail('Theme-owned Woo wrapper end function is missing');
}
if (false === strpos($presentation, 'class="aznet-theme-main aznet-theme-main--woocommerce"')) {
    $fail('Woo wrapper must preserve the canonical aznet-theme-main presentation shell');
}
if (false === strpos($presentation, '</main>')) {
    $fail('Woo wrapper must close the main landmark');
}

if (!preg_match('/\.single-product\s+\.aznet-theme-main\s+\.product\s*>\s*\*\s*\{[^}]*grid-column\s*:\s*1\s*\/\s*-1/is', $css)) {
    $fail('direct Product extension children must default to full-width grid placement');
}
if (!preg_match('/\.single-product\s+\.aznet-theme-main\s+\.product\s*>\s*\.woocommerce-product-gallery\s*\{[^}]*grid-column\s*:\s*1\b/is', $css)) {
    $fail('native Woo gallery must be explicitly placed in product grid column 1');
}
if (!preg_match('/\.single-product\s+\.aznet-theme-main\s+\.product\s*>\s*\.summary\s*\{[^}]*grid-column\s*:\s*2\b/is', $css)) {
    $fail('native Woo summary must be explicitly placed in product grid column 2');
}

foreach (['choiceguide_', '.choiceguide-', 'convertflow'] as $forbidden) {
    if (false !== stripos($presentation . "\n" . $css, $forbidden)) {
        $fail('Theme Product shell must stay provider-agnostic; found forbidden coupling: ' . $forbidden);
    }
}

echo "PASS: Woo native Single Product shell integration regression\n";
