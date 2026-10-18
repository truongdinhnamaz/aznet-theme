<?php
/**
 * WooCommerce loop CTA is product navigation, never blog "Read more".
 * Only the public Woo label/loop args are adapted; purchase eligibility,
 * target URL, stock, product content and price remain WooCommerce-owned.
 */
declare(strict_types=1);

define('ABSPATH', __DIR__);

$GLOBALS['aznet_registered_filters'] = [];

function add_filter(string $hook, $callback, int $priority = 10, int $accepted_args = 1): bool {
    $GLOBALS['aznet_registered_filters'][$hook] = [$callback, $priority, $accepted_args];
    return true;
}

function __(string $text, string $domain = 'default'): string {
    return 'woocommerce' === $domain && 'Read more' === $text ? 'Đọc tiếp' : $text;
}

class WC_Product {
    public function __construct(private string $label, private string $name = 'Rèm cuốn xuyên sáng') {}
    public function add_to_cart_text(): string {
        return AZnet\Theme\woocommerce_loop_product_cta_text($this->label, $this);
    }
    public function get_name(): string { return $this->name; }
}

$root = dirname(__DIR__, 2);
require $root . '/inc/theme/woocommerce-archive.php';

$fail = static function (string $reason): never {
    fwrite(STDERR, "FAIL: Woo product loop CTA: {$reason}\n");
    exit(1);
};

foreach ([
    'woocommerce_product_add_to_cart_text' => 'woocommerce_loop_product_cta_text',
    'woocommerce_loop_add_to_cart_args' => 'woocommerce_loop_product_cta_args',
] as $hook => $callback) {
    $registration = $GLOBALS['aznet_registered_filters'][$hook] ?? null;
    if (!$registration || $registration[0] !== 'AZnet\\Theme\\' . $callback || $registration[2] !== 2) {
        $fail("missing public Woo hook {$hook}");
    }
}

$label = 'AZnet\\Theme\\woocommerce_loop_product_cta_text';
$argsCallback = 'AZnet\\Theme\\woocommerce_loop_product_cta_args';
$product = new WC_Product('Đọc tiếp');
foreach (['Đọc tiếp', 'Read more'] as $fallback) {
    if ($label($fallback, $product) !== 'Xem chi tiết') {
        $fail("fallback {$fallback} must be product-detail navigation");
    }
}
foreach (['Thêm vào giỏ hàng', 'Chọn', 'Mua hàng', 'Xem sản phẩm'] as $unchanged) {
    if ($label($unchanged, $product) !== $unchanged) {
        $fail("existing Woo purchase CTA changed: {$unchanged}");
    }
}
if ($label('Đọc tiếp', new stdClass()) !== 'Đọc tiếp') {
    $fail('non-product source must not be touched');
}

$args = [
    'class' => 'button product_type_simple',
    'attributes' => ['aria-label' => 'Đọc thêm về Rèm cuốn xuyên sáng', 'data-product_id' => '123'],
    'quantity' => 1,
];
$changed = $argsCallback($args, $product);
if (($changed['attributes']['aria-label'] ?? '') !== 'Xem chi tiết sản phẩm: Rèm cuốn xuyên sáng') {
    $fail('product-detail CTA must have an action-accurate accessible name');
}
if (($changed['attributes']['data-product_id'] ?? null) !== '123'
    || ($changed['class'] ?? '') !== $args['class']
    || ($changed['quantity'] ?? null) !== 1
) {
    $fail('Woo cart metadata or button behavior was modified');
}
$buy = new WC_Product('Thêm vào giỏ hàng');
if ($argsCallback($args, $buy) !== $args) {
    $fail('purchasable Woo CTA args must remain unchanged');
}
if ($argsCallback($args, new stdClass()) !== $args) {
    $fail('non-product CTA args must remain unchanged');
}

$forbidden = ['get_post_meta(', 'get_option(', '$wpdb', 'WC()->', 'query_posts(', 'pre_get_posts', 'choiceguide_', 'convertflow_'];
$code = (string) file_get_contents($root . '/inc/theme/woocommerce-archive.php');
foreach ($forbidden as $needle) {
    if (str_contains($code, $needle)) {
        $fail('Theme must not read private Woo/provider data: ' . $needle);
    }
}
echo "PASS: Woo product navigation CTA uses Xem chi tiết, preserves Woo purchase actions and a11y label\n";
