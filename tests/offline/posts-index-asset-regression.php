<?php
/** RED -> GREEN: WordPress posts-on-front loads its existing listing CSS. */
declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    $GLOBALS['aznet_is_home'] = false;
    $GLOBALS['aznet_woo_surface'] = null;
    function is_home(): bool { return $GLOBALS['aznet_is_home']; }
    function is_page(): bool { return false; }
    function is_singular(string $type = ''): bool { return false; }
    function is_archive(): bool { return false; }
    function is_search(): bool { return false; }
    function is_404(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['aznet_woo_surface']; }
}
namespace {
    require dirname(__DIR__, 2) . '/inc/theme/content-shell.php';
    $should_enqueue = 'AZnet\\Theme\\should_enqueue_generic_content_assets';

    $GLOBALS['aznet_is_home'] = true;
    if (!$should_enqueue()) {
        fwrite(STDERR, "FAIL: WordPress posts-on-front requires generic listing CSS\n");
        exit(1);
    }

    $GLOBALS['aznet_woo_surface'] = 'archive';
    if ($should_enqueue()) {
        fwrite(STDERR, "FAIL: WooCommerce archive must remain isolated from generic listing assets\n");
        exit(1);
    }

    $GLOBALS['aznet_is_home'] = false;
    $GLOBALS['aznet_woo_surface'] = null;
    if ($should_enqueue()) {
        fwrite(STDERR, "FAIL: unrelated WordPress surface must not enqueue listing CSS\n");
        exit(1);
    }

    echo "PASS: posts-on-front generic-content asset, Woo isolation, unrelated surface isolation\n";
}
