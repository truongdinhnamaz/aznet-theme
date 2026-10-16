<?php
declare(strict_types=1);
$root = dirname(__DIR__, 2);
$tpl = (string) file_get_contents($root . '/template-parts/team/member.php');
$css = (string) file_get_contents($root . '/assets/css/components/team-member.css');
foreach (['aznet-theme-team-member__honorific','aznet-theme-team-member__person-name','get_the_title()', 'esc_html( $member_name )'] as $needle) {
    if (!str_contains($tpl,$needle)) { fwrite(STDERR,"FAIL: missing distinct presentation {$needle}\n"); exit(1); }
}
foreach (['.aznet-theme-team-member__honorific','.aznet-theme-team-member__person-name','white-space: nowrap;','font-size: clamp('] as $needle) {
    if (!str_contains($css,$needle)) { fwrite(STDERR,"FAIL: missing one-line title style {$needle}\n"); exit(1); }
}
echo "PASS: scoped team member title presentation contract\n";
