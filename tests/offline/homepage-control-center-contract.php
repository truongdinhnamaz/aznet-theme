<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$source=(string)file_get_contents($root.'/inc/admin/homepage.php');
$css=(string)file_get_contents($root.'/assets/css/admin/control-center.css');
foreach(['Mẫu đang chỉnh','Sửa nhanh','Chỉnh đầy đủ','Đổi nguồn','Nguồn & chẩn đoán','aznet-theme-homepage-section-card','aznet-theme-homepage-section-card__status','homepage_authoring_sections','homepage_source_value'] as $needle)assert(str_contains($source.$css,$needle),"Missing authoring-console contract: {$needle}");
foreach(["[ 'hero', 'services', 'about', 'team', 'knowledge', 'case_analysis', 'legal_news', 'process', 'faq', 'contact' ]","[ 'hero', 'proof', 'about', 'process', 'projects', 'knowledge', 'contact' ]"] as $needle)assert(str_contains($source,$needle),"Missing preset order: {$needle}");
assert(str_contains($source,'get_edit_post_link'));assert(str_contains($source,'get_edit_term_link'));assert(str_contains($source,'<details class="aznet-theme-homepage-diagnostics"'));
echo "PASS: D-038 preset-aware Homepage Control Center contract\n";
