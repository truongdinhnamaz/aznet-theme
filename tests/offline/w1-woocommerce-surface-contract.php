<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['w1_woo_state'] = [
    'product' => false,
    'shop' => false,
    'taxonomy' => false,
    'cart' => false,
    'checkout' => false,
    'account' => false,
];

function WC(): object
{
    return (object) ['test' => true];
}

function is_product(): bool { return (bool) $GLOBALS['w1_woo_state']['product']; }
function is_shop(): bool { return (bool) $GLOBALS['w1_woo_state']['shop']; }
function is_product_taxonomy(): bool { return (bool) $GLOBALS['w1_woo_state']['taxonomy']; }
function is_cart(): bool { return (bool) $GLOBALS['w1_woo_state']['cart']; }
function is_checkout(): bool { return (bool) $GLOBALS['w1_woo_state']['checkout']; }
function is_account_page(): bool { return (bool) $GLOBALS['w1_woo_state']['account']; }

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function set_woo_state(string $key): void
{
    foreach (array_keys($GLOBALS['w1_woo_state']) as $name) {
        $GLOBALS['w1_woo_state'][$name] = ($name === $key);
    }
}

$root = dirname(__DIR__, 2);
$module = $root . '/inc/integrations/woocommerce.php';

if (!is_file($module)) {
    fail_test('WooCommerce integration module does not exist');
}

require $module;

$available = 'AZnet\\Theme\\Integrations\\WooCommerce\\available';
$current_surface = 'AZnet\\Theme\\Integrations\\WooCommerce\\current_surface';

if (!function_exists($available) || !function_exists($current_surface)) {
    fail_test('WooCommerce integration API is incomplete');
}

if ($available() !== true) {
    fail_test('WooCommerce must report available when WC() exists');
}

$cases = [
    'product' => 'product',
    'shop' => 'archive',
    'taxonomy' => 'archive',
    'cart' => 'cart',
    'checkout' => 'checkout',
    'account' => 'account',
];

foreach ($cases as $state => $expected) {
    set_woo_state($state);
    $actual = $current_surface();
    if ($actual !== $expected) {
        fail_test("{$state} must resolve to {$expected}; got " . var_export($actual, true));
    }
}

foreach (array_keys($GLOBALS['w1_woo_state']) as $name) {
    $GLOBALS['w1_woo_state'][$name] = false;
}
if ($current_surface() !== null) {
    fail_test('non-Woo request must resolve to null');
}

echo "PASS: W1 WooCommerce normalized surface contract\n";
