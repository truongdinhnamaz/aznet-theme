<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);$actionPath=$root.'/inc/admin/homepage-authoring.php';
if(!is_file($actionPath)){fwrite(STDERR,"FAIL: Homepage authoring action module missing\n");exit(1);}
$action=(string)file_get_contents($actionPath);$bootstrap=(string)file_get_contents($root.'/inc/admin/bootstrap.php');$admin=(string)file_get_contents($root.'/inc/admin/homepage.php');$jsPath=$root.'/assets/js/admin/homepage-authoring.js';assert(is_file($jsPath));$js=(string)file_get_contents($jsPath);
foreach(['current_user_can(','check_admin_referer(','homepage_source_descriptor(','homepage_source_value(','wp_update_post(','wp_update_term(','set_post_thumbnail(','delete_post_thumbnail('] as $n)assert(str_contains($action,$n),"Missing {$n}");
foreach(['edit_post','manage_categories','wp_attachment_is_image','Nguồn chỉnh sửa không còn khớp với cấu hình trang chủ.'] as $n)assert(str_contains($action,$n),"Missing validation {$n}");
$quickStart=strpos($action,'function handle_homepage_quick_edit_source');$duplicateStart=strpos($action,'function handle_homepage_duplicate_source');$quickSlice=false===$quickStart?'':substr($action,$quickStart,false===$duplicateStart?null:$duplicateStart-$quickStart);assert(!str_contains($quickSlice,"set_theme_mod( 'aznet_theme_settings'"));
foreach(['get_page_by_path(','get_page_by_title(','sanitize_title(','url_to_postid('] as $n)assert(!str_contains($action,$n),"Forbidden {$n}");
assert(str_contains($bootstrap,'admin_post_aznet_theme_quick_edit_homepage_source'));assert(str_contains($bootstrap,'wp_enqueue_media'));assert(str_contains($bootstrap,'homepage-authoring.js'));
foreach(['aznet_theme_quick_edit_homepage_source','homepage_source_id','homepage_preset_scope','homepage_source_slot','homepage_featured_image_id'] as $n)assert(str_contains($admin,$n),"Form missing {$n}");
foreach(['wp.media','aznet-theme-homepage-media-select','aznet-theme-homepage-media-clear'] as $n)assert(str_contains($js,$n),"JS missing {$n}");
foreach(['handle_homepage_duplicate_source','aznet_theme_duplicate_homepage_source','wp_insert_post(',"'post_status'","'draft'",'homepage_shared_source_uses(',"set_theme_mod( 'aznet_theme_settings'"] as $n)assert(str_contains($action,$n),"Missing duplication {$n}");
assert(str_contains($bootstrap,'admin_post_aznet_theme_duplicate_homepage_source'));assert(str_contains($admin,'Tạo nguồn riêng cho mẫu này'));
echo "PASS: D-038 bounded Homepage Quick Edit action contract\n";
