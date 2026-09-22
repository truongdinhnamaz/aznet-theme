<?php
/** Curtain 01 cinematic Hero motion contract. */
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$assetsPath = $root . '/inc/theme/assets.php';
$heroPath   = $root . '/template-parts/homepage/curtain-01/hero.php';
$cssPath    = $root . '/assets/css/components/homepage-curtain-01.css';
$jsPath     = $root . '/assets/js/homepage-curtain-01.js';

$fail = static function (string $message): void {
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
};

foreach ([$assetsPath, $heroPath, $cssPath] as $path) {
    if (!is_file($path)) {
        $fail('missing required Curtain 01 artifact: ' . basename($path));
    }
}

if (!is_file($jsPath)) {
    $fail('Curtain 01 cinematic Hero JS is missing.');
}

$assets = (string) file_get_contents($assetsPath);
$hero   = (string) file_get_contents($heroPath);
$css    = (string) file_get_contents($cssPath);
$js     = (string) file_get_contents($jsPath);

foreach ([
    "aznet-theme-homepage-curtain-01-motion",
    "/assets/js/homepage-curtain-01.js",
    "enqueue_homepage_curtain01_asset",
] as $needle) {
    if (!str_contains($assets, $needle)) {
        $fail('Curtain 01 motion asset is not surface-scoped: ' . $needle);
    }
}

if (!str_contains($hero, 'data-aznet-curtain-cinematic')) {
    $fail('Curtain 01 Hero lacks an explicit cinematic enhancement hook.');
}

foreach ([
    "data-aznet-curtain-cinematic",
    "prefers-reduced-motion: reduce",
    "pointermove",
    "requestAnimationFrame",
    "--aznet-curtain-shift-x",
    "--aznet-curtain-shift-y",
    "--aznet-curtain-scroll-shift",
    "is-cinematic-ready",
] as $needle) {
    if (!str_contains($js, $needle)) {
        $fail('Curtain 01 cinematic JS missing behavior marker: ' . $needle);
    }
}

foreach ([
    ".aznet-theme-curtain01-hero[data-aznet-curtain-cinematic]",
    ".is-cinematic-ready",
    "::after",
    "@keyframes aznet-curtain01-hero-reveal",
    "@keyframes aznet-curtain01-hero-breathe",
    "@media (prefers-reduced-motion: reduce)",
] as $needle) {
    if (!str_contains($css, $needle)) {
        $fail('Curtain 01 cinematic CSS missing presentation marker: ' . $needle);
    }
}

foreach ([
    "setInterval(",
    "setTimeout(",
    "gsap",
    "swiper",
    "slick",
] as $forbidden) {
    if (stripos($js, $forbidden) !== false) {
        $fail('Curtain 01 cinematic motion must remain lightweight: ' . $forbidden);
    }
}

echo "PASS: Curtain 01 cinematic Hero is scoped, lightweight and reduced-motion safe\n";
