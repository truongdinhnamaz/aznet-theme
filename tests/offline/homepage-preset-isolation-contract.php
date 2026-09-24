<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ ); }
$root = dirname(__DIR__, 2);
require_once $root . '/inc/theme/settings.php';
$authoring = $root . '/inc/theme/homepage-authoring.php';
if ( ! is_file( $authoring ) ) {
    fwrite(STDERR, "FAIL: homepage authoring registry module missing\n");
    exit(1);
}
require_once $authoring;

$defaults = AZnet\Theme\settings_defaults();
$required = [
    'homepage_law01_sources_initialized',
    'homepage_curtain01_sources_initialized',
    'homepage_law01_hero_block',
    'homepage_law01_hero_variant',
    'homepage_law01_hero_page',
    'homepage_law01_services_page',
    'homepage_law01_about_page',
    'homepage_law01_team_page',
    'homepage_law01_knowledge_terms',
    'homepage_law01_case_analysis_term',
    'homepage_law01_legal_news_term',
    'homepage_law01_process_page',
    'homepage_law01_faq_page',
    'homepage_law01_contact_page',
    'homepage_curtain01_hero_block',
    'homepage_curtain01_proof_block',
    'homepage_curtain01_about_page',
    'homepage_curtain01_about_image',
    'homepage_curtain01_knowledge_terms',
    'homepage_curtain01_contact_page',
    'homepage_curtain01_process_page',
    'homepage_curtain01_projects_term',
];
foreach ( $required as $key ) {
    assert(array_key_exists($key, $defaults), "Missing scoped Homepage key: {$key}");
}
assert($defaults['schema_version'] === 3);
assert(AZnet\Theme\homepage_source_key('law-01', 'about') === 'homepage_law01_about_page');
assert(AZnet\Theme\homepage_source_key('curtain-01', 'about') === 'homepage_curtain01_about_page');
assert(AZnet\Theme\homepage_source_key('unknown', 'about') === null);
assert(AZnet\Theme\homepage_source_descriptor('law-01', 'services')['type'] === 'page');
assert(AZnet\Theme\homepage_source_descriptor('law-01', 'services')['children'] === true);

$legacy = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_knowledge_terms' => [17, 19],
    'homepage_hero_variant' => 'inverse',
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $legacy) === 91);
assert(AZnet\Theme\homepage_source_value('curtain-01', 'about', $legacy) === 91);
assert(AZnet\Theme\homepage_source_value('law-01', 'knowledge', $legacy) === [17, 19]);
assert(AZnet\Theme\homepage_source_value('law-01', 'hero_variant', $legacy) === 'inverse');

$scoped = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_law01_about_page' => 101,
    'homepage_curtain01_about_page' => 202,
    'homepage_knowledge_terms' => [17],
    'homepage_law01_knowledge_terms' => [18],
    'homepage_curtain01_knowledge_terms' => [19],
    'homepage_hero_variant' => 'inverse',
    'homepage_law01_hero_variant' => 'centered',
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $scoped) === 101);
assert(AZnet\Theme\homepage_source_value('curtain-01', 'about', $scoped) === 202);
assert(AZnet\Theme\homepage_source_value('law-01', 'knowledge', $scoped) === [18]);
assert(AZnet\Theme\homepage_source_value('curtain-01', 'knowledge', $scoped) === [19]);
assert(AZnet\Theme\homepage_source_value('law-01', 'hero_variant', $scoped) === 'centered');

$empty_scoped = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_law01_about_page' => 0,
    'homepage_knowledge_terms' => [17, 19],
    'homepage_law01_knowledge_terms' => [],
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $empty_scoped) === 91);
assert(AZnet\Theme\homepage_source_value('law-01', 'knowledge', $empty_scoped) === [17, 19]);

$initialized = AZnet\Theme\normalize_settings([
    'homepage_about_page' => 91,
    'homepage_law01_sources_initialized' => true,
    'homepage_law01_about_page' => 0,
]);
assert(AZnet\Theme\homepage_source_value('law-01', 'about', $initialized) === 0);

echo "PASS: D-038 preset-isolated Homepage source registry\n";
