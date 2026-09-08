<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$patternPath = $root . '/patterns/homepage-professional-services.php';
$patternsPath = $root . '/inc/theme/patterns.php';
$frontPagePath = $root . '/front-page.php';

function homepage_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (!is_file($patternPath)) {
    homepage_fail('homepage professional-services pattern missing');
}

$pattern = (string) file_get_contents($patternPath);
$patterns = (string) file_get_contents($patternsPath);
$frontPage = (string) file_get_contents($frontPagePath);

$required = [
    'Title: Homepage — Professional Services',
    'Slug: aznet-theme/homepage-professional-services',
    'Categories: aznet-theme-pages',
    'aznet-theme-homepage-blueprint',
    'aznet-theme-homepage-blueprint__hero',
    'aznet-theme-homepage-blueprint__trust',
    'id="services"',
    'aznet-theme-homepage-blueprint__about',
    'aznet-theme-homepage-blueprint__process',
    'aznet-theme-homepage-blueprint__articles',
    '<!-- wp:query ',
    '"postType":"post"',
    'aznet-theme-homepage-blueprint__faq',
    '<!-- wp:details -->',
    'id="contact"',
];

foreach ($required as $needle) {
    if (!str_contains($pattern, $needle)) {
        homepage_fail("pattern missing required marker: {$needle}");
    }
}

if (1 !== substr_count($pattern, '<h1')) {
    homepage_fail('homepage blueprint must contain exactly one H1');
}

if (!str_contains($patterns, "'aznet-theme-pages'")) {
    homepage_fail('AZnet Pages pattern category missing');
}

$forbidden = [
    'WP_Query',
    'query_posts(',
    'get_option(',
    'update_option(',
    'get_post_meta(',
    'update_post_meta(',
    '$wpdb',
    'page_on_front',
    'wp_insert_post(',
    'wp_update_post(',
    'template_redirect',
    'choiceguide_',
    'journey_',
    'schema.org',
    'application/ld+json',
    'rel="canonical"',
    'og:',
];

foreach ($forbidden as $needle) {
    if (str_contains($pattern, $needle)) {
        homepage_fail("pattern contains forbidden coupling/mutation: {$needle}");
    }
}

if (!str_contains($frontPage, 'the_content();')) {
    homepage_fail('front-page.php must preserve WordPress Page content boundary');
}

echo "PASS: Homepage blueprint ownership/structure contract\n";
