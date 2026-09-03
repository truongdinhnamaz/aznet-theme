<?php

declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['w1'] = [
    'product' => false,
    'shop' => false,
    'taxonomy' => false,
    'cart' => false,
    'checkout' => false,
    'account' => false,
    'page' => false,
    'post' => false,
    'archive' => false,
    'search' => false,
    '404' => false,
];

function WC(): object { return (object) ['test' => true]; }
function is_product(): bool { return (bool) $GLOBALS['w1']['product']; }
function is_shop(): bool { return (bool) $GLOBALS['w1']['shop']; }
function is_product_taxonomy(): bool { return (bool) $GLOBALS['w1']['taxonomy']; }
function is_cart(): bool { return (bool) $GLOBALS['w1']['cart']; }
function is_checkout(): bool { return (bool) $GLOBALS['w1']['checkout']; }
function is_account_page(): bool { return (bool) $GLOBALS['w1']['account']; }
function is_page(): bool { return (bool) $GLOBALS['w1']['page']; }
function is_singular(string $type = ''): bool { return 'post' === $type && (bool) $GLOBALS['w1']['post']; }
function is_archive(): bool { return (bool) $GLOBALS['w1']['archive']; }
function is_search(): bool { return (bool) $GLOBALS['w1']['search']; }
function is_404(): bool { return (bool) $GLOBALS['w1']['404']; }

function fail_test(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function reset_state(): void
{
    foreach (array_keys($GLOBALS['w1']) as $key) {
        $GLOBALS['w1'][$key] = false;
    }
}

function assert_asset_scope(bool $expected, string $label): void
{
    $fn = 'AZnet\\Theme\\should_enqueue_generic_content_assets';
    $actual = $fn();
    if ($actual !== $expected) {
        fail_test("{$label}: expected " . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

$root = dirname(__DIR__, 2);
require $root . '/inc/integrations/woocommerce.php';
require $root . '/inc/theme/content-shell.php';

reset_state();
$GLOBALS['w1']['page'] = true;
assert_asset_scope(true, 'generic WordPress Page must retain generic-content asset');

reset_state();
$GLOBALS['w1']['post'] = true;
assert_asset_scope(true, 'generic Post must retain generic-content asset');

reset_state();
$GLOBALS['w1']['archive'] = true;
assert_asset_scope(true, 'generic Archive must retain generic-content asset');

$woo_cases = [
    'product' => ['product' => true],
    'shop archive' => ['shop' => true, 'archive' => true],
    'product taxonomy' => ['taxonomy' => true, 'archive' => true],
    'cart page' => ['cart' => true, 'page' => true],
    'checkout page' => ['checkout' => true, 'page' => true],
    'account page' => ['account' => true, 'page' => true],
];

foreach ($woo_cases as $label => $state) {
    reset_state();
    foreach ($state as $key => $value) {
        $GLOBALS['w1'][$key] = $value;
    }
    assert_asset_scope(false, $label . ' must not load generic-content asset');
}

echo "PASS: W1 WooCommerce generic asset scope contract\n";
