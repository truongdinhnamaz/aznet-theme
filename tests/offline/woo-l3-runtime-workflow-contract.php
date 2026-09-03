<?php
$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/woo-l3-runtime.yml';
$fixture = $root . '/tests/runtime/woo-l3-fixtures.php';
$smoke = $root . '/scripts/verify-woo-l3-runtime.sh';

foreach ([$workflow, $fixture, $smoke] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, 'missing L3 runtime path: ' . str_replace($root . '/', '', $path) . "\n");
        exit(1);
    }
}

$yaml = file_get_contents($workflow);
$requiredWorkflow = [
    'pull_request:',
    'mysql:8.0',
    'php-version: \'8.1\'',
    'wp core download',
    'wp plugin install woocommerce --activate',
    'wp theme activate aznet-theme',
    'wp server --host=127.0.0.1 --port=8080',
    'bash "$GITHUB_WORKSPACE/scripts/verify-woo-l3-runtime.sh"',
    'actions/upload-artifact@v4',
];
foreach ($requiredWorkflow as $needle) {
    if (false === strpos($yaml, $needle)) {
        fwrite(STDERR, "workflow missing invariant: {$needle}\n");
        exit(2);
    }
}

$fixtureCode = file_get_contents($fixture);
foreach (['WC_Product_Simple', 'woocommerce_shop_page_id', 'woocommerce_cart_page_id', 'woocommerce_checkout_page_id', 'woocommerce_myaccount_page_id'] as $needle) {
    if (false === strpos($fixtureCode, $needle)) {
        fwrite(STDERR, "fixture missing runtime setup: {$needle}\n");
        exit(3);
    }
}

$smokeCode = file_get_contents($smoke);
if (false === strpos($smokeCode, '$BASE_URL/?page_id=$SHOP_PAGE_ID')) {
    fwrite(STDERR, "archive smoke must use registered Woo Shop page ID\n");
    exit(4);
}

foreach (['aznet-theme-woocommerce-product-css', 'aznet-theme-woocommerce-archive-css', 'aznet-theme-woocommerce-cart-css', 'aznet-theme-woocommerce-checkout-css', 'aznet-theme-woocommerce-account-css', 'aznet-theme-generic-content-css'] as $needle) {
    if (false === strpos($smokeCode, $needle)) {
        fwrite(STDERR, "smoke missing asset assertion: {$needle}\n");
        exit(4);
    }
}

foreach (['wp_remote_get(', 'curl_exec(', 'file_get_contents(\'https://remquocanh.vn'] as $needle) {
    if (false !== strpos($fixtureCode . $smokeCode, $needle)) {
        fwrite(STDERR, "forbidden external pilot coupling: {$needle}\n");
        exit(5);
    }
}

echo "PASS: Woo L3 runtime workflow contract\n";
