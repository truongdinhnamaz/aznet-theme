<?php
/** Native Front Page body must be represented in the unified Homepage Map. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$surface = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$front = file_get_contents($root . '/front-page.php');
$admin = file_get_contents($root . '/inc/admin/homepage.php');

foreach ([
    [$surface, "'front-page-content'", 'shared model does not expose native Front Page content'],
    [$surface, "'native'", 'native content boundary metadata missing'],
    [$surface, "'front_page'", 'native Front Page source type missing'],
    [$front, 'data-aznet-homepage-surface="front-page-content"', 'public Front Page content marker missing'],
    [$admin, "'front-page-content' === \$key", 'Homepage Map does not handle native Front Page content'],
    [$admin, 'Chỉnh nội dung trang', 'native Front Page edit action missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

echo "PASS: native Front Page content is represented in unified Homepage Map\n";
