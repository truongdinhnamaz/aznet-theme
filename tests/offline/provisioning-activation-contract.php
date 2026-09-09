<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$files=[$root.'/inc/theme/bootstrap.php',$root.'/inc/admin/bootstrap.php',$root.'/inc/admin/provisioning.php'];
foreach($files as $f){ if(!is_file($f)){fwrite(STDERR,"FAIL: missing ".basename($f)."\n");exit(1);} }
$source=''; foreach($files as $f){$source.=file_get_contents($f)."\n";}
foreach(['after_switch_theme','switch_theme','wp_insert_post(','wp_update_post(','wp_insert_term(','wp_update_term(','wp_create_nav_menu(','wp_update_nav_menu_item('] as $bad){ assert(!str_contains($source,$bad),"Activation/bootstrap must not provision: {$bad}"); }
assert(str_contains(file_get_contents($root.'/inc/admin/provisioning.php'),'Thiết lập website nhanh'));
assert(str_contains(file_get_contents($root.'/inc/admin/provisioning.php'),'render_provisioning_invitation'));
echo "PASS: Provisioning activation invitation is mutation-free\n";
