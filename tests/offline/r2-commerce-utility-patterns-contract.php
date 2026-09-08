<?php
/**
 * R2 Commerce and Utility pattern contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$core_expected = [
    'commerce-promotion.php' => ['aznet-theme/commerce-promotion', 'aznet-theme-commerce'],
    'utility-contact.php'    => ['aznet-theme/utility-contact', 'aznet-theme-utility'],
    'utility-footer-cta.php' => ['aznet-theme/utility-footer-cta', 'aznet-theme-utility'],
];

foreach ($core_expected as $filename => [$slug, $category]) {
    $path = $root . '/patterns/' . $filename;
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing R2 Commerce/Utility core pattern {$filename}\n");
        exit(1);
    }

    $content = file_get_contents($path);
    if (false === $content) {
        fwrite(STDERR, "FAIL: unable to read {$filename}\n");
        exit(1);
    }

    foreach (['Slug: ' . $slug, 'Categories: ' . $category, 'aznet-theme-pattern'] as $needle) {
        if (! str_contains($content, $needle)) {
            fwrite(STDERR, "FAIL: {$filename} missing {$needle}\n");
            exit(1);
        }
    }

    if (str_contains($content, '<!-- wp:woocommerce/')) {
        fwrite(STDERR, "FAIL: {$filename} must remain Woo-independent\n");
        exit(1);
    }
}

$woo_expected = [
    'category-grid.php'     => 'woocommerce/product-categories',
    'featured-products.php' => 'woocommerce/product-collection',
];

foreach ($woo_expected as $filename => $block_name) {
    $path = $root . '/inc/patterns/woocommerce/' . $filename;
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing fail-soft Woo pattern {$filename}\n");
        exit(1);
    }

    $content = file_get_contents($path);
    if (false === $content || ! str_contains($content, 'wp:' . $block_name)) {
        fwrite(STDERR, "FAIL: {$filename} does not use expected public block {$block_name}\n");
        exit(1);
    }
}

$core_count = count(glob($root . '/patterns/*.php') ?: []);
$woo_count  = count(glob($root . '/inc/patterns/woocommerce/*.php') ?: []);
if (16 > $core_count || 2 !== $woo_count) {
    fwrite(STDERR, "FAIL: expected at least 16 core + exactly 2 Woo-gated R2 candidates, got {$core_count} + {$woo_count}\n");
    exit(1);
}

$registry = file_get_contents($root . '/inc/theme/patterns.php');
$bootstrap = file_get_contents($root . '/inc/theme/bootstrap.php');
if (false === $registry || false === $bootstrap) {
    fwrite(STDERR, "FAIL: unable to inspect R2 pattern registry wiring\n");
    exit(1);
}

foreach ([
    'function register_woocommerce_patterns(): void',
    "'woocommerce/product-categories'",
    "'woocommerce/product-collection'",
    "'aznet-theme/commerce-category-grid'",
    "'aznet-theme/commerce-featured-products'",
] as $needle) {
    if (! str_contains($registry, $needle)) {
        fwrite(STDERR, "FAIL: Woo pattern registry missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains($bootstrap, "add_action( 'init', __NAMESPACE__ . '\\\\register_woocommerce_patterns', 20 );")) {
    fwrite(STDERR, "FAIL: Woo pattern registration is not wired after native block registration\n");
    exit(1);
}

foreach (['WC(', 'WC_', 'wc_get_', 'get_option(', 'get_post_meta(', '$wpdb', 'class-wc-', 'woocommerce_blocks_'] as $needle) {
    if (str_contains($registry, $needle)) {
        fwrite(STDERR, "FAIL: Woo registry uses private/domain state token {$needle}\n");
        exit(1);
    }
}

if (! defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

if (! function_exists('__')) {
    function __(string $text, string $domain = ''): string {
        return $text;
    }
}

$GLOBALS['r2_registered_patterns'] = [];
if (! function_exists('register_block_pattern')) {
    function register_block_pattern(string $slug, array $properties): bool {
        $GLOBALS['r2_registered_patterns'][$slug] = $properties;
        return true;
    }
}

if (! class_exists('WP_Block_Type_Registry')) {
    class WP_Block_Type_Registry {
        /** @var array<string, bool> */
        public array $registered = [];
        private static ?self $instance = null;

        public static function get_instance(): self {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function is_registered(string $name): bool {
            return ! empty($this->registered[$name]);
        }
    }
}

require_once $root . '/inc/theme/patterns.php';

$block_registry = WP_Block_Type_Registry::get_instance();
$block_registry->registered = [];
$GLOBALS['r2_registered_patterns'] = [];
AZnet\Theme\register_woocommerce_patterns();
if ([] !== $GLOBALS['r2_registered_patterns']) {
    fwrite(STDERR, "FAIL: Woo patterns registered when Woo blocks were absent\n");
    exit(1);
}

$block_registry->registered = ['woocommerce/product-categories' => true];
$GLOBALS['r2_registered_patterns'] = [];
AZnet\Theme\register_woocommerce_patterns();
if (['aznet-theme/commerce-category-grid'] !== array_keys($GLOBALS['r2_registered_patterns'])) {
    fwrite(STDERR, "FAIL: category pattern capability gating is not exact\n");
    exit(1);
}

$block_registry->registered = ['woocommerce/product-collection' => true];
$GLOBALS['r2_registered_patterns'] = [];
AZnet\Theme\register_woocommerce_patterns();
if (['aznet-theme/commerce-featured-products'] !== array_keys($GLOBALS['r2_registered_patterns'])) {
    fwrite(STDERR, "FAIL: featured-products capability gating is not exact\n");
    exit(1);
}

$block_registry->registered = [
    'woocommerce/product-categories' => true,
    'woocommerce/product-collection' => true,
];
$GLOBALS['r2_registered_patterns'] = [];
AZnet\Theme\register_woocommerce_patterns();
if (2 !== count($GLOBALS['r2_registered_patterns'])) {
    fwrite(STDERR, "FAIL: both supported Woo patterns did not register\n");
    exit(1);
}

foreach ($GLOBALS['r2_registered_patterns'] as $slug => $properties) {
    if (empty($properties['content']) || ! str_contains((string) $properties['content'], '<!-- wp:woocommerce/')) {
        fwrite(STDERR, "FAIL: {$slug} registered without portable Woo block content\n");
        exit(1);
    }
}

echo "PASS: R2 Commerce and Utility fail-soft patterns contract\n";
