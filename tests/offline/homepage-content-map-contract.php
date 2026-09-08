<?php
declare(strict_types=1);
if (! defined('ABSPATH')) { define('ABSPATH', __DIR__ . '/'); }
class WP_Post { public int $ID; public string $post_type; public string $post_status; public int $post_parent = 0; public function __construct(int $id,string $type='page',string $status='publish'){ $this->ID=$id; $this->post_type=$type; $this->post_status=$status; } }
class WP_Term { public int $term_id; public string $taxonomy; public function __construct(int $id,string $taxonomy='category'){ $this->term_id=$id; $this->taxonomy=$taxonomy; } }
$GLOBALS['hc_posts'] = [1=>new WP_Post(1,'page','publish'),2=>new WP_Post(2,'post','publish'),3=>new WP_Post(3,'page','draft')];
$GLOBALS['hc_terms'] = [8=>new WP_Term(8,'category'),9=>new WP_Term(9,'post_tag')];
function get_post($id){ return $GLOBALS['hc_posts'][$id] ?? null; }
function get_term($id){ return $GLOBALS['hc_terms'][$id] ?? null; }
function get_posts($args){ $GLOBALS['hc_last_query']=$args; return []; }
$root = dirname(__DIR__, 2);
$path = $root . '/inc/theme/homepage-content-map.php';
if (! is_file($path)) { fwrite(STDERR, "FAIL: homepage content map module missing\n"); exit(1); }
require_once $path;
assert(AZnet\Theme\homepage_page_reference(1) instanceof WP_Post);
assert(AZnet\Theme\homepage_page_reference(2) === null);
assert(AZnet\Theme\homepage_page_reference(3) === null);
assert(AZnet\Theme\homepage_category_reference(8) instanceof WP_Term);
assert(AZnet\Theme\homepage_category_reference(9) === null);
AZnet\Theme\homepage_latest_posts([8], 5, [99]);
assert(($GLOBALS['hc_last_query']['post_type'] ?? null) === 'post');
assert(($GLOBALS['hc_last_query']['post_status'] ?? null) === 'publish');
assert(($GLOBALS['hc_last_query']['category__in'] ?? null) === [8]);
assert(($GLOBALS['hc_last_query']['post__not_in'] ?? null) === [99]);
$source = file_get_contents($path);
foreach (['get_page_by_title(', 'get_page_by_path(', "get_term_by( 'slug'", 'url_to_postid('] as $forbidden) {
    assert(! str_contains($source, $forbidden));
}
echo "PASS: Homepage Content Map contract\n";
