<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings_path = $root . '/inc/theme/settings.php';
$composer_path = $root . '/inc/theme/homepage-composer.php';
$admin_path = $root . '/inc/admin/homepage.php';
$template_path = $root . '/template-parts/homepage/curtain-01/projects.php';
$css_path = $root . '/assets/css/components/homepage-curtain-01.css';

$settings = is_file($settings_path) ? file_get_contents($settings_path) : false;
$composer = is_file($composer_path) ? file_get_contents($composer_path) : false;
$admin = is_file($admin_path) ? file_get_contents($admin_path) : false;
$css = is_file($css_path) ? file_get_contents($css_path) : false;

if (! is_string($settings) || ! is_string($composer) || ! is_string($admin) || ! is_string($css)) {
    fwrite(STDERR, "FAIL: Curtain 01 project showcase source files missing.\n");
    exit(1);
}

foreach ([
    "'homepage_curtain01_projects_term' => 0",
    "'homepage_curtain01_projects_term' => \$normalize_id",
] as $needle) {
    if (! str_contains($settings, $needle)) {
        fwrite(STDERR, "FAIL: Rèm 01 project showcase needs its own normalized WordPress Category mapping: {$needle}\n");
        exit(1);
    }
}

$curtain_sections = "[ 'about', 'category-showcase', 'catalogue', 'process', 'projects', 'knowledge', 'final-cta' ]";
if (! str_contains($composer, $curtain_sections)) {
    fwrite(STDERR, "FAIL: Curtain 01 projects must render after process and before knowledge without reordering approved sections.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 project showcase template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach ([
    "setting( 'homepage_curtain01_projects_term', 0 )",
    'homepage_category_reference',
    'homepage_latest_posts',
    'homepage_ledger_ids',
    'homepage_ledger_add',
    'aznet-theme-curtain01-projects__grid',
] as $needle) {
    if (! is_string($template) || ! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: project showcase must consume one explicit WordPress Category, fail soft, and expose presentation hooks: {$needle}\n");
        exit(1);
    }
}

foreach ([
    'Căn hộ',
    'Biệt thự',
    'Khách sạn',
    'Văn phòng',
    'Chờ dữ liệu xác minh',
    'Rèm Quốc Anh',
] as $forbidden_copy) {
    if (is_string($template) && str_contains($template, $forbidden_copy)) {
        fwrite(STDERR, "FAIL: project-domain copy/facts must remain WordPress-owned, not hard-coded in Theme PHP: {$forbidden_copy}\n");
        exit(1);
    }
}

if (! str_contains($admin, 'homepage_curtain01_projects_term') || ! str_contains($admin, 'Công trình Rèm 01')) {
    fwrite(STDERR, "FAIL: Homepage admin must expose the independent Rèm 01 project Category mapping.\n");
    exit(1);
}

foreach ([
    '.aznet-theme-curtain01-projects {',
    '.aznet-theme-curtain01-projects__grid {',
    '.aznet-theme-curtain01-project-card {',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: Curtain 01 project showcase presentation missing: {$needle}\n");
        exit(1);
    }
}

echo "PASS: Curtain 01 project showcase contract\n";
