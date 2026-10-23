<?php
/**
 * R-C Header/Footer retirement static contract.
 *
 * Protects the Milestone C canonical destination and compatibility wiring while
 * rejecting reintroduction of the exact historical source paths as active Theme
 * dependencies. This contract intentionally does not classify external source
 * files by filename alone.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$failures = [];

$assert = static function (bool $condition, string $message) use (&$failures): void {
    if (! $condition) {
        $failures[] = $message;
    }
};

$read = static function (string $path) use ($root, $assert): string {
    $absolute = $root . '/' . $path;
    $assert(is_file($absolute), 'Required canonical/compatibility path missing: ' . $path);

    if (! is_file($absolute)) {
        return '';
    }

    $content = file_get_contents($absolute);
    $assert(false !== $content, 'Unable to read required path: ' . $path);

    return false === $content ? '' : $content;
};

$requiredPaths = [
    'header.php',
    'footer.php',
    'template-parts/header/site-header.php',
    'template-parts/footer/site-footer.php',
    'assets/css/components/site-header.css',
    'assets/css/components/site-footer.css',
    'inc/theme/assets.php',
    'inc/theme/setup.php',
];

foreach ($requiredPaths as $path) {
    $assert(is_file($root . '/' . $path), 'Required Milestone C path missing: ' . $path);
}

$header = $read('header.php');
$footer = $read('footer.php');
$assets = $read('inc/theme/assets.php');
$setup = $read('inc/theme/setup.php');
$siteHeader = $read('template-parts/header/site-header.php');
$siteFooter = $read('template-parts/footer/site-footer.php');

$assert(
    str_contains($header, "get_template_part( 'template-parts/header/site-header' )"),
    'header.php must delegate to the canonical Header destination.'
);
$assert(
    str_contains($footer, "get_template_part( 'template-parts/footer/site-footer' )"),
    'footer.php must delegate to the canonical Footer destination.'
);
$assert(
    str_contains($assets, "get_theme_file_uri( '/assets/css/components/site-header.css' )"),
    'Canonical Header CSS enqueue is missing.'
);
$assert(
    str_contains($assets, "get_theme_file_uri( '/assets/css/components/site-footer.css' )"),
    'Canonical Footer CSS enqueue is missing.'
);

foreach (['primary', 'footer', 'footer-contact', 'footer-social', 'footer-policy'] as $location) {
    $assert(
        str_contains($setup, "'" . $location . "'"),
        'Required WordPress menu compatibility location missing: ' . $location
    );
}

foreach ([$siteHeader, $siteFooter] as $index => $surface) {
    $label = 0 === $index ? 'Header' : 'Footer';
    foreach (['get_option(', 'get_post_meta(', '$wpdb', '_rootprofile_', '_choiceguide_'] as $forbidden) {
        $assert(
            ! str_contains($surface, $forbidden),
            $label . ' presentation must not direct-read private/domain storage marker: ' . $forbidden
        );
    }
}

$assert(
    str_contains($siteHeader, "function_exists( 'wc_get_page_permalink' )")
        && str_contains($siteHeader, "function_exists( 'wc_get_cart_url' )"),
    'Header Woo utilities must remain public-function guarded/fail-soft.'
);

$legacyThemePaths = [
    'SiteHeaderRenderer.php',
    'SiteFooterRenderer.php',
    'assets/css/site-header.css',
    'assets/css/site-footer.css',
];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (
        str_starts_with($relative, 'docs/')
        || str_starts_with($relative, 'tests/')
        || str_starts_with($relative, '.git/')
    ) {
        continue;
    }

    foreach ($legacyThemePaths as $legacyPath) {
        $matchesLegacyFile = str_contains($legacyPath, '/')
            ? $relative === $legacyPath
            : basename($relative) === $legacyPath;
        $assert(! $matchesLegacyFile, 'Theme-local exact historical duplicate path is present: ' . $relative);
    }

    if ('php' !== strtolower((string) pathinfo($relative, PATHINFO_EXTENSION))) {
        continue;
    }

    $content = file_get_contents($file->getPathname());
    if (false === $content) {
        $failures[] = 'Unable to scan production PHP file: ' . $relative;
        continue;
    }

    $assert(
        0 === preg_match('/\b(?:require|require_once|include|include_once)\b[^;\n]*(?:SiteHeaderRenderer|SiteFooterRenderer)\.php/i', $content),
        'Active include/require references a historical Header/Footer renderer: ' . $relative
    );
    $assert(
        0 === preg_match('#get_theme_file_uri\s*\(\s*[\'\"]/assets/css/(?:site-header|site-footer)\.css[\'\"]#i', $content),
        'Active enqueue references a superseded historical Header/Footer CSS path: ' . $relative
    );
}

if ([] !== $failures) {
    fwrite(STDERR, "FAIL: R-C Header/Footer retirement static contract\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, ' - ' . $failure . "\n");
    }
    exit(1);
}

fwrite(STDOUT, "PASS: R-C Header/Footer retirement static contract\n");
