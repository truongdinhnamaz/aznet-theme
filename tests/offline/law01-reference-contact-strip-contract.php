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
        '/\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-hero__contact-list a\s*\{[^}]*display:\s*inline-flex;[^}]*width:\s*2\.75rem;[^}]*height:\s*2\.75rem;[^}]*font-size:\s*0;/s',
        $css
    ),
    'Law 01 Hero contact items must render as compact icon-only controls while preserving menu-owned link text in the DOM.'
);

$must(
    str_contains($css, '.aznet-theme-law01-hero__contact-list a::before') &&
    str_contains($css, 'a[href^="tel:"]::before') &&
    str_contains($css, 'a[href^="mailto:"]::before') &&
    str_contains($css, 'a[href*="facebook.com"]::before'),
    'Law 01 Hero contact strip must add presentation-only link-type icons for location/default, phone, email and Facebook/Page sources.'
);

$must(
    1 === preg_match('/a\[href\^="tel:"\]::before\s*\{[^}]*content:\s*"";/s', $css) &&
    1 === preg_match('/a\[href\^="mailto:"\]::before\s*\{[^}]*content:\s*"";/s', $css),
    'Law 01 semantic contact icons must stay decorative and suppress inherited unicode glyph content.'
);

$must(
    1 === preg_match(
        '/@media \(max-width:\s*640px\)[\s\S]*\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-hero__contact-list\s*\{[^}]*display:\s*flex;[^}]*flex-wrap:\s*wrap;[^}]*gap:\s*\.55rem;/s',
        $css
    ),
    'Law 01 Hero icon-only contact controls must wrap safely on narrow mobile.'
);

echo "PASS: Law 01 reference Hero contact-strip presentation contract\n";
