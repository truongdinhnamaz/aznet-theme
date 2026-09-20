<?php
declare(strict_types=1);
namespace {
    class WP_Post { public function __construct(public int $ID, public string $post_title = '') {} }
}
namespace AZnet\Theme {
    define('ABSPATH', __DIR__);
    $GLOBALS['team_test'] = ['caption'=>'AI illustration <b>not staff</b>','image'=>true,'members'=>[],'team'=>true];
    function setting($key,$default=null){return ['homepage_about_page'=>1,'homepage_team_page'=>2,'homepage_services_page'=>3][$key]??$default;}
    function homepage_page_reference($id){return $id>0&&($id!==2||$GLOBALS['team_test']['team'])?new \WP_Post($id,'Page '.$id):null;}
    function homepage_direct_published_children($id,$limit):array{return $id===2?$GLOBALS['team_test']['members']:[new \WP_Post(30)];}
    function get_the_excerpt($post):string{return 'Native page summary';}
    function has_post_thumbnail($post):bool{return $post->ID===2&&$GLOBALS['team_test']['image'];}
    function get_the_post_thumbnail($post,$size,$attr):string{return '<img src="https://example.test/native.webp" class="'.$attr['class'].'" alt="Editorial illustration">';}
    function get_the_post_thumbnail_url($post,$size):string{return $GLOBALS['team_test']['image']?'https://example.test/native.webp':'';}
    function get_the_post_thumbnail_caption($post):string{return $GLOBALS['team_test']['caption'];}
    function wp_strip_all_tags($text):string{return strip_tags($text);}
    function get_the_title($post):string{return $post->post_title;}
    function get_permalink($post):string{return 'https://example.test/page/'.$post->ID;}
    function esc_html($text):string{return htmlspecialchars((string)$text,ENT_QUOTES,'UTF-8');}
    function esc_attr($text):string{return esc_html($text);}
    function esc_url($text):string{return esc_html($text);}
    function __($text,$domain=''):string{return $text;}
    function esc_html_e($text,$domain=''):void{echo esc_html($text);}
    function esc_attr_e($text,$domain=''):void{echo esc_attr($text);}
    function wp_kses_post($html):string{return $html;}
    function render_profile_test():string{ob_start();require dirname(__DIR__,2).'/template-parts/homepage/law-01/profile.php';return (string)ob_get_clean();}
    $must=static function(bool $ok,string $message):void{if(!$ok){fwrite(STDERR,"FAIL: $message\n");exit(1);}};
    $html=render_profile_test();
    $must(substr_count($html,'aznet-theme-law01-profile__team-illustration-slot')===4,'captioned native Team media must render four visual portrait slots without invented member records');
    $must(substr_count($html,'class="aznet-theme-law01-profile__team-illustration-image ')===4,'each portrait slot must reuse only the mapped native Team featured media');
    $must(str_contains($html,'<figcaption>AI illustration not staff</figcaption>'),'visible caption must be source-backed plain text');
    $must(!str_contains($html,'<article class="aznet-theme-law01-profile__member'),'editorial image must not become a member');
    $cta=strpos($html,'class="aznet-theme-law01-button"');$facts=strpos($html,'class="aznet-theme-law01-profile__facts"');
    $must($cta!==false&&$facts!==false&&$cta<$facts,'About CTA must precede reference facts');
    $GLOBALS['team_test']['caption']='';
    $must(!str_contains(render_profile_test(),'class="aznet-theme-law01-profile__team-illustration"'),'uncaptioned fallback must remain hidden');
    $GLOBALS['team_test']['caption']='AI artwork';$GLOBALS['team_test']['image']=false;
    $must(!str_contains(render_profile_test(),'class="aznet-theme-law01-profile__team-illustration"'),'missing media fails soft');
    $GLOBALS['team_test']['image']=true;$GLOBALS['team_test']['members']=[new \WP_Post(4,'Native member')];
    $html=render_profile_test();
    $must(str_contains($html,'Native member')&&!str_contains($html,'class="aznet-theme-law01-profile__team-illustration"'),'real source members keep precedence');
    $GLOBALS['team_test']['team']=false;
    $must(!str_contains(render_profile_test(),'class="aznet-theme-law01-profile__team-illustration"'),'unmapped Team has no fallback');
    echo "PASS: native Team media fallback, source precedence, CTA order\n";
}
