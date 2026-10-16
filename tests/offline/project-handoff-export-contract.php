<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$bootstrap=(string)file_get_contents($root.'/inc/admin/bootstrap.php');
$ui=(string)file_get_contents($root.'/inc/admin/control-center.php');
$export=(string)file_get_contents($root.'/inc/admin/project-handoff.php');
foreach (['project-handoff.php','admin_post_aznet_theme_export_handoff','handle_export_handoff'] as $needle) {
 if (!str_contains($bootstrap,$needle)) {fwrite(STDERR,"FAIL: missing registered handoff: {$needle}\n");exit(1);}
}
foreach (['Project Handoff','aznet_theme_export_handoff','wp_nonce_field'] as $needle) {
 if (!str_contains($ui,$needle)) {fwrite(STDERR,"FAIL: missing handoff UI: {$needle}\n");exit(1);}
}
foreach (['require_settings_capability()','check_admin_referer(', 'wp_json_encode(', 'Content-Disposition:', 'handoff_schema_version', 'canonical_repository','revalidate_before_use','export_payload()'] as $needle) {
 if (!str_contains($export,$needle)) {fwrite(STDERR,"FAIL: missing safe export: {$needle}\n");exit(1);}
}
foreach (['set_theme_mod(', 'update_option(', 'get_posts(', 'get_post_meta('] as $forbidden) {
 if (str_contains($export,$forbidden)) {fwrite(STDERR,"FAIL: forbidden data operation {$forbidden}\n");exit(1);}
}
echo "PASS: Handoff export-only contract\n";
