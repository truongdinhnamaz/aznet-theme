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

/** Machine roles used by the bounded Hero form. */
function homepage_hero_form_roles(): array {
    return [
        'root'          => 'aznet-hero-managed-v1',
        'eyebrow'       => 'aznet-hero-eyebrow',
        'title'         => 'aznet-hero-title',
        'value'         => 'aznet-hero-value',
        'lead'          => 'aznet-hero-lead',
        'primary_cta'   => 'aznet-hero-primary-cta',
        'secondary_cta' => 'aznet-hero-secondary-cta',
        'media'         => 'aznet-hero-media',
        'trust_1'       => 'aznet-hero-trust-1',
        'trust_2'       => 'aznet-hero-trust-2',
        'trust_3'       => 'aznet-hero-trust-3',
        'trust_4'       => 'aznet-hero-trust-4',
    ];
}
function homepage_hero_form_content_hash( string $content ): string { return hash( 'sha256', trim( $content ) ); }
function homepage_hero_legacy_scaffold_hash(): string { return '0ece22abcd0b9b93109000b43e36a96ce830236e2005b53327467a6867694a10'; }
function homepage_hero_form_is_legacy_scaffold_content( string $content ): bool { return hash_equals( homepage_hero_legacy_scaffold_hash(), homepage_hero_form_content_hash( $content ) ); }
function homepage_hero_form_block_role( array $block ): string {
    $metadata = isset( $block['attrs']['metadata'] ) && is_array( $block['attrs']['metadata'] ) ? $block['attrs']['metadata'] : [];
    return isset( $metadata['name'] ) && is_string( $metadata['name'] ) ? $metadata['name'] : '';
}
function homepage_hero_form_collect_role_blocks( array $blocks, array &$found ): void {
    foreach ( $blocks as $block ) {
        if ( ! is_array( $block ) ) { continue; }
        $role = homepage_hero_form_block_role( $block );
        if ( '' !== $role ) { $found[ $role ][] = $block; }
        $children = isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ? $block['innerBlocks'] : [];
        if ( [] !== $children ) { homepage_hero_form_collect_role_blocks( $children, $found ); }
    }
}
function homepage_hero_form_text_from_block( array $block ): string {
    return trim( html_entity_decode( wp_strip_all_tags( (string) ( $block['innerHTML'] ?? '' ) ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}
function homepage_hero_form_button_url_from_block( array $block ): string {
    $url = isset( $block['attrs']['url'] ) && is_string( $block['attrs']['url'] ) ? trim( $block['attrs']['url'] ) : '';
    if ( '' !== $url ) { return $url; }
    if ( class_exists( '\\WP_HTML_Tag_Processor' ) ) {
        $processor = new \WP_HTML_Tag_Processor( (string) ( $block['innerHTML'] ?? '' ) );
        if ( $processor->next_tag( 'a' ) ) { $href = $processor->get_attribute( 'href' ); return is_string( $href ) ? $href : ''; }
    }
    return '';
}
function homepage_hero_form_media_id_from_block( array $block ): int {
    foreach ( (array) ( $block['innerBlocks'] ?? [] ) as $child ) {
        if ( is_array( $child ) && 'core/image' === (string) ( $child['blockName'] ?? '' ) ) {
            $id = isset( $child['attrs']['id'] ) ? (int) $child['attrs']['id'] : 0; return $id > 0 ? $id : 0;
        }
    }
    return 0;
}
function homepage_hero_form_model_from_content( string $content ): ?array {
    $blocks = parse_blocks( $content ); if ( ! is_array( $blocks ) || [] === $blocks ) { return null; }
    $found = []; homepage_hero_form_collect_role_blocks( $blocks, $found ); $roles = homepage_hero_form_roles();
    foreach ( $roles as $role ) { if ( ! isset( $found[ $role ] ) || 1 !== count( $found[ $role ] ) ) { return null; } }
    return [
        'eyebrow' => homepage_hero_form_text_from_block( $found[ $roles['eyebrow'] ][0] ),
        'title' => homepage_hero_form_text_from_block( $found[ $roles['title'] ][0] ),
        'value' => homepage_hero_form_text_from_block( $found[ $roles['value'] ][0] ),
        'lead' => homepage_hero_form_text_from_block( $found[ $roles['lead'] ][0] ),
        'primary_label' => homepage_hero_form_text_from_block( $found[ $roles['primary_cta'] ][0] ),
        'primary_url' => homepage_hero_form_button_url_from_block( $found[ $roles['primary_cta'] ][0] ),
        'secondary_label' => homepage_hero_form_text_from_block( $found[ $roles['secondary_cta'] ][0] ),
        'secondary_url' => homepage_hero_form_button_url_from_block( $found[ $roles['secondary_cta'] ][0] ),
        'image_id' => homepage_hero_form_media_id_from_block( $found[ $roles['media'] ][0] ),
        'trust_1' => homepage_hero_form_text_from_block( $found[ $roles['trust_1'] ][0] ),
        'trust_2' => homepage_hero_form_text_from_block( $found[ $roles['trust_2'] ][0] ),
        'trust_3' => homepage_hero_form_text_from_block( $found[ $roles['trust_3'] ][0] ),
        'trust_4' => homepage_hero_form_text_from_block( $found[ $roles['trust_4'] ][0] ),
    ];
}
function homepage_hero_form_is_managed_content( string $content ): bool { return null !== homepage_hero_form_model_from_content( $content ); }
function homepage_hero_form_seed_model_from_public_sources( int $preferred_image_id = 0 ): array {
    $model = homepage_hero_form_default_model();
    $theme_settings = settings();
    $page = homepage_page_reference((int)homepage_source_value('law-01','hero_page',$theme_settings));
    if ( ! $page instanceof \WP_Post ) {
        $front_id = (int) get_option( 'page_on_front', 0 );
        $page = $front_id > 0 ? homepage_page_reference( $front_id ) : null;
    }

    $site_title = trim( (string) get_bloginfo('name') );
    $site_tagline = trim( (string) get_bloginfo('description') );
    $page_title = $page instanceof \WP_Post ? trim( (string) get_the_title( $page ) ) : '';
    $excerpt = $page instanceof \WP_Post ? trim( (string) get_the_excerpt( $page ) ) : '';
    $body = $page instanceof \WP_Post ? trim( wp_strip_all_tags( (string) $page->post_content ) ) : '';

    $model['eyebrow'] = __( 'Văn phòng luật sư', 'aznet-theme' );
    $model['title'] = '' !== $site_title ? $site_title : $page_title;
    $model['value'] = '' !== $page_title && 0 !== strcasecmp( $page_title, (string) $model['title'] ) ? $page_title : $excerpt;
    $support = [];
    foreach ( [ $excerpt, $body, $site_tagline ] as $line ) {
        if ( '' !== $line && ! in_array( $line, $support, true ) ) { $support[] = $line; }
    }
    $model['lead'] = implode( ' — ', $support );

    $contact = homepage_page_reference( (int) homepage_source_value( 'law-01', 'contact', $theme_settings ) );
    $services = homepage_page_reference( (int) homepage_source_value( 'law-01', 'services', $theme_settings ) );
    if ( $contact instanceof \WP_Post ) { $url = get_permalink( $contact ); if ( is_string( $url ) ) { $model['primary_url'] = $url; } }
    if ( $services instanceof \WP_Post ) { $url = get_permalink( $services ); if ( is_string( $url ) ) { $model['secondary_url'] = $url; } }

    if ( $preferred_image_id > 0 && wp_attachment_is_image( $preferred_image_id ) ) {
        $model['image_id'] = $preferred_image_id;
    } elseif ( $page instanceof \WP_Post ) {
        $model['image_id'] = (int) get_post_thumbnail_id( $page->ID );
    }
    return $model;
}
function homepage_hero_form_is_default_model( array $model ): bool {
    $defaults = homepage_hero_form_default_model();
    foreach ( [ 'eyebrow', 'title', 'value', 'lead', 'primary_label', 'secondary_label', 'trust_1', 'trust_2', 'trust_3', 'trust_4' ] as $key ) {
        if ( (string) ( $model[ $key ] ?? '' ) !== (string) ( $defaults[ $key ] ?? '' ) ) { return false; }
    }
    return true;
}
function homepage_hero_form_default_model(): array {
    return [
        'eyebrow'=>__( 'Thông điệp mở đầu','aznet-theme' ), 'title'=>__( 'Viết tiêu đề Hero của bạn tại đây','aznet-theme' ),
        'value'=>__( 'Viết dòng giá trị chính của Hero.','aznet-theme' ), 'lead'=>__( 'Viết thông điệp hoặc khẩu hiệu hỗ trợ.','aznet-theme' ),
        'primary_label'=>__( 'Liên hệ tư vấn','aznet-theme' ), 'primary_url'=>'', 'secondary_label'=>__( 'Xem dịch vụ','aznet-theme' ), 'secondary_url'=>'',
        'image_id'=>0, 'trust_1'=>__( 'Tư vấn rõ ràng','aznet-theme' ), 'trust_2'=>__( 'Giải pháp thực tiễn','aznet-theme' ),
        'trust_3'=>__( 'Bảo mật thông tin','aznet-theme' ), 'trust_4'=>__( 'Đồng hành tận tâm','aznet-theme' ),
    ];
}
function homepage_hero_form_text_block_html( array $block, string $text ): string {
    $name=(string)($block['blockName']??''); $class=isset($block['attrs']['className'])&&is_string($block['attrs']['className'])?trim($block['attrs']['className']):'';
    if('core/heading'===$name){$level=isset($block['attrs']['level'])?max(1,min(6,(int)$block['attrs']['level'])):2;$classes=trim('wp-block-heading '.$class);return '<h'.$level.(''!==$classes?' class="'.esc_attr($classes).'"':'').'>'.esc_html($text).'</h'.$level.'>';}
    return '<p'.(''!==$class?' class="'.esc_attr($class).'"':'').'>'.esc_html($text).'</p>';
}
function homepage_hero_form_button_html( array $block, string $label, string $url ): string {
    $class=isset($block['attrs']['className'])&&is_string($block['attrs']['className'])?trim($block['attrs']['className']):'';$outer=trim('wp-block-button '.$class);$link='wp-block-button__link';
    $background=isset($block['attrs']['backgroundColor'])&&is_string($block['attrs']['backgroundColor'])?sanitize_html_class($block['attrs']['backgroundColor']):'';
    if(''!==$background){$link.=' has-'.$background.'-background-color has-background';}$link.=' wp-element-button';$href=''!==$url?' href="'.esc_url($url).'"':'';
    return '<div class="'.esc_attr($outer).'"><a class="'.esc_attr($link).'"'.$href.'>'.esc_html($label).'</a></div>';
}
function homepage_hero_form_media_children( int $image_id ): array {
    if($image_id>0){$url=wp_get_attachment_image_url($image_id,'large');if(is_string($url)&&''!==$url){$alt=trim((string)get_post_meta($image_id,'_wp_attachment_image_alt',true));$html='<figure class="wp-block-image size-large"><img src="'.esc_url($url).'" alt="'.esc_attr($alt).'" class="wp-image-'.esc_attr((string)$image_id).'"/></figure>';return [['blockName'=>'core/image','attrs'=>['id'=>$image_id,'sizeSlug'=>'large','linkDestination'=>'none'],'innerBlocks'=>[],'innerHTML'=>$html,'innerContent'=>[$html]]];}}
    $html='<p class="has-text-align-center aznet-theme-homepage-hero-content__media-help has-sm-font-size">'.esc_html__( 'Chèn block Ảnh vào cột này để chọn ảnh Hero.','aznet-theme' ).'</p>';
    return [['blockName'=>'core/paragraph','attrs'=>['align'=>'center','className'=>'aznet-theme-homepage-hero-content__media-help','fontSize'=>'sm'],'innerBlocks'=>[],'innerHTML'=>$html,'innerContent'=>[$html]]];
}
function homepage_hero_form_update_blocks( array $blocks, array $model ): array {
    $roles=homepage_hero_form_roles();$textMap=[$roles['eyebrow']=>'eyebrow',$roles['title']=>'title',$roles['value']=>'value',$roles['lead']=>'lead',$roles['trust_1']=>'trust_1',$roles['trust_2']=>'trust_2',$roles['trust_3']=>'trust_3',$roles['trust_4']=>'trust_4'];
    foreach($blocks as &$block){if(!is_array($block)){continue;}$role=homepage_hero_form_block_role($block);
        if(isset($textMap[$role])){$html=homepage_hero_form_text_block_html($block,(string)($model[$textMap[$role]]??''));$block['innerHTML']=$html;$block['innerContent']=[$html];continue;}
        if($roles['primary_cta']===$role||$roles['secondary_cta']===$role){$prefix=$roles['primary_cta']===$role?'primary':'secondary';$url=(string)($model[$prefix.'_url']??'');$label=(string)($model[$prefix.'_label']??'');if(''!==$url){$block['attrs']['url']=$url;}else{unset($block['attrs']['url']);}$html=homepage_hero_form_button_html($block,$label,$url);$block['innerHTML']=$html;$block['innerContent']=[$html];continue;}
        if($roles['media']===$role&&'core/column'===(string)($block['blockName']??'')){$children=homepage_hero_form_media_children((int)($model['image_id']??0));$classes='wp-block-column';if(isset($block['attrs']['verticalAlignment'])&&is_string($block['attrs']['verticalAlignment'])&&''!==$block['attrs']['verticalAlignment']){$classes.=' is-vertically-aligned-'.sanitize_html_class($block['attrs']['verticalAlignment']);}if(isset($block['attrs']['className'])&&is_string($block['attrs']['className'])&&''!==trim($block['attrs']['className'])){$classes.=' '.trim($block['attrs']['className']);}$open='<div class="'.esc_attr($classes).'">';$close='</div>';$block['innerBlocks']=$children;$block['innerContent']=[$open,null,$close];$block['innerHTML']=$open.(string)($children[0]['innerHTML']??'').$close;continue;}
        if(isset($block['innerBlocks'])&&is_array($block['innerBlocks'])&&[]!==$block['innerBlocks']){$block['innerBlocks']=homepage_hero_form_update_blocks($block['innerBlocks'],$model);}
    }unset($block);return $blocks;
}
function homepage_hero_form_managed_content( array $model ): string { $scaffold=homepage_hero_scaffold_content();if(''===$scaffold){return '';}$blocks=parse_blocks($scaffold);return serialize_blocks(homepage_hero_form_update_blocks($blocks,$model)); }
function homepage_hero_form_updated_content( string $content, array $model ): string { if(!homepage_hero_form_is_managed_content($content)){return '';}return serialize_blocks(homepage_hero_form_update_blocks(parse_blocks($content),$model)); }
function homepage_hero_form_post_model(): array {
    $text=static fn(string $key):string=>isset($_POST[$key])?sanitize_text_field(wp_unslash($_POST[$key])):'';
    $textarea=static fn(string $key):string=>isset($_POST[$key])?sanitize_textarea_field(wp_unslash($_POST[$key])):'';
    $url=static fn(string $key):string=>isset($_POST[$key])?esc_url_raw(trim((string)wp_unslash($_POST[$key]))):'';
    return ['eyebrow'=>$text('homepage_hero_eyebrow'),'title'=>$text('homepage_hero_title'),'value'=>$text('homepage_hero_value'),'lead'=>$textarea('homepage_hero_lead'),'primary_label'=>$text('homepage_hero_primary_label'),'primary_url'=>$url('homepage_hero_primary_url'),'secondary_label'=>$text('homepage_hero_secondary_label'),'secondary_url'=>$url('homepage_hero_secondary_url'),'image_id'=>isset($_POST['homepage_featured_image_id'])?absint($_POST['homepage_featured_image_id']):0,'trust_1'=>$text('homepage_hero_trust_1'),'trust_2'=>$text('homepage_hero_trust_2'),'trust_3'=>$text('homepage_hero_trust_3'),'trust_4'=>$text('homepage_hero_trust_4')];
}
function homepage_hero_form_require_source(): \WP_Post {
    require_law01_hero_scope();$source_id=isset($_POST['homepage_hero_source_id'])?absint($_POST['homepage_hero_source_id']):0;$mapped_id=(int)homepage_source_value('law-01','hero',settings());
    if($source_id<=0||$source_id!==$mapped_id){wp_die(esc_html__('Nguồn Hero không còn khớp với cấu hình Luật 01.','aznet-theme'));}$hero=homepage_hero_candidate_reference($source_id);
    if(!$hero instanceof \WP_Post||!current_user_can('edit_post',$source_id)){wp_die(esc_html__('Hero WordPress không hợp lệ hoặc bạn không có quyền chỉnh sửa.','aznet-theme'));}return $hero;
}
function handle_homepage_hero_form_save(): void {
    if(!current_user_can('edit_theme_options')){wp_die(esc_html__('Bạn không có quyền thay đổi Hero trang chủ.','aznet-theme'));}check_admin_referer('aznet_theme_save_homepage_hero_form');$hero=homepage_hero_form_require_source();
    if(!homepage_hero_form_is_managed_content((string)$hero->post_content)){wp_die(esc_html__('Hero này đã được tùy biến ngoài phạm vi form đơn giản. Hãy dùng trình soạn thảo WordPress.','aznet-theme'));}
    $expected_hash=isset($_POST['homepage_hero_content_hash'])?sanitize_text_field(wp_unslash($_POST['homepage_hero_content_hash'])):'';
    if(''===$expected_hash||!hash_equals(homepage_hero_form_content_hash((string)$hero->post_content),$expected_hash)){wp_die(esc_html__('Hero đã thay đổi ở nơi khác. Hãy tải lại trang trước khi lưu.','aznet-theme'));}
    $model=homepage_hero_form_post_model();if((int)$model['image_id']>0&&!wp_attachment_is_image((int)$model['image_id'])){wp_die(esc_html__('Ảnh Hero không hợp lệ.','aznet-theme'));}
    $updated_content=homepage_hero_form_updated_content((string)$hero->post_content,$model);if(''===$updated_content){wp_die(esc_html__('Không thể cập nhật Hero bằng form đơn giản.','aznet-theme'));}
    $update=['ID'=>$hero->ID,'post_content'=>$updated_content];
    if('draft'===$hero->post_status){if(!current_user_can('publish_posts')){wp_die(esc_html__('Bạn không có quyền xuất bản Hero lên website.','aznet-theme'));}$update['post_status']='publish';}
    $result=wp_update_post($update,true);if(is_wp_error($result)){wp_die(esc_html($result->get_error_message()));}
    $variants=homepage_hero_library_variants();$requested=isset($_POST['homepage_hero_variant'])?sanitize_key(wp_unslash($_POST['homepage_hero_variant'])):'split';$theme_settings=settings();$theme_settings['homepage_law01_hero_variant']=isset($variants[$requested])?$requested:'split';set_theme_mod('aznet_theme_settings',normalize_settings($theme_settings));
    wp_safe_redirect(add_query_arg(['page'=>'aznet-theme','section'=>'homepage','hero_form'=>'saved'],admin_url('admin.php')));exit;
}
function handle_homepage_hero_form_upgrade(): void {
    if(!current_user_can('edit_theme_options')){wp_die(esc_html__('Bạn không có quyền thay đổi Hero trang chủ.','aznet-theme'));}check_admin_referer('aznet_theme_upgrade_homepage_hero_form');$hero=homepage_hero_form_require_source();
    if(!homepage_hero_form_is_legacy_scaffold_content((string)$hero->post_content)){wp_die(esc_html__('Hero hiện tại không còn là scaffold mặc định cũ nên Theme sẽ không tự viết lại.','aznet-theme'));}
    $content=homepage_hero_form_managed_content(homepage_hero_form_default_model());if(''===$content){wp_die(esc_html__('Không thể khởi tạo cấu trúc Hero form.','aznet-theme'));}$result=wp_update_post(['ID'=>$hero->ID,'post_content'=>$content],true);if(is_wp_error($result)){wp_die(esc_html($result->get_error_message()));}
    wp_safe_redirect(add_query_arg(['page'=>'aznet-theme','section'=>'homepage','hero_form'=>'upgraded'],admin_url('admin.php')));exit;
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
        $content=homepage_hero_form_managed_content(homepage_hero_form_seed_model_from_public_sources());
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
