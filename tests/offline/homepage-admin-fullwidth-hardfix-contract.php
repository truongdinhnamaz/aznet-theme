<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$bootstrap = file_get_contents($root . '/inc/admin/bootstrap.php');
$control = file_get_contents($root . '/inc/admin/control-center.php');
$css = file_get_contents($root . '/assets/css/admin/control-center.css');

if (false === $bootstrap || false === $control || false === $css) {
    fwrite(STDERR, "FAIL: unable to read admin sources\n");
    exit(2);
}

foreach ([
    "wp_add_inline_style(",
    ".aznet-theme-homepage-section-grid,.aznet-theme-homepage-section-list",
    "width:100%!important",
    "grid-template-columns:none!important",
    "flex:0 0 100%!important",
    "float:none!important",
] as $required) {
    if (! str_contains($bootstrap, $required)) {
        fwrite(STDERR, "FAIL: missing hard full-width admin guard: {$required}\n");
        exit(1);
    }
}

if (! str_contains($control, 'aznet-theme-control-center--homepage')) {
    fwrite(STDERR, "FAIL: Homepage Control Center has no page-scoped full-width class.\n");
    exit(1);
}

foreach ([
    '.aznet-theme-control-center--homepage{max-width:none',
    '.aznet-theme-homepage-section-grid,.aznet-theme-homepage-section-list',
    'width:100%!important',
] as $required) {
    if (! str_contains($css, $required)) {
        fwrite(STDERR, "FAIL: stylesheet missing full-width rule: {$required}\n");
        exit(1);
    }
}

echo "PASS: Homepage admin is full-width and single-column with legacy/current guards.\n";
