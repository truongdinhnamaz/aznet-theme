<?php
declare(strict_types=1);
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/');
if(!defined('AZNET_THEME_TEST_PROVISIONING_FAILURE')) define('AZNET_THEME_TEST_PROVISIONING_FAILURE','homepage:map');
class WP_Error { public string $message; function __construct($c='e',$m='error'){$this->message=$m;} function get_error_message(){return $this->message;} }
function is_wp_error($v){return $v instanceof WP_Error;}
class WP_Post {public int $ID;public string $post_type='page';public string $post_status='publish';public string $post_title='';public string $post_content='';public int $post_parent=0; function __construct($id,$title='',$content='',$status='publish'){$this->ID=$id;$this->post_title=$title;$this->post_content=$content;$this->post_status=$status;}}
class WP_Term {public int $term_id;public string $name;public string $taxonomy='category'; function __construct($id,$n=''){$this->term_id=$id;$this->name=$n;}}
$GLOBALS['fake_posts']=[42=>new WP_Post(42,'Existing About','Keep About'),51=>new WP_Post(51,'Existing Contact','Keep Contact')];
$GLOBALS['fake_terms']=[];$GLOBALS['fake_next_post']=100;$GLOBALS['fake_post_meta']=[];$GLOBALS['fake_term_meta']=[];
$GLOBALS['fake_options']=['show_on_front'=>'posts','page_on_front'=>0];
$GLOBALS['fake_mods']=['aznet_theme_settings'=>['schema_version'=>2,'homepage_preset'=>'off'],'nav_menu_locations'=>[]];
function get_post($id){return $GLOBALS['fake_posts'][$id]??null;}
function get_pages($args=[]){return array_values($GLOBALS['fake_posts']);}
function get_categories($args=[]){return array_values($GLOBALS['fake_terms']);}
function wp_count_posts($type='post'){return (object)['publish'=>0,'draft'=>0,'private'=>0,'pending'=>0,'future'=>0];}
function get_option($k,$d=false){return $GLOBALS['fake_options'][$k]??$d;}
function update_option($k,$v){$GLOBALS['fake_options'][$k]=$v;return true;}
function get_theme_mod($k,$d=false){return $GLOBALS['fake_mods'][$k]??$d;}
function set_theme_mod($k,$v){$GLOBALS['fake_mods'][$k]=$v;}
function get_nav_menu_locations(){return $GLOBALS['fake_mods']['nav_menu_locations']??[];}
function wp_insert_post($data,$wp_error=false){$id=$GLOBALS['fake_next_post']++;$p=new WP_Post($id,$data['post_title']??'',$data['post_content']??'',$data['post_status']??'publish');$p->post_parent=(int)($data['post_parent']??0);$GLOBALS['fake_posts'][$id]=$p;return $id;}
function wp_delete_post($id,$force=false){if(!isset($GLOBALS['fake_posts'][$id]))return false;unset($GLOBALS['fake_posts'][$id]);return true;}
function update_post_meta($id,$k,$v){$GLOBALS['fake_post_meta'][$id][$k]=$v;return true;}
function get_post_meta($id,$k,$single=true){return $GLOBALS['fake_post_meta'][$id][$k]??'';}
function get_posts($args=[]){$out=[];foreach($GLOBALS['fake_posts'] as $p){$ok=true;foreach(($args['meta_query']??[]) as $q){if(($GLOBALS['fake_post_meta'][$p->ID][$q['key']]??null)!==($q['value']??null))$ok=false;}if($ok)$out[]=$p;}return $out;}
function wp_insert_term($name,$tax,$args=[]){$id=count($GLOBALS['fake_terms'])+200;$GLOBALS['fake_terms'][$id]=new WP_Term($id,$name);return ['term_id'=>$id,'term_taxonomy_id'=>$id];}
function wp_delete_term($id,$tax){unset($GLOBALS['fake_terms'][$id]);return true;}
function update_term_meta($id,$k,$v){$GLOBALS['fake_term_meta'][$id][$k]=$v;return true;}
function get_term_meta($id,$k,$single=true){return $GLOBALS['fake_term_meta'][$id][$k]??'';}
function get_terms($args=[]){return [];}
function wp_create_nav_menu($name){return 300;}
function wp_delete_nav_menu($id){return true;}
function wp_get_nav_menu_items($id){return [];}
function wp_update_nav_menu_item($menu,$item,$args){return 1;}
require_once dirname(__DIR__,2).'/inc/theme/settings.php';require_once dirname(__DIR__,2).'/inc/theme/provisioning-blueprints.php';require_once dirname(__DIR__,2).'/inc/theme/provisioning-discovery.php';require_once dirname(__DIR__,2).'/inc/theme/provisioning-plan.php';
$prov=dirname(__DIR__,2).'/inc/theme/provisioning-provenance.php';$runner=dirname(__DIR__,2).'/inc/theme/provisioning-runner.php';if(!is_file($prov)||!is_file($runner)){fwrite(STDERR,"FAIL: provisioning runner modules missing\n");exit(1);}require_once $prov;require_once $runner;
$d=\AZnet\Theme\provisioning_discovery();
$sel=['pages'=>['home'=>['action'=>'create','object_id'=>0],'about'=>['action'=>'reuse','object_id'=>42],'services'=>['action'=>'create','object_id'=>0],'contact'=>['action'=>'reuse','object_id'=>51]],'categories'=>[],'menu'=>['action'=>'skip','menu_id'=>0],'front_page'=>['action'=>'set_to_role','role'=>'home']];
$plan=\AZnet\Theme\provisioning_build_plan('law01-v1',$sel,$d);$beforeSettings=\AZnet\Theme\settings();$r=\AZnet\Theme\provisioning_apply_plan($plan);
assert($r['ok']===false);assert(isset($GLOBALS['fake_posts'][42],$GLOBALS['fake_posts'][51]));assert(count($GLOBALS['fake_posts'])===2);assert($GLOBALS['fake_options']['show_on_front']==='posts');assert($GLOBALS['fake_options']['page_on_front']===0);assert(\AZnet\Theme\settings()===$beforeSettings);
$src=file_get_contents($runner).file_get_contents($prov);foreach(['$wpdb','SELECT ','INSERT INTO','rootprofile','choiceguide_'] as $bad){assert(!str_contains(strtolower($src),strtolower($bad)),"Forbidden runner access: {$bad}");}
echo "PASS: Provisioning runner rollback/public-API contract\n";
