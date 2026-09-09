<?php
declare(strict_types=1);
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/');
if(!class_exists('WP_Post')){ class WP_Post{public int $ID;public string $post_title;public string $post_status; function __construct($id,$t,$s='publish'){$this->ID=$id;$this->post_title=$t;$this->post_status=$s;}}}
if(!class_exists('WP_Term')){ class WP_Term{public int $term_id;public string $name;public string $taxonomy='category'; function __construct($id,$n){$this->term_id=$id;$this->name=$n;}}}
$GLOBALS['azd_opts']=['show_on_front'=>'page','page_on_front'=>44];
function get_option($k,$d=false){return $GLOBALS['azd_opts'][$k]??$d;}
function get_pages($args=[]){return [new WP_Post(10,'About'),new WP_Post(44,'Home'),new WP_Post(51,'Contact','draft')];}
function get_categories($args=[]){return [new WP_Term(2,'A'),new WP_Term(3,'B'),new WP_Term(4,'C'),new WP_Term(5,'D')];}
function wp_count_posts($type='post'){return (object)['publish'=>10,'draft'=>2,'private'=>0,'pending'=>0,'future'=>0];}
function get_nav_menu_locations(){return ['primary'=>9];}
function get_theme_mod($k,$d=[]){return $k==='aznet_theme_settings'?['schema_version'=>2,'homepage_preset'=>'off']: $d;}
require_once dirname(__DIR__,2).'/inc/theme/settings.php';
require_once dirname(__DIR__,2).'/inc/theme/provisioning-discovery.php';
$state=\AZnet\Theme\provisioning_discovery();
assert($state['page_count']===3);assert($state['category_count']===4);assert($state['post_count']===12);assert($state['front_page_id']===44);assert($state['homepage_preset']==='off');assert(isset($state['homepage_slots']));assert($state['primary_menu_id']===9);
assert(count(\AZnet\Theme\provisioning_page_candidates())===3);assert(count(\AZnet\Theme\provisioning_category_candidates())===4);
$src=file_get_contents(dirname(__DIR__,2).'/inc/theme/provisioning-discovery.php');
foreach(['get_page_by_title(','get_page_by_path(','url_to_postid(','get_term_by( \'slug\''] as $bad){assert(!str_contains($src,$bad),"No semantic heuristic: {$bad}");}
foreach(['wp_insert_post(','wp_insert_term(','update_option(','set_theme_mod('] as $bad){assert(!str_contains($src,$bad),"Discovery must be read-only: {$bad}");}
echo "PASS: Provisioning discovery is typed and read-only\n";
