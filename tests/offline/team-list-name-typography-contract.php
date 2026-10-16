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
echo "PASS: Team list name typography\n";
