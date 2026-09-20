<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profilePath = $root . '/template-parts/homepage/law-01/profile.php';

function law01_demo_missing_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

if (!is_file($profilePath)) {
    law01_demo_missing_fail("required production file missing: {$profilePath}");
}

$profile = (string) file_get_contents($profilePath);

$profileRequired = [
    'aznet-theme-law01-profile__facts',
    'aznet-theme-law01-profile__fact-value',
    'homepage_services_page',
    'homepage_process_page',
    'homepage_faq_page',
];

foreach ($profileRequired as $needle) {
    if (!str_contains($profile, $needle)) {
        law01_demo_missing_fail("Law 01 profile missing source-safe demo completion marker: {$needle}");
    }
}

$forbiddenProfileClaims = [
    '10+',
    '500+',
    '95%',
];

foreach ($forbiddenProfileClaims as $needle) {
    if (str_contains($profile, $needle)) {
        law01_demo_missing_fail("Law 01 profile must not hardcode unverifiable demo claim: {$needle}");
    }
}


echo "PASS: Law 01 source-safe reference facts contract\n";
