<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$template = (string) file_get_contents($root . '/template-parts/content/page.php');

if (! str_contains($template, "$is_service_detail && '' !== $excerpt")) {
    fwrite(STDERR, "FAIL: generic Page header still renders Page excerpt as a visible lead\n");
    exit(1);
}

if (! str_contains($template, 'aznet-theme-page__content-section') || ! str_contains($template, 'the_content();')) {
    fwrite(STDERR, "FAIL: generic Page must continue to render authored WordPress content normally\n");
    exit(1);
}

echo "PASS: generic Pages render title + authored WordPress content without an injected excerpt lead\n";
