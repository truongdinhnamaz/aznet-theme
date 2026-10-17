<?php
/** D-042 peer pilot coverage whenever shared Page/Curtain CSS changes. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$targets = [
    '.github/workflows/curtain01-c5-pilot-preview.yml',
    '.github/workflows/homepage-law-01-browser.yml',
    '.github/workflows/industrial01-pilot-runtime.yml',
];
$changedSources = [
    'assets/css/components/page.css',
    'assets/css/presets/curtain-01.css',
];

foreach ($targets as $workflow) {
    $contents = file_get_contents($root . '/' . $workflow);
    if (! is_string($contents)) {
        fwrite(STDERR, "FAIL: peer pilot workflow unavailable: {$workflow}\n");
        exit(1);
    }
    $header = explode('permissions:', $contents, 2)[0];
    if (! str_contains($header, 'pull_request:') || ! str_contains($header, 'paths:')) {
        fwrite(STDERR, "FAIL: peer pilot workflow PR path filter missing: {$workflow}\n");
        exit(2);
    }
    foreach ($changedSources as $source) {
        if (! str_contains($header, "- '{$source}'")) {
            fwrite(STDERR, "FAIL: D-042 peer pilot {$workflow} does not trigger on {$source}\n");
            exit(3);
        }
    }
}
echo "PASS: D-042 Curtain, Law and Industrial L4 workflows trigger on shared Page/Curtain CSS changes\n";
