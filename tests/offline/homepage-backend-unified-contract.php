<?php
/** Unified Homepage backend presentation contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$admin = file_get_contents($root . '/inc/admin/homepage.php');
$surface = file_get_contents($root . '/inc/theme/homepage-surface-map.php');
$composer = file_get_contents($root . '/inc/theme/homepage-composer.php');
$css = file_get_contents($root . '/assets/css/admin/control-center.css');
$teamDirectory = file_get_contents($root . '/inc/theme/team-directory.php');
$profile = file_get_contents($root . '/template-parts/homepage/law-01/profile.php');

foreach ([
    [$admin, 'function render_homepage_map(', 'Homepage Map renderer missing'],
    [$admin, 'Trang chủ đang hiển thị', 'Homepage Map heading missing'],
    [$admin, 'homepage_effective_surface_map(', 'admin is not using effective surface model'],
    [$admin, 'Nguồn & cài đặt nâng cao', 'advanced source disclosure missing'],
    [$admin, 'Xem trên trang chủ', 'frontend deep-link action missing'],
    [$surface, 'function homepage_effective_surface_map(', 'shared effective surface model missing'],
    [$surface, 'function homepage_effective_source_key(', 'effective source-key compatibility helper missing'],
    [$surface, 'function homepage_effective_source_value(', 'effective source-value compatibility helper missing'],
    [$surface, "'profile'", 'composite profile surface missing'],
    [$composer, 'homepage_effective_surface_map(', 'frontend composer not using shared model'],
    [$css, '/* Homepage Map shared-model primary authoring */', 'Homepage Map CSS missing'],
] as [$haystack, $needle, $message]) {
    if (! is_string($haystack) || ! str_contains($haystack, $needle)) {
        fwrite(STDERR, "FAIL: {$message}.\n");
        exit(1);
    }
}

$anchors = [
    'hero.php'      => 'data-aznet-homepage-surface="hero"',
    'services.php'  => 'data-aznet-homepage-surface="services"',
    'profile.php'   => 'data-aznet-homepage-surface="profile"',
    'latest.php'    => 'data-aznet-homepage-surface="latest"',
    'topics.php'    => 'data-aznet-homepage-surface="topics"',
    'final-cta.php' => 'data-aznet-homepage-surface="final-cta"',
];
if (! str_contains((string) $teamDirectory, "homepage_effective_source_value( 'law-01', 'team' )")) {
    fwrite(STDERR, "FAIL: Team directory is not consuming effective source.\n");
    exit(1);
}
if (! str_contains((string) $profile, "homepage_effective_source_value( 'law-01', 'team'")) {
    fwrite(STDERR, "FAIL: Profile is not consuming effective Team source.\n");
    exit(1);
}

foreach ($anchors as $file => $needle) {
    $source = file_get_contents($root . '/template-parts/homepage/law-01/' . $file);
    if (! is_string($source) || ! str_contains($source, $needle)) {
        fwrite(STDERR, "FAIL: frontend surface anchor missing in {$file}.\n");
        exit(1);
    }
}

echo "PASS: unified Template Library + Homepage Map backend contract\n";
