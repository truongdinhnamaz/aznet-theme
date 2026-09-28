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

$runtime_contract = file_get_contents($root . '/tests/runtime/homepage-curtain01-map-runtime.php');
if (
    ! is_string($runtime_contract)
    || ! str_contains($composer, "homepage_effective_surface_map( 'curtain-01' )")
    || ! str_contains($runtime_contract, "'process',")
    || ! str_contains($runtime_contract, "'projects',")
    || ! str_contains($runtime_contract, "'knowledge',")
) {
    fwrite(STDERR, "FAIL: Curtain 01 projects must remain in the accepted shared surface-map order between process and knowledge.\n");
    exit(1);
}

if (! is_file($template_path)) {
    fwrite(STDERR, "FAIL: Curtain 01 project showcase template is missing.\n");
    exit(1);
}

$template = file_get_contents($template_path);
foreach ([
    "homepage_effective_source_value( 'curtain-01', 'projects' )",
    'homepage_category_reference',
    'homepage_latest_posts',
    'homepage_ledger_ids',
    'homepage_ledger_add',
    "if ( [] === \$posts )",
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

$authoring = file_get_contents($root . '/inc/theme/homepage-authoring.php');
if (
    ! is_string($authoring)
    || ! str_contains($authoring, "'projects'    => [ 'type' => 'category', 'key' => 'homepage_curtain01_projects_term'")
    || ! str_contains($admin, "'projects' => __( 'Công trình', 'aznet-theme' )")
    || ! str_contains($admin, "homepage_effective_source_key( \$active_preset, \$slot )")
) {
    fwrite(STDERR, "FAIL: Homepage admin must expose the independent Rèm 01 project Category through the shared typed-source model.\n");
    exit(1);
}

foreach ([
    "homepage_source_value( \$preset, 'projects', \$s )",
    "null === homepage_category_reference( \$project_term_id ) ? 'INVALID'",
    "[] === homepage_latest_posts( [ \$project_term_id ], 1 ) ? 'EMPTY' : 'READY'",
] as $needle) {
    if (! str_contains($admin, $needle)) {
        fwrite(STDERR, "FAIL: Homepage diagnostics must expose fail-soft project source state through the current shared diagnostics model: {$needle}\n");
        exit(1);
    }
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
