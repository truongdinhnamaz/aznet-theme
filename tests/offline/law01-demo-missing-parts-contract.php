<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$profilePath = $root . '/template-parts/homepage/law-01/profile.php';
$footerPath = $root . '/template-parts/footer/site-footer.php';

function law01_demo_missing_fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

foreach ([$profilePath, $footerPath] as $path) {
    if (!is_file($path)) {
        law01_demo_missing_fail("required production file missing: {$path}");
    }
}

$profile = (string) file_get_contents($profilePath);
$footer = (string) file_get_contents($footerPath);

$profileRequired = [
    'aznet-theme-law01-profile__facts',
    'aznet-theme-law01-profile__fact-value',
    'homepage_services_page',
    'homepage_process_page',
    'homepage_faq_page',
    'aznet-theme-law01-profile__team-empty',
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

$footerRequired = [
    'aznet-theme-site-footer__connect-fallback',
    "homepage_contact_page",
    "homepage_services_page",
];

foreach ($footerRequired as $needle) {
    if (!str_contains($footer, $needle)) {
        law01_demo_missing_fail("Law 01 footer missing source-backed fallback column marker: {$needle}");
    }
}

echo "PASS: Law 01 demo missing-parts source-safe contract\n";
