<?php

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    define('AZNET_THEME_VERSION', '1.0.0');

    $GLOBALS['r6_styles'] = [];
    $GLOBALS['r6_scripts'] = [];
    $GLOBALS['r6_preset'] = 'default';
    $GLOBALS['r6_mobile_panel'] = false;
    $GLOBALS['r6_sticky_mode'] = 'off';
    $GLOBALS['r6_generic'] = false;
    $GLOBALS['r6_is_post'] = false;
    $GLOBALS['r6_woo_product'] = false;
    $GLOBALS['r6_woo_archive'] = false;
    $GLOBALS['r6_woo_cart'] = false;
    $GLOBALS['r6_woo_checkout'] = false;
    $GLOBALS['r6_woo_account'] = false;
    $GLOBALS['r6_woo_blocks'] = false;

    function wp_enqueue_style($handle, $src = '', $deps = [], $ver = null): void {
        $GLOBALS['r6_styles'][$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
        ];
    }

    function wp_enqueue_script($handle, $src = '', $deps = [], $ver = null, $in_footer = false): void {
        $GLOBALS['r6_scripts'][$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $in_footer,
        ];
    }

    function get_theme_file_uri($path): string {
        return 'https://example.test/wp-content/themes/aznet-theme' . $path;
    }

    function get_stylesheet_uri(): string {
        return 'https://example.test/wp-content/themes/aznet-theme/style.css';
    }

    function is_singular($post_types = ''): bool {
        return 'post' === $post_types && (bool) ($GLOBALS['r6_is_post'] ?? false);
    }
}

namespace AZnet\Theme {
    function visual_preset(): string {
        return (string) ($GLOBALS['r6_preset'] ?? 'default');
    }

    function header_mobile_panel_enabled(): bool {
        return (bool) ($GLOBALS['r6_mobile_panel'] ?? false);
    }

    function header_sticky_mode(): string {
        return (string) ($GLOBALS['r6_sticky_mode'] ?? 'off');
    }

    function should_enqueue_generic_content_assets(): bool {
        return (bool) ($GLOBALS['r6_generic'] ?? false);
    }

    function should_enqueue_woocommerce_product_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_product'] ?? false);
    }

    function should_enqueue_woocommerce_archive_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_archive'] ?? false);
    }

    function should_enqueue_woocommerce_cart_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_cart'] ?? false);
    }

    function should_enqueue_woocommerce_checkout_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_checkout'] ?? false);
    }

    function should_enqueue_woocommerce_account_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_account'] ?? false);
    }

    function should_enqueue_woocommerce_blocks_assets(): bool {
        return (bool) ($GLOBALS['r6_woo_blocks'] ?? false);
    }
}

namespace {
    function fail_r6_asset_scope(string $message): never {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }

    function reset_r6_asset_scope(array $state = []): void {
        $defaults = [
            'preset' => 'default',
            'mobile_panel' => false,
            'sticky_mode' => 'off',
            'generic' => false,
            'is_post' => false,
            'woo_product' => false,
            'woo_archive' => false,
            'woo_cart' => false,
            'woo_checkout' => false,
            'woo_account' => false,
            'woo_blocks' => false,
        ];

        foreach (array_merge($defaults, $state) as $key => $value) {
            $GLOBALS['r6_' . $key] = $value;
        }

        $GLOBALS['r6_styles'] = [];
        $GLOBALS['r6_scripts'] = [];
    }

    function assert_r6_handles(string $label, array $expectedStyles, array $expectedScripts = []): void {
        $actualStyles = array_keys($GLOBALS['r6_styles']);
        $actualScripts = array_keys($GLOBALS['r6_scripts']);
        sort($actualStyles);
        sort($actualScripts);
        sort($expectedStyles);
        sort($expectedScripts);

        if ($actualStyles !== $expectedStyles) {
            fail_r6_asset_scope($label . ' styles mismatch: ' . json_encode($actualStyles));
        }
        if ($actualScripts !== $expectedScripts) {
            fail_r6_asset_scope($label . ' scripts mismatch: ' . json_encode($actualScripts));
        }

        foreach (array_merge($GLOBALS['r6_styles'], $GLOBALS['r6_scripts']) as $handle => $asset) {
            if ('1.0.0' !== ($asset['ver'] ?? null)) {
                fail_r6_asset_scope("{$label}: {$handle} must use exact Theme version");
            }
        }
    }

    $themeRoot = dirname(__DIR__, 2);
    require $themeRoot . '/inc/theme/assets.php';

    $coreStyles = [
        'aznet-theme-tokens',
        'aznet-theme-convertflow-contract',
        'aznet-theme-style',
        'aznet-theme-site-header',
        'aznet-theme-site-footer',
    ];

    $cases = [
        'clean-page-default' => [
            ['generic' => true],
            [...$coreStyles, 'aznet-theme-generic-content'],
            [],
        ],
        'clean-post-editorial' => [
            ['generic' => true, 'preset' => 'editorial', 'is_post' => true],
            [...$coreStyles, 'aznet-theme-generic-content', 'aznet-theme-preset-editorial', 'aznet-theme-article'],
            [],
        ],
        'woo-product' => [
            ['woo_product' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-product'],
            [],
        ],
        'woo-archive' => [
            ['woo_archive' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-archive'],
            [],
        ],
        'woo-cart' => [
            ['woo_cart' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-cart'],
            [],
        ],
        'woo-checkout' => [
            ['woo_checkout' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-checkout'],
            [],
        ],
        'woo-account' => [
            ['woo_account' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-account'],
            [],
        ],
        'woo-blocks' => [
            ['woo_blocks' => true],
            [...$coreStyles, 'aznet-theme-woocommerce-blocks'],
            [],
        ],
        'header-mobile-panel' => [
            ['mobile_panel' => true],
            $coreStyles,
            ['aznet-theme-header-navigation'],
        ],
        'header-sticky-compact' => [
            ['sticky_mode' => 'sticky-compact'],
            $coreStyles,
            ['aznet-theme-sticky-header'],
        ],
        'header-sticky-standard' => [
            ['sticky_mode' => 'sticky'],
            $coreStyles,
            [],
        ],
        'header-interactions-combined' => [
            ['mobile_panel' => true, 'sticky_mode' => 'sticky-compact'],
            $coreStyles,
            ['aznet-theme-header-navigation', 'aznet-theme-sticky-header'],
        ],
    ];

    foreach ($cases as $label => [$state, $styles, $scripts]) {
        reset_r6_asset_scope($state);
        \AZnet\Theme\enqueue_assets();
        assert_r6_handles($label, $styles, $scripts);
    }

    foreach (['clean-page-default', 'clean-post-editorial'] as $label) {
        [$state] = $cases[$label];
        reset_r6_asset_scope($state);
        \AZnet\Theme\enqueue_assets();
        foreach (array_keys($GLOBALS['r6_styles']) as $handle) {
            if (str_starts_with($handle, 'aznet-theme-woocommerce-')) {
                fail_r6_asset_scope("{$label}: Woo presentation asset leaked onto clean route");
            }
        }
    }

    echo "PASS: R6 exact Theme asset-scope matrix (12 cases)\n";
}