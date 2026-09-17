<?php
/**
 * Y1 Page Experience ownership/settings/renderer contract.
 *
 * @package AZnetTheme
 */

declare(strict_types=1);

$root = dirname(__DIR__, 2);

$pageExperience = file_get_contents($root . '/inc/theme/page-experience.php');
assert(is_string($pageExperience));
assert(str_contains($pageExperience, 'function page_variant'));
assert(str_contains($pageExperience, 'function page_breadcrumb_items'));
assert(str_contains($pageExperience, 'function page_excerpt'));
assert(!str_contains($pageExperience, 'application/ld+json'));
assert(!str_contains($pageExperience, 'schema.org'));

foreach (['page.php', 'page-templates/wide.php', 'page-templates/landing.php'] as $templatePath) {
    $template = file_get_contents($root . '/' . $templatePath);
    assert(is_string($template));
    assert(str_contains($template, "get_template_part( 'template-parts/content/page' )"));
    assert(1 === substr_count($template, '<main id="main" class="aznet-theme-main">'));
}

$shared = file_get_contents($root . '/template-parts/content/page.php');
assert(is_string($shared));
assert(str_contains($shared, 'aznet-theme-page'));
assert(str_contains($shared, 'page_variant'));
assert(str_contains($shared, 'page_breadcrumb_items'));
assert(str_contains($shared, 'page_excerpt'));
assert(str_contains($shared, 'the_title'));
assert(str_contains($shared, 'the_content'));
assert(str_contains($shared, 'wp_link_pages'));
assert(!str_contains($shared, 'application/ld+json'));
assert(!str_contains($shared, 'schema.org'));

$assets = file_get_contents($root . '/inc/theme/assets.php');
assert(is_string($assets));
assert(str_contains($assets, 'function should_enqueue_page_assets(): bool'));
assert(str_contains($assets, "'aznet-theme-page'"));
assert(str_contains($assets, "'/assets/css/components/page.css'"));

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
assert(is_string($style) && preg_match('/^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*$/mi', $style, $styleVersion) === 1);
assert(is_string($functions) && preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'\s*\)/", $functions, $functionVersion) === 1);
assert($styleVersion[1] === $functionVersion[1]);

echo "PASS: Y1 Page Experience ownership/settings/renderer contract\n";