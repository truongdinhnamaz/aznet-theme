<?php
/** Regression: WordPress home/blog index must load the generic listing stylesheet. */
declare(strict_types=1);

namespace { 
    define('ABSPATH', __DIR__);
    $GLOBALS['home_index'] = false;
    $GLOBALS['woo_surface'] = null;
    function is_home(): bool { return $GLOBALS['home_index']; }
    function is_page(): bool { return false; }
    function is_singular(string $type = ''): bool { return false; }
    function is_archive(): bool { return false; }
    function is_search(): bool { return false; }
    function is_404(): bool { return false; }
}
namespace AZnet\Theme\Integrations\WooCommerce {
    function current_surface(): ?string { return $GLOBALS['woo_surface']; }
}
namespace {
    require dirname(__DIR__, 2) . '/inc/theme/content-shell.php';
    $check = 'AZnet\\Theme\\should_enqueue_generic_content_assets';
    $GLOBALS['home_index'] = true;
    if (!$check()) { fwrite(STDERR, "FAIL: native WordPress posts index must enqueue generic-content CSS\n"); exit(1); }
    $GLOBALS['woo_surface'] = 'archive';
    if ($check()) { fwrite(STDERR, "FAIL: WooCommerce archive must not load generic content CSS\n"); exit(1); }
    $GLOBALS['woo_surface'] = null;
    $GLOBALS['home_index'] = false;
    if ($check()) { fwrite(STDERR, "FAIL: unrelated surface must not load generic content CSS\n"); exit(1); }
    echo "PASS: posts-index asset scope, Woo isolation, unrelated-surface isolation\n";
}
