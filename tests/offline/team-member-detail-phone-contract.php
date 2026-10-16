<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/team/member.php');
$css = (string) file_get_contents($root . '/assets/css/components/team-member.css');

foreach ([
    'team_member_phone(',
    'team_member_phone_href(',
    'team_member_phone_display(',
    'aznet-theme-team-member__phone',
    'esc_url( $phone_href )',
    'esc_html( $phone_display )',
] as $needle) {
    if (! str_contains($template, $needle)) {
        fwrite(STDERR, "FAIL: Team detail telephone presentation missing: {$needle}\n");
        exit(1);
    }
}
if (! str_contains($template, 'the_title();')) {
    fwrite(STDERR, "FAIL: Preserve WordPress-owned member title\n");
    exit(1);
}
foreach ([
    '.aznet-theme-team-member__phone',
    '.aznet-theme-team-member__phone:focus-visible',
    'overflow-wrap:',
] as $needle) {
    if (! str_contains($css, $needle)) {
        fwrite(STDERR, "FAIL: Team detail telephone responsive/a11y CSS missing: {$needle}\n");
        exit(1);
    }
}
echo "PASS: Team member detail phone presentation contract\n";
