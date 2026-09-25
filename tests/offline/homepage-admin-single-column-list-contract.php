<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$homepage = file_get_contents($root . '/inc/admin/homepage.php');
$css = file_get_contents($root . '/assets/css/admin/control-center.css');

if (false === $homepage || false === $css) {
    fwrite(STDERR, "FAIL: unable to read Homepage admin sources\n");
    exit(2);
}

if (! str_contains($homepage, 'aznet-theme-homepage-section-list')) {
    fwrite(STDERR, "FAIL: Homepage admin does not use the vertical section list.\n");
    exit(1);
}

if (str_contains($homepage, 'aznet-theme-homepage-section-grid')) {
    fwrite(STDERR, "FAIL: Homepage admin still renders the legacy grid container.\n");
    exit(1);
}

if (! preg_match('/\\.aznet-theme-homepage-section-list\\s*\\{[^}]*display\\s*:\\s*block\\s*;/s', $css)) {
    fwrite(STDERR, "FAIL: Homepage section list is not block layout.\n");
    exit(1);
}

if (! preg_match('/\\.aznet-theme-homepage-section-list\\s*>\\s*\\.aznet-theme-homepage-section-card\\s*\\{[^}]*width\\s*:\\s*100%\\s*;/s', $css)) {
    fwrite(STDERR, "FAIL: Homepage cards are not full width.\n");
    exit(1);
}

echo "PASS: Homepage sections are one full-width row each.\n";
