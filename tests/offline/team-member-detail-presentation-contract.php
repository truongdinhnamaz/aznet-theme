<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$pageExperience = (string) file_get_contents($root . '/inc/theme/page-experience.php');
$pageTemplate = (string) file_get_contents($root . '/template-parts/content/page.php');
$memberTemplatePath = $root . '/template-parts/team/member.php';
$memberCssPath = $root . '/assets/css/components/team-member.css';
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');

foreach ([
    'function team_member_page_is_detail',
    "homepage_source_value( 'law-01', 'team' )",
] as $needle) {
    if (! str_contains($pageExperience, $needle)) {
        fwrite(STDERR, "FAIL: Team member detail missing {$needle}\n");
        exit(1);
    }
}

if (! str_contains($pageTemplate, "'template-parts/team/member'")) {
    fwrite(STDERR, "FAIL: generic Page template does not route mapped Team children to member presentation\n");
    exit(1);
}

if (! str_contains($pageTemplate, 'aznet-theme-page--team-member')) {
    fwrite(STDERR, "FAIL: Team member detail class is missing\n");
    exit(1);
}

if (! is_file($memberTemplatePath) || ! is_file($memberCssPath)) {
    fwrite(STDERR, "FAIL: Team member presentation files are missing\n");
    exit(1);
}

$memberTemplate = (string) file_get_contents($memberTemplatePath);
$memberCss = (string) file_get_contents($memberCssPath);

foreach ([
    'aznet-theme-team-member__hero',
    'aznet-theme-team-member__portrait',
    'aznet-theme-team-member__body',
    'the_title()',
    'the_content()',
    'has_post_thumbnail()',
] as $needle) {
    if (! str_contains($memberTemplate, $needle)) {
        fwrite(STDERR, "FAIL: Team member template missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    '.aznet-theme-team-member__hero',
    'grid-template-columns',
    '.aznet-theme-team-member__portrait img',
    '@media (max-width: 47.999rem)',
] as $needle) {
    if (! str_contains($memberCss, $needle)) {
        fwrite(STDERR, "FAIL: Team member CSS missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function should_enqueue_team_member_assets',
    'function enqueue_team_member_assets',
    "'aznet-theme-team-member'",
    "team_member_page_is_detail()",
] as $needle) {
    if (! str_contains($assets, $needle)) {
        fwrite(STDERR, "FAIL: Team member assets missing {$needle}\n");
        exit(1);
    }
}

foreach (["get_page_by_path(", "REQUEST_URI", "post_name"] as $forbidden) {
    if (str_contains($pageExperience . $pageTemplate . $memberTemplate, $forbidden)) {
        fwrite(STDERR, "FAIL: Team member presentation uses forbidden heuristic {$forbidden}\n");
        exit(1);
    }
}

echo "PASS: mapped Team member detail presentation contract\n";
