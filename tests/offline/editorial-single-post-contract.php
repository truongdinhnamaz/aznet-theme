<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_editorial_single(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function source_editorial_single(string $path): string {
    if (! is_file($path)) {
        fail_editorial_single('missing required file: ' . $path);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fail_editorial_single('cannot read required file: ' . $path);
    }

    return $source;
}

$single = source_editorial_single($root . '/single.php');
$assets = source_editorial_single($root . '/inc/theme/assets.php');

if (! str_contains($single, "is_singular( 'post' )")) {
    fail_editorial_single('single.php must scope editorial composition to native Posts');
}

if (! str_contains($single, "get_template_part( 'template-parts/content/content', 'single' )")) {
    fail_editorial_single('single.php must delegate native Post composition to content-single.php');
}

if (! str_contains($single, "post_class( 'aznet-theme-entry aznet-theme-entry--post' )")) {
    fail_editorial_single('single.php must retain the generic fallback for non-Post single types');
}

$content = source_editorial_single($root . '/template-parts/content/content-single.php');
$meta = source_editorial_single($root . '/template-parts/content/meta.php');
$author = source_editorial_single($root . '/template-parts/content/author.php');
$articleCss = source_editorial_single($root . '/assets/css/components/article.css');

$requiredContentMarkers = [
    'the_title()',
    "get_template_part( 'template-parts/content/meta' )",
    'has_post_thumbnail()',
    'the_post_thumbnail(',
    'the_content()',
    'wp_link_pages(',
    'the_category(',
    '<nav class="aznet-theme-article__categories"',
    'the_tags(',
    'the_post_navigation(',
    "get_template_part( 'template-parts/content/author' )",
];

foreach ($requiredContentMarkers as $marker) {
    if (! str_contains($content, $marker)) {
        fail_editorial_single('content-single.php missing native editorial presentation marker: ' . $marker);
    }
}

$requiredMetaMarkers = [
    "get_the_author_meta( 'ID' )",
    'get_author_posts_url(',
    'get_the_author()',
    'get_the_date()',
    'get_the_time( DATE_W3C )',
    "get_the_modified_time( 'U' )",
    "get_the_time( 'U' )",
    'get_the_modified_date()',
];

foreach ($requiredMetaMarkers as $marker) {
    if (! str_contains($meta, $marker)) {
        fail_editorial_single('meta.php missing native publication marker: ' . $marker);
    }
}

$requiredAuthorMarkers = [
    "get_the_author_meta( 'ID' )",
    'get_avatar(',
    "get_the_author_meta( 'description'",
    'get_author_posts_url(',
];

foreach ($requiredAuthorMarkers as $marker) {
    if (! str_contains($author, $marker)) {
        fail_editorial_single('author.php missing WordPress-native fallback marker: ' . $marker);
    }
}

$requiredAssetMarkers = [
    "is_singular( 'post' )",
    "'aznet-theme-article'",
    "'/assets/css/components/article.css'",
];

foreach ($requiredAssetMarkers as $marker) {
    if (! str_contains($assets, $marker)) {
        fail_editorial_single('article asset scope missing marker: ' . $marker);
    }
}

$requiredCssMarkers = [
    '.aznet-theme-article',
    '.aznet-theme-article__meta',
    '.aznet-theme-article__content',
    '.aznet-theme-article .screen-reader-text',
    'blockquote',
    'table',
    ':focus-visible',
];

foreach ($requiredCssMarkers as $marker) {
    if (! str_contains($articleCss, $marker)) {
        fail_editorial_single('article.css missing editorial presentation marker: ' . $marker);
    }
}

$articleSources = $single . "\n" . $content . "\n" . $meta . "\n" . $author . "\n" . $assets;
$forbidden = [
    'new WP_Query',
    'WP_Query(',
    'get_posts(',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    '<meta name=',
    'rel="canonical"',
    'application/ld+json',
    'schema.org',
];

foreach ($forbidden as $marker) {
    if (str_contains($articleSources, $marker)) {
        fail_editorial_single('forbidden query/storage/SEO takeover marker found: ' . $marker);
    }
}

echo "PASS: editorial single-post presentation contract\n";
