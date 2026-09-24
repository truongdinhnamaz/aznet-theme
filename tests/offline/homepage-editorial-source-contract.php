<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);$dir=$root.'/template-parts/homepage/law-01';$files=glob($dir.'/*.php')?:[];$joined='';foreach($files as $file){$joined.="\n".(string)file_get_contents($file);}
foreach(['Dịch vụ nổi bật','Các dịch vụ pháp lý dành cho bạn','Giới thiệu về văn phòng','Đội ngũ luật sư','Kiến thức pháp luật','Bài viết mới nhất','Chủ đề pháp luật','Tìm hiểu theo lĩnh vực','Bạn đang cần hỗ trợ về một vấn đề pháp lý?','Trao đổi với đội ngũ để xác định hướng xử lý phù hợp cho trường hợp của bạn.'] as $n)assert(!str_contains($joined,$n),"Hard-coded Law 01 editorial copy remains: {$n}");
$services=(string)file_get_contents($dir.'/services.php');$latest=(string)file_get_contents($dir.'/latest.php');$cta=(string)file_get_contents($dir.'/final-cta.php');
assert(str_contains($services,'get_the_title( $parent )'));assert(str_contains($latest,'get_the_title( $posts_page_id )'));assert(str_contains($cta,'get_the_title( $page )'));assert(str_contains($cta,'get_the_excerpt( $page )'));
echo "PASS: D-038 Law 01 editorial content source contract\n";
