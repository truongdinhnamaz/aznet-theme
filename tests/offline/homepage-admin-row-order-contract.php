<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = file_get_contents($root . '/assets/css/admin/control-center.css');
$homepage = file_get_contents($root . '/inc/admin/homepage.php');

if (false === $css || false === $homepage) {
    fwrite(STDERR, "FAIL: unable to read Homepage admin sources\n");
    exit(2);
}

if (! preg_match('/\\.aznet-theme-control-center\\s+\\.aznet-theme-homepage-section-grid\\s*\\{[^}]*display\\s*:\\s*flex\\s*!important\\s*;[^}]*flex-direction\\s*:\\s*column\\s*;/s', $css)) {
    fwrite(STDERR, "FAIL: Homepage sections are not forced into a vertical row stack.\n");
    exit(1);
}

if (! str_contains($homepage, "return [ 'hero', 'services', 'about', 'team'")) {
    fwrite(STDERR, "FAIL: Law 01 order no longer starts Hero then Services.\n");
    exit(1);
}

echo "PASS: Homepage admin maps sections vertically in frontend order.\n";
