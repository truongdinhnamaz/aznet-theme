<?php
/** Wellness 01 native pattern contract; does not require WordPress runtime. */
$path = dirname(__DIR__, 2) . '/patterns/homepage-wellness-01.php';
if (!is_file($path)) { fwrite(STDERR, "RED: missing Wellness 01 native pattern\n"); exit(1); }
$s = file_get_contents($path);
$checks = [
 'slug' => str_contains($s,'Slug: aznet-theme/homepage-wellness-01'),
 'category' => str_contains($s,'Categories: aznet-theme-pages'),
 'native groups' => substr_count($s,'<!-- wp:group') >= 5,
 'single H1' => preg_match_all('/<h1\b/', $s) === 1,
 'program anchor' => str_contains($s,'id="aznet-theme-wellness-programs"'),
 'contact anchor' => str_contains($s,'id="aznet-theme-wellness-contact"'),
 'FAQ and Query' => str_contains($s,'<!-- wp:query ') && substr_count($s,'<!-- wp:details') >= 2,
 'no external scripts' => !preg_match('~<script\b|<iframe\b~i',$s),
 'no private domain storage' => !preg_match('/get_option|get_post_meta|update_post_meta|\$wpdb|_rootprofile_|_choiceguide_|appointment_status|booking_capacity|wc_get_product/i',$s),
 'no takeover hooks' => !preg_match('/template_redirect|template_include|add_action\s*\(|add_filter\s*\(/i',$s),
];
$matches=[];preg_match_all('/<!--\s*(\/?)wp:([a-z][a-z0-9\/-]*)(.*?)-->/s',$s,$matches,PREG_SET_ORDER);
$stack=[];$ok=true;
foreach($matches as $m){$closing=$m[1]==='/';$raw=trim($m[3]);$self=!$closing && str_ends_with($raw,'/');
 if($self)$raw=trim(substr($raw,0,-1));
 if($raw!==''){json_decode($raw,true);if(json_last_error()!==JSON_ERROR_NONE){$ok=false;break;}}
 if($closing){if(array_pop($stack)!==$m[2]){$ok=false;break;}}
 elseif(!$self)$stack[]=$m[2];
}
$checks['balanced Gutenberg block markup']=$ok && !$stack && count($matches)>50;
preg_match_all('/\bid="([^"]+)"/',$s,$ids);preg_match_all('/\bhref="#([^"]+)"/',$s,$hrefs);
$checks['unique IDs and resolved anchors']= count($ids[1])===count(array_unique($ids[1])) && !array_diff($hrefs[1],$ids[1]);
$checks['no stray freeform prefix before Gutenberg root'] = preg_match('/\?>\s*<!-- wp:group\b/s', $s) === 1;
foreach($checks as $label=>$pass){if(!$pass){fwrite(STDERR,"FAIL: $label\n");exit(1);}}
echo 'PASS: Wellness 01 native offline pattern '.count($checks)." checks\n";
