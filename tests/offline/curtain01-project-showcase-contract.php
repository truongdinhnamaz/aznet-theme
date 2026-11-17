<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$settings_path = $root . '/inc/theme/settings.php';
$composer_path = $root . '/inc/theme/homepage-composer.php';
$surface_map_path = $root . '/inc/theme/homepage-surface-map.php';
$admin_path = $root . '/inc/admin/homepage.php';
$template_path = $root . '/template-parts/homepage/curtain-01/projects.php';
$css_path = $root . '/assets/css/components/homepage-curtain-01.css';

$settings = is_file($settings_path) ? file_get_contents($settings_path) : false;
$composer = is_file($composer_path) ? file_get_contents($composer_path) : false;
$surface_map = is_file($surface_map_path) ? file_get_contents($surface_map_path) : false;
$admin = is_file($admin_path) ? file_get_contents($admin_path) : false;
$css = is_file($css_path) ? file_get_contents($css_path) : false;

if (! is_string($settings) || ! is_string($composer) || ! is_string($surface_map) || ! is_string($admin) || ! is_string($css)) {
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

$process_pos = strpos($surface_map, "\$surfaces[] = homepage_surface_entry(\n                'process',");
$projects_pos = strpos($surface_map, "\$surfaces[] = homepage_surface_entry(\n            'projects',");
$knowledge_pos = strpos($surface_map, "\$surfaces[] = homepage_surface_entry(\n            'knowledge',");
if (
    false === $process_pos
    || false === $projects_pos
    || false === $knowledge_pos
    || ! ($process_pos < $projects_pos && $projects_pos < $knowledge_pos)
) {
    fwrite(STDERR, "FAIL: Curtain 01 shared surface map must keep projects after process and before knowledge.\n");
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

foreach ([
    "'projects' => __( 'Công trình', 'aznet-theme' )",
    "homepage_source_value( \$preset, 'projects', \$s )",
    "\$statuses[ \$slot ] = 'UNMAPPED'",
    "\$statuses[ \$slot ] = 'INVALID'",
    "homepage_latest_posts( [ \$id ], 1 ) ? 'EMPTY' : 'READY'",
] as $needle) {
    if (! str_contains($admin, $needle)) {
        fwrite(STDERR, "FAIL: Homepage admin must expose the Curtain project source through the shared typed authoring/diagnostic model: {$needle}\n");
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

// Images with portrait, landscape, or square intrinsic dimensions must not
// control card height. The third desktop item retains its split grid layout.
$css_rule = static function (string $selector) use ($css): string {
    $start = strpos($css, $selector . ' {');
    if (false === $start) {
        return '';
    }
    $end = strpos($css, '}', $start);
    return false === $end ? '' : substr($css, $start, $end - $start);
};

$media_rule = $css_rule('.aznet-theme-curtain01-project-card__media');
$image_rule = $css_rule('.aznet-theme-curtain01-project-card__image');
$third_rule = $css_rule('.aznet-theme-curtain01-project-card:nth-child(3) .aznet-theme-curtain01-project-card__media');
if (
    ! str_contains($media_rule, 'position: relative;')
    || ! str_contains($media_rule, 'height: clamp(')
    || str_contains($media_rule, 'min-height: clamp(')
    || ! str_contains($image_rule, 'position: absolute;')
    || ! str_contains($image_rule, 'inset: 0;')
    || ! str_contains($image_rule, 'object-fit: cover;')
    || str_contains($image_rule, 'min-height: inherit;')
    || ! str_contains($third_rule, 'height: auto;')
    || ! str_contains($third_rule, 'min-height: 22rem;')
) {
    fwrite(STDERR, "FAIL: project images must be clipped inside a bounded independent frame.\n");
    exit(1);
}

$mobile_offset = strpos($css, '@media (max-width: 640px)');
$mobile_css = false === $mobile_offset ? '' : substr($css, $mobile_offset);
if (
    ! preg_match(
        '~\.aznet-theme-curtain01-project-card__media,\s*\.aznet-theme-curtain01-project-card:nth-child\(3\) \.aznet-theme-curtain01-project-card__media\s*\{(?=[^}]*height:\s*auto;)(?=[^}]*min-height:\s*0;)(?=[^}]*aspect-ratio:\s*16\s*/\s*10;)[^}]*\}~',
        $mobile_css
    )
) {
    fwrite(STDERR, "FAIL: mobile project image frames need a bounded responsive ratio.\n");
    exit(1);
}

echo "PASS: Curtain 01 project showcase contract\n";
