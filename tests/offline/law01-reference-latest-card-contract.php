<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$referenceCss = (string) file_get_contents($root . '/assets/css/components/homepage-law-01-reference.css');
$latest = (string) file_get_contents($root . '/template-parts/homepage/law-01/latest.php');

$must = static function (bool $condition, string $message): void {
    if (! $condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
};

$must(
    str_contains($latest, 'homepage_latest_posts') &&
    str_contains($latest, 'aznet-theme-law01-grid--articles') &&
    str_contains($latest, 'aznet-theme-law01-article-card'),
    'Law 01 Latest must continue rendering WordPress-native posts through the existing Theme presentation.'
);

$must(
    1 === preg_match(
        '/\.aznet-theme-homepage--law-01-burgundy-gold \.aznet-theme-law01-grid--articles \.aznet-theme-law01-article-card:first-child h3\s*\{[^}]*font-size:\s*var\(--law01-type-article-title\);[^}]*line-height:\s*1\.35;/s',
        $referenceCss
    ),
    'Law 01 reference Latest must neutralize the inherited featured-card H2 scale so all three demo cards share one title hierarchy.'
);

echo "PASS: Law 01 reference Latest card title hierarchy contract\n";
