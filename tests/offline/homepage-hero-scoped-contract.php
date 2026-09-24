<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);$heroAdmin=(string)file_get_contents($root.'/inc/admin/homepage-hero.php');$homepageAdmin=(string)file_get_contents($root.'/inc/admin/homepage.php');$heroTemplate=(string)file_get_contents($root.'/template-parts/homepage/law-01/hero.php');$bootstrap=(string)file_get_contents($root.'/inc/admin/bootstrap.php');
foreach(['homepage_law01_hero_block','homepage_law01_hero_variant','homepage_preset_scope','aznet_theme_migrate_legacy_homepage_hero','handle_homepage_hero_legacy_migration',"'post_status'","'draft'",'wp_insert_post(',"homepage_source_value( 'law-01', 'hero_page'"] as $n)assert(str_contains($heroAdmin.$homepageAdmin.$bootstrap,$n),"Missing Hero contract: {$n}");
assert(!str_contains($heroAdmin,"\$theme_settings['homepage_hero_block'] ="));assert(!str_contains($heroAdmin,"\$theme_settings['homepage_hero_variant'] ="));
assert(str_contains($heroTemplate,"homepage_source_value( 'law-01', 'hero' )"));assert(str_contains($heroTemplate,"homepage_source_value( 'law-01', 'hero_page' )"));assert(str_contains($homepageAdmin,"'law-01' === \$active_preset"));
echo "PASS: D-038 scoped Law 01 Hero migration contract\n";
