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
assert(str_contains($pageExperience, 'function service_page_is_detail'), 'service detail detector missing');
assert(str_contains($pageExperience, "'homepage_services_page'"), 'service detector must use explicit mapped Services Page');
assert(str_contains($pageExperience, 'function service_page_contact_url'), 'service contact resolver missing');
assert(str_contains($pageExperience, 'function service_page_siblings'), 'service sibling resolver missing');
assert(str_contains($pageExperience, 'function service_hub_is_current_page'), 'mapped Services Hub detector missing');
assert(str_contains($pageExperience, 'function service_hub_items'), 'Services Hub item resolver missing');
assert(str_contains($pageExperience, 'function service_hub_latest_posts'), 'Services Hub latest-post resolver missing');
assert(str_contains($pageExperience, 'function service_hub_support_page'), 'Services Hub support-page resolver missing');
assert(!str_contains($pageExperience, 'get_page_by_path'), 'service presentation must not infer by slug/path');
assert(!str_contains($pageExperience, 'sanitize_title'), 'service presentation must not infer by title/slug');
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
assert(str_contains($shared, 'service_page_is_detail'), 'shared Page renderer must branch only on explicit service context');
assert(str_contains($shared, 'aznet-theme-page--service-detail'), 'service detail presentation class missing');
assert(str_contains($shared, 'aznet-theme-page__service-actions'), 'service CTA presentation missing');
assert(str_contains($shared, 'aznet-theme-page__service-siblings'), 'sibling service navigation missing');
assert(str_contains($shared, 'aznet-theme-page--service-hub'), 'mapped Services Hub presentation class missing');
assert(str_contains($shared, 'aznet-theme-service-hub__selection'), 'Services Hub selection section missing');
assert(str_contains($shared, 'aznet-theme-service-hub__orientation'), 'Services Hub orientation section missing');
assert(str_contains($shared, 'aznet-theme-service-hub__knowledge'), 'Services Hub knowledge section missing');
assert(str_contains($shared, 'aznet-theme-service-hub__final-cta'), 'Services Hub final CTA missing');
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
assert(str_contains($assets, 'function should_enqueue_service_page_assets(): bool'), 'service asset guard missing');
assert(str_contains($assets, "'aznet-theme-service-page'"), 'service asset handle missing');
assert(str_contains($assets, "'/assets/css/components/service-page.css'"), 'service asset path missing');
assert(str_contains($assets, 'function should_enqueue_service_hub_assets(): bool'), 'Services Hub asset guard missing');
assert(str_contains($assets, "'aznet-theme-service-hub'"), 'Services Hub asset handle missing');
assert(str_contains($assets, "'/assets/css/components/service-hub.css'"), 'Services Hub asset path missing');

$hubCssPath = $root . '/assets/css/components/service-hub.css';
assert(is_file($hubCssPath), 'service-hub.css missing');
$hubCss = file_get_contents($hubCssPath);
assert(is_string($hubCss));
foreach ([
    '.aznet-theme-page--service-hub',
    '.aznet-theme-service-hub__selection',
    '.aznet-theme-service-hub__card',
    '.aznet-theme-service-hub__orientation',
    '.aznet-theme-service-hub__knowledge',
    '.aznet-theme-service-hub__final-cta',
] as $hubSelector) {
    assert(str_contains($hubCss, $hubSelector), 'Services Hub CSS missing selector ' . $hubSelector);
}
assert(!preg_match('/#(?:[0-9a-fA-F]{3}){1,2}\\b/', $hubCss), 'Services Hub CSS must use semantic Theme tokens rather than hard-coded colors');
assert(!str_contains($hubCss, 'var(--aznet-theme-surface-inverse)'), 'Services Hub must reuse the About light editorial language instead of a dark inverse surface');
assert(!str_contains($hubCss, 'var(--aznet-theme-on-inverse)'), 'Services Hub must not introduce an inverse text system separate from About');

$tokensCss = file_get_contents($root . '/assets/css/tokens.css');
assert(is_string($tokensCss), 'tokens.css missing');
foreach ([
    '--aznet-theme-law01-brand-red: #8f111b;',
    '--aznet-theme-law01-brand-red-deep: #6d0b13;',
    '--aznet-theme-law01-warm-paper: #fffaf2;',
    '--aznet-theme-law01-body: #6f625b;',
] as $law01SharedPaletteToken) {
    assert(str_contains($tokensCss, $law01SharedPaletteToken), 'missing shared Law01 inner-page palette token: ' . $law01SharedPaletteToken);
}
assert(str_contains($shared, 'aznet-theme-page--law01-burgundy-gold'), 'mapped inner Page must expose the configured Law01 Burgundy presentation class');
preg_match_all('/var\\((--aznet-theme-[a-z0-9-]+)/i', $hubCss, $hubTokenUses);
preg_match_all('/(--aznet-theme-[a-z0-9-]+)\\s*:/i', $tokensCss . "\n" . $hubCss, $hubTokenDefinitions);
$undefinedHubTokens = array_values(array_diff(array_unique($hubTokenUses[1]), array_unique($hubTokenDefinitions[1])));
assert([] === $undefinedHubTokens, 'Services Hub CSS uses undefined Theme tokens: ' . implode(', ', $undefinedHubTokens));

$serviceCssPath = $root . '/assets/css/components/service-page.css';
assert(is_file($serviceCssPath), 'service-page.css missing');
$serviceCss = file_get_contents($serviceCssPath);
assert(is_string($serviceCss));
assert(str_contains($serviceCss, '.aznet-theme-page--service-detail'));
assert(str_contains($serviceCss, '.aznet-theme-page__service-actions'));
assert(str_contains($serviceCss, '.aznet-theme-page__service-siblings'));
assert(str_contains($serviceCss, 'var(--aznet-theme-accent)'));
assert(!preg_match('/#(?:[0-9a-fA-F]{3}){1,2}\b/', $serviceCss), 'service CSS must use semantic Theme tokens rather than hard-coded colors');

$style = file_get_contents($root . '/style.css');
$functions = file_get_contents($root . '/functions.php');
assert(is_string($style) && preg_match('/^Version:\s*([0-9]+\.[0-9]+\.[0-9]+)\s*$/mi', $style, $styleVersion) === 1);
assert(is_string($functions) && preg_match("/define\(\s*'AZNET_THEME_VERSION'\s*,\s*'([0-9]+\.[0-9]+\.[0-9]+)'\s*\)/", $functions, $functionVersion) === 1);
assert($styleVersion[1] === $functionVersion[1]);

echo "PASS: Y1 Page Experience ownership/settings/renderer contract\n";
