<?php
/**
 * Explicit WordPress-native Homepage Hero initialization and legacy migration.
 *
 * @package AZnetTheme
 */
namespace AZnet\Theme\Admin;

use function AZnet\Theme\homepage_page_reference;
use function AZnet\Theme\homepage_source_value;
use function AZnet\Theme\normalize_settings;
use function AZnet\Theme\settings;

if ( ! defined( 'ABSPATH' ) ) { exit; }

function homepage_hero_library_variants(): array {
    return [ 'split'=>__( 'Chia đôi','aznet-theme' ), 'centered'=>__( 'Căn giữa','aznet-theme' ), 'inverse'=>__( 'Tương phản','aznet-theme' ), 'media-left'=>__( 'Ảnh bên trái','aznet-theme' ) ];
}
function homepage_hero_candidate_reference( int $id ): ?\WP_Post {
    if ( $id <= 0 ) { return null; }
    $post=get_post($id);
    return $post instanceof \WP_Post && 'wp_block'===$post->post_type && in_array($post->post_status,['draft','publish'],true) ? $post : null;
}
function homepage_hero_scaffold_content(): string {
    if ( ! class_exists( '\\WP_Block_Patterns_Registry' ) ) { return ''; }
    $pattern=\WP_Block_Patterns_Registry::get_instance()->get_registered('aznet-theme/homepage-hero-content');
    return is_array($pattern)&&isset($pattern['content']) ? trim((string)$pattern['content']) : '';
}
function require_law01_hero_scope(): void {
    $preset=isset($_POST['homepage_preset_scope'])?sanitize_key(wp_unslash($_POST['homepage_preset_scope'])):'';
    if('law-01'!==$preset){wp_die(esc_html__('Hero Library này chỉ áp dụng cho Luật 01.','aznet-theme'));}
}
function handle_homepage_hero_apply(): void {
    if(!current_user_can('edit_theme_options')){wp_die(esc_html__('Bạn không có quyền thay đổi Hero trang chủ.','aznet-theme'));}
    check_admin_referer('aznet_theme_apply_homepage_hero'); require_law01_hero_scope();
    $variants=homepage_hero_library_variants();
    $requested=isset($_POST['homepage_hero_variant'])?sanitize_key((string)wp_unslash($_POST['homepage_hero_variant'])):'split';
    $variant=isset($variants[$requested])?$requested:'split';
    $theme_settings=settings();
    $hero_id=(int)homepage_source_value('law-01','hero',$theme_settings);
    $hero=homepage_hero_candidate_reference($hero_id);
    if(!$hero instanceof \WP_Post){
        if(!current_user_can('publish_posts')){wp_die(esc_html__('Bạn không có quyền tạo nội dung Hero WordPress.','aznet-theme'));}
        $content=homepage_hero_scaffold_content();
        if(''===$content){wp_die(esc_html__('Không thể khởi tạo nội dung Hero WordPress.','aznet-theme'));}
        $hero_id=wp_insert_post(['post_type'=>'wp_block','post_status'=>'draft','post_title'=>__('Hero trang chủ — Luật 01','aznet-theme'),'post_content'=>$content],true);
        if(is_wp_error($hero_id)||(int)$hero_id<=0){wp_die(esc_html__('Không thể tạo Hero WordPress.','aznet-theme'));}
        $hero_id=(int)$hero_id; $theme_settings['homepage_law01_hero_block']=$hero_id; $hero=get_post($hero_id);
    }
    $theme_settings['homepage_law01_hero_variant']=$variant;
    set_theme_mod('aznet_theme_settings',normalize_settings($theme_settings));
    wp_safe_redirect(add_query_arg(['page'=>'aznet-theme','section'=>'homepage','hero'=>$hero instanceof \WP_Post&&'draft'===$hero->post_status?'draft':'ready'],admin_url('admin.php'))); exit;
}
function homepage_legacy_page_to_hero_content( \WP_Post $page ): string {
    $title=trim((string)get_the_title($page)); $excerpt=trim((string)$page->post_excerpt);
    $content='<!-- wp:group {"className":"aznet-theme-homepage-hero-content"} --><div class="wp-block-group aznet-theme-homepage-hero-content">';
    if(''!==$title){$content.='<!-- wp:heading {"level":1} --><h1 class="wp-block-heading">'.esc_html($title).'</h1><!-- /wp:heading -->';}
    if(''!==$excerpt){$content.='<!-- wp:paragraph --><p>'.esc_html($excerpt).'</p><!-- /wp:paragraph -->';}
    $image_id=(int)get_post_thumbnail_id($page->ID);
    if($image_id>0){$url=wp_get_attachment_image_url($image_id,'large');if(is_string($url)&&''!==$url){$alt=trim((string)get_post_meta($image_id,'_wp_attachment_image_alt',true));$attrs=wp_json_encode(['id'=>$image_id,'sizeSlug'=>'large','linkDestination'=>'none']);$content.='<!-- wp:image '.(is_string($attrs)?$attrs:'{}').' --><figure class="wp-block-image size-large"><img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="wp-image-'.esc_attr((string)$image_id).'"/></figure><!-- /wp:image -->';}}
    return $content.'</div><!-- /wp:group -->';
}
function handle_homepage_hero_legacy_migration(): void {
    if(!current_user_can('edit_theme_options')||!current_user_can('publish_posts')){wp_die(esc_html__('Bạn không có quyền nâng cấp Hero trang chủ.','aznet-theme'));}
    check_admin_referer('aznet_theme_migrate_legacy_homepage_hero'); require_law01_hero_scope();
    $theme_settings=settings(); $existing_id=(int)homepage_source_value('law-01','hero',$theme_settings);
    if(homepage_hero_candidate_reference($existing_id) instanceof \WP_Post){wp_die(esc_html__('Luật 01 đã có Hero WordPress để chỉnh sửa.','aznet-theme'));}
    $legacy_page_id=(int)homepage_source_value('law-01','hero_page',$theme_settings); $legacy_page=homepage_page_reference($legacy_page_id);
    if(!$legacy_page instanceof \WP_Post){wp_die(esc_html__('Không tìm thấy Hero Page cũ hợp lệ để nâng cấp.','aznet-theme'));}
    $hero_id=wp_insert_post(['post_type'=>'wp_block','post_status'=>'draft','post_title'=>__('Hero trang chủ — Luật 01','aznet-theme'),'post_content'=>homepage_legacy_page_to_hero_content($legacy_page)],true);
    if(is_wp_error($hero_id)||(int)$hero_id<=0){wp_die(esc_html__('Không thể tạo bản nháp Hero WordPress.','aznet-theme'));}
    $theme_settings['homepage_law01_hero_block']=(int)$hero_id;
    $effective_variant=(string)homepage_source_value('law-01','hero_variant',$theme_settings);
    $theme_settings['homepage_law01_hero_variant']=in_array($effective_variant,array_keys(homepage_hero_library_variants()),true)?$effective_variant:'split';
    set_theme_mod('aznet_theme_settings',normalize_settings($theme_settings));
    wp_safe_redirect(add_query_arg(['page'=>'aznet-theme','section'=>'homepage','hero'=>'migrated-draft'],admin_url('admin.php'))); exit;
}
