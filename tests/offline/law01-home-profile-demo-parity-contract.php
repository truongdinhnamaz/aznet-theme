<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profile = (string) file_get_contents($root . '/template-parts/homepage/law-01/profile.php');
$referenceCss = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-reference.css');

$must = static function (bool $condition, string $message): void {
    assert($condition, $message);
};

foreach ([
    "setting( 'homepage_about_page', 0 )",
    'aznet-theme-law01-profile__about-fallback',
    'aznet-theme-law01-profile__stats',
    'aznet-theme-law01-profile__team-heading',
    'Xem thêm về đội ngũ',
] as $needle) {
    $must(str_contains($profile, $needle), "Profile demo parity missing {$needle}");
}

foreach ([
    '.aznet-theme-law01-profile__about-grid',
    'grid-template-columns: minmax(0, 1fr);',
    '.aznet-theme-law01-profile__stats',
    'grid-template-columns: repeat(3, minmax(0, 1fr));',
    '.aznet-theme-law01-profile__members',
    'grid-template-columns: repeat(4, minmax(0, 1fr));',
    '.aznet-theme-law01-profile__team-heading',
] as $needle) {
    $must(str_contains($referenceCss, $needle), "Profile demo CSS missing {$needle}");
}

$must(
    str_contains($referenceCss, '@media (max-width: 960px)') &&
    str_contains($referenceCss, '@media (max-width: 640px)'),
    'Profile demo parity must preserve tablet/mobile collapse rules.'
);

echo "PASS: Law 01 Homepage Profile demo parity contract\n";
