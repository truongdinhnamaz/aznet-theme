<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);

function fail_editorial_listing(string $message): never {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function source_editorial_listing(string $path): string {
    if (! is_file($path)) {
        fail_editorial_listing('missing required file: ' . $path);
    }

    $source = file_get_contents($path);
    if (false === $source) {
        fail_editorial_listing('cannot read required file: ' . $path);
    }

    return $source;
}

$card = source_editorial_listing($root . '/template-parts/content/card.php');
$archive = source_editorial_listing($root . '/archive.php');
$search = source_editorial_listing($root . '/search.php');
$css = source_editorial_listing($root . '/assets/css/components/generic-content.css');

$requiredCardMarkers = [
    'has_post_thumbnail()',
    'the_post_thumbnail(',
    'get_the_date()',
    'get_the_time( DATE_W3C )',
    'the_title()',
    'the_excerpt()',
    'aznet-theme-card__media',
    'aznet-theme-card__meta',
    'aznet-theme-card__date',
];

foreach ($requiredCardMarkers as $marker) {
    if (! str_contains($card, $marker)) {
        fail_editorial_listing('listing card missing native scan-presentation marker: ' . $marker);
    }
}

$requiredLoopMarkers = [
    'have_posts()',
    'the_post()',
    "get_template_part( 'template-parts/content/card' )",
    'the_posts_pagination()',
];

foreach (['archive.php' => $archive, 'search.php' => $search] as $name => $source) {
    foreach ($requiredLoopMarkers as $marker) {
        if (! str_contains($source, $marker)) {
            fail_editorial_listing($name . ' must retain the native WordPress main loop marker: ' . $marker);
        }
    }
}

if (! str_contains($search, 'get_search_form()')) {
    fail_editorial_listing('search.php must retain the native WordPress no-results search form');
}

$requiredCssMarkers = [
    '.aznet-theme-card__media',
    '.aznet-theme-card__thumbnail',
    '.aznet-theme-card__meta',
    '.aznet-theme-card__date',
    '.aznet-theme-card__title',
    'overflow-wrap: anywhere',
    'aspect-ratio:',
    'object-fit: cover',
];

foreach ($requiredCssMarkers as $marker) {
    if (! str_contains($css, $marker)) {
        fail_editorial_listing('generic-content.css missing resilient editorial listing marker: ' . $marker);
    }
}

$listingSources = $card . "\n" . $archive . "\n" . $search;
$forbidden = [
    'new WP_Query',
    'WP_Query(',
    'query_posts(',
    'get_posts(',
    'pre_get_posts',
    'get_option(',
    'get_post_meta(',
    '$wpdb',
    '<meta name=',
    'rel="canonical"',
    'application/ld+json',
    'schema.org',
];

foreach ($forbidden as $marker) {
    if (str_contains($listingSources, $marker)) {
        fail_editorial_listing('forbidden query/storage/SEO takeover marker found: ' . $marker);
    }
}

echo "PASS: editorial listing/search presentation contract\n";
