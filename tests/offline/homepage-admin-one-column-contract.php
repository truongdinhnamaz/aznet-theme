<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = file_get_contents($root . '/assets/css/admin/control-center.css');
$homepage = file_get_contents($root . '/inc/admin/homepage.php');

if (false === $css || false === $homepage) {
    fwrite(STDERR, "FAIL: unable to read Homepage admin sources\n");
    exit(2);
}

if (! preg_match('/\\.aznet-theme-homepage-section-grid\\s*\\{[^}]*grid-template-columns\\s*:\\s*1fr\\s*;/s', $css)) {
    fwrite(STDERR, "FAIL: Homepage section grid is not single-column.\n");
    exit(1);
}

if (preg_match('/\\.aznet-theme-homepage-section-grid\\s*\\{[^}]*grid-template-columns\\s*:\\s*repeat\\(2\\s*,/s', $css)) {
    fwrite(STDERR, "FAIL: Homepage section grid still declares a two-column primary layout.\n");
    exit(1);
}

$lawManifest = file_get_contents($root . '/inc/theme/templates/law-01/manifest.php');
if (! is_string($lawManifest) || ! str_contains($lawManifest, "'sections' => [ 'hero', 'services', 'about', 'team'")) {
    fwrite(STDERR, "FAIL: Law 01 manifest authoring order no longer starts Hero then Services.\n");
    exit(1);
}
if (! str_contains($homepage, "['homepage']['authoring']['sections']")) {
    fwrite(STDERR, "FAIL: Homepage admin does not consume manifest authoring order.\n");
    exit(1);
}

echo "PASS: Homepage authoring sections render in one ordered frontend-aligned column.\n";
