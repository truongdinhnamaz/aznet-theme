<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$composer_path = $root . '/inc/theme/homepage-composer.php';
$template_path = $root . '/template-parts/homepage/curtain-01/process.php';
$css_path = $root . '/assets/css/components/homepage-curtain-01.css';

$composer = is_file($composer_path) ? file_get_contents($composer_path) : false;
$css = is_file($css_path) ? file_get_contents($css_path) : false;

if (! is_string($composer) || ! is_string($css)) {
    fwrite(STDERR, "FAIL: Curtain 01 process source files missing.\n");
    exit(1);
}

$curtain_sections = "[ 'about', 'category-showcase', 'catalogue', 'process', 'knowledge', 'final-cta' ]";
if (! str_contains($composer, $curtain_sections)) {
    fwrite(STDERR, "FAIL: Curtain 01 process must render after catalogue and before knowledge.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 process template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach ([
    "setting( 'homepage_process_page', 0 )",
    'homepage_page_reference',
    "apply_filters( 'the_content'",
    'aznet-theme-curtain01-process__rail',
] as $needle) {
    if (! is_string($template) || ! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: process section must consume the mapped WordPress Page and expose the rail presentation hook: {$needle}\n");
        exit(1);
    }
}

foreach ([
    'Khảo sát không gian',
    'Tư vấn giải pháp',
    'Chọn mẫu và vật liệu',
    'Đo đạc',
    'Sản xuất',
    'Thi công',
    'Nghiệm thu và bảo hành',
] as $copy) {
    if (is_string($template) && str_contains($template, $copy)) {
        fwrite(STDERR, "FAIL: process business copy must remain WordPress-owned, not hard-coded in Theme PHP.\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-curtain01-process {',
    '.aznet-theme-curtain01-process__rail ol {',
    'grid-template-columns: repeat(7, minmax(0, 1fr));',
    'counter-reset: aznet-curtain01-process;',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: approved Curtain 01 process-rail presentation missing: {$needle}\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 process rail contract\n";
