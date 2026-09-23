<?php
/**
 * Regression: Woo breadcrumbs must meet Theme-owned contrast on Curtain 01 surfaces.
 *
 * Production evidence on remquocanh.vn showed WooCommerce's default breadcrumb
 * color failing Lighthouse contrast on Shop and Single Product while the same
 * Theme shell passed 100/100 on the homepage. AZnet Theme owns presentation,
 * so its Woo surface adapters must map breadcrumb text/links onto an accessible
 * Theme token without changing WooCommerce semantics or markup.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = [
    'archive' => $root . '/assets/css/components/woocommerce-archive.css',
    'product' => $root . '/assets/css/components/woocommerce-product.css',
];

foreach ($files as $surface => $path) {
    if (! is_file($path)) {
        fwrite(STDERR, "FAIL: missing Woo {$surface} CSS\n");
        exit(1);
    }

    $css = file_get_contents($path);
    if (false === $css) {
        fwrite(STDERR, "FAIL: unable to read Woo {$surface} CSS\n");
        exit(1);
    }

    if (! preg_match('/\\.woocommerce-breadcrumb(?:\\s*,|\\s*\\{)/', $css)) {
        fwrite(STDERR, "FAIL: Woo {$surface} surface does not style breadcrumb contrast\n");
        exit(1);
    }

    if (! preg_match('/\\.woocommerce-breadcrumb[^{]*\\{[^}]*color\\s*:\\s*var\\(--aznet-theme-muted/is', $css)) {
        fwrite(STDERR, "FAIL: Woo {$surface} breadcrumb does not use the accessible Theme muted token\n");
        exit(1);
    }

    if (! preg_match('/\\.woocommerce-breadcrumb\\s+a[^{]*\\{[^}]*color\\s*:\\s*var\\(--aznet-theme-muted/is', $css)) {
        fwrite(STDERR, "FAIL: Woo {$surface} breadcrumb links do not use the accessible Theme muted token\n");
        exit(1);
    }
}

echo "PASS: R4 Woo breadcrumb contrast regression\n";
