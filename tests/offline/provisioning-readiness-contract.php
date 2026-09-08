<?php
declare(strict_types=1);
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/');
class WP_Post{public int $ID;public string $post_type='page';public string $post_status='publish';function __construct($id){$this->ID=$id;}}
$GLOBALS['fr_posts']=[10=>new WP_Post(10),20=>new WP_Post(20),30=>new WP_Post(30),40=>new WP_Post(40)];$GLOBALS['fr_opts']=['show_on_front'=>'page','page_on_front'=>10];$GLOBALS['fr_mods']=['aznet_theme_settings'=>['schema_version'=>2,'homepage_preset'=>'law-01','homepage_services_page'=>20,'homepage_about_page'=>30,'homepage_contact_page'=>40],'nav_menu_locations'=>['primary'=>9]];$GLOBALS['fr_meta']=[40=>['_aznet_theme_provisioned_by'=>'law01-v1']];
function get_post($id){return$GLOBALS['fr_posts'][$id]??null;}function get_option($k,$d=false){return$GLOBALS['fr_opts'][$k]??$d;}function get_theme_mod($k,$d=false){return$GLOBALS['fr_mods'][$k]??$d;}function get_post_meta($id,$k,$single=true){return$GLOBALS['fr_meta'][$id][$k]??'';}function get_nav_menu_locations(){return$GLOBALS['fr_mods']['nav_menu_locations'];}function has_custom_logo(){return false;}
$root=dirname(__DIR__,2);require_once$root.'/inc/theme/settings.php';require_once$root.'/inc/theme/homepage-composer.php';require_once$root.'/inc/theme/provisioning-provenance.php';$module=$root.'/inc/theme/provisioning-readiness.php';if(!is_file($module)){fwrite(STDERR,"FAIL: provisioning readiness module missing\n");exit(1);}require_once$module;
$status=\AZnet\Theme\provisioning_readiness();assert(in_array($status['status'],['READY','READY_WITH_WARNINGS','INCOMPLETE'],true));assert($status['setup_ready']===true);assert($status['status']==='READY_WITH_WARNINGS');$warnings=\AZnet\Theme\provisioning_launch_warnings();assert(count($warnings)>=2);
$src=file_get_contents($module);assert(str_contains($src,'READY_WITH_WARNINGS'));assert(str_contains($src,'INCOMPLETE'));assert(!str_contains($src,'Website đã sẵn sàng phát hành'));
echo "PASS: Provisioning Setup Ready vs Launch Ready contract\n";
