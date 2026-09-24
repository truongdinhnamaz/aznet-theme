<?php
declare(strict_types=1);
if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ ); }
$root=dirname(__DIR__,2);
require_once $root.'/inc/theme/settings.php';
require_once $root.'/inc/theme/homepage-authoring.php';
$module=$root.'/inc/admin/homepage-migration.php';
if(!is_file($module)){fwrite(STDERR,"FAIL: Homepage preset migration module missing\n");exit(1);}
require_once $module;
$before=AZnet\Theme\normalize_settings([
 'homepage_hero_block'=>77,'homepage_hero_variant'=>'inverse','homepage_hero_page'=>44,
 'homepage_services_page'=>81,'homepage_about_page'=>91,'homepage_team_page'=>92,
 'homepage_knowledge_terms'=>[17,19],'homepage_case_analysis_term'=>20,'homepage_legal_news_term'=>21,
 'homepage_process_page'=>93,'homepage_faq_page'=>94,'homepage_contact_page'=>95,
 'homepage_curtain01_about_page'=>300,
]);
$after=AZnet\Theme\Admin\migrate_homepage_preset_references('law-01',$before);
assert($after['homepage_law01_sources_initialized']===true);
assert($after['homepage_law01_hero_block']===77);
assert($after['homepage_law01_hero_variant']==='inverse');
assert($after['homepage_law01_hero_page']===44);
assert($after['homepage_law01_services_page']===81);
assert($after['homepage_law01_about_page']===91);
assert($after['homepage_law01_team_page']===92);
assert($after['homepage_law01_knowledge_terms']===[17,19]);
assert($after['homepage_law01_case_analysis_term']===20);
assert($after['homepage_law01_legal_news_term']===21);
assert($after['homepage_law01_process_page']===93);
assert($after['homepage_law01_faq_page']===94);
assert($after['homepage_law01_contact_page']===95);
assert($after['homepage_curtain01_about_page']===300);
assert($after['homepage_about_page']===91);
assert(AZnet\Theme\Admin\migrate_homepage_preset_references('law-01',$after)===$after);
$keep=AZnet\Theme\normalize_settings(['homepage_about_page'=>91,'homepage_law01_about_page'=>123]);
$keep=AZnet\Theme\Admin\migrate_homepage_preset_references('law-01',$keep);
assert($keep['homepage_law01_about_page']===123);

$curtain=AZnet\Theme\normalize_settings([
 'homepage_hero_block'=>11,'homepage_proof_block'=>12,'homepage_about_page'=>13,
 'homepage_about_image'=>14,'homepage_knowledge_terms'=>[15],'homepage_contact_page'=>16,
 'homepage_curtain01_process_page'=>17,'homepage_curtain01_projects_term'=>18,
]);
$curtain=AZnet\Theme\Admin\migrate_homepage_preset_references('curtain-01',$curtain);
assert($curtain['homepage_curtain01_sources_initialized']===true);
assert($curtain['homepage_curtain01_hero_block']===11);
assert($curtain['homepage_curtain01_proof_block']===12);
assert($curtain['homepage_curtain01_about_page']===13);
assert($curtain['homepage_curtain01_about_image']===14);
assert($curtain['homepage_curtain01_knowledge_terms']===[15]);
assert($curtain['homepage_curtain01_contact_page']===16);
assert($curtain['homepage_curtain01_process_page']===17);
assert($curtain['homepage_curtain01_projects_term']===18);

$source=(string)file_get_contents($module);
foreach(['wp_insert_post(','wp_update_post(','wp_delete_post(','wp_insert_term(','wp_update_term(','wp_delete_term(','set_post_thumbnail('] as $forbidden) assert(!str_contains($source,$forbidden),"Migration mutates content: {$forbidden}");
foreach(['current_user_can(','check_admin_referer(','aznet_theme_migrate_homepage_preset'] as $required) assert(str_contains($source,$required),"Migration action missing: {$required}");
echo "PASS: D-038 explicit reference-only preset migration\n";
