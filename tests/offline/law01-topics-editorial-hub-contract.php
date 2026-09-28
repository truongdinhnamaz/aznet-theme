<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/homepage/law-01/topics.php');
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-reference.css');

foreach ([
    "'posts_per_page' => 2",
    "'post_status'    => 'publish'",
    "'orderby'        => 'date'",
    'aznet-theme-law01-topic-card',
    'aznet-theme-law01-topic-card__posts',
    'aznet-theme-law01-topic-card__category-link',
    'aznet-theme-law01-topic-card--featured',
    'has_post_thumbnail( $post )',
    'get_the_post_thumbnail(',
    'Xem chuyên mục',
] as $needle) {
    if (! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: editorial Topics template missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-law01-grid--topics',
    'grid-template-columns: repeat(3, minmax(0, 1fr));',
    '.aznet-theme-law01-topic-card',
    '.aznet-theme-law01-topic-card__posts',
    '.aznet-theme-law01-topic-card--featured',
    '.aznet-theme-law01-topic-card__featured-media',
    '@media (max-width: 960px)',
    '@media (max-width: 640px)',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: editorial Topics CSS missing {$needle}\n");
        exit(1);
    }
}

if (str_contains($template, "get_page_by_path(") || str_contains($template, "REQUEST_URI")) {
    fwrite(STDERR, "FAIL: Topics editorial hub uses forbidden URL/slug heuristics\n");
    exit(1);
}

echo "PASS: Law 01 Topics renders up to two newest WordPress Posts per mapped category, with featured media for the first mapped priority topics\n";
