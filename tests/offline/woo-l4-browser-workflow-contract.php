<?php
$root = dirname(__DIR__, 2);
$workflow = $root . '/.github/workflows/woo-l4-browser.yml';
$browser = $root . '/tests/browser/woo-l4-browser.mjs';

foreach ([$workflow, $browser] as $path) {
    if (!is_file($path)) {
        fwrite(STDERR, 'missing L4 browser path: ' . str_replace($root . '/', '', $path) . "\n");
        exit(1);
    }
}

$yaml = file_get_contents($workflow);
$requiredWorkflow = [
    'pull_request:',
    'mysql:8.0',
    "php-version: '8.1'",
    'wp plugin install woocommerce --activate',
    'wp theme activate aznet-theme',
    'npx playwright install chromium --with-deps',
    'node tests/browser/woo-l4-browser.mjs',
    'actions/upload-artifact@v4',
];
foreach ($requiredWorkflow as $needle) {
    if (false === strpos($yaml, $needle)) {
        fwrite(STDERR, "workflow missing invariant: {$needle}\n");
        exit(2);
    }
}

$browserCode = file_get_contents($browser);
$requiredBrowser = [
    'chromium.launch',
    '1440',
    '390',
    'AxeBuilder',
    "['critical', 'serious']",
    'scrollWidth',
    'screenshot',
    "page.keyboard.press('Tab')",
    'add-to-cart=',
    'SHOP_PAGE_ID',
    'CHECKOUT_PAGE_ID',
];
foreach ($requiredBrowser as $needle) {
    if (false === strpos($browserCode, $needle)) {
        fwrite(STDERR, "browser harness missing invariant: {$needle}\n");
        exit(3);
    }
}

$combined = $yaml . "\n" . $browserCode;
$forbidden = [
    'remquocanh.vn',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'woocommerce_session_',
    'wp_wc_sessions',
    'inc/theme/',
    'assets/css/components/',
];
foreach ($forbidden as $needle) {
    if (false !== strpos($combined, $needle)) {
        fwrite(STDERR, "forbidden L4 coupling/change: {$needle}\n");
        exit(4);
    }
}

echo "PASS: Woo L4 browser workflow contract\n";
