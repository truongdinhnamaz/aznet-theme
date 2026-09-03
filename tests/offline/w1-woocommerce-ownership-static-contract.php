<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_gate(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function read_path(string $root, string $path): string
{
    $text = @file_get_contents($root . '/' . $path);
    if (!is_string($text)) {
        fail_gate("cannot read {$path}");
    }
    return $text;
}

$production = [
    'inc/integrations/woocommerce.php',
    'inc/theme/content-shell.php',
    'inc/theme/bootstrap.php',
];

$forbidden = [
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    "'_woocommerce_",
    '"_woocommerce_',
    'Automattic\\WooCommerce\\Internal\\',
];

foreach ($production as $path) {
    $text = read_path($root, $path);
    foreach ($forbidden as $needle) {
        if (str_contains($text, $needle)) {
            fail_gate("forbidden Woo ownership/storage token {$needle} found in {$path}");
        }
    }
}

if (is_dir($root . '/woocommerce')) {
    fail_gate('W1 must not introduce a woocommerce/ template override directory');
}

$integration = read_path($root, 'inc/integrations/woocommerce.php');
foreach (['WC', 'is_product', 'is_shop', 'is_product_taxonomy', 'is_cart', 'is_checkout', 'is_account_page'] as $public_api) {
    if (!str_contains($integration, $public_api)) {
        fail_gate("expected public Woo capability/conditional API {$public_api} missing");
    }
}

$bootstrap = read_path($root, 'inc/theme/bootstrap.php');
if (substr_count($bootstrap, "require_once __DIR__ . '/../integrations/woocommerce.php';") !== 1) {
    fail_gate('bootstrap must load WooCommerce integration exactly once');
}
if (substr_count($bootstrap, 'add_action(') !== 2) {
    fail_gate('W1 must not add WordPress action registrations');
}
if (str_contains($bootstrap, 'add_filter(')) {
    fail_gate('W1 must not add WordPress filter registrations');
}

$content = read_path($root, 'inc/theme/content-shell.php');
if (!str_contains($content, 'Integrations\\WooCommerce\\current_surface()')) {
    fail_gate('generic asset eligibility must consume normalized Woo surface context');
}

if (is_file($root . '/assets/css/components/woocommerce-shell.css')) {
    fail_gate('W1 must not add a Woo stylesheet before a styled surface slice exists');
}

echo "PASS: W1 WooCommerce ownership / no-override static contract\n";
