<?php
/**
 * R4 Woo Blocks public-capability and exact asset-scope contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

$root = dirname(__DIR__, 2);
$module_file = $root . '/inc/theme/woocommerce-blocks.php';
$bootstrap_file = $root . '/inc/theme/bootstrap.php';
$assets_file = $root . '/inc/theme/assets.php';
$css_file = $root . '/assets/css/components/woocommerce-blocks.css';

if (! is_file($module_file)) {
    fwrite(STDERR, "FAIL: R4 Woo Blocks module missing inc/theme/woocommerce-blocks.php\n");
    exit(1);
}
if (! is_file($css_file)) {
    fwrite(STDERR, "FAIL: R4 Woo Blocks stylesheet missing\n");
    exit(1);
}

$module = file_get_contents($module_file);
$bootstrap = file_get_contents($bootstrap_file);
$assets = file_get_contents($assets_file);
$css = file_get_contents($css_file);
if (false === $module || false === $bootstrap || false === $assets || false === $css) {
    fwrite(STDERR, "FAIL: unable to inspect R4 Woo Blocks files\n");
    exit(1);
}

foreach ([
    'function should_enqueue_woocommerce_blocks_assets(): bool',
    "'woocommerce/product-collection'",
    "'woocommerce/product-categories'",
    'WP_Block_Type_Registry',
    'has_block(',
] as $needle) {
    if (! str_contains($module, $needle)) {
        fwrite(STDERR, "FAIL: R4 Woo Blocks module missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'WC_VERSION',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    'Automattic\\WooCommerce\\Internal',
    'class-wc-',
    'woocommerce_blocks_',
] as $needle) {
    if (str_contains($module, $needle)) {
        fwrite(STDERR, "FAIL: R4 Woo Blocks capability uses forbidden heuristic/private token {$needle}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "require_once __DIR__ . '/woocommerce-blocks.php';")) {
    fwrite(STDERR, "FAIL: bootstrap does not load Woo Blocks module\n");
    exit(1);
}
if (strpos($bootstrap, "require_once __DIR__ . '/woocommerce-blocks.php';") > strpos($bootstrap, "require_once __DIR__ . '/assets.php';")) {
    fwrite(STDERR, "FAIL: Woo Blocks capability module must load before assets.php\n");
    exit(1);
}

foreach ([
    "'aznet-theme-woocommerce-blocks'",
    "'/assets/css/components/woocommerce-blocks.css'",
    'should_enqueue_woocommerce_blocks_assets()',
] as $needle) {
    if (! str_contains($assets, $needle)) {
        fwrite(STDERR, "FAIL: assets.php missing scoped Woo Blocks enqueue marker {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.wp-block-woocommerce-product-collection',
    '.wc-block-product',
    '.wc-block-components-product-price',
    '.wp-block-button',
    ':focus-visible',
    '--aznet-theme-',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: Woo Blocks CSS missing presentation marker {$needle}\n");
        exit(1);
    }
}

$GLOBALS['r4_registered_blocks'] = [];
$GLOBALS['r4_content_blocks'] = [];

if (! class_exists('WP_Block_Type_Registry')) {
    class WP_Block_Type_Registry {
        private static ?self $instance = null;
        public static function get_instance(): self {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        public function is_registered(string $name): bool {
            return ! empty($GLOBALS['r4_registered_blocks'][$name]);
        }
    }
}

if (! function_exists('has_block')) {
    function has_block(string $block_name, mixed $post = null): bool {
        return ! empty($GLOBALS['r4_content_blocks'][$block_name]);
    }
}

require_once $module_file;

$cases = [
    ['registered' => [], 'content' => [], 'expected' => false, 'label' => 'no capability'],
    ['registered' => ['woocommerce/product-collection' => true], 'content' => [], 'expected' => false, 'label' => 'capability without content'],
    ['registered' => [], 'content' => ['woocommerce/product-collection' => true], 'expected' => false, 'label' => 'content without capability'],
    ['registered' => ['woocommerce/product-collection' => true], 'content' => ['woocommerce/product-collection' => true], 'expected' => true, 'label' => 'product collection exact match'],
    ['registered' => ['woocommerce/product-categories' => true], 'content' => ['woocommerce/product-categories' => true], 'expected' => true, 'label' => 'product categories exact match'],
    ['registered' => ['woocommerce/product-collection' => true], 'content' => ['woocommerce/cart' => true], 'expected' => false, 'label' => 'unrelated Woo content'],
];

foreach ($cases as $case) {
    $GLOBALS['r4_registered_blocks'] = $case['registered'];
    $GLOBALS['r4_content_blocks'] = $case['content'];
    $actual = AZnet\Theme\should_enqueue_woocommerce_blocks_assets();
    if ($actual !== $case['expected']) {
        fwrite(STDERR, "FAIL: Woo Blocks asset gate mismatch for {$case['label']}\n");
        exit(1);
    }
}

echo "PASS: R4 Woo Blocks public capability and exact asset scope contract\n";
