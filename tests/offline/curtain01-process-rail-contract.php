<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings_path = $root . '/inc/theme/settings.php';
$composer_path = $root . '/inc/theme/homepage-composer.php';
$surface_path = $root . '/inc/theme/homepage-surface-map.php';
$admin_path = $root . '/inc/admin/homepage.php';
$authoring_path = $root . '/inc/theme/homepage-authoring.php';
$template_path = $root . '/template-parts/homepage/curtain-01/process.php';
$css_path = $root . '/assets/css/components/homepage-curtain-01.css';

$settings = is_file($settings_path) ? file_get_contents($settings_path) : false;
$composer = is_file($composer_path) ? file_get_contents($composer_path) : false;
$surface = is_file($surface_path) ? file_get_contents($surface_path) : false;
$admin = is_file($admin_path) ? file_get_contents($admin_path) : false;
$authoring = is_file($authoring_path) ? file_get_contents($authoring_path) : false;
$css = is_file($css_path) ? file_get_contents($css_path) : false;

if (! is_string($settings) || ! is_string($composer) || ! is_string($surface) || ! is_string($admin) || ! is_string($authoring) || ! is_string($css)) {
    fwrite(STDERR, "FAIL: Curtain 01 process source files missing.\n");
    exit(1);
}

foreach ([
    "'homepage_curtain01_process_page' => 0",
    "'homepage_curtain01_process_page' => \$normalize_id",
] as $needle) {
    if (! str_contains($settings, $needle)) {
        fwrite(STDERR, "FAIL: Rèm 01 process source must use its own normalized setting, isolated from Law 01: {$needle}\n");
        exit(1);
    }
}

if (! str_contains($composer, "homepage_effective_surface_map( 'curtain-01' )")) {
    fwrite(STDERR, "FAIL: Curtain 01 composer must consume the shared effective surface map.\n");
    exit(1);
}

$curtain_start = strpos($surface, 'function homepage_curtain01_effective_surface_map(');
$curtain_end = strpos($surface, 'function homepage_effective_surface_map(', false === $curtain_start ? 0 : $curtain_start);
if (false === $curtain_start || false === $curtain_end || $curtain_end <= $curtain_start) {
    fwrite(STDERR, "FAIL: Curtain 01 effective surface-map function boundary is missing.\n");
    exit(1);
}
$curtain_map = substr($surface, $curtain_start, $curtain_end - $curtain_start);
$catalogue_pos = strpos($curtain_map, "'catalogue'");
$process_pos = strpos($curtain_map, "'process'");
$knowledge_pos = strpos($curtain_map, "'knowledge'");
if (false === $catalogue_pos || false === $process_pos || false === $knowledge_pos
    || ! ($catalogue_pos < $process_pos && $process_pos < $knowledge_pos)) {
    fwrite(STDERR, "FAIL: shared Curtain 01 map must place process after catalogue and before knowledge.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 process template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach ([
    "homepage_effective_source_value( 'curtain-01', 'process' )",
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

if (! str_contains($authoring, "'process'     => [ 'type' => 'page', 'key' => 'homepage_curtain01_process_page'")
    || ! str_contains($admin, "return [ 'hero', 'proof', 'about', 'process', 'projects', 'knowledge', 'contact' ]")) {
    fwrite(STDERR, "FAIL: Homepage authoring must expose the independent Curtain 01 process Page through the preset registry.\n");
    exit(1);
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
