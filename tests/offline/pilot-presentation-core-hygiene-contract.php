<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$core_css = file_get_contents($root . '/assets/css/components/page.css');
if (false === $core_css) {
    fwrite(STDERR, "FAIL: unable to read generic page.css\n");
    exit(1);
}

foreach (['.page-id-', 'rqa-'] as $forbidden) {
    if (str_contains($core_css, $forbidden)) {
        fwrite(STDERR, "FAIL: generic page.css still contains pilot-specific selector {$forbidden}\n");
        exit(2);
    }
}

$compat_path = $root . '/assets/css/compatibility/curtain01-rqa-pages.css';
if (! is_file($compat_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 RQA compatibility asset missing\n");
    exit(3);
}

$assets = file_get_contents($root . '/inc/theme/assets.php');
if (false === $assets) {
    fwrite(STDERR, "FAIL: unable to read assets.php\n");
    exit(4);
}

foreach ([
    'enqueue_curtain01_rqa_page_compat_asset',
    "/assets/css/compatibility/curtain01-rqa-pages.css",
    "visual_preset()",
    "'curtain-01'",
    "should_enqueue_page_assets()",
] as $needle) {
    if (! str_contains($assets, $needle)) {
        fwrite(STDERR, "FAIL: Curtain 01 compatibility enqueue boundary missing {$needle}\n");
        exit(5);
    }
}

echo "PASS: generic Page Core is free of pilot-specific RQA/Page-ID selectors and legacy compatibility is isolated.\n";
