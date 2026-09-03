<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
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

if ($available() !== false) {
    fail_test('WooCommerce must report unavailable when WC() is absent');
}

if ($current_surface() !== null) {
    fail_test('WooCommerce surface must be null when WC() is absent');
}

echo "PASS: W1 WooCommerce absent capability contract\n";
