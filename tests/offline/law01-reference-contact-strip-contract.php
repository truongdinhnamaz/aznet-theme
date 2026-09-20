<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$css = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-reference.css');
$hero = (string) file_get_contents($root . '/template-parts/homepage/law-01/hero.php');

$must = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$must(
    str_contains($hero, 'aznet-theme-law01-hero__contact-nav') &&
    str_contains($hero, "'theme_location' => 'header-utility'"),
    'Law 01 Hero contact strip must continue consuming the existing WordPress-owned header-utility menu.'
);

$must(
    1 === preg_match(
        '/\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-hero__contact-list\s*\{[^}]*display:\s*grid;[^}]*grid-template-columns:\s*repeat\(3,\s*minmax\(0,\s*1fr\)\);/s',
        $css
    ),
    'Law 01 Hero contact strip must use the approved three-column desktop geometry from the supplied demo.'
);

$must(
    1 === preg_match(
        '/\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-hero__contact-list a\s*\{[^}]*display:\s*grid;[^}]*grid-template-columns:\s*2rem minmax\(0,\s*1fr\);[^}]*min-height:\s*3\.5rem;/s',
        $css
    ),
    'Law 01 Hero contact items must reserve a compact icon rail without changing menu-owned text.'
);

$must(
    str_contains($css, '.aznet-theme-law01-hero__contact-list a::before') &&
    str_contains($css, 'a[href^="tel:"]::before') &&
    str_contains($css, 'a[href^="mailto:"]::before'),
    'Law 01 Hero contact strip must add presentation-only location/phone/email icon treatment from semantic link schemes.'
);

$must(
    1 === preg_match(
        '/@media \(max-width:\s*640px\)[\s\S]*\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-hero__contact-list\s*\{[^}]*grid-template-columns:\s*1fr;/s',
        $css
    ),
    'Law 01 Hero contact strip must stack safely on narrow mobile.'
);

echo "PASS: Law 01 reference Hero contact-strip presentation contract\n";
