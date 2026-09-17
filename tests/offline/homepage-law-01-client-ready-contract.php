<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$files = [
    'css' => $root . '/assets/css/components/homepage-law-01-variants.css',
    'composer' => $root . '/inc/theme/homepage-composer.php',
    'hero' => $root . '/template-parts/homepage/law-01/hero.php',
    'services' => $root . '/template-parts/homepage/law-01/services.php',
    'profile' => $root . '/template-parts/homepage/law-01/profile.php',
    'latest' => $root . '/template-parts/homepage/law-01/latest.php',
];

foreach ($files as $label => $path) {
    assert(is_file($path), "Client-ready Law 01 {$label} file missing: {$path}");
}

$css = file_get_contents($files['css']);
$composer = file_get_contents($files['composer']);
$hero = file_get_contents($files['hero']);
$services = file_get_contents($files['services']);
$profile = file_get_contents($files['profile']);
$latest = file_get_contents($files['latest']);

foreach ([
    '--law01-client-burgundy:',
    '--law01-client-gold:',
    '.aznet-theme-law01-hero__trust',
    'grid-template-columns: repeat(6, minmax(0, 1fr));',
    '.aznet-theme-law01-profile__grid',
    '.aznet-theme-law01-grid--articles',
    'grid-column: auto;',
    '.aznet-theme-site-header',
    '.aznet-theme-site-footer',
    '@media (max-width: 960px)',
    '@media (max-width: 640px)',
    '@media (prefers-reduced-motion: reduce)',
] as $needle) {
    assert(str_contains($css, $needle), "Client-ready Law 01 CSS contract missing: {$needle}");
}

assert(str_contains($composer, "[ 'hero', 'services', 'profile' ]"), 'Composer must render the combined client-ready profile band.');
assert(! str_contains($composer, "[ 'hero', 'services', 'about', 'team' ]"), 'Composer must not render duplicate legacy About/Team sections.');

foreach ([
    'aznet-theme-law01-button--secondary',
    'aznet-theme-law01-hero__trust',
    'Tư vấn rõ ràng',
    'Giải pháp thực tiễn',
    'Bảo mật thông tin',
    'Đồng hành tận tâm',
] as $needle) {
    assert(str_contains($hero, $needle), "Client-ready Law 01 hero contract missing: {$needle}");
}

assert(str_contains($services, 'aznet-theme-law01-card__badge'), 'Service cards must expose a presentation-only badge hook.');
assert(str_contains($profile, 'aznet-theme-law01-profile__grid'), 'Profile band must combine About and Team presentation.');
assert(str_contains($latest, 'aznet-theme-law01-article-card__media'), 'Latest cards must render featured media when available.');
assert(str_contains($latest, 'get_the_category'), 'Latest cards must expose WordPress-owned taxonomy labels.');

foreach (['500+', '95%', '98%', '1.000+', '1000+', 'Nguyễn Văn A', 'Nguyễn Văn Minh', 'Trần Thị Lan', 'Phạm Anh Tuấn', 'Lê Thị Hoa'] as $forbidden) {
    foreach ([$hero, $services, $profile, $latest, $css] as $surface) {
        assert(! str_contains($surface, $forbidden), "Fabricated trust/team content must not be introduced: {$forbidden}");
    }
}

echo "PASS: Law 01 client-ready redesign contract\n";
