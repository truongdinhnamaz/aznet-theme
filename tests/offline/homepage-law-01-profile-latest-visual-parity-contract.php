<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$latest = (string) file_get_contents($root . '/template-parts/homepage/law-01/latest.php');
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-variants.css');

$must = static function (bool $condition, string $message): void {
    assert($condition, $message);
};

// Latest owner-approved visual target:
// About and Team share one reference band on wide desktop, while Latest Posts retains
// a disciplined three-card editorial row. Source ownership remains unchanged.
$must(
    str_contains($profile, 'aznet-theme-law01-profile__about-grid'),
    'Law 01 target About presentation must retain its dedicated presentation hook.'
);
$must(
    str_contains($profile, 'aznet-theme-law01-profile__team-band'),
    'Law 01 target Team presentation must retain its dedicated presentation hook.'
);
$must(
    str_contains($css, '.aznet-theme-law01-profile > .aznet-theme-law01-container { display: grid; grid-template-columns: minmax(0, 48%) minmax(0, 52%);'),
    'Law 01 reference Profile band must place About and Team side-by-side at the approved 48/52 desktop ratio.'
);
$must(
    str_contains($css, '.aznet-theme-law01-profile__about-media { display: none; }'),
    'Law 01 reference About column must remain text-led instead of adding a second large media panel.'
);
$must(
    str_contains($css, '.aznet-theme-law01-profile__team-band {'),
    'Law 01 target Team band styling must remain explicit and presentation-only.'
);
$must(
    str_contains($css, 'grid-template-columns: repeat(4, minmax(0, 1fr));'),
    'Law 01 target Team presentation must retain four portrait slots on wide desktop.'
);
$must(
    str_contains($css, '.aznet-theme-law01-grid--articles .aznet-theme-law01-article-card {'),
    'Law 01 target Latest cards must retain an explicit card presentation rule.'
);
$must(
    str_contains($css, 'display: flex;') && str_contains($css, 'flex-direction: column;'),
    'Law 01 target Latest cards must support equal-height editorial card rhythm.'
);
$must(
    str_contains($css, 'aspect-ratio: 3 / 2;'),
    'Law 01 target Latest media must use the approved 3:2 editorial image ratio.'
);
$must(
    str_contains($latest, "homepage_latest_posts( (array) setting( 'homepage_knowledge_terms', [] ), 3, homepage_ledger_ids() )"),
    'Law 01 Latest must remain bounded to three WordPress-native Posts.'
);

echo "PASS: Law 01 About/Profile + Latest visual parity contract\n";
