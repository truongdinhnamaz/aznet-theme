<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$about_path = $root . '/template-parts/homepage/curtain-01/about.php';
$css_path = $root . '/assets/css/components/homepage-curtain-01.css';

$about = is_file($about_path) ? file_get_contents($about_path) : false;
$css = is_file($css_path) ? file_get_contents($css_path) : false;

if (false === $about || false === $css) {
    fwrite(STDERR, "FAIL: Curtain 01 About source files missing.\n");
    exit(1);
}

foreach ([
    "homepage_about_page",
    "get_the_title",
    "post_excerpt",
    "get_the_post_thumbnail",
] as $needle) {
    if (! str_contains($about, $needle)) {
        fwrite(STDERR, "FAIL: About must remain WordPress-source-owned via typed Page title/excerpt/featured image. Missing: {$needle}\n");
        exit(1);
    }
}

foreach ([
    "aznet-theme-curtain01-about__history",
    "aznet-theme-curtain01-about__media-frame",
    "aznet-theme-curtain01-about__story-link",
    "aznet-theme-curtain01-about__quote",
] as $needle) {
    if (! str_contains($about, $needle)) {
        fwrite(STDERR, "FAIL: approved history-first About structure missing: {$needle}\n");
        exit(1);
    }
}

foreach ([
    ".aznet-theme-curtain01-about__history {",
    "grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);",
    ".aznet-theme-curtain01-about__media-frame {",
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: approved editorial history presentation missing: {$needle}\n");
        exit(1);
    }
}

if (str_contains($about, 'anh-nen-xua.png') || preg_match('/\\b993\\b/', $about)) {
    fwrite(STDERR, "FAIL: site-specific media must stay in WordPress, not be hard-coded into Theme presentation.\n");
    exit(1);
}

echo "PASS: Curtain 01 history-first About presentation contract\n";
