<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$requiredFiles = [
    'inc/theme/archive-presentation.php',
    'template-parts/archive/law01-category.php',
    'template-parts/archive/law01-item.php',
    'assets/css/components/archive-law-01.css',
];

foreach ($requiredFiles as $relative) {
    if (!is_file($root . '/' . $relative)) {
        $fail('missing Law 01 category archive artifact: ' . $relative);
    }
}

$archive = (string) file_get_contents($root . '/archive.php');
$helper = (string) file_get_contents($root . '/inc/theme/archive-presentation.php');
$template = (string) file_get_contents($root . '/template-parts/archive/law01-category.php');
$item = (string) file_get_contents($root . '/template-parts/archive/law01-item.php');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');
$bootstrap = (string) file_get_contents($root . '/inc/theme/bootstrap.php');
$css = (string) file_get_contents($root . '/assets/css/components/archive-law-01.css');

foreach ([
    'law01_category_archive_active()',
    "get_template_part( 'template-parts/archive/law01-category' )",
] as $needle) {
    if (!str_contains($archive, $needle)) {
        $fail('archive.php missing Law 01 category routing marker: ' . $needle);
    }
}

foreach ([
    'function law01_category_archive_active',
    "is_category()",
    'header_law01_active()',
    'function archive_contact_page_url',
    "setting( 'homepage_contact_page', 0 )",
] as $needle) {
    if (!str_contains($helper, $needle)) {
        $fail('archive presentation helper missing marker: ' . $needle);
    }
}

foreach ([
    'archive-presentation.php',
] as $needle) {
    if (!str_contains($bootstrap, $needle)) {
        $fail('bootstrap missing archive presentation module: ' . $needle);
    }
}

foreach ([
    'should_enqueue_law01_archive_asset',
    'enqueue_law01_archive_asset',
    '/assets/css/components/archive-law-01.css',
    "'aznet-theme-archive-law-01'",
] as $needle) {
    if (!str_contains($assets, $needle)) {
        $fail('asset boundary missing Law 01 archive marker: ' . $needle);
    }
}

foreach ([
    'have_posts()',
    'the_post()',
    'template-parts/archive/law01-item',
    'the_posts_pagination(',
    "prev_text",
    "next_text",
    'wp_list_categories(',
    'archive_contact_page_url()',
    'aznet-theme-law01-archive__featured',
    'aznet-theme-law01-archive__latest-grid',
    'aznet-theme-law01-archive__editorial-list',
    'aznet-theme-law01-archive__sidebar',
] as $needle) {
    if (!str_contains($template, $needle)) {
        $fail('Law 01 category template missing presentation marker: ' . $needle);
    }
}

foreach ([
    "featured",
    "latest",
    "standard",
    'aznet-theme-law01-archive-item__media',
    'aznet-theme-law01-archive-item__meta',
    'aznet-theme-law01-archive-item__title',
    'aznet-theme-law01-archive-item__excerpt',
] as $needle) {
    if (!str_contains($item, $needle)) {
        $fail('Law 01 archive item missing variant/presentation marker: ' . $needle);
    }
}

foreach ([
    '.aznet-theme-law01-archive',
    '.aznet-theme-law01-archive__featured',
    '.aznet-theme-law01-archive__latest-grid',
    '.aznet-theme-law01-archive__editorial-layout',
    '.aznet-theme-law01-archive__editorial-list',
    '.aznet-theme-law01-archive-item--featured',
    '.aznet-theme-law01-archive-item--standard',
    '--law01-archive-burgundy',
    '@media (max-width:',
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Law 01 archive CSS missing marker: ' . $needle);
    }
}

$production = $archive . "\n" . $helper . "\n" . $template . "\n" . $item;
foreach ([
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    "is_category( 'tin-tuc' )",
    'REQUEST_URI',
    'parse_url(',
] as $forbidden) {
    if (str_contains($production, $forbidden)) {
        $fail('Law 01 archive must preserve native query/ownership and avoid route heuristics: ' . $forbidden);
    }
}

echo "PASS: Law 01 category archive Choice 1 presentation contract\n";
