<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$pageExperience = (string) file_get_contents($root . '/inc/theme/page-experience.php');
$pageTemplate = (string) file_get_contents($root . '/template-parts/content/page.php');
$teamPage = (string) file_get_contents($root . '/template-parts/team/page.php');
$assets = (string) file_get_contents($root . '/inc/theme/assets.php');

foreach ([
    'function team_page_is_mapped',
    'function team_page_presentation_active',
    "homepage_source_value( 'law-01', 'team' )",
] as $needle) {
    if (! str_contains($pageExperience, $needle)) {
        fwrite(STDERR, "FAIL: Team Page presentation missing {$needle}\n");
        exit(1);
    }
}

foreach (["get_page_by_path(", "is_page( 'doi-ngu", 'REQUEST_URI', 'post_name'] as $forbidden) {
    if (str_contains($pageExperience . $pageTemplate . $teamPage, $forbidden)) {
        fwrite(STDERR, "FAIL: Team Page uses forbidden heuristic {$forbidden}\n");
        exit(1);
    }
}

foreach ([
    "'template-parts/team/page'",
    'aznet-theme-page--team-directory',
] as $needle) {
    if (! str_contains($pageTemplate, $needle)) {
        fwrite(STDERR, "FAIL: generic Page template missing Team route {$needle}\n");
        exit(1);
    }
}

foreach ([
    'team_directory_members()',
    "'template-parts/team/card'",
    'aznet-theme-team-directory__grid',
] as $needle) {
    if (! str_contains($teamPage, $needle)) {
        fwrite(STDERR, "FAIL: Team directory template missing {$needle}\n");
        exit(1);
    }
}

foreach ([
    'function should_enqueue_team_page_assets',
    'function enqueue_team_page_assets',
    "'aznet-theme-team-directory'",
    "team_page_presentation_active()",
] as $needle) {
    if (! str_contains($assets, $needle)) {
        fwrite(STDERR, "FAIL: Team directory assets missing {$needle}\n");
        exit(1);
    }
}

echo "PASS: mapped Team directory Page presentation contract\n";
