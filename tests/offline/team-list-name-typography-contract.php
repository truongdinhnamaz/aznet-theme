<?php
declare(strict_types=1);
$root=dirname(__DIR__,2);
$directory=(string)file_get_contents($root.'/assets/css/components/team-directory.css');
$homepage=(string)file_get_contents($root.'/assets/css/components/homepage-law-01-variants.css');
foreach (['.aznet-theme-team-directory__grid .aznet-theme-team-card__name','font-size: clamp('] as $needle) {
 if (!str_contains($directory,$needle)) {fwrite(STDERR,"FAIL: Directory name typography missing {$needle}\n");exit(1);}
}
if (!str_contains($homepage,'.aznet-theme-homepage--law-01-burgundy-gold .aznet-theme-law01-profile__member h3 { margin: .75rem 0 .35rem; font-size: clamp(')) {
 fwrite(STDERR,"FAIL: Homepage Team name typography not enhanced\n");exit(1);
}

foreach (['template-parts/team/card.php','template-parts/homepage/law-01/team.php'] as $path) {
 $tpl=(string)file_get_contents($root.'/'.$path);
 foreach (['aznet-theme-team-card__honorific','aznet-theme-team-card__person-name','preg_match(', 'esc_html( $member_name )'] as $needle) {
  if(!str_contains($tpl,$needle)){fwrite(STDERR,"FAIL: {$path} missing split label/name {$needle}\\n");exit(1);}
 }
}
foreach ([$directory,$homepage] as $css) {
 if(!str_contains($css,'.aznet-theme-team-card__honorific')){fwrite(STDERR,"FAIL: honorific sizing missing\\n");exit(1);}
}
foreach (['template-parts/team/card.php','template-parts/homepage/law-01/team.php'] as $path) {
 $tpl=(string)file_get_contents($root.'/'.$path);
 if (!str_contains($tpl,'Luật sư|Luật gia')) {fwrite(STDERR,"FAIL: missing Luật gia honorific on {$path}\n");exit(1);}
}
echo "PASS: Team list name typography\n";
