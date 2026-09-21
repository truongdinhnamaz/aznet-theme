<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/header/site-header.php');
$css = (string) file_get_contents($root . '/assets/css/components/header-law-01.css');

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

$topbarCall = "get_template_part( 'template-parts/header/law01-topbar', null, \$context )";
$topbarPos = strpos($template, $topbarCall);
$headerPos = strpos($template, '<header class=');

if (false === $topbarPos || false === $headerPos) {
    $fail('Header composer must contain both the Law 01 topbar and the main Header landmark.');
}

if ($topbarPos > $headerPos) {
    $fail('Law 01 topbar must be outside and before the sticky main Header so it scrolls away naturally.');
}

foreach ([
    '.aznet-theme-law01-topbar {',
    '.aznet-theme-law01-topbar__inner {',
] as $selector) {
    if (! str_contains($css, $selector)) {
        $fail("Law 01 topbar styling must remain valid outside the sticky Header: {$selector}");
    }
}

if (str_contains($css, '.aznet-theme-site-header--law01-burgundy-gold .aznet-theme-law01-topbar')) {
    $fail('Law 01 topbar CSS must not depend on being a descendant of the sticky Header.');
}

echo "PASS: Law 01 topbar scrolls away while the main Header retains sticky ownership.\n";
