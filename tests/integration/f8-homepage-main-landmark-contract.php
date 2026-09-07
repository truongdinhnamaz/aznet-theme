<?php

declare(strict_types=1);

$themeRoot = dirname(__DIR__, 2);
$providerRoot = rtrim((string) getenv('CONVERTFLOW_ROOT'), '/');

if ('' === $providerRoot) {
    fwrite(STDERR, "BLOCKED: set CONVERTFLOW_ROOT to an extracted actual ConvertFlow package root.\n");
    exit(2);
}

$themePath = $themeRoot . '/front-page.php';
$providerPath = $providerRoot . '/src/Frontend/Homepage/HomepageJourneyRenderer.php';

if (! is_file($themePath) || ! is_file($providerPath)) {
    fwrite(STDERR, "BLOCKED: Theme front-page.php or actual provider HomepageJourneyRenderer.php is unavailable.\n");
    exit(2);
}

$theme = (string) file_get_contents($themePath);
$provider = (string) file_get_contents($providerPath);

$themeOwnsMain = str_contains($theme, '<main id="main"');
$providerOwnsNestedMain = str_contains($provider, '<main class="choiceguide-homepage-journey"');

if ($themeOwnsMain && $providerOwnsNestedMain) {
    fwrite(STDERR, "FAIL: integrated Homepage produces nested main landmarks: Theme owns main#main and actual ConvertFlow renderer also emits <main>.\n");
    exit(1);
}

echo "PASS: integrated Homepage has a single main-landmark owner.\n";
