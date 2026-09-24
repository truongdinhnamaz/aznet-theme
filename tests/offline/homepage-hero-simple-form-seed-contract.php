<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$heroAdmin = (string) file_get_contents($root . '/inc/admin/homepage-hero.php');
$homepageAdmin = (string) file_get_contents($root . '/inc/admin/homepage.php');

foreach ([
    'homepage_hero_form_seed_model_from_public_sources',
    'homepage_hero_form_is_default_model',
] as $function) {
    assert(str_contains($heroAdmin, 'function ' . $function . '('), "Missing Hero seed helper: {$function}");
}

assert(str_contains($heroAdmin, "homepage_page_reference((int)homepage_source_value('law-01','hero_page'"), 'Hero seed must prefer the mapped legacy Hero Page when available.');
assert(str_contains($heroAdmin, "get_bloginfo('name')"), 'Hero seed must preserve the current site title when building the easy form.');
assert(str_contains($heroAdmin, "get_bloginfo('description')"), 'Hero seed must preserve the current site tagline when building the easy form.');
assert(str_contains($heroAdmin, "homepage_hero_form_managed_content(homepage_hero_form_seed_model_from_public_sources("), 'New Hero draft must be created from the current public Hero model, not placeholder copy.');
assert(str_contains($homepageAdmin, "homepage_hero_form_is_default_model( $model )"), 'Existing placeholder draft must display seeded public content in the simple form.');

echo "PASS: Hero simple form seeds from current public Hero\n";
